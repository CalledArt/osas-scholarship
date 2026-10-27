{{--
    The courses a STEM-courses-only scholarship accepts, folded away until
    asked for: CvsuPrograms::STEM, DOST-SEI's priority fields as CvSU names them.
--}}
<details {{ $attributes->merge(['class' => 'group']) }}>
    <summary class="cursor-pointer rounded-sheet font-medium text-seal hover:text-seal-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">{{ __('Which courses count as STEM?') }}</summary>
    <ul class="mt-2 grid gap-x-6 gap-y-1 text-graphite sm:grid-cols-2">
        @foreach (\App\Support\CvsuPrograms::STEM as $course)
            <li>{{ $course }}</li>
        @endforeach
    </ul>
    <p class="mt-2 text-slate">{{ __('From the DOST-SEI list of priority science and technology courses.') }}</p>
</details>
