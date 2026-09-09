<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Scholarships') }}
            </h2>
            <a href="{{ route('admin.scholarships.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-cvsu-green px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-cvsu-green-dark focus:outline-none focus:ring-2 focus:ring-cvsu-gold focus:ring-offset-2">
                {{ __('Post Scholarship') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Title') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Slots') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Applicants') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Deadline') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($scholarships as $scholarship)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $scholarship->title }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $scholarship->remainingSlots() }} / {{ $scholarship->slots }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $scholarship->applications_count }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $scholarship->deadline?->format('M j, Y') ?? '—' }}</td>
                                    <td class="px-6 py-4">
                                        <x-badge :color="$scholarship->status->value === 'open' ? 'green' : 'gray'">
                                            {{ $scholarship->status->label() }}
                                        </x-badge>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm space-x-3">
                                        <a href="{{ route('admin.scholarships.show', $scholarship) }}" class="font-medium text-cvsu-green hover:text-cvsu-green-dark">{{ __('View') }}</a>
                                        <a href="{{ route('admin.scholarships.edit', $scholarship) }}" class="font-medium text-gray-500 hover:text-gray-700">{{ __('Edit') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                        {{ __('No scholarships have been posted yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($scholarships->hasPages())
                    <div class="border-t border-gray-100 px-6 py-4">
                        {{ $scholarships->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
