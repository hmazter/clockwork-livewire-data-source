<?php

namespace Tests\Unit;

use Clockwork\Request\Request;
use Clockwork\Request\UserData;
use Hmazter\ClockworkLivewireDataSource\LivewireDataSource;
use Livewire\LivewireManager;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LivewireDataSourceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[Test]
    public function data_source_can_run_listen_for_livewire_events_without_issues()
    {
        $manager = Mockery::mock(LivewireManager::class);
        $manager->expects('listen')->atLeast()->once();

        $livewireDataSource = new LivewireDataSource($manager);
        $livewireDataSource->listenForLivewireEvents();
    }

    #[Test]
    public function data_source_can_run_resolve()
    {
        $manager = Mockery::mock(LivewireManager::class);
        $livewireDataSource = new LivewireDataSource($manager);
        $this->setProperty($livewireDataSource, 'components', [
            'Aj02fKVplQsM52psYSaR' => [
                'Component' => 'test',
                'Id' => 'Aj02fKVplQsM52psYSaR',
            ],
        ]);

        $request = $livewireDataSource->resolve(new Request);

        self::assertArrayHasKey('livewire', $request->userData);
        self::assertInstanceOf(UserData::class, $request->userData['livewire']);
        $expected = [
            'Aj02fKVplQsM52psYSaR' => [
                'Component' => 'test',
                'Id' => 'Aj02fKVplQsM52psYSaR',
            ],
            '__meta' => [
                'showAs' => 'table',
                'title' => 'Components',
            ],
        ];
        self::assertEquals($expected, $request->userData['livewire']->toArray()[0]);
    }

    private function setProperty(object $object, string $propertyName, $value): void
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
