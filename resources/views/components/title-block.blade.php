{{--
    THE TITLE BLOCK — the plan convention, and the home of the stage register.

    A plan states what it is in a fixed block: what it draws, its reference, its
    dates, its extent. So does every record here, in the same place on every
    screen it appears on, which is what lets a reader stop hunting.

    It carries `view-transition-name: cycle`, so moving between a scholarship and
    a claim holds the block still and changes only the line that was entered.

    It is not a kicker. It sits beneath the heading it belongs to and carries
    real values, never a label floating above a title.
--}}
<div {{ $attributes->merge(['class' => 'border-t-2 border-ink bg-linen-sunk px-6 py-4 [view-transition-name:cycle]']) }}>
    <dl class="grid grid-cols-2 gap-x-6 gap-y-3 sm:grid-cols-4">
        {{ $slot }}
    </dl>
</div>
