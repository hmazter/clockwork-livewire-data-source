<?php

namespace Hmazter\ClockworkLivewireDataSource;


use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request as ClockworkRequest;
use Illuminate\Http\Request;
use Livewire\Component;
use Livewire\LivewireManager;

class LivewireDataSource extends DataSource
{
    /** @var array<mixed> */
    protected array $components = [];

    /** @var array<mixed> */
    protected array $events = [];

    public function __construct(private LivewireManager $livewireManager, private Request $request)
    {
    }

    public function listenForLivewireEvents(): self
    {
        $this->livewireManager->listen('profile', function (string $event, string $id, array $timing) {
            $this->events[] = [
                'event' => $event,
                'id' => $id,
                'timing' => $timing,
            ];
        });
        $this->livewireManager->listen('hydrate', function (...$args) {
            clock('hydrate', $args);
        });
        $this->livewireManager->listen('render', function (...$args) {
            clock('render', $args);
        });
        $this->livewireManager->listen('mount', function (...$args) {
            clock('mount', $args);
        });

        $this->livewireManager->listen('render', function (Component $component) {
            $data = [
                'Component' => $component::class,
                'Id' => $component->id(),
                'Properties' => $component->all(),
            ];

            if ($this->request->path() === 'livewire/update') {
                // parse some additional data for livewire requests
                $components = $this->request->array('components');
                $data['Method'] = $components[0]['calls'][0]['method'] ?? '';
                if (isset($components[0]['updates']) && ! empty($components[0]['updates'])) {
                    $data['Updates'] = $components[0]['updates'];
                }
            }

            $this->components[] = $data;
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
                ->finalize($event['timing'][0], $event['timing'][1]);
        }

        return $clockwork;
    }

    public function reset(): void
    {
        $this->components = [];
    }
}