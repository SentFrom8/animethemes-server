<?php

declare(strict_types=1);

namespace App\Http\Api\Query\Billing\Balance;

use App\Http\Api\Query\Base\EloquentWriteQuery;
use App\Http\Api\Schema\Billing\BalanceSchema;
use App\Http\Api\Schema\EloquentSchema;
use App\Http\Resources\BaseResource;
use App\Http\Resources\Billing\Resource\BalanceResource;
use App\Models\Billing\Balance;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class BalanceWriteQuery.
 */
class BalanceWriteQuery extends EloquentWriteQuery
{
    /**
     * Get the resource schema.
     *
     * @return EloquentSchema
     */
    public function schema(): EloquentSchema
    {
        return new BalanceSchema();
    }

    /**
     * Get the query builder of the resource.
     *
     * @return Builder
     */
    public function builder(): Builder
    {
        return Balance::query();
    }

    /**
     * Get the json resource.
     *
     * @param  mixed  $resource
     * @return BaseResource
     */
    public function resource(mixed $resource): BaseResource
    {
        return new BalanceResource($resource, new BalanceReadQuery());
    }
}
