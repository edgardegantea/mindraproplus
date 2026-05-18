<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlanRequest;
use App\Models\Plan;

class PlanController extends Controller
{
    public function index()
    {
        return response()->json([
            'ok' => true,
            'plans' => Plan::orderBy('price_cents')->get(),
        ]);
    }

    public function show(Plan $plan)
    {
        return response()->json([
            'ok' => true,
            'plan' => $plan,
        ]);
    }

    public function store(PlanRequest $request)
    {
        $plan = Plan::create($request->validated());

        return response()->json([
            'ok' => true,
            'plan' => $plan,
        ], 201);
    }

    public function update(PlanRequest $request, Plan $plan)
    {
        $plan->update($request->validated());

        return response()->json([
            'ok' => true,
            'plan' => $plan,
        ]);
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Plan eliminado correctamente.',
        ]);
    }
}
