<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tenant\Customer\StoreCustomerRequest;
use App\Http\Requests\Api\Tenant\Customer\UpdateCustomerRequest;
use App\Http\Resources\Api\Tenant\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return CustomerResource::collection(Customer::query()->latest()->paginate(perPage()));
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = Customer::create($request->validated());

        return $this->success(
            'Customer created successfully.',
            201,
            ["customer" => new CustomerResource($customer)]
        );
    }

    public function show(Customer $customer)
    {
        //
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $customer->update($request->validated());

        return $this->success('Customer updated successfully.', data: [
            'customer' => new CustomerResource($customer),
        ]);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return $this->success('Customer deleted successfully.');
    }
}
