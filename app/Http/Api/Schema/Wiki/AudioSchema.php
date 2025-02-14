<?php

declare(strict_types=1);

namespace App\Http\Api\Schema\Wiki;

use App\Http\Api\Field\Base\IdField;
use App\Http\Api\Field\Field;
use App\Http\Api\Field\Wiki\Audio\AudioBasenameField;
use App\Http\Api\Field\Wiki\Audio\AudioFilenameField;
use App\Http\Api\Field\Wiki\Audio\AudioLinkField;
use App\Http\Api\Field\Wiki\Audio\AudioMimeTypeField;
use App\Http\Api\Field\Wiki\Audio\AudioPathField;
use App\Http\Api\Field\Wiki\Audio\AudioSizeField;
use App\Http\Api\Include\AllowedInclude;
use App\Http\Api\Schema\EloquentSchema;
use App\Http\Resources\Wiki\Resource\AudioResource;
use App\Models\Wiki\Audio;

/**
 * Class AudioSchema.
 */
class AudioSchema extends EloquentSchema
{
    /**
     * The model this schema represents.
     *
     * @return string
     */
    public function model(): string
    {
        return Audio::class;
    }

    /**
     * Get the type of the resource.
     *
     * @return string
     */
    public function type(): string
    {
        return AudioResource::$wrap;
    }

    /**
     * Get the allowed includes.
     *
     * @return AllowedInclude[]
     */
    public function allowedIncludes(): array
    {
        return [
            new AllowedInclude(new VideoSchema(), Audio::RELATION_VIDEOS),
        ];
    }

    /**
     * Get the direct fields of the resource.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                new IdField(Audio::ATTRIBUTE_ID),
                new AudioBasenameField(),
                new AudioFilenameField(),
                new AudioMimeTypeField(),
                new AudioPathField(),
                new AudioSizeField(),
                new AudioLinkField(),
            ],
        );
    }
}
