<?php

namespace Spatie\LivewireComments\Livewire;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Comments\Enums\NotificationSubscriptionType;
use Spatie\LivewireComments\Support\Config;

class CommentsComponent extends Component
{
    use WithPagination;

    /** @var \Spatie\Comments\Models\Concerns\HasComments */
    public $model;

    public string $text = '';

    public bool $writable;
    public bool $showAvatars;
    public bool $showNotificationOptions;
    public bool $newestFirst;
    public string $selectedNotificationSubscriptionType = '';
    public $noCommentsText = null;

    public function mount(
        bool  $readOnly = false,
        ?bool $hideAvatars = null,
        bool  $hideNotificationOptions = false,
        bool $newestFirst = false,
    ) {
        $this->writable = ! $readOnly;

        $showAvatars = is_null($hideAvatars)
            ? null
            : ! $hideAvatars;

        $this->showAvatars = $showAvatars ?? Config::showAvatars();

        $this->showNotificationOptions = ! $hideNotificationOptions;

        $this->newestFirst = $newestFirst;

        $this->selectedNotificationSubscriptionType = auth()->user()
                ?->notificationSubscriptionType($this->model)?->value ?? NotificationSubscriptionType::Participating->value;
    }

    public function getListeners()
    {
        return [
            'delete' => '$refresh',
            'reply-created' => 'saveNotificationSubscription',
        ];
    }

    public function comment()
    {
        $this->validate(['text' => 'required']);

        $this->model->comment($this->text);

        $this->text = '';
        // @todo This is weird behaviour when your comment appears on a later page.
        // To revisit when we decide how to handle comment sorting.
        $this->goToPage(1);

        $this->saveNotificationSubscription();

        $this->emit('comment');
    }

    public function updatedSelectedNotificationSubscriptionType()
    {
        $this->saveNotificationSubscription();
    }

    public function saveNotificationSubscription()
    {
        if (! $this->showNotificationOptions) {
            return;
        }

        /** @var \Spatie\Comments\Models\Concerns\Interfaces\CanComment $currentUser */
        $currentUser = auth()->user();

        $type = NotificationSubscriptionType::from($this->selectedNotificationSubscriptionType);

        if ($type === NotificationSubscriptionType::None) {
            $currentUser->unsubscribeFromCommentNotifications($this->model);

            return;
        }

        $currentUser->subscribeToCommentNotifications($this->model, NotificationSubscriptionType::from($this->selectedNotificationSubscriptionType));
    }

    public function render()
    {
        $comments = $this->model
            ->comments()
            ->with([
                'commentator',
                'nestedComments' => function (HasMany $builder) {
                    if ($this->newestFirst) {
                        $builder->latest();
                    }
                },
                'nestedComments.commentator',
                'reactions',
                'reactions.commentator',
                'nestedComments.reactions',
                'nestedComments.reactions.commentator',
            ])
            ->when($this->newestFirst, fn (Builder $builder) => $builder->latest())
            ->paginate(10000);

        return view('comments::livewire.comments', [
            'comments' => $comments,
        ]);
    }
}
