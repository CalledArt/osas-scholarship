<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ApplyTestStudents;
use App\Enums\ScholarshipStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreScholarshipRequest;
use App\Http\Requests\Admin\UpdateScholarshipRequest;
use App\Models\Scholarship;
use App\Models\User;
use App\Notifications\ScholarshipPosted;
use App\Support\CvsuPrograms;
use App\Support\ScholarshipPresets;
use App\Support\StudentNotifier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ScholarshipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', Scholarship::class);

        $scholarships = Scholarship::withCount([
            'applications',
            'applications as held_slots_count' => fn (Builder $query) => $query->holdingSlot(),
        ])
            ->latest()
            // Twelve, so the card grid closes square at two, three or four across.
            ->paginate(12);

        return view('admin.scholarships.index', [
            'scholarships' => $scholarships,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        Gate::authorize('create', Scholarship::class);

        return view('admin.scholarships.create', [
            'presets' => ScholarshipPresets::ALL,
            'openPresets' => Scholarship::query()
                ->where('status', ScholarshipStatus::Open)
                ->whereNotNull('preset')
                ->distinct()
                ->pluck('preset')
                ->all(),
        ]);
    }

    /**
     * Store a newly created resource in storage, then notify every student who can apply in their bell.
     *
     * Only a scholarship posted from the DOST preset is limited to STEM
     * courses, and it is announced only to students in those courses.
     * Posting sends no email. A preset may have only one posting open for
     * applications at a time.
     */
    public function store(StoreScholarshipRequest $request, StudentNotifier $notifier, ApplyTestStudents $applyTestStudents): RedirectResponse
    {
        $preset = $request->validated('preset');
        $post = fn (): Scholarship => $request->user()->postedScholarships()->create([
            ...$request->safe()->only(['title', 'description', 'type', 'slots', 'deadline']),
            'preset' => $preset,
            'stem_courses_only' => ScholarshipPresets::limitsToStemCourses($preset),
        ]);

        // Two posts of one preset sent at the same moment both pass validation,
        // so the check is made again while holding the preset's lock, and only
        // the first one lands.
        $scholarship = $preset === null ? $post() : Cache::lock("scholarships:post-preset:{$preset}", 10)->block(5, function () use ($preset, $post): Scholarship {
            if (Scholarship::openFromPreset($preset)->exists()) {
                throw ValidationException::withMessages(['preset' => StoreScholarshipRequest::presetAlreadyOpenMessage($preset)]);
            }

            return $post();
        });

        foreach ($request->validated('required_documents', []) as $index => $document) {
            $requiredDocument = $scholarship->requiredDocuments()->make(Arr::only($document, ['name', 'description']));

            if ($request->hasFile("required_documents.{$index}.sample")) {
                $requiredDocument->attachSample($request->file("required_documents.{$index}.sample"));
            } else {
                $requiredDocument->save();
            }
        }

        $students = User::where('role', UserRole::Student)
            ->when($scholarship->stem_courses_only, fn (Builder $query) => $query->whereIn('course', CvsuPrograms::STEM))
            ->get(['id']);
        $notifiedCount = $notifier->send($students, new ScholarshipPosted($scholarship));

        // On a local machine only, the hardcoded test students apply at once,
        // so the pass list and Fund Allocation can be tried straight away.
        // Never on the live site, where they would take real students' slots.
        $testApplicantCount = app()->isLocal() ? $applyTestStudents->handle($scholarship) : 0;

        $status = $notifiedCount > 0
            ? "Scholarship posted and {$notifiedCount} ".Str::plural('student', $notifiedCount).' notified.'
            : 'Scholarship posted.';

        if ($testApplicantCount > 0) {
            $status .= " {$testApplicantCount} test ".Str::plural('student', $testApplicantCount).' applied (local machine only).';
        }

        return redirect()->route('admin.scholarships.show', $scholarship)->with('status', $status);
    }

    /**
     * Display the specified resource.
     */
    public function show(Scholarship $scholarship): View
    {
        Gate::authorize('view', $scholarship);

        $scholarship->loadCount([
            'applications as held_slots_count' => fn (Builder $query) => $query->holdingSlot(),
        ]);

        $scholarship->load([
            'requiredDocuments',
            'applications.user',
            'applications.documents.requiredDocument',
        ]);

        return view('admin.scholarships.show', [
            'scholarship' => $scholarship,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Scholarship $scholarship): View
    {
        Gate::authorize('update', $scholarship);

        $scholarship->loadCount('applications');

        return view('admin.scholarships.edit', [
            'scholarship' => $scholarship,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateScholarshipRequest $request, Scholarship $scholarship): RedirectResponse
    {
        $scholarship->update($request->validated());

        return redirect()->route('admin.scholarships.show', $scholarship)
            ->with('status', 'Scholarship updated.');
    }

    /**
     * Remove the specified resource from storage, archiving its fund allocation if it was completed.
     */
    public function destroy(Request $request, Scholarship $scholarship): RedirectResponse
    {
        Gate::authorize('delete', $scholarship);

        $scholarship->deleteWithStoredFiles($request->user());

        return redirect()->route('admin.scholarships.index')
            ->with('status', 'Scholarship deleted.');
    }
}
