<?php

declare(strict_types=1);

namespace App\Http\Api\Schema\Pivot;

use App\Http\Api\Field\Base\CreatedAtField;
use App\Http\Api\Field\Base\UpdatedAtField;
use App\Http\Api\Field\Field;
use App\Http\Api\Field\Pivot\AnimeImage\AnimeImageAnimeIdField;
use App\Http\Api\Field\Pivot\AnimeImage\AnimeImageImageIdField;
use App\Http\Api\Include\AllowedInclude;
use App\Http\Api\Schema\EloquentSchema;
use App\Http\Api\Schema\Wiki\AnimeSchema;
use App\Http\Api\Schema\Wiki\ImageSchema;
use App\Http\Resources\Pivot\Resource\AnimeImageResource;
use App\Pivots\AnimeImage;

/**
 * Class AnimeImageSchema.
 */
class AnimeImageSchema extends EloquentSchema
{
    /**
     * The model this schema represents.
     *
     * @return string
     */
    public function model(): string
    {
        return AnimeImage::class;
    }

    /**
     * Get the type of the resource.
     *
     * @return string
     */
    public function type(): string
    {
        return AnimeImageResource::$wrap;
    }

    /**
     * Get the allowed includes.
     *
     * @return AllowedInclude[]
     */
    public function allowedIncludes(): array
    {
        return [
            new AllowedInclude(new AnimeSchema(), AnimeImage::RELATION_ANIME),
            new AllowedInclude(new ImageSchema(), AnimeImage::RELATION_IMAGE),
        ];
    }

    /**
     * Get the direct fields of the resource.
     *
     * @return Field[]
     *
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function fields(): array
    {
        return [
            new CreatedAtField(),
            new UpdatedAtField(),
            new AnimeImageAnimeIdField(),
            new AnimeImageImageIdField(),
        ];
    }
}
