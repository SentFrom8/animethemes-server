<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\Wiki\Anime\Theme;

use App\Enums\Models\Wiki\AnimeSeason;
use App\Enums\Models\Wiki\ImageFacet;
use App\Enums\Models\Wiki\VideoOverlap;
use App\Enums\Models\Wiki\VideoSource;
use App\Http\Api\Field\Field;
use App\Http\Api\Include\AllowedInclude;
use App\Http\Api\Parser\FieldParser;
use App\Http\Api\Parser\FilterParser;
use App\Http\Api\Parser\IncludeParser;
use App\Http\Api\Query\Wiki\Anime\Theme\ThemeReadQuery;
use App\Http\Api\Schema\Wiki\Anime\ThemeSchema;
use App\Http\Resources\Wiki\Anime\Resource\ThemeResource;
use App\Models\Wiki\Anime;
use App\Models\Wiki\Anime\AnimeTheme;
use App\Models\Wiki\Anime\Theme\AnimeThemeEntry;
use App\Models\Wiki\Image;
use App\Models\Wiki\Song;
use App\Models\Wiki\Video;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutEvents;
use Tests\TestCase;

/**
 * Class ThemeShowTest.
 */
class ThemeShowTest extends TestCase
{
    use WithFaker;
    use WithoutEvents;

    /**
     * By default, the Theme Show Endpoint shall return a Theme Resource.
     *
     * @return void
     */
    public function testDefault(): void
    {
        $theme = AnimeTheme::factory()
            ->for(Anime::factory())
            ->createOne();

        $theme->unsetRelations();

        $response = $this->get(route('api.animetheme.show', ['animetheme' => $theme]));

        $response->assertJson(
            json_decode(
                json_encode(
                    (new ThemeResource($theme, new ThemeReadQuery()))
                        ->response()
                        ->getData()
                ),
                true
            )
        );
    }

    /**
     * The Theme Show Endpoint shall return a Theme Resource for soft deleted themes.
     *
     * @return void
     */
    public function testSoftDelete(): void
    {
        $theme = AnimeTheme::factory()
            ->for(Anime::factory())
            ->createOne();

        $theme->delete();

        $theme->unsetRelations();

        $response = $this->get(route('api.animetheme.show', ['animetheme' => $theme]));

        $response->assertJson(
            json_decode(
                json_encode(
                    (new ThemeResource($theme, new ThemeReadQuery()))
                        ->response()
                        ->getData()
                ),
                true
            )
        );
    }

    /**
     * The Theme Show Endpoint shall allow inclusion of related resources.
     *
     * @return void
     */
    public function testAllowedIncludePaths(): void
    {
        $schema = new ThemeSchema();

        $allowedIncludes = collect($schema->allowedIncludes());

        $selectedIncludes = $allowedIncludes->random($this->faker->numberBetween(1, $allowedIncludes->count()));

        $includedPaths = $selectedIncludes->map(fn (AllowedInclude $include) => $include->path());

        $parameters = [
            IncludeParser::param() => $includedPaths->join(','),
        ];

        $theme = AnimeTheme::factory()
            ->for(Anime::factory())
            ->for(Song::factory())
            ->has(
                AnimeThemeEntry::factory()
                    ->count($this->faker->randomDigitNotNull())
                    ->has(Video::factory()->count($this->faker->randomDigitNotNull()))
            )
            ->createOne();

        $response = $this->get(route('api.animetheme.show', ['animetheme' => $theme] + $parameters));

        $response->assertJson(
            json_decode(
                json_encode(
                    (new ThemeResource($theme, new ThemeReadQuery($parameters)))
                        ->response()
                        ->getData()
                ),
                true
            )
        );
    }

    /**
     * The Theme Show Endpoint shall implement sparse fieldsets.
     *
     * @return void
     */
    public function testSparseFieldsets(): void
    {
        $schema = new ThemeSchema();

        $fields = collect($schema->fields());

        $includedFields = $fields->random($this->faker->numberBetween(1, $fields->count()));

        $parameters = [
            FieldParser::param() => [
                ThemeResource::$wrap => $includedFields->map(fn (Field $field) => $field->getKey())->join(','),
            ],
        ];

        $theme = AnimeTheme::factory()
            ->for(Anime::factory())
            ->count($this->faker->randomDigitNotNull())
            ->createOne();

        $theme->unsetRelations();

        $response = $this->get(route('api.animetheme.show', ['animetheme' => $theme] + $parameters));

        $response->assertJson(
            json_decode(
                json_encode(
                    (new ThemeResource($theme, new ThemeReadQuery($parameters)))
                        ->response()
                        ->getData()
                ),
                true
            )
        );
    }

