<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Support\FundAllocationSheet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FundAllocationController extends Controller
{
    /**
     * List the scholarships that students have passed, with each one's fund and equal share.
     *
     * The passed count comes as a subquery of the one scholarship query, so
     * the cards cost no query each, and so does `unfunded_total`, how many
     * of them across every page still have no total funds, for the banner.
     * Twelve to a page fills whole rows of the two-, three- and four-column grid.
     */
    public function index(): View
    {
        $withPassedStudents = fn (Builder $query): Builder => $query->whereHas('applications', fn (Builder $query) => $query->approved());

        $scholarships = Scholarship::query()
            ->select('scholarships.*')
            ->selectSub($withPassedStudents(Scholarship::query())->whereNull('total_funds')->toBase()->selectRaw('count(*)'), 'unfunded_total')
            ->tap($withPassedStudents)
            ->withCount(['applications as passed_count' => fn (Builder $query) => $query->approved()])
            ->latest()
            ->paginate(12);

        return view('admin.fund-allocation.index', [
            'scholarships' => $scholarships,
            'unfundedCount' => (int) ($scholarships->first()?->unfunded_total ?? 0),
        ]);
    }

    /**
     * Display the scholarship's fund allocation: every student who passed and their equal share.
     */
    public function show(Scholarship $scholarship): View
    {
        $passedApplications = $this->passedApplications($scholarship);

        return view('admin.fund-allocation.show', [
            'scholarship' => $scholarship,
            'passedApplications' => $passedApplications,
            'share' => $scholarship->fundShareInCentavos($passedApplications->count()),
        ]);
    }

    /**
     * Download the scholarship's fund allocation as an Excel sheet for students to sign as they receive their share.
     *
     * The workbook is built before the response starts, so a failure is an
     * error page rather than a truncated file.
     */
    public function report(Request $request, Scholarship $scholarship): StreamedResponse
    {
        $generatedAt = now(config('app.display_timezone'));
        $workbook = (new FundAllocationSheet($scholarship, $this->passedApplications($scholarship), $request->user(), $generatedAt))->toXlsx();

        $filename = 'fund-allocation-'.Str::slug(Str::limit($scholarship->title, 60, '')).'-'.$generatedAt->format('Y-m-d').'.xlsx';

        return response()->streamDownload(function () use ($workbook): void {
            echo $workbook;
        }, $filename, ['Content-Type' => FundAllocationSheet::CONTENT_TYPE]);
    }

    /**
     * The scholarship's passed applications with their students, by surname.
     *
     * @return Collection<int, ScholarshipApplication>
     */
    private function passedApplications(Scholarship $scholarship): Collection
    {
        return $scholarship->applications()
            ->approved()
            ->orderedByStudentName()
            ->with('user')
            ->get();
    }
}
