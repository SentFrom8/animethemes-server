<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\Wiki\Artist;

use App\Models\Auth\User;
use App\Models\Wiki\Artist;
use Illuminate\Foundation\Testing\WithoutEvents;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Class ArtistDestroyTest.
 */
class ArtistDestroyTest extends TestCase
{
    use WithoutEvents;

    /**
     * The Artist Destroy Endpoint shall be protected by sanctum.
     *
     * @return void
     */
    public function testProtected(): void
    {
        $artist = Artist::factory()->createOne();

        $response = $this->delete(route('api.artist.destroy', ['artist' => $artist]));

        $response->assertUnauthorized();
    }

    /**
     * The Artist Destroy Endpoint shall delete the artist.
     *
     * @return void
     */
    public function testDeleted(): void
    {
        $artist = Artist::factory()->createOne();

        $user = User::factory()->withPermission('delete artist')->createOne();

        Sanctum::actingAs($user);

        $response = $this->delete(route('api.artist.destroy', ['artist' => $artist]));

        $response->assertOk();
        static::assertSoftDeleted($artist);
    }
}
