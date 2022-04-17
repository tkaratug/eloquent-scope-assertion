# Laravel Scope Assertion

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tkaratug/eloquent-scope-assertion.svg?style=flat-square)](https://packagist.org/packages/tkaratug/eloquent-scope-assertion)
[![Total Downloads](https://img.shields.io/packagist/dt/tkaratug/eloquent-scope-assertion.svg?style=flat-square)](https://packagist.org/packages/tkaratug/eloquent-scope-assertion)

This package allows you to assert that the scope of a model is called in your tests.

## Installation

You can install the package via composer:

```bash
composer require tkaratug/eloquent-scope-assertion
```

## Use Case

Let's say you have a complicated conditional query for Orders that is imported to be tested.

- Get the orders that have not been paid.
- Sorted by creation date descending.
- The list could go on.

The above query constraints should be tested at the feature level, so that you have tests like so;
- `user_can_get_orders`
- `user_can_get_only_unpaid_orders`
- `user_can_get_orders_by_created_at_desc`
- The list could go on.

Since the query is happening in the model scope it would be nice to test the query in the model's unit test and therefore only write the test coverage once.

However, in your feature tests, it's hard to be sure that the model scope with test coverage is actually used in your controller, so you'll likely duplicate that test coverage in your Feature tests in some way. Thus, you make the unit test you write quite meaningless.

As a solution of that, this package triggers an event that contains the name of the scope and model when a model scope is called. In this way, it is quite easy to assert that the event is dispatched with the correct scope and model names in feature tests.

## Usage
Add the `HasScopeAssertion` trait to the `TestCase` class in order to be able to call `assertScopeCalled()` method in your feature tests.

Since the `ModelScopeCalled` event is triggered when a named scope is called, you should fake it in `setUp()` method.

```php
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tkaratug\LaravelScopeAssertion\Traits\HasScopeAssertion;
use Illuminate\Support\Facades\Event;
use App\Events\ModelScopeCalled;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use HasScopeAssertion;

    public function setUp(): void
    {
        parent::setUp();

        Event::fake([ModelScopeCalled::class]);
    }
}
```

Then add the `HasScopeWatcher` trait in your models to be able to assert its scopes.

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Tkaratug\LaravelScopeAssertion\Traits\HasScopeWatcher;

class Order extends Model
{
    use HasFactory;
    use HasScopeWatcher;

    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->where('is_paid', false);
    }

    public function scopeCreatedAtDesc(Builder $query): Builder
    {
        return $query->latest('created_at');
    }
}
```

Let's say you want to see only unpaid orders sorted by creation date descending.

The `OrderController` should be like this;
```php
use App\Models\Order;
use App\Http\Resources\OrderResource;

public OrderController extends Controller
{
    public function index(): void
    {
        $orders = Order::query()
                       ->unpaid()
                       ->createdAtDesc()
                       ->get();

        return OrderResource::collection($orders);
    }
}
```

Now you can simplify your test coverage in `OrderControllerTest` like so;

```php
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Sequence;

class OrderControllerTest extends TestCase
{
    public function user_can_get_unpaid_orders_sorted_by_creation_date_descenting(): void
    {
        $users = User::factory()
                     ->unpaid()
                     ->createMany([
                        ['created_at' => Carbon::parse('7 days ago')],
                        ['created_at' => Carbon::parse('14 days ago')],
                        ['created_at' => Carbon::parse('21 days ago')],
                    ]);

        $response = $this->get(route('orders.index'));

        $response->assertOk();

        $this->assertScopeCalled('unpaid', Order::class);
        $this->assertScopeCalled('createdAtDesc', Order::class);
    }
}
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security


If you discover any security related issues, please email tkaratug@hotmail.com.tr instead of using the issue tracker.

## Credits

-   [Turan Karatug](https://github.com/tkaratug)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
