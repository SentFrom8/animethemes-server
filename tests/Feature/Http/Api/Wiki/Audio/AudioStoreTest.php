<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\Wiki\Audio;

use App\Models\Auth\User;
use App\Models\Wiki\Audio;
use Illuminate\Foundation\Testing\WithoutEvents;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Class AudioStoreTest.
 */
class AudioStoreTest extends TestCase
{
    use WithoutEvents;

    /**
     * The Audio Store Endpoint shall be protected by sanctum.
     *
     * @return void
     */
    public function testProtected(): void
    {
        $audio = Audio::factory()->makeOne();

        $response = $this->post(route('api.audio.store', $audio->toArray()));

        $response->assertUnauthorized();
    }

    /**
     * The Audio Store Endpoint shall require basename, filename, mimetype, path & size fields.
     *
     * @return void
     */
    public function testRequiredFields(): void
    {
        $user = User::factory()->withPermission('create audio')->createOne();

        Sanctum::actingAs($user);

        $response = $this->post(route('api.audio.store'));

        $response->assertJsonValidationErrors([
            Audio::ATTRIBUTE_BASENAME,
            Audio::ATTRIBUTE_FILENAME,
            Audio::ATTRIBUTE_MIMETYPE,
            Audio::ATTRIBUTE_PATH,
            Audio::ATTRIBUTE_SIZE,
        ]);
    }

    /**
     * The Audio Store Endpoint shall create a audio.
     *
     * @return void
     */
    public function testCreate(): void
    {
        $parameters = Audio::factory()->raw();

        $user = User::factory()->withPermission('create audio')->createOne();

        Sanctum::actingAs($user);

        $response = $this->post(route('api.audio.store', $parameters));

        $response->assertCreated();
        static::assertDatabaseCount(Audio::TABLE, 1);
    }
}
