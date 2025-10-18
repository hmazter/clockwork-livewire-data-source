<?php

namespace Hmazter\ClockworkLivewireDataSource;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request as ClockworkRequest;
use Livewire\Component;
use Livewire\LivewireManager;

class LivewireDataSource extends DataSource
{
    /** @var array<mixed> */
    protected array $components = [];

    /** @var array{event:string, id:string, start:int, finish:int}[] */
    protected array $events = [];

    public function __construct(private LivewireManager $livewireManager) {}

    public function listenForLivewireEvents(): self
    {
        /*
         * Listen to the "profile" event, which contains profiling info about the other events, including start and finish timestamps
         */
        $this->livewireManager->listen('profile', function (string $event, string $id, array $timing) {
            $this->events[] = [
                'event' => $event,
                'id' => $id,
                'start' => $timing[0],
                'finish' => $timing[1],
            ];
        });

        /*
         * Get properties that is passed to the Component in the "mount" event
         */
        $this->livewireManager->listen('mount', function (Component $component, $properties) {
            $this->components[$component->id()]['Component'] = $component::class;
            $this->components[$component->id()]['Id'] = $component->id();
            $this->components[$component->id()]['Properties'] = $properties;
        });

        /*
         * Get which Component properties that receives updates
         */
        $this->livewireManager->listen('update', function ($component, $path, $value) {
            $this->components[$component->id()]['Component'] = $component::class;
            $this->components[$component->id()]['Id'] = $component->id();
            $this->components[$component->id()]['Updates'][$path] = $value;
        });

        /*
         * Which Component method has been called, with which params
         */
        $this->livewireManager->listen('call', function (Component $component, $method, $params) {
            $this->components[$component->id()]['Component'] = $component::class;
            $this->components[$component->id()]['Id'] = $component->id();
            $this->components[$component->id()]['Method'] = $method;
            $this->components[$component->id()]['Params'] = $params ?: '';
        });

        $this->livewireManager->listen('render', function (Component $component) {
            // set new values in the array for a consistent ordering of columns in the resulting table
            $this->components[$component->id()] = [
                'Component' => $component::class,
                'Id' => $component->id(),
                'Properties' => '',
                ...$this->components[$component->id()] ?? [],
            ];
        });

        return $this;
    }

    public function resolve(ClockworkRequest $clockwork): ClockworkRequest
    {
        // add a table with all Livewire components rendered during the request
        if (! empty($this->components)) {
            $clockwork->userData('livewire')
                ->title('Livewire')
                ->table('Components', $this->components);
        }

        // add livewire events to the timeline
        foreach ($this->events as $event) {
            $clockwork->timeline()
                ->event("livewire:{$event['event']} - {$event['id']}", ['color' => 'blue'])
                ->finalize($event['start'], $event['finish']);
        }

        return $clockwork;
    }

    public function reset(): void
    {
        $this->components = [];
    }
}
