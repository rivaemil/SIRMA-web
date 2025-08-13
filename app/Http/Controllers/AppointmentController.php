<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index()
    {
        return response()->json(
            Appointment::with('client', 'vehicle')->get(),
            200
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'scheduled_at' => 'required|date|after_or_equal:today'
        ]);

        $appointment = Appointment::create($validated);

        return response()->json($appointment, 201);
    }

    public function show($id)
    {
        $appointment = Appointment::with('client', 'vehicle')->findOrFail($id);
        return response()->json($appointment, 200);
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'client_id' => 'sometimes|exists:clients,id',
            'vehicle_id' => 'sometimes|exists:vehicles,id',
            'scheduled_at' => 'sometimes|date|after_or_equal:today'
        ]);

        $appointment->update($validated);

        return response()->json($appointment, 200);
    }

    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();

        return response()->json(null, 204);
    }

    public function myAppointments(Request $request)
{
    $clientId = $request->user()->client->id; // asumiendo relación User->Client
    return response()->json(
        Appointment::with('client', 'vehicle')
            ->where('client_id', $clientId)
            ->get(),
        200
    );
}
}
