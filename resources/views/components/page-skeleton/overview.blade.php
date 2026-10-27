{{--
    The admin dashboard: the banner and its Post action, the three figures in
    their own tinted boxes (their labels are fixed, so they are written), then
    the newest postings.
--}}
<div data-draft aria-hidden="true" class="draft min-h-[calc(100dvh-3.5rem)] bg-white lg:min-h-screen">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-[100rem]">
            <x-page-skeleton.banner>
                <span class="draft-block h-[2.625rem] w-40 shrink-0 self-start rounded-sheet sm:self-auto"></span>
            </x-page-skeleton.banner>

            <div class="mt-6 grid gap-6 md:grid-cols-3">
                @foreach ([
                    ['label' => __('Total scholarships'), 'box' => 'draft-on-seal border-seal-tint bg-seal-wash', 'disc' => 'bg-seal-tint/60'],
                    ['label' => __('Students with Active status'), 'box' => 'draft-on-blue border-blue-200 bg-blue-50', 'disc' => 'bg-blue-100'],
                    ['label' => __('Students on leave of absence'), 'box' => 'draft-on-gold border-gold/40 bg-gold-wash/60', 'disc' => 'bg-gold/60'],
                ] as $figure)
                    <div class="rounded-lg border p-5 {{ $figure['box'] }}">
                        <span class="block h-10 w-10 rounded-full {{ $figure['disc'] }}"></span>
                        <p class="mt-4 font-semibold text-graphite">{{ $figure['label'] }}</p>
                        <span class="draft-line draft-line--figure mt-1 w-16"></span>
                        <span class="draft-line mt-2 w-2/3"></span>
                    </div>
                @endforeach
            </div>

            <x-page-skeleton.panel class="mt-10" :rows="2" />
        </div>
    </div>
</div>
