@props(['rows' => 4])

{{-- A bordered panel headed like the admin dashboard's postings table, its rows still to come. --}}
<div {{ $attributes->class(['overflow-hidden rounded-lg border border-rule bg-white']) }}>
    <div class="px-5 py-4">
        <span class="draft-line draft-line--lead w-56"></span>
    </div>

    <div class="divide-y divide-rule border-t border-rule">
        @foreach (array_slice(['w-3/4', 'w-1/2', 'w-2/3', 'w-5/12', 'w-7/12', 'w-1/3'], 0, $rows) as $width)
            <div class="px-5 py-3.5">
                <span class="draft-line {{ $width }}"></span>
                <span class="draft-line w-1/4"></span>
            </div>
        @endforeach
    </div>
</div>
