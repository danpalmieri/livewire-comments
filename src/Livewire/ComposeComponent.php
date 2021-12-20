<?php

namespace Spatie\LivewireComments\Livewire;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class ComposeComponent extends Component
{
    use AuthorizesRequests;

    public string $onSubmit;

    public ?string $onCancel;

    public string $text;

    public function mount(
        string $onSubmit,
        ?string $onCancel = null,
        ?string $text = '',
    ) {
        $this->onCancel = $onCancel;

        $this->onSubmit = $onSubmit;

        $this->text = $text;
    }

    public function submit()
    {
        $this->validate([
            'text' => 'required',
        ]);

        $this->emit($this->onSubmit, $this->text);
        $this->emitSelf('submit');

        $this->text = '';
    }

    public function cancel()
    {
        $this->emit($this->onCancel);

        $this->text = '';
    }

    public function render()
    {
        return view('comments::livewire.compose');
    }
}