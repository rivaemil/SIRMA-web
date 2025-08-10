<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    /**
     * Listar todos los clientes
     */
    public function index()
    {
        return response()->json(Client::with('user')->get(), 200);
    }

    /**
     * Crear un nuevo cliente y usuario vinculado
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string|max:20',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6'
        ]);

        // Crear usuario con rol "client"
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'rol'      => 'client'
        ]);

        // Crear cliente vinculado al usuario
        $client = Client::create([
            'user_id' => $user->id,
            'name'    => $request->name,
            'phone'   => $request->phone,
            'email'   => $request->email
        ]);

        return response()->json([
            'message' => 'Cliente creado correctamente',
            'client'  => $client
        ], 201);
    }

    /**
     * Mostrar un cliente
     */
    public function show($id)
    {
        $client = Client::with('user')->findOrFail($id);
        return response()->json($client, 200);
    }

    /**
     * Actualizar datos del cliente y usuario
     */
    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        $user   = $client->user;

        $request->validate([
            'name'  => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:6'
        ]);

        // Actualizar usuario
        $user->update([
            'name'     => $request->name ?? $user->name,
            'email'    => $request->email ?? $user->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password
        ]);

        // Actualizar cliente
        $client->update([
            'name'  => $request->name ?? $client->name,
            'phone' => $request->phone ?? $client->phone,
            'email' => $request->email ?? $client->email
        ]);

        return response()->json([
            'message' => 'Cliente actualizado correctamente',
            'client'  => $client
        ], 200);
    }

    /**
     * Eliminar cliente y usuario
     */
    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $user   = $client->user;

        $client->delete();
        $user->delete();

        return response()->json(['message' => 'Cliente eliminado correctamente'], 200);
    }
}
