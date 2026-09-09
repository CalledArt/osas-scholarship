<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreScholarshipRequest;
use App\Http\Requests\Admin\UpdateScholarshipRequest;
use App\Models\Scholarship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
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
     * Store a newly created resource in storage.
     */
    public function store(StoreScholarshipRequest $request): RedirectResponse
    {
        $scholarship = $request->user()->postedScholarships()->create(
            $request->safe()->only(['title', 'description', 'slots', 'deadline'])
        );

        $scholarship->requiredDocuments()->createMany(
            $request->validated('required_documents', [])
        );

        return redirect()->route('admin.scholarships.show', $scholarship)
            ->with('status', 'Scholarship posted.');
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

        $scholarship->delete();

        return redirect()->route('admin.scholarships.index')
            ->with('status', 'Scholarship deleted.');
    }
}
