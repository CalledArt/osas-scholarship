{{--
    The applications report: every applicant to the scholarship, by surname,
    with their decision and a checklist of the required documents, each one
    marked submitted (with the file's name) or missing. A document added after
    a student applied shows as missing for them. The frame and the limits of
    dompdf it works within are in components/pdf-report.
--}}
@php
    $statusCounts = $applications->countBy(fn ($application) => $application->status->value);
    $requiredCount = $scholarship->requiredDocuments->count();
    $completeCount = $applications->filter(fn ($application) => $application->documents->count() >= $requiredCount)->count();
@endphp

<x-pdf-report :title="__('Scholarship Applications Report')" :scholarship="$scholarship" :prepared-by="$preparedBy" :generated-at="$generatedAt">
    {{-- Fixed widths, since dompdf cannot break a long filename and would squeeze every other column to fit it instead. --}}
    <x-slot:styles>
        .records { table-layout: fixed; }
        .document { padding: 1px 0; }
        .filename { color: #5A6472; }
    </x-slot:styles>

    <table class="summary">
        <tr>
            <td>
                <div class="caption">{{ __('Applicants') }}</div>
                <div class="figure">{{ $applications->count() }}</div>
                <div class="note">{{ __(':slots slots', ['slots' => $scholarship->slots]) }}</div>
            </td>
            <td>
                <div class="caption">{{ __('Approved') }}</div>
                <div class="figure">{{ $statusCounts->get(\App\Enums\ApplicationStatus::Approved->value, 0) }}</div>
            </td>
            <td>
                <div class="caption">{{ __('Pending') }}</div>
                <div class="figure">{{ $statusCounts->get(\App\Enums\ApplicationStatus::Pending->value, 0) }}</div>
            </td>
            <td>
                <div class="caption">{{ __('Rejected') }}</div>
                <div class="figure">{{ $statusCounts->get(\App\Enums\ApplicationStatus::Rejected->value, 0) }}</div>
                @if ($statusCounts->has(\App\Enums\ApplicationStatus::Voided->value))
                    <div class="note">{{ __(':count voided (leave of absence)', ['count' => $statusCounts->get(\App\Enums\ApplicationStatus::Voided->value)]) }}</div>
                @endif
            </td>
            <td>
                <div class="caption">{{ __('Required documents') }}</div>
                <div class="figure">{{ $requiredCount }}</div>
                <div class="note">{{ $scholarship->deadline ? __('Deadline :date', ['date' => $scholarship->deadline->format('M j, Y')]) : __('No deadline') }}</div>
            </td>
            <td>
                <div class="caption">{{ __('Submitted every document') }}</div>
                <div class="figure">{{ $completeCount }}</div>
                <div class="note">{{ __('of :count applicants', ['count' => $applications->count()]) }}</div>
            </td>
        </tr>
    </table>

    <table class="records">
        <thead>
            <tr>
                <th style="width: 3%;">{{ __('No.') }}</th>
                <th style="width: 9%;">{{ __('Student no.') }}</th>
                <th style="width: 14%;">{{ __('Name') }}</th>
                <th style="width: 16%;">{{ __('Course') }}</th>
                <th style="width: 4%;">{{ __('Year') }}</th>
                <th style="width: 8%;">{{ __('Applied') }}</th>
                <th style="width: 7%;">{{ __('Status') }}</th>
                <th style="width: 39%;">{{ __('Documents submitted') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($applications as $application)
                @php $submitted = $application->documents->keyBy('scholarship_required_document_id'); @endphp
                <tr>
                    <td class="muted">{{ $loop->iteration }}</td>
                    <td class="nowrap">{{ $application->user->student_number ?? '—' }}</td>
                    <td>{{ mb_strtoupper($application->user->surname_first_name) }}</td>
                    <td>{{ $application->user->course ?? '—' }}</td>
                    <td>{{ $application->user->year_level ?? '—' }}</td>
                    <td class="nowrap">{{ ($application->submitted_at ?? $application->created_at)->timezone(config('app.display_timezone'))->format('M j, Y') }}</td>
                    <td>
                        <span @class([
                            'mark-green' => $application->status === \App\Enums\ApplicationStatus::Approved,
                            'mark-gold' => $application->status === \App\Enums\ApplicationStatus::Pending,
                            'mark-red' => $application->status === \App\Enums\ApplicationStatus::Rejected,
                        ])>{{ $application->status->label() }}</span>
                        @if ($application->passed_while_holding_active)
                            <div class="muted">{{ __('Passed; already held an active scholarship') }}</div>
                        @elseif ($application->status === \App\Enums\ApplicationStatus::Voided)
                            <div class="muted">{{ __('Student went on leave of absence') }}</div>
                        @endif
                    </td>
                    <td>
                        @forelse ($scholarship->requiredDocuments as $requiredDocument)
                            @php $document = $submitted->get($requiredDocument->id); @endphp
                            <div class="document">
                                @if ($document)
                                    <span class="mark-green">✓</span> {{ $requiredDocument->name }}
                                    <span class="filename">· {{ mb_strlen($document->original_name) > 48 ? mb_substr($document->original_name, 0, 34).'…'.mb_substr($document->original_name, -12) : $document->original_name }}</span>
                                @else
                                    <span class="mark-red">✗</span> {{ $requiredDocument->name }}
                                    <span class="mark-red">· {{ __('Not submitted') }}</span>
                                @endif
                            </div>
                        @empty
                            <span class="muted">{{ __('No documents required') }}</span>
                        @endforelse
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="muted">{{ __('No one has applied to this scholarship yet.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</x-pdf-report>
