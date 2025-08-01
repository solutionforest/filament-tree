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
        try {

            if ($this->translatableLocales) {
                return $this->translatableLocales;
            }

            if (method_exists(static::class, 'getResource')) {
                $resource = static::getResource();
                if (method_exists($resource, 'getTranslatableLocales')) {
                    return $resource::getTranslatableLocales();
                }
            }

            // Check any translatable plugin
            $fiPanel = filament()?->getCurrentPanel();

            // Find translatable locales from the resource
            foreach ($fiPanel?->getPlugins() as $pluginKey => $plugin) {
                if (method_exists($plugin, 'getDefaultLocales')) {
                    $locales = $plugin->getDefaultLocales();
                    if (! empty($locales)) {
                        return $locales;
                    }
                }
            }

        } catch (\Throwable $e) {
            //
        }

        return [];
    }
}
