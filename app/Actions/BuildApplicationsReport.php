<?php

namespace App\Actions;

use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\ScholarshipApplicationDocument;
use App\Models\User;
use App\Support\PrintedImage;
use App\Support\Qpdf;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Builds the applications report with every applicant's documents in it, a
 * step at a time, so a browser can show its progress.
 *
 * Nothing on Vercel runs a queue, and one request can neither run for long
 * nor answer with more than 4.5 MB, so the browser drives the build: each
 * step does about STEP_SECONDS of work and returns how far along it is. The
 * first step renders the list of applicants, each later step adds whole
 * applicants (a cover page, then each document on its own page), and the last
 * joins the parts with qpdf. The result is stored beside the scholarship and
 * opened from a temporary link. A report that would pass the storage upload
 * limit is split into parts at applicant boundaries.
 *
 * Progress lives in the cache (the database store in production, shared by
 * every instance), and a lock keeps two steps of one build from running at
 * once. Only the newest report of a scholarship is kept, since each holds
 * copies of students' documents; deleting the scholarship deletes it too.
 *
 * @phpstan-type Build array{id: string, scholarship_id: int, user_id: int, generated_at: string, directory: string, application_ids: array<int, int>, next: int, documents_total: int, documents_done: int, parts: array<int, string>, status: string, downloads: array<int, array{label: string, path: string}>, busy?: bool}
 */
class BuildApplicationsReport
{
    public const DISK = 'supabase';

    private const STEP_SECONDS = 8;

    private const LIFETIME_HOURS = 2;

    public function __construct(private Qpdf $qpdf) {}

    /**
     * Begin a new report for the scholarship, replacing any earlier one.
     *
     * @return Build
     */
    public function start(Scholarship $scholarship, User $admin): array
    {
        Storage::disk(self::DISK)->deleteDirectory($scholarship->reportDirectory());

        $id = (string) Str::uuid();
        $applicationIds = $scholarship->applications()->orderedByStudentName()->pluck('scholarship_applications.id')->all();

        $build = [
            'id' => $id,
            'scholarship_id' => $scholarship->id,
            'user_id' => $admin->id,
            'generated_at' => now(config('app.display_timezone'))->toIso8601String(),
            'directory' => "{$scholarship->reportDirectory()}/{$id}",
            'application_ids' => $applicationIds,
            'next' => 0,
            'documents_total' => ScholarshipApplicationDocument::query()->whereIn('scholarship_application_id', $applicationIds)->count(),
            'documents_done' => 0,
            'parts' => [],
            'status' => 'building',
            'downloads' => [],
        ];

        $this->save($build);

        return $build;
    }

    /**
     * @return Build|null
     */
    public function find(string $id): ?array
    {
        return Cache::get(self::key($id));
    }

    /**
     * Do the next stretch of work on the report.
     *
     * @param  Build  $build
     * @return Build
     */
    public function step(array $build): array
    {
        if ($build['status'] !== 'building') {
            return $build;
        }

        $lock = Cache::lock(self::key($build['id']).':lock', 300);

        if (! $lock->get()) {
            return [...$build, 'busy' => true];
        }

        $workspace = sys_get_temp_dir().'/applications-report-'.Str::random(16);
        File::ensureDirectoryExists($workspace);

        try {
            // A phone photo decoded for resampling takes about 50 MB.
            @ini_set('memory_limit', '512M');
            @set_time_limit(280);

            $build = $this->find($build['id']) ?? $build;
            $scholarship = Scholarship::with('requiredDocuments')->findOrFail($build['scholarship_id']);
            $started = microtime(true);

            if ($build['parts'] === []) {
                $build = $this->addApplicantList($build, $scholarship);
            } elseif ($build['next'] < count($build['application_ids'])) {
                do {
                    $build = $this->addApplicant($build, $scholarship, $workspace);
                } while ($build['next'] < count($build['application_ids']) && microtime(true) - $started < self::STEP_SECONDS);
            } else {
                $build = $this->assemble($build, $scholarship, $workspace);
            }
        } catch (Throwable $exception) {
            report($exception);
            $build['status'] = 'failed';
            $this->save($build);
        } finally {
            File::deleteDirectory($workspace);
            $lock->release();
        }

        return $build;
    }

