<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\Billing\Balance;

use App\Enums\Models\Billing\BalanceFrequency;
use App\Enums\Models\Billing\Service;
use App\Models\Auth\User;
use App\Models\Billing\Balance;
use Illuminate\Foundation\Testing\WithoutEvents;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Class BalanceStoreTest.
 */
class BalanceStoreTest extends TestCase
{
    use WithoutEvents;

    /**
     * The Balance Store Endpoint shall be protected by sanctum.
     *
     * @return void
     */
    public function testProtected(): void
    {
        $balance = Balance::factory()->makeOne();

        $response = $this->post(route('api.balance.store', $balance->toArray()));

        $response->assertUnauthorized();
    }

    /**
     * The Balance Store Endpoint shall require date, service, frequency, usage & balance fields.
     *
     * @return void
     */
    public function testRequiredFields(): void
    {
        $user = User::factory()->withPermission('create balance')->createOne();

        Sanctum::actingAs($user);

        $response = $this->post(route('api.balance.store'));

        $response->assertJsonValidationErrors([
            Balance::ATTRIBUTE_DATE,
            Balance::ATTRIBUTE_SERVICE,
            Balance::ATTRIBUTE_FREQUENCY,
            Balance::ATTRIBUTE_USAGE,
            Balance::ATTRIBUTE_BALANCE,
        ]);
    }

    /**
     * The Balance Store Endpoint shall create a balance.
     *
     * @return void
     */
    public function testCreate(): void
    {
        $parameters = array_merge(
            Balance::factory()->raw(),
            [
                Balance::ATTRIBUTE_FREQUENCY => BalanceFrequency::getRandomInstance()->description,
                Balance::ATTRIBUTE_SERVICE => Service::getRandomInstance()->description,
            ]
        );

        $user = User::factory()->withPermission('create balance')->createOne();

        Sanctum::actingAs($user);

        $response = $this->post(route('api.balance.store', $parameters));

        $response->assertCreated();
        static::assertDatabaseCount(Balance::TABLE, 1);
    }
}
