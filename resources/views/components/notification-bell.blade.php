{{--
    The student's bell: an icon button in the top-right corner of the
    dashboard's welcome banner (moved out of the rail at the user's request,
    2026-10-25) that opens their ten latest notifications from
    User::bellNotifications(). The unread count is Gold, since unread
    notifications are the reader's own and waiting on them; Oxide stays
    reserved for refusals. A read notification stays, on white, for 24 hours
    (User::READ_NOTIFICATION_HOURS) and then leaves the panel. Opening one is a
    small POST form, so marking it read never rides on a GET. The panel drops
    below the bell, right-aligned, over the page with the Lift shadow. Escape
    closes it and returns focus to the bell.
--}}
@props(['notifications'])

@php
    $unreadCount = (int) ($notifications->first()?->unread_total ?? 0);
@endphp

<div x-data="{ open: false }"
     x-id="['notifications-panel']"
     @keydown.escape="if (open) { $event.stopPropagation(); open = false; $refs.bell.focus() }"
     @click.outside="open = false"
     class="relative">
    <button type="button"
            x-ref="bell"
            @click="open = ! open"
            :aria-expanded="open"
            :aria-controls="$id('notifications-panel')"
            :class="open && 'bg-seal-tint/60'"
            class="relative flex h-10 w-10 items-center justify-center rounded-full text-seal transition duration-150 ease-in-out hover:bg-seal-tint/60 focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2 focus-visible:ring-offset-seal-wash">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>
        <span class="sr-only">{{ __('Notifications') }}</span>
        @if ($unreadCount > 0)
            <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-gold px-1 text-annot font-semibold tabular-nums text-ink ring-2 ring-seal-wash" aria-hidden="true">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
            <span class="sr-only">{{ trans_choice('(:count unread)|(:count unread)', $unreadCount, ['count' => $unreadCount]) }}</span>
        @endif
    </button>

    <div x-show="open"
         x-cloak
         :id="$id('notifications-panel')"
         x-transition:enter="transition ease-sheet duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute right-0 top-full z-40 mt-2 w-[min(24rem,calc(100vw-4.5rem))] overflow-hidden rounded-sheet border border-rule bg-white text-left text-graphite shadow-lift motion-reduce:transition-none">
        <div class="flex items-center justify-between gap-3 border-b border-rule px-4 py-3">
            <p class="font-semibold text-ink">{{ __('Notifications') }}</p>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="rounded font-semibold text-seal hover:text-seal-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">{{ __('Mark all as read') }}</button>
                </form>
            @endif
        </div>

        @if ($notifications->isEmpty())
            <p class="px-4 py-6 text-center text-slate">{{ __('No notifications yet. New scholarships you can apply to will show up here.') }}</p>
        @else
            <ul class="max-h-[60vh] divide-y divide-rule overflow-y-auto">
                @foreach ($notifications as $notification)
                    @php $isUnread = $notification->read_at === null; @endphp
                    <li>
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                            @csrf
                            <button type="submit" class="flex w-full gap-3 px-4 py-3 text-left transition duration-150 ease-in-out hover:bg-linen-sunk focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-seal {{ $isUnread ? 'bg-gold-wash' : 'bg-white' }}">
                                <span class="mt-2 h-2 w-2 shrink-0 rounded-full {{ $isUnread ? 'bg-gold-deep' : '' }}" aria-hidden="true"></span>
                                <span class="min-w-0">
                                    <span class="block font-semibold text-ink">{{ __('New scholarship posted') }}</span>
                                    <span class="block [overflow-wrap:anywhere]">{{ $notification->data['title'] ?? '' }}</span>
                                    <span class="block text-slate">
                                        {{ $notification->created_at->diffForHumans() }}@if ($isUnread)<span class="sr-only">, {{ __('unread') }}</span>@endif
                                    </span>
                                </span>
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
