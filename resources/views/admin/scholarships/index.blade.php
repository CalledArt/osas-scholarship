{{--
    Every posted scholarship as a working table, ruled like the roster rather
    than boxed: a list of records is the document. The title is the row's way
    in; numbers sit right-aligned in tabular figures so they compare down the
    column. Below 52rem the table scrolls sideways instead of folding.
--}}
<x-app-layout :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-7xl">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Manage Scholarships') }}</h1>

                <a href="{{ route('admin.scholarships.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-transparent bg-cvsu-green px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition duration-150 ease-in-out hover:bg-cvsu-green-dark focus:outline-none focus:ring-2 focus:ring-cvsu-gold focus:ring-offset-2">
                    {{ __('Post Scholarship') }}
                </a>
            </div>

            @if (session('status'))
                <div class="mt-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mt-8 overflow-x-auto">
                <table class="w-full min-w-[52rem] text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500">
                            <th scope="col" class="py-2 pr-4 text-left font-medium">{{ __('Title') }}</th>
                            <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Type') }}</th>
                            <th scope="col" class="px-4 py-2 text-right font-medium">{{ __('Slots') }}</th>
                            <th scope="col" class="px-4 py-2 text-right font-medium">{{ __('Applicants') }}</th>
                            <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Deadline') }}</th>
                            <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Status') }}</th>
                            <th scope="col" class="py-2 pl-4"><span class="sr-only">{{ __('Actions') }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($scholarships as $scholarship)
                            <tr class="border-b border-gray-100 transition-colors duration-150 ease-in-out hover:bg-gray-50">
                                <td class="py-3 pr-4">
                                    <a href="{{ route('admin.scholarships.show', $scholarship) }}" data-vt-title class="rounded font-medium text-gray-900 [overflow-wrap:anywhere] hover:text-cvsu-green focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">{{ $scholarship->title }}</a>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-600">
                                    {{ $scholarship->type->label() }}
                                    @if ($scholarship->stem_courses_only)
                                        <span class="block text-xs text-gray-500">{{ __('STEM only') }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums text-gray-600">{{ $scholarship->remainingSlots() }} / {{ $scholarship->slots }}</td>
                                <td class="px-4 py-3 text-right tabular-nums text-gray-600">{{ $scholarship->applications_count }}</td>
                                <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-600">
                                    @if ($scholarship->deadline)
                                        {{ $scholarship->deadline->format('M j, Y') }}
                                    @else
                                        <span aria-hidden="true">&mdash;</span><span class="sr-only">{{ __('No deadline') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <x-badge :color="$scholarship->status->value === 'open' ? 'green' : 'gray'">
                                        {{ $scholarship->status->label() }}
                                    </x-badge>
                                </td>
                                <td class="whitespace-nowrap py-3 pl-4 text-right">
                                    <a href="{{ route('admin.scholarships.show', $scholarship) }}" class="rounded font-semibold text-cvsu-green hover:text-cvsu-green-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">{{ __('View') }}<span class="sr-only"> {{ $scholarship->title }}</span></a>
                                    <a href="{{ route('admin.scholarships.edit', $scholarship) }}" class="ml-4 rounded font-medium text-gray-600 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">{{ __('Edit') }}<span class="sr-only"> {{ $scholarship->title }}</span></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-500">
                                    {{ __('No scholarships have been posted yet.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($scholarships->hasPages())
                <div class="mt-6">
                    {{ $scholarships->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
