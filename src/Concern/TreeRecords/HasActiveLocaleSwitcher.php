<?php

namespace SolutionForest\FilamentTree\Concern\TreeRecords;

trait HasActiveLocaleSwitcher
{
    public $activeLocale = null;

    public ?array $translatableLocales = null;

    public function bootHasActiveLocaleSwitcher()
    {
        $this->setTranslatableLocales($this->getTranslatableLocales());
    }

    public function setTranslatableLocales(array $locales): void
    {
        $this->translatableLocales = $locales;
    }

    public function getTranslatableLocales(): array
    {
        return $this->translatableLocales ?? (
            method_exists(static::class, 'getResource')
                ? static::getResource()::getTranslatableLocales()
                : (
                    method_exists(static::class, 'getTranslatableLocales') 
                        ? $this->getTranslatableLocales()
                        : []
                )
        );
    }
}
