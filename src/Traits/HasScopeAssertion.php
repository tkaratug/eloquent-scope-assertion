<?php

declare(strict_types = 1);

namespace Tkaratug\EloquentScopeAssertion\Traits;

use Illuminate\Support\Facades\Event;
use Tkaratug\EloquentScopeAssertion\Events\ModelScopeCalled;

trait HasScopeAssertion
{
    public function assertScopeCalled(string $scope, string $model, ?int $times = null)
    {
        $triggeredScopes = [];

        Event::assertDispatched(ModelScopeCalled::class);
        Event::assertDispatched(function (ModelScopeCalled $event) use (&$triggeredScopes) {
            $triggeredScopes[$event->model][] = $event->scope;

            return true;
        });

        $this->assertTrue(in_array($scope, $triggeredScopes[$model]));

        if (!is_null($times)) {
            $this->assertCount(
                $times,
                array_filter($triggeredScopes[$model], fn ($item) => $item === $scope)
            );
        }
    }
}