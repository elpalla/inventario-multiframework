<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Alternativa al script .sql: ejecutar  php artisan migrate --seed
 * (solo si NO importaste el archivo .sql, para no duplicar tablas).
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            ['Administrador General', 'admin@voltrider.com', 'Admin12345', 'admin'],
            ['Operador de Bodega', 'operador@voltrider.com', 'Operador12345', 'operador']
        ];
        foreach ($usuarios as [$nombre, $email, $password, $rol]) {
            User::updateOrCreate(['email' => $email], compact('nombre', 'password', 'rol'));
        }

        $categorias = [
            ['Automóviles eléctricos', 'Sedanes, SUV y pickups 100% eléctricos'],
            ['Motocicletas eléctricas', 'Motos y scooters eléctricos urbanos y de carretera'],
            ['Baterías y celdas', 'Paquetes de baterías de litio y celdas de repuesto'],
            ['Cargadores y energía', 'Cargadores de pared, rápidos y portátiles'],
            ['Repuestos y accesorios', 'Motores, controladores, neumáticos y accesorios']
        ];
        foreach ($categorias as [$nombre, $descripcion]) {
            Categoria::updateOrCreate(['nombre' => $nombre], compact('descripcion'));
        }

        $productos = [
            ['VR-AUT-001', 'VoltRider Aero S Sedán', 1, 'Sedán eléctrico de 5 plazas, tracción trasera', 38900.0, 42, 10, 'Medellín, Colombia', 520],
            ['VR-AUT-002', 'VoltRider Terra X SUV', 1, 'SUV eléctrico AWD con batería de 95 kWh', 54900.0, 27, 8, 'Monterrey, México', 610],
            ['VR-AUT-003', 'VoltRider Cargo T Pickup', 1, 'Pickup eléctrica para flotas comerciales', 61500.0, 12, 5, 'Stuttgart, Alemania', 480],
            ['VR-AUT-004', 'VoltRider City Mini', 1, 'Citycar eléctrico compacto de 4 plazas', 24900.0, 63, 15, 'Shenzhen, China', 330],
            ['VR-MOT-001', 'Pulse 3000 Urbana', 2, 'Moto eléctrica urbana de 3 kW', 3490.0, 150, 30, 'Pune, India', 110],
            ['VR-MOT-002', 'Pulse 8000 Sport', 2, 'Moto eléctrica deportiva de 8 kW', 7990.0, 64, 15, 'Medellín, Colombia', 190],
            ['VR-MOT-003', 'Pulse Delivery Pro', 2, 'Moto eléctrica para reparto con batería doble', 4590.0, 98, 20, 'Pune, India', 160],
            ['VR-MOT-004', 'Scooter Neo 1500', 2, 'Scooter eléctrico plegable de 1.5 kW', 1890.0, 220, 40, 'Shenzhen, China', 70],
            ['VR-BAT-001', 'Batería LFP 60 kWh', 3, 'Paquete de baterías LFP para automóviles', 6200.0, 35, 10, 'Shenzhen, China', null],
            ['VR-BAT-002', 'Batería Li-ion 3 kWh Moto', 3, 'Paquete extraíble para motocicletas', 780.0, 310, 60, 'Pune, India', null],
            ['VR-CAR-001', 'Cargador de pared 11 kW', 4, 'Wallbox monofásico/trifásico para hogar', 620.0, 180, 40, 'Stuttgart, Alemania', null],
            ['VR-CAR-002', 'Cargador rápido DC 60 kW', 4, 'Estación de carga rápida comercial', 14800.0, 9, 5, 'Stuttgart, Alemania', null],
            ['VR-REP-001', 'Motor eléctrico 150 kW', 5, 'Motor síncrono de imanes permanentes', 2950.0, 4, 6, 'Monterrey, México', null],
            ['VR-REP-002', 'Controlador inversor 400 V', 5, 'Inversor de potencia para vehículos', 1350.0, 52, 12, 'Shenzhen, China', null]
        ];
        foreach ($productos as [$sku, $nombre, $cat, $descripcion, $precio, $stock, $stock_minimo, $planta, $autonomia_km]) {
            Producto::updateOrCreate(['sku' => $sku], [
                'nombre' => $nombre,
                'categoria_id' => $cat,
                'descripcion' => $descripcion,
                'precio' => $precio,
                'stock' => $stock,
                'stock_minimo' => $stock_minimo,
                'planta' => $planta,
                'autonomia_km' => $autonomia_km,
            ]);
        }
    }
}
