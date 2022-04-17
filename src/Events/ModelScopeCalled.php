<?php

declare(strict_types = 1);

namespace Tkaratug\EloquentScopeAssertion\Events;

class ModelScopeCalled
{
    public function __construct(
      public string $scope,
      public string $model,
    ) {
    }
}