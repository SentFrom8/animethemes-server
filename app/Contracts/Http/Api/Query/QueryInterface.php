<?php

declare(strict_types=1);

namespace App\Contracts\Http\Api\Query;

use App\Http\Api\Schema\Schema;

/**
 * Interface QueryInterface.
 */
interface QueryInterface
{
    /**
     * Get the resource schema.
     *
     * @return Schema
     */
    public function schema(): Schema;
}
