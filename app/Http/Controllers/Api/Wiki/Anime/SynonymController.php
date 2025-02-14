<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Wiki\Anime;

use App\Enums\Http\Api\Paging\PaginationStrategy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Wiki\Anime\Synonym\SynonymDestroyRequest;
use App\Http\Requests\Api\Wiki\Anime\Synonym\SynonymForceDeleteRequest;
use App\Http\Requests\Api\Wiki\Anime\Synonym\SynonymIndexRequest;
use App\Http\Requests\Api\Wiki\Anime\Synonym\SynonymRestoreRequest;
use App\Http\Requests\Api\Wiki\Anime\Synonym\SynonymShowRequest;
use App\Http\Requests\Api\Wiki\Anime\Synonym\SynonymStoreRequest;
use App\Http\Requests\Api\Wiki\Anime\Synonym\SynonymUpdateRequest;
use App\Models\Wiki\Anime\AnimeSynonym;
use Illuminate\Http\JsonResponse;
use Spatie\RouteDiscovery\Attributes\Route;

/**
 * Class SynonymController.
 */
class SynonymController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  SynonymIndexRequest  $request
     * @return JsonResponse
     */
    #[Route(fullUri: 'animesynonym', name: 'animesynonym.index')]
    public function index(SynonymIndexRequest $request): JsonResponse
    {
        $query = $request->getQuery();

        if ($query->hasSearchCriteria()) {
            return $query->search(PaginationStrategy::OFFSET())->toResponse($request);
        }

        return $query->index()->toResponse($request);
    }

    /**
     * Store a newly created resource.
     *
     * @param  SynonymStoreRequest  $request
     * @return JsonResponse
     */
    #[Route(fullUri: 'animesynonym', name: 'animesynonym.store', middleware: 'auth:sanctum')]
    public function store(SynonymStoreRequest $request): JsonResponse
    {
        $resource = $request->getQuery()->store();

        return $resource->toResponse($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  SynonymShowRequest  $request
     * @param  AnimeSynonym  $synonym
     * @return JsonResponse
     */
    #[Route(fullUri: 'animesynonym/{animesynonym}', name: 'animesynonym.show')]
    public function show(SynonymShowRequest $request, AnimeSynonym $synonym): JsonResponse
    {
        $resource = $request->getQuery()->show($synonym);

        return $resource->toResponse($request);
    }

    /**
     * Update the specified resource.
     *
     * @param  SynonymUpdateRequest  $request
     * @param  AnimeSynonym  $synonym
     * @return JsonResponse
     */
    #[Route(fullUri: 'animesynonym/{animesynonym}', name: 'animesynonym.update', middleware: 'auth:sanctum')]
    public function update(SynonymUpdateRequest $request, AnimeSynonym $synonym): JsonResponse
    {
        $resource = $request->getQuery()->update($synonym);

        return $resource->toResponse($request);
    }

    /**
     * Remove the specified resource.
     *
     * @param  SynonymDestroyRequest  $request
     * @param  AnimeSynonym  $synonym
     * @return JsonResponse
     */
    #[Route(fullUri: 'animesynonym/{animesynonym}', name: 'animesynonym.destroy', middleware: 'auth:sanctum')]
    public function destroy(SynonymDestroyRequest $request, AnimeSynonym $synonym): JsonResponse
    {
        $resource = $request->getQuery()->destroy($synonym);

        return $resource->toResponse($request);
    }

    /**
     * Restore the specified resource.
     *
     * @param  SynonymRestoreRequest  $request
     * @param  AnimeSynonym  $synonym
     * @return JsonResponse
     */
    #[Route(method: 'patch', fullUri: 'restore/animesynonym/{animesynonym}', name: 'animesynonym.restore', middleware: 'auth:sanctum')]
    public function restore(SynonymRestoreRequest $request, AnimeSynonym $synonym): JsonResponse
    {
        $resource = $request->getQuery()->restore($synonym);

        return $resource->toResponse($request);
    }

    /**
     * Hard-delete the specified resource.
     *
     * @param  SynonymForceDeleteRequest  $request
     * @param  AnimeSynonym  $synonym
     * @return JsonResponse
     */
    #[Route(method: 'delete', fullUri: 'forceDelete/animesynonym/{animesynonym}', name: 'animesynonym.forceDelete', middleware: 'auth:sanctum')]
    public function forceDelete(SynonymForceDeleteRequest $request, AnimeSynonym $synonym): JsonResponse
    {
        return $request->getQuery()->forceDelete($synonym);
    }
}
