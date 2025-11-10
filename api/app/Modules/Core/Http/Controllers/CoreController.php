<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Domain\Serivce;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CoreController extends Controller
{
    public function __construct(
        private readonly Serivce $service,
        private readonly string $resourceClass,
        private readonly string $collectionClass,
        private readonly string $storeRequestClass,
        private readonly ?string $updateRequestClass = null
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $filters = $request->except(['page', 'per_page']);
        $paginator = $this->service->paginate($filters, $perPage);
        $collectionClass = $this->collectionClass;

        return (new $collectionClass($paginator))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function show(int|string $id): JsonResponse
    {
        $model = $this->service->findOrFail($id);
        $resourceClass = $this->resourceClass;

        return (new $resourceClass($model))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $formRequest = $this->resolveFormRequest($request, $this->storeRequestClass);
        $resourceClass = $this->resourceClass;

        $model = DB::transaction(function () use ($formRequest) {
            return $this->service->create($formRequest->validated());
        });

        return (new $resourceClass($model))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        $requestClass = $this->updateRequestClass ?? $this->storeRequestClass;
        $formRequest = $this->resolveFormRequest($request, $requestClass);
        $resourceClass = $this->resourceClass;

        $model = DB::transaction(function () use ($id, $formRequest) {
            $model = $this->service->findOrFail($id);
            return $this->service->update($model, $formRequest->validated());
        });

        return (new $resourceClass($model))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function destroy(int|string $id): Response
    {
        DB::transaction(function () use ($id) {
            $model = $this->service->findOrFail($id);
            $this->service->delete($model);
        });

        return response()->noContent();
    }

    protected function resolveFormRequest(Request $request, string $requestClass): FormRequest
    {
        /** @var FormRequest $formRequest */
        $formRequest = $requestClass::createFromBase($request);

        if (method_exists($formRequest, 'setContainer')) {
            $formRequest->setContainer(app());
        }

        $formRequest->validateResolved();

        return $formRequest;
    }
}

