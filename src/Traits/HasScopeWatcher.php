<?php

declare(strict_types = 1);

namespace Tkaratug\EloquentScopeAssertion\Traits;

use Tkaratug\EloquentScopeAssertion\Events\ModelScopeCalled;

trait HasScopeWatcher
{
    public function callNamedScope($scope, array $parameters = [])
    {
        if (app()->runningUnitTests()) {
            event(new ModelScopeCalled($scope, get_class($this)));
        }

        return parent::callNamedScope($scope, $parameters);
    }
}
