<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-100">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ __('Welcome, :name!', ['name' => Auth::user()->full_name]) }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ __("You're signed in as a Student. Your scholarship applications and awards will appear here.") }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
