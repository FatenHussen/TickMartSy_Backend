<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class TinyEditor extends Field
{
    protected string $view = 'forms.components.tiny-editor';

    protected bool $isRtl = false;

    protected int $minHeight = 420;

    protected function setUp(): void
    {
        parent::setUp();

        $this->isRtl = app()->getLocale() === 'ar';
    }

    public function rtl(bool $condition = true): static
    {
        $this->isRtl = $condition;

        return $this;
    }

    public function minHeight(int $height): static
    {
        $this->minHeight = $height;

        return $this;
    }

    public function isRtl(): bool
    {
        return $this->isRtl;
    }

    public function getMinHeight(): int
    {
        return $this->minHeight;
    }
}
