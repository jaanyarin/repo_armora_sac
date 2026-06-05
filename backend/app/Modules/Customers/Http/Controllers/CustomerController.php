<?php

namespace App\Modules\Customers\Http\Controllers;

use App\Modules\Customers\Http\Requests\StoreCustomerRequest;
use App\Modules\Customers\Http\Requests\UpdateCustomerRequest;
use App\Modules\Customers\Http\Resources\CustomerResource;
use App\Modules\Customers\Models\Customer;
use App\Modules\Customers\Services\CustomerService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class CustomerController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly CustomerService $customerService,
    ) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Customer::class);
        $customers = $this->customerService->paginate(request()->only(['search', 'activo', 'per_page']));
        return response()->json($customers);
    }

    public function show(int $id): JsonResponse
    {
        $customer = $this->customerService->findById($id);
        if (!$customer) {
            return response()->json(['message' => 'Cliente no encontrado.'], 404);
        }
        return response()->json(new CustomerResource($customer));
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customerService->create($request->validated());
        return response()->json(new CustomerResource($customer), 201);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $customer = $this->customerService->update($customer, $request->validated());
        return response()->json(new CustomerResource($customer));
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->customerService->delete($customer);
        return response()->json(['message' => 'Cliente eliminado.']);
    }
}