    /**
     * The Theme Show Endpoint shall support constrained eager loading of anime by season.
     *
     * @return void
     */
    public function testAnimeBySeason(): void
    {
        $seasonFilter = AnimeSeason::getRandomInstance();

        $parameters = [
            FilterParser::param() => [
                Anime::ATTRIBUTE_SEASON => $seasonFilter->description,
            ],
            IncludeParser::param() => AnimeTheme::RELATION_ANIME,
        ];

        $theme = AnimeTheme::factory()
            ->for(Anime::factory())
            ->createOne();

        $theme->unsetRelations()->load([
            AnimeTheme::RELATION_ANIME => function (BelongsTo $query) use ($seasonFilter) {
                $query->where(Anime::ATTRIBUTE_SEASON, $seasonFilter->value);
            },
        ]);

        $response = $this->get(route('api.animetheme.show', ['animetheme' => $theme] + $parameters));

        $response->assertJson(
            json_decode(
                json_encode(
                    (new ThemeResource($theme, new ThemeReadQuery($parameters)))
                        ->response()
                        ->getData()
                ),
                true
            )
        );
    }

    /**
     * The Theme Show Endpoint shall support constrained eager loading of anime by year.
     *
     * @return void
     */
    public function testAnimeByYear(): void
    {
        $yearFilter = intval($this->faker->year());
        $excludedYear = $yearFilter + 1;

        $parameters = [
            FilterParser::param() => [
                Anime::ATTRIBUTE_YEAR => $yearFilter,
            ],
            IncludeParser::param() => AnimeTheme::RELATION_ANIME,
        ];

        $theme = AnimeTheme::factory()
            ->for(
                Anime::factory()
                    ->state([
                        Anime::ATTRIBUTE_YEAR => $this->faker->boolean() ? $yearFilter : $excludedYear,
                    ])
            )
            ->createOne();

        $theme->unsetRelations()->load([
            AnimeTheme::RELATION_ANIME => function (BelongsTo $query) use ($yearFilter) {
                $query->where(Anime::ATTRIBUTE_YEAR, $yearFilter);
            },
        ]);

        $response = $this->get(route('api.animetheme.show', ['animetheme' => $theme] + $parameters));

        $response->assertJson(
            json_decode(
                json_encode(
                    (new ThemeResource($theme, new ThemeReadQuery($parameters)))
                        ->response()
                        ->getData()
                ),
                true
            )
        );
    }

    /**
     * The Theme Show Endpoint shall support constrained eager loading of images by facet.
     *
     * @return void
     */
    public function testImagesByFacet(): void
    {
        $facetFilter = ImageFacet::getRandomInstance();

        $parameters = [
            FilterParser::param() => [
                Image::ATTRIBUTE_FACET => $facetFilter->description,
            ],
            IncludeParser::param() => AnimeTheme::RELATION_IMAGES,
        ];

        $theme = AnimeTheme::factory()
            ->for(
                Anime::factory()
                    ->has(Image::factory()->count($this->faker->randomDigitNotNull()))
            )
            ->createOne();

        $theme->unsetRelations()->load([
            AnimeTheme::RELATION_IMAGES => function (BelongsToMany $query) use ($facetFilter) {
                $query->where(Image::ATTRIBUTE_FACET, $facetFilter->value);
            },
        ]);

        $response = $this->get(route('api.animetheme.show', ['animetheme' => $theme] + $parameters));

        $response->assertJson(
            json_decode(
                json_encode(
                    (new ThemeResource($theme, new ThemeReadQuery($parameters)))
                        ->response()
                        ->getData()
                ),
                true
            )
        );
    }

