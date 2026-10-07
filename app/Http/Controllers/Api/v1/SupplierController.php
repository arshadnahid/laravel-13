<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Supplier\CreateSupplier;
use App\Http\Requests\Api\Supplier\FilterSupplier;
use App\Http\Requests\Api\Supplier\UpdateSupplier;
use App\Http\Resources\Supplier\SupplierResource;
use App\Services\SupplierService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

class SupplierController extends Controller
{
    use ApiResponses;

    public function __construct(private readonly SupplierService $supplierService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(FilterSupplier $request): JsonResponse
    {
        $suppliers = $this->supplierService->getAllSuppliers($request->toDTO());

        return $this->sendResponse('Suppliers retrieved successfully', SupplierResource::collection($suppliers));
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateSupplier $request): JsonResponse
    {
        $supplier = $this->supplierService->createSupplier($request->toDTO());

        return $this->sendCreatedResponse('Supplier created successfully', new SupplierResource($supplier));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $supplier = $this->supplierService->getSupplierById($id);

        return $this->sendResponse('Supplier retrieved successfully', new SupplierResource($supplier));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSupplier $request, string $id): JsonResponse
    {
        $supplier = $this->supplierService->updateSupplier($id, $request->toDTO());

        return $this->sendUpdatedResponse('Supplier updated successfully', new SupplierResource($supplier));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $this->supplierService->deleteSupplier($id);

        return $this->sendResponse('Supplier deleted successfully');
    }
}
