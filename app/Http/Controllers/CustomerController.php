<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;

class CustomerController extends Controller
{
    /**
     * Display a listing of the customers.
     */
    public function index()
    {
        Gate::authorize('viewAny', Customer::class);
        return CustomerResource::collection(Customer::all());
    }

    /**
     * Store a newly created customer in the database.
     */
    public function store(StoreCustomerRequest $request)
    {
        Gate::authorize('create', Customer::class);
        $customer = Customer::create($request->validated());
        return new CustomerResource($customer);
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer)
    {
        Gate::authorize('view', $customer);
        return new CustomerResource($customer);
    }

    /**
     * Update the specified customer in the database.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        Gate::authorize('update', $customer);
        $customer->update($request->validated());
        return new CustomerResource($customer);
    }

    /**
     * Remove the specified customer from the database (Soft Delete).
     */
    public function destroy(Customer $customer)
    {
        Gate::authorize('delete', $customer);
        $customer->delete();
        return response()->json(['message' => 'Customer deleted successfully']);
    }
}