    /**
     * The Theme Show Endpoint shall support constrained eager loading of entries by nsfw.
     *
     * @return void
     */
    public function testEntriesByNsfw(): void
    {
        $nsfwFilter = $this->faker->boolean();

        $parameters = [
            FilterParser::param() => [
                AnimeThemeEntry::ATTRIBUTE_NSFW => $nsfwFilter,
            ],
            IncludeParser::param() => AnimeTheme::RELATION_ENTRIES,
        ];

        $theme = AnimeTheme::factory()
            ->for(Anime::factory())
            ->has(AnimeThemeEntry::factory()->count($this->faker->randomDigitNotNull()))
            ->createOne();

        $theme->unsetRelations()->load([
            AnimeTheme::RELATION_ENTRIES => function (HasMany $query) use ($nsfwFilter) {
                $query->where(AnimeThemeEntry::ATTRIBUTE_NSFW, $nsfwFilter);
            },
        ]);

        $response = $this->get(route('api.animetheme.show', ['animetheme' => $theme] + $parameters));

        $response->assertJson(
            json_decode(
                json_encode(
                    (new ThemeResource($theme, new ThemeReadQuery($parameters)))
                        ->response()
                        ->getData()
                ),
                true
            )
        );
    }

    /**
     * The Anime Index Endpoint shall support constrained eager loading of entries by spoiler.
     *
     * @return void
     */
    public function testEntriesBySpoiler(): void
    {
        $spoilerFilter = $this->faker->boolean();

        $parameters = [
            FilterParser::param() => [
                AnimeThemeEntry::ATTRIBUTE_SPOILER => $spoilerFilter,
            ],
            IncludeParser::param() => AnimeTheme::RELATION_ENTRIES,
        ];

        $theme = AnimeTheme::factory()
            ->for(Anime::factory())
            ->has(AnimeThemeEntry::factory()->count($this->faker->randomDigitNotNull()))
            ->createOne();

        $theme->unsetRelations()->load([
            AnimeTheme::RELATION_ENTRIES => function (HasMany $query) use ($spoilerFilter) {
                $query->where(AnimeThemeEntry::ATTRIBUTE_SPOILER, $spoilerFilter);
            },
        ]);

        $response = $this->get(route('api.animetheme.show', ['animetheme' => $theme] + $parameters));

        $response->assertJson(
            json_decode(
                json_encode(
                    (new ThemeResource($theme, new ThemeReadQuery($parameters)))
                        ->response()
                        ->getData()
                ),
                true
            )
        );
    }

    /**
     * The Anime Index Endpoint shall support constrained eager loading of entries by version.
     *
     * @return void
     */
    public function testEntriesByVersion(): void
    {
        $versionFilter = $this->faker->randomDigitNotNull();
        $excludedVersion = $versionFilter + 1;

        $parameters = [
            FilterParser::param() => [
                AnimeThemeEntry::ATTRIBUTE_VERSION => $versionFilter,
            ],
            IncludeParser::param() => AnimeTheme::RELATION_ENTRIES,
        ];

        $theme = AnimeTheme::factory()
            ->for(Anime::factory())
            ->has(
                AnimeThemeEntry::factory()
                    ->count($this->faker->randomDigitNotNull())
                    ->state(new Sequence(
                        [AnimeThemeEntry::ATTRIBUTE_VERSION => $versionFilter],
                        [AnimeThemeEntry::ATTRIBUTE_VERSION => $excludedVersion],
                    ))
            )
            ->createOne();

        $theme->unsetRelations()->load([
            AnimeTheme::RELATION_ENTRIES => function (HasMany $query) use ($versionFilter) {
                $query->where(AnimeThemeEntry::ATTRIBUTE_VERSION, $versionFilter);
            },
        ]);

        $response = $this->get(route('api.animetheme.show', ['animetheme' => $theme] + $parameters));

        $response->assertJson(
            json_decode(
                json_encode(
                    (new ThemeResource($theme, new ThemeReadQuery($parameters)))
                        ->response()
                        ->getData()
                ),
                true
            )
        );
    }

    /**
     * The Theme Show Endpoint shall support constrained eager loading of videos by lyrics.
     *
     * @return void
     */
    public function testVideosByLyrics(): void
    {
        $lyricsFilter = $this->faker->boolean();

        $parameters = [
            FilterParser::param() => [
                Video::ATTRIBUTE_LYRICS => $lyricsFilter,
            ],
            IncludeParser::param() => AnimeTheme::RELATION_VIDEOS,
        ];

        $theme = AnimeTheme::factory()
            ->for(Anime::factory())
            ->has(
                AnimeThemeEntry::factory()
                    ->count($this->faker->randomDigitNotNull())
                    ->has(Video::factory()->count($this->faker->randomDigitNotNull()))
            )
            ->createOne();

        $theme->unsetRelations()->load([
            AnimeTheme::RELATION_VIDEOS => function (BelongsToMany $query) use ($lyricsFilter) {
                $query->where(Video::ATTRIBUTE_LYRICS, $lyricsFilter);
            },
        ]);

        $response = $this->get(route('api.animetheme.show', ['animetheme' => $theme] + $parameters));

        $response->assertJson(
            json_decode(
                json_encode(
                    (new ThemeResource($theme, new ThemeReadQuery($parameters)))
                        ->response()
                        ->getData()
                ),
                true
            )
        );
    }

