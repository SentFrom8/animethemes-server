<?php

declare(strict_types=1);

namespace App\Nova\Lenses\Studio\Resource;

use App\Enums\Models\Wiki\ResourceSite;
use App\Nova\Lenses\Studio\StudioResourceLens;

/**
 * Class StudioAniDbResourceLens.
 */
class StudioAniDbResourceLens extends StudioResourceLens
{
    /**
     * The resource site.
     *
     * @return ResourceSite
     */
    protected static function site(): ResourceSite
    {
        return ResourceSite::ANIDB();
    }

    /**
     * Get the URI key for the lens.
     *
     * @return string
     *
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function uriKey(): string
    {
        return 'studio-anidb-resource-lens';
    }
}
