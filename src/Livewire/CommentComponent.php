<?php

namespace Spatie\LivewireComments\Livewire;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class CommentComponent extends Component
{
    use AuthorizesRequests;

    /** @var \Spatie\Comments\Models\Comment */
    public $comment;

    public bool $showAvatar;

    public bool $writable;

    public $replyText = '';

    public $isEditing = false;

    public $editText = '';

    public function getListeners()
    {
        return [
            'delete' => '$refresh',
        ];
    }

    public function startEditing()
    {
        $this->isEditing = true;
        $this->editText = $this->comment->original_text;
    }

    public function stopEditing()
    {
        $this->isEditing = false;
    }

    public function edit()
    {
        $this->authorize('update', $this->comment);

        $this->validate(['editText' => 'required']);

        $this->comment->update([
            'original_text' => $this->editText,
        ]);

        $this->isEditing = false;
    }

    public function cancel()
    {
        $this->isEditing = false;
    }

    public function reply()
    {
        $this->validate(['replyText' => 'required']);

        $this->comment->comment($this->replyText);
        $this->comment->load('nestedComments');

        $this->replyText = '';
        $this->emit('reply-' . $this->comment->id);
    }

    public function deleteComment()
    {
        $this->authorize('delete', $this->comment);

        $this->comment->delete();

        $this->emitUp('delete');
    }

    public function react(string $reaction)
    {
        $this->authorize('react', $this->comment);

        $this->comment->react($reaction);

        $this->comment->load('reactions');
    }

    public function deleteReaction(string $reaction)
    {
        if ($reactionModel = $this->comment->findReaction($reaction)) {
            $this->authorize('delete', $reactionModel);
        }

        $this->comment->deleteReaction($reaction);

        $this->comment->load('reactions');
    }

    public function render()
    {
        return view('comments::livewire.comment');
    }
}