    /**
     * The Theme Show Endpoint shall support constrained eager loading of videos by nc.
     *
     * @return void
     */
    public function testVideosByNc(): void
    {
        $ncFilter = $this->faker->boolean();

        $parameters = [
            FilterParser::param() => [
                Video::ATTRIBUTE_NC => $ncFilter,
            ],
            IncludeParser::param() => AnimeTheme::RELATION_VIDEOS,
        ];

        $theme = AnimeTheme::factory()
            ->for(Anime::factory())
            ->has(
                AnimeThemeEntry::factory()
                    ->count($this->faker->randomDigitNotNull())
                    ->has(Video::factory()->count($this->faker->randomDigitNotNull()))
            )
            ->createOne();

        $theme->unsetRelations()->load([
            AnimeTheme::RELATION_VIDEOS => function (BelongsToMany $query) use ($ncFilter) {
                $query->where(Video::ATTRIBUTE_NC, $ncFilter);
            },
        ]);

        $response = $this->get(route('api.animetheme.show', ['animetheme' => $theme] + $parameters));

        $response->assertJson(
            json_decode(
                json_encode(
                    (new ThemeResource($theme, new ThemeReadQuery($parameters)))
                        ->response()
                        ->getData()
                ),
                true
            )
        );
    }

    /**
     * The Theme Show Endpoint shall support constrained eager loading of videos by overlap.
     *
     * @return void
     */
    public function testVideosByOverlap(): void
    {
        $overlapFilter = VideoOverlap::getRandomInstance();

        $parameters = [
            FilterParser::param() => [
                Video::ATTRIBUTE_OVERLAP => $overlapFilter->description,
            ],
            IncludeParser::param() => AnimeTheme::RELATION_VIDEOS,
        ];

        $theme = AnimeTheme::factory()
            ->for(Anime::factory())
            ->has(
                AnimeThemeEntry::factory()
                    ->count($this->faker->randomDigitNotNull())
                    ->has(Video::factory()->count($this->faker->randomDigitNotNull()))
            )
            ->createOne();

        $theme->unsetRelations()->load([
            AnimeTheme::RELATION_VIDEOS => function (BelongsToMany $query) use ($overlapFilter) {
                $query->where(Video::ATTRIBUTE_OVERLAP, $overlapFilter->value);
            },
        ]);

        $response = $this->get(route('api.animetheme.show', ['animetheme' => $theme] + $parameters));

        $response->assertJson(
            json_decode(
                json_encode(
                    (new ThemeResource($theme, new ThemeReadQuery($parameters)))
                        ->response()
                        ->getData()
                ),
                true
            )
        );
    }

    /**
     * The Theme Show Endpoint shall support constrained eager loading of videos by resolution.
     *
     * @return void
     */
    public function testVideosByResolution(): void
    {
        $resolutionFilter = $this->faker->randomNumber();
        $excludedResolution = $resolutionFilter + 1;

        $parameters = [
            FilterParser::param() => [
                Video::ATTRIBUTE_RESOLUTION => $resolutionFilter,
            ],
            IncludeParser::param() => AnimeTheme::RELATION_VIDEOS,
        ];

        $theme = AnimeTheme::factory()
            ->for(Anime::factory())
            ->has(
                AnimeThemeEntry::factory()
                    ->count($this->faker->randomDigitNotNull())
                    ->has(
                        Video::factory()
                            ->count($this->faker->randomDigitNotNull())
                            ->state(new Sequence(
                                [Video::ATTRIBUTE_RESOLUTION => $resolutionFilter],
                                [Video::ATTRIBUTE_RESOLUTION => $excludedResolution],
                            ))
                    )
            )
            ->createOne();

        $theme->unsetRelations()->load([
            AnimeTheme::RELATION_VIDEOS => function (BelongsToMany $query) use ($resolutionFilter) {
                $query->where(Video::ATTRIBUTE_RESOLUTION, $resolutionFilter);
            },
        ]);

        $response = $this->get(route('api.animetheme.show', ['animetheme' => $theme] + $parameters));

        $response->assertJson(
            json_decode(
                json_encode(
                    (new ThemeResource($theme, new ThemeReadQuery($parameters)))
                        ->response()
                        ->getData()
                ),
                true
            )
        );
    }

