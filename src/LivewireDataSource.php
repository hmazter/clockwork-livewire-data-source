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

    public function __construct(private LivewireManager $livewireManager, private Request $request)
    {
    }

    public function listenForLivewireEvents(): self
    {
        $this->livewireManager->listen('profile', function (string $event, string $id, array $timing) {
            // add livewire events to the timeline
            clock()->event("livewire:$event - $id", ['color' => 'blue'])->finalize(...$timing);
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
        // Create the "Livewire"-tab
        $livewireTab = $clockwork->userData('livewire')->title('Livewire');

        // add a table with all Livewire components loaded during the request
        if (! empty($this->components)) {
            $livewireTab->table('Components', $this->components);
        }

        return $clockwork;
    }

    public function reset(): void
    {
        $this->components = [];
    }
}