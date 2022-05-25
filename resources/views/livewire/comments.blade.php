@php
    use Spatie\Comments\Enums\NotificationSubscriptionType;
@endphp

<section class="comments {{ $newestFirst ? 'comments-newest-first' : '' }}">
    <header class="comments-header">
        @if($writable)
            @auth
                @if($showNotificationOptions)
                    <div x-data="{ subscriptionsOpen: false}" class="comments-subscription">
                        <button @click.prevent="subscriptionsOpen = true" class="comments-subscription-trigger">
                            Send notifications:
                            <span class="comments-subscription-current">{{ $selectedNotificationSubscriptionType }}</span>
                        </button>                            
                        <x-comments::modal
                                bottom
                                compact
                                x-show="subscriptionsOpen"
                                @click.outside="subscriptionsOpen = false"
                            >
                            @foreach(NotificationSubscriptionType::cases() as $case)
                                <button class="comments-subscription-item" @click="subscriptionsOpen = false" wire:click="updateSelectedNotificationSubscriptionType('{{ $case->value }}')">
                                    {{ $case->description() }}
                                </button>
                            @endforeach
                        </x-comments::modal>
                    </div>
                @endif
            @endif
        @endauth
    </header>

    @if ($newestFirst)
        @include('comments::livewire.partials.newComment')
    @endif

    @if($comments->count())
        @foreach($comments as $comment)
            @can('see', $comment)
                <livewire:comments-comment
                    :key="$comment->id"
                    :comment="$comment"
                    :show-avatar="$showAvatars"
                    :newest-first="$newestFirst"
                    :writable="$writable"
                    :show-replies="$showReplies"
                />
            @endcan
        @endforeach
        {{ $comments->links() }}
    @else
        <p class="comments-no-comment-yet">{{ $noCommentsText ?? __('comments::comments.no_comments_yet') }}</p>
    @endif

    @if (! $newestFirst)
        @include('comments::livewire.partials.newComment')
    @endif
</section>