    /**
     * How far along the report is, for the browser.
     *
     * @param  Build  $build
     * @return array{status: string, percent: int, message: string, downloads: array<int, array{label: string, url: string}>, retryAfter: int}
     */
    public function progress(array $build): array
    {
        $applicants = count($build['application_ids']);
        $total = 2 + $applicants + $build['documents_total'];
        $done = ($build['parts'] === [] ? 0 : 1) + $build['next'] + $build['documents_done'];

        $message = match (true) {
            $build['status'] === 'failed' => __('The report could not be built. Try again; if it keeps failing, tell the system administrator.'),
            $build['status'] === 'done' => __('The report is ready.'),
            $build['parts'] === [] => __('Listing the applicants…'),
            $build['next'] < $applicants && $build['documents_total'] > 0 => __('Adding documents: :done of :total', ['done' => $build['documents_done'], 'total' => $build['documents_total']]),
            $build['next'] < $applicants => __('Adding applicants: :done of :total', ['done' => $build['next'], 'total' => $applicants]),
            default => __('Putting the report together…'),
        };

        return [
            'status' => $build['status'],
            'percent' => $build['status'] === 'done' ? 100 : min(99, intdiv(100 * $done, $total)),
            'message' => $message,
            'downloads' => array_map(fn (array $download): array => [
                'label' => $download['label'],
                'url' => Storage::disk(self::DISK)->temporaryUrl($download['path'], now()->addMinutes(30)),
            ], $build['downloads']),
            'retryAfter' => ($build['busy'] ?? false) ? 1500 : 0,
        ];
    }

    /**
     * The first part: every applicant, by surname, with their decision and
     * which documents they sent.
     *
     * @param  Build  $build
     * @return Build
     */
    private function addApplicantList(array $build, Scholarship $scholarship): array
    {
        $applications = $scholarship->applications()
            ->orderedByStudentName()
            ->with(['user', 'documents'])
            ->get();

        $pdf = Pdf::loadView('admin.scholarships.report', [
            'scholarship' => $scholarship,
            'applications' => $applications,
            'preparedBy' => User::findOrFail($build['user_id']),
            'generatedAt' => $this->generatedAt($build),
            'withDocuments' => true,
        ])
            ->setPaper('a4', 'landscape')
            ->setOption('enable_font_subsetting', true)
            ->output();

        $path = "{$build['directory']}/part-00000.pdf";
        $this->store($path, $pdf);
        $build['parts'][] = $path;
        $this->save($build);

        return $build;
    }

    /**
     * The next applicant's part: a cover page listing their documents, then
     * each document on its own pages.
     *
     * @param  Build  $build
     * @return Build
     */
    private function addApplicant(array $build, Scholarship $scholarship, string $workspace): array
    {
        $number = $build['next'] + 1;
        $application = ScholarshipApplication::with(['user', 'documents'])->find($build['application_ids'][$build['next']]);

        // An application deleted since the build began is simply left out.
        if ($application !== null) {
            $directory = "{$workspace}/{$number}";
            File::ensureDirectoryExists($directory);

            [$pages, $entries] = $this->documentPages($application, $scholarship, $directory);

            $cover = $this->render("{$directory}/cover.pdf", 'admin.scholarships.report-applicant', [
                'scholarship' => $scholarship,
                'application' => $application,
                'number' => $number,
                'count' => count($build['application_ids']),
                'entries' => $entries,
                'generatedAt' => $this->generatedAt($build),
            ], 'a4');

            $this->qpdf->merge([$cover, ...$pages], "{$directory}/part.pdf");

            $path = sprintf('%s/part-%05d.pdf', $build['directory'], $number);
            $this->storeFile($path, "{$directory}/part.pdf");
            $build['parts'][] = $path;
            $build['documents_done'] += $application->documents->count();
        }

        $build['next']++;
        $this->save($build);

        return $build;
    }

    /**
     * One file of pages per document the applicant sent, in the order the
     * scholarship lists them, and a cover entry for every required document.
     *
     * @return array{0: array<int, string>, 1: array<int, array{name: string, file: ?string, note: string}>}
     */
    private function documentPages(ScholarshipApplication $application, Scholarship $scholarship, string $directory): array
    {
        $pages = [];
        $entries = [];
        $caption = "{$application->user->surname_first_name} · {$application->user->student_number}";

        foreach ($scholarship->requiredDocuments as $index => $required) {
            $document = $application->documents->firstWhere('scholarship_required_document_id', $required->id);

            if ($document === null) {
                $entries[] = ['name' => $required->name, 'file' => null, 'note' => __('Not submitted')];

                continue;
            }

            $bytes = Storage::disk($document->disk)->get($document->path);
            $label = "{$caption} — {$required->name} ({$document->original_name})";
            $file = "{$directory}/document-{$index}";

            if ($bytes === null) {
                $note = __('Missing from storage: open the application to check it.');
                $pages[] = $this->notice("{$file}.pdf", $label, $note);
            } elseif (str_contains(substr($bytes, 0, 1024), '%PDF')) {
                file_put_contents("{$file}-upload.pdf", $bytes);

                if ($this->qpdf->normalize("{$file}-upload.pdf", "{$file}.pdf")) {
                    $note = __('PDF, at its original page size');
                    $pages[] = "{$file}.pdf";
                } else {
                    $note = __('Could not be added: the PDF may be locked with a password or damaged. Open it from the scholarship page.');
                    $pages[] = $this->notice("{$file}.pdf", $label, $note);
                }
            } else {
                $image = PrintedImage::fromBytes($bytes);
                $jpeg = $image?->toJpeg($bytes);

                if ($image !== null && $jpeg !== null) {
                    $note = $image->sizeNote();
                    $placement = $image->placement();
                    $pages[] = $this->render("{$file}.pdf", 'admin.scholarships.report-page', [
                        'caption' => $placement['caption'] ? "{$label} — {$note}" : null,
                        'placement' => $placement,
                        'image' => 'data:image/jpeg;base64,'.base64_encode($jpeg),
                        'message' => null,
                    ], [0, 0, $placement['paper'][0], $placement['paper'][1]]);
                } else {
                    $note = __('Could not be added: the image could not be read. Open it from the scholarship page.');
                    $pages[] = $this->notice("{$file}.pdf", $label, $note);
                }
            }

            $entries[] = ['name' => $required->name, 'file' => $document->original_name, 'note' => $note];
        }

        return [$pages, $entries];
    }

