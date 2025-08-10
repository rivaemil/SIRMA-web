<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Client;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index()
    {
        return response()->json(
            Log::with('client', 'vehicle', 'mechanic')->get(),
            200
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'vehicle_id' => 'required|exists:vehicles,id',
            'client_id' => 'required|exists:clients,id',
            'mechanic_id' => 'required|exists:mechanics,id',
            'description' => 'required|string'
        ]);

        $log = Log::create($validated);

        return response()->json($log, 201);
    }

    public function show($id)
    {
        $log = Log::with('client', 'vehicle', 'mechanic')->findOrFail($id);
        return response()->json($log, 200);
    }

    public function update(Request $request, $id)
    {
        $log = Log::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'vehicle_id' => 'sometimes|exists:vehicles,id',
            'client_id' => 'sometimes|exists:clients,id',
            'mechanic_id' => 'sometimes|exists:mechanics,id',
            'description' => 'sometimes|string'
        ]);

        $log->update($validated);

        return response()->json($log, 200);
    }

    public function destroy($id)
    {
        $log = Log::findOrFail($id);
        $log->delete();

        return response()->json(null, 204);
    }

    public function clientLogs(Request $request)
    {
        $client = Client::where('user_id', $request->user()->id)->firstOrFail();

        $logs = Log::with('vehicle', 'mechanic')
            ->where('client_id', $client->id)
            ->get();

        return response()->json($logs, 200);
    }
}
