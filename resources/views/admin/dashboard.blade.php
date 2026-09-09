<x-app-layout :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-5xl">
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Dashboard') }}</h1>

            {{-- The standing line: what the office would say out loud, in place of a wall of tiles. --}}
            <p class="mt-2 max-w-2xl text-lg leading-relaxed text-gray-600">
                @if ($pendingTotal > 0)
                    <span class="font-semibold tabular-nums text-gray-900">{{ $pendingTotal }}</span>
                    {{ trans_choice('application is waiting on you across|applications are waiting on you across', $pendingTotal) }}
                    <span class="font-semibold tabular-nums text-gray-900">{{ $needsDecisionCount }}</span>
                    {{ trans_choice('scholarship.|scholarships.', $needsDecisionCount) }}

                    @if ($nextToClose)
                        @php($daysToClose = $nextToClose->daysUntilDeadline())
                        <a href="{{ route('admin.scholarships.show', $nextToClose) }}"
                           class="font-semibold text-cvsu-green underline-offset-2 hover:text-cvsu-green-dark hover:underline focus:outline-none focus-visible:rounded-sm focus-visible:ring-2 focus-visible:ring-cvsu-green">{{ $nextToClose->title }}</a>
                        @if ($daysToClose < 0)
                            {{ __('is past its deadline.') }}
                        @elseif ($daysToClose === 0)
                            {{ __('closes today.') }}
                        @elseif ($daysToClose === 1)
                            {{ __('closes tomorrow.') }}
                        @else
                            {{ __('closes in :count days.', ['count' => $daysToClose]) }}
                        @endif
                    @endif
                @elseif ($register->isEmpty())
                    {{ __('No scholarships have been posted yet.') }}
                    <a href="{{ route('admin.scholarships.create') }}"
                       class="font-semibold text-cvsu-green underline-offset-2 hover:text-cvsu-green-dark hover:underline focus:outline-none focus-visible:rounded-sm focus-visible:ring-2 focus-visible:ring-cvsu-green">{{ __('Post the first one') }}</a>{{ __(' to start accepting applications.') }}
                @else
                    {{ __('Nothing is waiting on you.') }}
                    <span class="font-semibold tabular-nums text-gray-900">{{ $openCount }}</span>
                    {{ trans_choice('scholarship is open.|scholarships are open.', $openCount) }}
                @endif
            </p>

            <div class="mt-8 overflow-x-auto">
                <table class="w-full min-w-[46rem] text-sm">
                    <caption class="sr-only">{{ __('Scholarships, ordered by how urgently they need a decision') }}</caption>

                    <thead>
                        <tr class="border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500">
                            <th scope="col" class="py-2 pr-4 text-left font-medium">{{ __('Scholarship') }}</th>
                            <th scope="col" class="px-4 py-2 text-right font-medium">{{ __('Pending') }}</th>
                            <th scope="col" class="px-4 py-2 text-right font-medium">{{ __('Awarded') }}</th>
                            <th scope="col" class="px-4 py-2 text-right font-medium">{{ __('Oldest wait') }}</th>
                            <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Closes') }}</th>
                            <th scope="col" class="py-2 pl-4 text-left font-medium">{{ __('Status') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($register as $index => $scholarship)
                            @if ($needsDecisionCount > 0 && $index === $needsDecisionCount)
                                <tr>
                                    <th colspan="6" scope="colgroup"
                                        class="border-b border-gray-200 pb-2 pt-10 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        {{ __('Settled') }}
                                    </th>
                                </tr>
                            @endif

                            <tr class="border-b border-gray-100 transition duration-150 ease-in-out hover:bg-gray-50">
                                <td class="py-3 pr-4">
                                    <a href="{{ route('admin.scholarships.show', $scholarship) }}"
                                       title="{{ $scholarship->title }}"
                                       class="block max-w-[22rem] truncate font-semibold text-gray-900 hover:text-cvsu-green focus:outline-none focus-visible:rounded-sm focus-visible:ring-2 focus-visible:ring-cvsu-green">
                                        {{ $scholarship->title }}
                                    </a>
                                </td>

                                <td class="px-4 py-3 text-right tabular-nums">
                                    @if ($scholarship->pending_count > 0)
                                        <span class="font-semibold text-gray-900">{{ $scholarship->pending_count }}</span>
                                    @else
                                        <span class="text-gray-400" aria-hidden="true">&mdash;</span>
                                        <span class="sr-only">{{ __('None') }}</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-right tabular-nums">
                                    <span class="{{ $scholarship->slots > 0 && $scholarship->remainingSlots() === 0 ? 'font-semibold text-cvsu-green' : 'text-gray-900' }}">{{ $scholarship->approved_applications_count }}</span><span class="text-gray-500"> / {{ $scholarship->slots }}</span>
                                </td>

                                <td class="px-4 py-3 text-right tabular-nums">
                                    @if ($scholarship->oldest_pending_at)
                                        @php($waited = (int) $scholarship->oldest_pending_at->startOfDay()->diffInDays(now()->startOfDay()))
                                        <span class="{{ $waited >= 7 ? 'font-semibold text-gray-900' : 'text-gray-500' }}">
                                            {{ $waited === 0 ? __('today') : trans_choice(':count day|:count days', $waited, ['count' => $waited]) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400" aria-hidden="true">&mdash;</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    @if ($scholarship->deadline)
                                        @php($daysToClose = $scholarship->daysUntilDeadline())
                                        @php($closingSoon = $scholarship->pending_count > 0 && $daysToClose >= 0 && $daysToClose <= 3)
                                        <span title="{{ $scholarship->deadline->format('F j, Y') }}"
                                              class="{{ $closingSoon ? 'font-semibold text-red-700' : 'text-gray-500' }}">
                                            @if ($daysToClose < 0)
                                                {{ trans_choice(':count day ago|:count days ago', abs($daysToClose), ['count' => abs($daysToClose)]) }}
                                            @elseif ($daysToClose === 0)
                                                {{ __('today') }}
                                            @elseif ($daysToClose === 1)
                                                {{ __('tomorrow') }}
                                            @else
                                                {{ __('in :count days', ['count' => $daysToClose]) }}
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-gray-400" aria-hidden="true">&mdash;</span>
                                        <span class="sr-only">{{ __('No deadline') }}</span>
                                    @endif
                                </td>

                                <td class="py-3 pl-4">
                                    <x-badge :color="$scholarship->status->value === 'open' ? 'green' : 'gray'">
                                        {{ $scholarship->status->label() }}
                                    </x-badge>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="border-b border-gray-100 py-8 text-sm text-gray-500">
                                    {{ __('Nothing to review yet. Posted scholarships and their applicants will be listed here.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <section class="mt-14">
                <h2 class="text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Recent submissions') }}</h2>

                <ul class="mt-2 border-t border-gray-200">
                    @forelse ($recentApplications as $application)
                        <li class="flex items-baseline justify-between gap-4 border-b border-gray-100 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-900">
                                    {{ $application->applicant_name }}
                                    @if ($application->applicant_student_number)
                                        <span class="font-normal text-gray-500">{{ $application->applicant_student_number }}</span>
                                    @endif
                                </p>
                                <a href="{{ route('admin.scholarships.show', $application->scholarship) }}"
                                   class="block truncate text-sm text-gray-500 underline-offset-2 hover:text-cvsu-green hover:underline focus:outline-none focus-visible:rounded-sm focus-visible:ring-2 focus-visible:ring-cvsu-green">
                                    {{ $application->scholarship->title }}
                                </a>
                            </div>

                            <div class="flex shrink-0 items-center gap-3">
                                <span class="text-sm tabular-nums text-gray-500">
                                    {{ ($application->submitted_at ?? $application->created_at)->diffForHumans() }}
                                </span>
                                <x-badge :color="match ($application->status->value) {
                                    'approved' => 'green',
                                    'rejected' => 'red',
                                    default => 'gold',
                                }">
                                    {{ $application->status->label() }}
                                </x-badge>
                            </div>
                        </li>
                    @empty
                        <li class="border-b border-gray-100 py-8 text-sm text-gray-500">
                            {{ __('No applications have been submitted yet.') }}
                        </li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
</x-app-layout>
