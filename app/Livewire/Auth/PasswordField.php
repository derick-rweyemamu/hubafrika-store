<?php

namespace App\Livewire\Auth;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class PasswordField extends Component
{
    public string $name = 'password';

    public string $label = 'Password';

    public string $autocomplete = 'current-password';

    public bool $autofocus = false;

    public bool $visible = false;

    public function mount(
        string $name = 'password',
        string $label = 'Password',
        string $autocomplete = 'current-password',
        bool $autofocus = false,
    ): void {
        $this->name = $name;
        $this->label = $label;
        $this->autocomplete = $autocomplete;
        $this->autofocus = $autofocus;
    }

    public function toggleVisibility(): void
    {
        $this->visible = ! $this->visible;
    }

    public function render(): View
    {
        return view('livewire.auth.password-field');
    }
}
