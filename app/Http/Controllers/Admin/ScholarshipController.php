<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreScholarshipRequest;
use App\Http\Requests\Admin\UpdateScholarshipRequest;
use App\Mail\ScholarshipPosted;
use App\Models\Scholarship;
use App\Models\User;
use App\Support\BccMailer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
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
            'applications as approved_applications_count' => fn (Builder $query) => $query->where('status', ApplicationStatus::Approved),
        ])
            ->latest()
            ->paginate(10);

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

        return view('admin.scholarships.create');
    }

    /**
     * Store a newly created resource in storage, then email every student about it.
     *
     * The scholarship stays posted even if the email fails; the admin is told
     * how many students it actually reached.
     */
    public function store(StoreScholarshipRequest $request, BccMailer $mailer): RedirectResponse
    {
        $scholarship = $request->user()->postedScholarships()->create(
            $request->safe()->only(['title', 'description', 'slots', 'deadline'])
        );

        foreach ($request->validated('required_documents', []) as $index => $document) {
            $requiredDocument = $scholarship->requiredDocuments()->make(Arr::only($document, ['name', 'description']));

            if ($request->hasFile("required_documents.{$index}.sample")) {
                $requiredDocument->attachSample($request->file("required_documents.{$index}.sample"));
            } else {
                $requiredDocument->save();
            }
        }

        $studentEmails = User::where('role', UserRole::Student)->pluck('email')->all();
        $studentCount = count($studentEmails);
        $emailed = $mailer->send($studentEmails, new ScholarshipPosted($scholarship));

        $redirect = redirect()->route('admin.scholarships.show', $scholarship);

        if ($emailed < $studentCount) {
            return $redirect->with('status', 'Scholarship posted.')
                ->with('error', "The announcement email reached only {$emailed} of {$studentCount} students. Check the mail settings and the application log.");
        }

        return $redirect->with('status', $studentCount > 0
            ? "Scholarship posted and emailed to {$studentCount} ".Str::plural('student', $studentCount).'.'
            : 'Scholarship posted.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Scholarship $scholarship): View
    {
        Gate::authorize('view', $scholarship);

        $scholarship->loadCount([
            'applications as approved_applications_count' => fn (Builder $query) => $query->where('status', ApplicationStatus::Approved),
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
     * Remove the specified resource from storage.
     */
    public function destroy(Scholarship $scholarship): RedirectResponse
    {
        Gate::authorize('delete', $scholarship);

        $scholarship->deleteWithStoredFiles();

        return redirect()->route('admin.scholarships.index')
            ->with('status', 'Scholarship deleted.');
    }
}
