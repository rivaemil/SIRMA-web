<?php

namespace App\Http\Controllers;

use App\Models\Mechanic;
use Illuminate\Http\Request;

class MechanicController extends Controller
{
    public function index()
    {
        return response()->json(
            Mechanic::with('user')->get(),
            200
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255'
        ]);

        $mechanic = Mechanic::create($validated);

        return response()->json($mechanic, 201);
    }

    public function show($id)
    {
        $mechanic = Mechanic::with('user')->findOrFail($id);
        return response()->json($mechanic, 200);
    }

    public function update(Request $request, $id)
    {
        $mechanic = Mechanic::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'name' => 'sometimes|string|max:255'
        ]);

        $mechanic->update($validated);

        return response()->json($mechanic, 200);
    }

    public function destroy($id)
    {
        $mechanic = Mechanic::findOrFail($id);
        $mechanic->delete();

        return response()->json(null, 204);
    }
}
