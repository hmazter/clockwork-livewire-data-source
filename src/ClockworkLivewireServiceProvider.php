<?php

namespace Hmazter\ClockworkLivewireDataSource;

use Clockwork\Clockwork;
use Illuminate\Support\ServiceProvider;

class ClockworkLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /** @var Clockwork $clockwork */
        $clockwork = $this->app->make('clockwork');
        $clockwork->addDataSource(
            $this->app->make(LivewireDataSource::class)->listenForLivewireEvents()
        );
    }
}