    /**
     * Join the parts into the finished report, in parts of its own when it
     * would pass the storage upload limit.
     *
     * @param  Build  $build
     * @return Build
     */
    private function assemble(array $build, Scholarship $scholarship, string $workspace): array
    {
        $disk = Storage::disk(self::DISK);
        $limit = (int) (config('filesystems.disks.supabase.max_upload_bytes') * 0.9);
        $volumes = [[]];
        $size = 0;

        foreach ($build['parts'] as $path) {
            $local = "{$workspace}/".basename($path);
            $source = $disk->readStream($path) ?? throw new RuntimeException("Report part {$path} is missing.");
            $target = fopen($local, 'w');
            stream_copy_to_stream($source, $target);
            fclose($source);
            fclose($target);

            if ($volumes[array_key_last($volumes)] !== [] && $size + filesize($local) > $limit) {
                $volumes[] = [];
                $size = 0;
            }

            $volumes[array_key_last($volumes)][] = $local;
            $size += filesize($local);
        }

        $name = 'applications-report-'.Str::slug(Str::limit($scholarship->title, 60, '')).'-'.$this->generatedAt($build)->format('Y-m-d');
        $count = count($volumes);

        foreach ($volumes as $index => $volume) {
            $filename = $count === 1 ? "{$name}.pdf" : sprintf('%s-part-%d-of-%d.pdf', $name, $index + 1, $count);
            $this->qpdf->merge($volume, "{$workspace}/{$filename}");
            $this->storeFile("{$build['directory']}/{$filename}", "{$workspace}/{$filename}");

            $build['downloads'][] = [
                'label' => $count === 1 ? __('Open the report') : __('Open part :number of :count', ['number' => $index + 1, 'count' => $count]),
                'path' => "{$build['directory']}/{$filename}",
            ];
        }

        $disk->delete($build['parts']);
        $build['status'] = 'done';
        $this->save($build);

        return $build;
    }

    /**
     * A page standing in for a document that cannot be shown, saying why.
     */
    private function notice(string $file, string $label, string $message): string
    {
        return $this->render($file, 'admin.scholarships.report-page', [
            'caption' => $label,
            'placement' => null,
            'image' => null,
            'message' => $message,
        ], 'a4');
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  string|array{0: float, 1: float, 2: float, 3: float}  $paper
     */
    private function render(string $file, string $view, array $data, string|array $paper): string
    {
        file_put_contents($file, Pdf::loadView($view, $data)
            ->setPaper($paper)
            ->setOption('enable_font_subsetting', true)
            ->output());

        return $file;
    }

    /**
     * Store a file on the report disk, which reports failure rather than throwing.
     *
     * @param  string|resource  $contents
     */
    private function store(string $path, mixed $contents): void
    {
        if (! Storage::disk(self::DISK)->put($path, $contents)) {
            throw new RuntimeException("Could not store report file {$path}.");
        }
    }

    /**
     * Store a local file on the report disk, streamed rather than read whole.
     */
    private function storeFile(string $path, string $local): void
    {
        $stream = fopen($local, 'r');

        try {
            $this->store($path, $stream);
        } finally {
            fclose($stream);
        }
    }

    /**
     * @param  Build  $build
     */
    private function generatedAt(array $build): Carbon
    {
        return Carbon::parse($build['generated_at'])->timezone(config('app.display_timezone'));
    }

    /**
     * @param  Build  $build
     */
    private function save(array $build): void
    {
        unset($build['busy']);
        Cache::put(self::key($build['id']), $build, now()->addHours(self::LIFETIME_HOURS));
    }

    private static function key(string $id): string
    {
        return "applications-report-build:{$id}";
    }
}
