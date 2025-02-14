<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Wiki;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Wiki\Image\ImageDestroyRequest;
use App\Http\Requests\Api\Wiki\Image\ImageForceDeleteRequest;
use App\Http\Requests\Api\Wiki\Image\ImageIndexRequest;
use App\Http\Requests\Api\Wiki\Image\ImageRestoreRequest;
use App\Http\Requests\Api\Wiki\Image\ImageShowRequest;
use App\Http\Requests\Api\Wiki\Image\ImageStoreRequest;
use App\Http\Requests\Api\Wiki\Image\ImageUpdateRequest;
use App\Models\Wiki\Image;
use Illuminate\Http\JsonResponse;
use Spatie\RouteDiscovery\Attributes\Route;

/**
 * Class ImageController.
 */
class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  ImageIndexRequest  $request
     * @return JsonResponse
     */
    #[Route(fullUri: 'image', name: 'image.index')]
    public function index(ImageIndexRequest $request): JsonResponse
    {
        $images = $request->getQuery()->index();

        return $images->toResponse($request);
    }

    /**
     * Store a newly created resource.
     *
     * @param  ImageStoreRequest  $request
     * @return JsonResponse
     */
    #[Route(fullUri: 'image', name: 'image.store', middleware: 'auth:sanctum')]
    public function store(ImageStoreRequest $request): JsonResponse
    {
        $resource = $request->getQuery()->store();

        return $resource->toResponse($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  ImageShowRequest  $request
     * @param  Image  $image
     * @return JsonResponse
     */
    #[Route(fullUri: 'image/{image}', name: 'image.show')]
    public function show(ImageShowRequest $request, Image $image): JsonResponse
    {
        $resource = $request->getQuery()->show($image);

        return $resource->toResponse($request);
    }

    /**
     * Update the specified resource.
     *
     * @param  ImageUpdateRequest  $request
     * @param  Image  $image
     * @return JsonResponse
     */
    #[Route(fullUri: 'image/{image}', name: 'image.update', middleware: 'auth:sanctum')]
    public function update(ImageUpdateRequest $request, Image $image): JsonResponse
    {
        $resource = $request->getQuery()->update($image);

        return $resource->toResponse($request);
    }

    /**
     * Remove the specified resource.
     *
     * @param  ImageDestroyRequest  $request
     * @param  Image  $image
     * @return JsonResponse
     */
    #[Route(fullUri: 'image/{image}', name: 'image.destroy', middleware: 'auth:sanctum')]
    public function destroy(ImageDestroyRequest $request, Image $image): JsonResponse
    {
        $resource = $request->getQuery()->destroy($image);

        return $resource->toResponse($request);
    }

    /**
     * Restore the specified resource.
     *
     * @param  ImageRestoreRequest  $request
     * @param  Image  $image
     * @return JsonResponse
     */
    #[Route(method: 'patch', fullUri: 'restore/image/{image}', name: 'image.restore', middleware: 'auth:sanctum')]
    public function restore(ImageRestoreRequest $request, Image $image): JsonResponse
    {
        $resource = $request->getQuery()->restore($image);

        return $resource->toResponse($request);
    }

    /**
     * Hard-delete the specified resource.
     *
     * @param  ImageForceDeleteRequest  $request
     * @param  Image  $image
     * @return JsonResponse
     */
    #[Route(method: 'delete', fullUri: 'forceDelete/image/{image}', name: 'image.forceDelete', middleware: 'auth:sanctum')]
    public function forceDelete(ImageForceDeleteRequest $request, Image $image): JsonResponse
    {
        return $request->getQuery()->forceDelete($image);
    }
}