    /**
     * The Theme Show Endpoint shall support constrained eager loading of videos by source.
     *
     * @return void
     */
    public function testVideosBySource(): void
    {
        $sourceFilter = VideoSource::getRandomInstance();

        $parameters = [
            FilterParser::param() => [
                Video::ATTRIBUTE_SOURCE => $sourceFilter->description,
            ],
            IncludeParser::param() => AnimeTheme::RELATION_VIDEOS,
        ];

        $theme = AnimeTheme::factory()
            ->for(Anime::factory())
            ->has(
                AnimeThemeEntry::factory()
                    ->count($this->faker->randomDigitNotNull())
                    ->has(Video::factory()->count($this->faker->randomDigitNotNull()))
            )
            ->createOne();

        $theme->unsetRelations()->load([
            AnimeTheme::RELATION_VIDEOS => function (BelongsToMany $query) use ($sourceFilter) {
                $query->where(Video::ATTRIBUTE_SOURCE, $sourceFilter->value);
            },
        ]);

        $response = $this->get(route('api.animetheme.show', ['animetheme' => $theme] + $parameters));

        $response->assertJson(
            json_decode(
                json_encode(
                    (new ThemeResource($theme, new ThemeReadQuery($parameters)))
                        ->response()
                        ->getData()
                ),
                true
            )
        );
    }

    /**
     * The Theme Show Endpoint shall support constrained eager loading of videos by subbed.
     *
     * @return void
     */
    public function testVideosBySubbed(): void
    {
        $subbedFilter = $this->faker->boolean();

        $parameters = [
            FilterParser::param() => [
                Video::ATTRIBUTE_SUBBED => $subbedFilter,
            ],
            IncludeParser::param() => AnimeTheme::RELATION_VIDEOS,
        ];

        $theme = AnimeTheme::factory()
            ->for(Anime::factory())
            ->has(
                AnimeThemeEntry::factory()
                    ->count($this->faker->randomDigitNotNull())
                    ->has(Video::factory()->count($this->faker->randomDigitNotNull()))
            )
            ->createOne();

        $theme->unsetRelations()->load([
            AnimeTheme::RELATION_VIDEOS => function (BelongsToMany $query) use ($subbedFilter) {
                $query->where(Video::ATTRIBUTE_SUBBED, $subbedFilter);
            },
        ]);

        $response = $this->get(route('api.animetheme.show', ['animetheme' => $theme] + $parameters));

        $response->assertJson(
            json_decode(
                json_encode(
                    (new ThemeResource($theme, new ThemeReadQuery($parameters)))
                        ->response()
                        ->getData()
                ),
                true
            )
        );
    }

    /**
     * The Theme Show Endpoint shall support constrained eager loading of videos by uncen.
     *
     * @return void
     */
    public function testVideosByUncen(): void
    {
        $uncenFilter = $this->faker->boolean();

        $parameters = [
            FilterParser::param() => [
                Video::ATTRIBUTE_UNCEN => $uncenFilter,
            ],
            IncludeParser::param() => AnimeTheme::RELATION_VIDEOS,
        ];

        $theme = AnimeTheme::factory()
            ->for(Anime::factory())
            ->has(
                AnimeThemeEntry::factory()
                    ->count($this->faker->randomDigitNotNull())
                    ->has(Video::factory()->count($this->faker->randomDigitNotNull()))
            )
            ->createOne();

        $theme->unsetRelations()->load([
            AnimeTheme::RELATION_VIDEOS => function (BelongsToMany $query) use ($uncenFilter) {
                $query->where(Video::ATTRIBUTE_UNCEN, $uncenFilter);
            },
        ]);

        $response = $this->get(route('api.animetheme.show', ['animetheme' => $theme] + $parameters));

        $response->assertJson(
            json_decode(
                json_encode(
                    (new ThemeResource($theme, new ThemeReadQuery($parameters)))
                        ->response()
                        ->getData()
                ),
                true
            )
        );
    }
}
