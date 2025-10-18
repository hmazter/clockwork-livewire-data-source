<?php

namespace Hmazter\ClockworkLivewireDataSource;

use Clockwork\Clockwork;
use Clockwork\DataSource\DataSourceInterface;
use Illuminate\Support\ServiceProvider;
use Livewire\LivewireManager;

class ClockworkLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /** @var Clockwork $clockwork */
        $clockwork = $this->app->make('clockwork');
        //$clockwork->addDataSource(
        //    new LivewireDataSource($this->app->make(LivewireManager::class))
        //);
        $clockwork->addDataSource(
            $this->app->make(LivewireDataSource::class)->listenForLivewireEvents()
        );
    }
}