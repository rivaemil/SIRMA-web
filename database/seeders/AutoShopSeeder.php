<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Faker\Factory as Faker;
use App\Models\User;
use App\Models\Client;
use App\Models\Mechanic;
use App\Models\Vehicle;
use App\Models\Appointment;
use App\Models\Log;

class AutoShopSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_MX');

        // ===== 1) ADMIN =====
        $admin = User::create([
            'name'     => 'Administrador',
            'email'    => 'admin@example.com',
            'password' => Hash::make('password'), 
            'role'      => 'admin',
        ]);

        // ===== 2) MECÁNICOS (users + mechanics) =====
        $mechanicUsers = [];
        $mechanicIds = [];
        for ($i = 1; $i <= 5; $i++) {
            $u = User::create([
                'name'     => $faker->name(),
                'email'    => "mechanic{$i}@example.com",
                'password' => Hash::make('password'),
                'role'      => 'mechanic',
            ]);
            $m = Mechanic::create([
                'user_id' => $u->id,
                'name'    => $u->name,
            ]);
            $mechanicUsers[] = $u;
            $mechanicIds[] = $m->id;
        }

        // ===== 3) CLIENTES (users + clients) =====
        $clientUsers = [];
        $clientIds = [];
        for ($i = 1; $i <= 10; $i++) {
            $name  = $faker->name();
            $email = "client{$i}@example.com";

            $u = User::create([
                'name'     => $name,
                'email'    => $email,
                'password' => Hash::make('password'),
                'role'      => 'client',
            ]);

            $c = Client::create([
                'user_id' => $u->id,
                'name'    => $name,
                'phone'   => $faker->numerify('55########'),
                'email'   => $email,
            ]);

            $clientUsers[] = $u;
            $clientIds[]   = $c->id;
        }

        // ===== 4) VEHÍCULOS (50) repartidos entre 10 clientes =====
        $brandsModels = [
            'Nissan' => ['Versa','Sentra','March','Altima','Kicks'],
            'Toyota' => ['Corolla','Yaris','Camry','RAV4','Hilux'],
            'VW'     => ['Gol','Jetta','Vento','Tiguan','Polo'],
            'Chevrolet' => ['Aveo','Onix','Spark','Tracker','Cruze'],
            'Ford'   => ['Fiesta','Focus','Fusion','Ecosport','Ranger'],
            'Honda'  => ['City','Civic','Fit','CR-V','HR-V'],
            'Mazda'  => ['2','3','CX-3','CX-5','6'],
            'Kia'    => ['Rio','Forte','Soul','Sportage','Seltos'],
        ];

        $platesUsed = [];
        $clientVehicles = []; // client_id => [vehicle_ids]
        foreach ($clientIds as $cid) { $clientVehicles[$cid] = []; }

        for ($i = 0; $i < 50; $i++) {
            // repartir de forma “uniforme”: 5 por cliente aprox.
            $clientId = $clientIds[$i % count($clientIds)];

            // generar plate único
            do {
                // Formato tipo MX simple: ABC-1234
                $plate = strtoupper($faker->bothify('???-####'));
            } while (isset($platesUsed[$plate]));
            $platesUsed[$plate] = true;

            $brand = array_rand($brandsModels);
            $model = $brandsModels[$brand][array_rand($brandsModels[$brand])];

            $vehicle = Vehicle::create([
                'client_id' => $clientId,
                'brand'     => $brand,
                'model'     => $model,
                'year'      => $faker->numberBetween(1998, (int)date('Y') + 1),
                'plate'     => $plate,
            ]);

            $clientVehicles[$clientId][] = $vehicle->id;
        }

        // Seguridad: si algún cliente quedó sin vehículo (no debería), dale 1
        foreach ($clientVehicles as $cid => $vlist) {
            if (count($vlist) === 0) {
                $brand = array_rand($brandsModels);
                $model = $brandsModels[$brand][array_rand($brandsModels[$brand])];
                do {
                    $plate = strtoupper($faker->bothify('???-####'));
                } while (isset($platesUsed[$plate]));
                $platesUsed[$plate] = true;

                $v = Vehicle::create([
                    'client_id' => $cid,
                    'brand'     => $brand,
                    'model'     => $model,
                    'year'      => $faker->numberBetween(2005, (int)date('Y') + 1),
                    'plate'     => $plate,
                ]);
                $clientVehicles[$cid][] = $v->id;
            }
        }

        // ===== 5) APPOINTMENTS (50) coherentes =====
        $apptTitles = [
            'Servicio General', 'Cambio de Aceite', 'Revisión de Frenos',
            'Diagnóstico Motor', 'Alineación y Balanceo', 'Cambio de Batería',
            'Afinación', 'Cambio de Filtros', 'Revisión Eléctrica', 'Inspección General'
        ];

        for ($i = 0; $i < 50; $i++) {
            // elige cliente y uno de sus vehículos
            $clientId = $clientIds[array_rand($clientIds)];
            $vehicleList = $clientVehicles[$clientId];
            $vehicleId = $vehicleList[array_rand($vehicleList)];

            // fechas próximas (hoy..60 días), horario laboral 9–18h
            $date = Carbon::now()
                ->addDays(rand(0, 60))
                ->setTime(rand(9, 18), [0, 15, 30, 45][rand(0,3)], 0);

            Appointment::create([
                'title'       => $apptTitles[array_rand($apptTitles)],
                'client_id'   => $clientId,
                'vehicle_id'  => $vehicleId,
                'scheduled_at'=> $date->format('Y-m-d H:i:s'),
            ]);
        }

        // ===== 6) LOGS (50) coherentes =====
        $logTitles = [
            'Diagnóstico inicial', 'Reemplazo de pastillas', 'Cambio de bujías',
            'Falla intermitente', 'Revisión de suspensión', 'Fuga de aceite',
            'Chequeo de batería', 'Cambio de filtro de aire', 'Ajuste de banda', 'Revisión de escape'
        ];

        for ($i = 0; $i < 50; $i++) {
            $clientId = $clientIds[array_rand($clientIds)];
            $vehicleList = $clientVehicles[$clientId];
            $vehicleId = $vehicleList[array_rand($vehicleList)];
            $mechanicId = $mechanicIds[array_rand($mechanicIds)];

            Log::create([
                'title'       => $logTitles[array_rand($logTitles)],
                'vehicle_id'  => $vehicleId,
                'client_id'   => $clientId,
                'mechanic_id' => $mechanicId,
                'description' => $faker->paragraph(rand(1,3)),
            ]);
        }
    }
}
