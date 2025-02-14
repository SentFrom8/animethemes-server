<?php

declare(strict_types=1);

namespace Tests\Feature\Events\Pivot;

use App\Events\Pivot\AnimeImage\AnimeImageCreated;
use App\Events\Pivot\AnimeImage\AnimeImageDeleted;
use App\Models\Wiki\Anime;
use App\Models\Wiki\Image;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Class AnimeImageTest.
 */
class AnimeImageTest extends TestCase
{
    /**
     * When an Anime is attached to an Image or vice versa, an AnimeImageCreated event shall be dispatched.
     *
     * @return void
     */
    public function testAnimeImageCreatedEventDispatched(): void
    {
        Event::fake();

        $anime = Anime::factory()->createOne();
        $image = Image::factory()->createOne();

        $anime->images()->attach($image);

        Event::assertDispatched(AnimeImageCreated::class);
    }

    /**
     * When an Anime is detached from an Image or vice versa, an AnimeImageDeleted event shall be dispatched.
     *
     * @return void
     */
    public function testAnimeImageDeletedEventDispatched(): void
    {
        Event::fake();

        $anime = Anime::factory()->createOne();
        $image = Image::factory()->createOne();

        $anime->images()->attach($image);
        $anime->images()->detach($image);

        Event::assertDispatched(AnimeImageDeleted::class);
    }
}
