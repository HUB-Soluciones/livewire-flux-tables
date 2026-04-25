<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Workbench\App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::truncate();

        $users = [
            ['Ana López', 'ana.lopez'],
            ['Carlos García', 'carlos.garcia'],
            ['María Martínez', 'maria.martinez'],
            ['Juan Rodríguez', 'juan.rodriguez'],
            ['Laura Sánchez', 'laura.sanchez'],
            ['Pedro González', 'pedro.gonzalez'],
            ['Sofía Fernández', 'sofia.fernandez'],
            ['Diego Torres', 'diego.torres'],
            ['Valentina Díaz', 'valentina.diaz'],
            ['Andrés Ruiz', 'andres.ruiz'],
            ['Camila Moreno', 'camila.moreno'],
            ['Roberto Jiménez', 'roberto.jimenez'],
            ['Isabella Álvarez', 'isabella.alvarez'],
            ['Felipe Romero', 'felipe.romero'],
            ['Catalina Navarro', 'catalina.navarro'],
            ['Alejandro Medina', 'alejandro.medina'],
            ['Lucía Herrera', 'lucia.herrera'],
            ['Martín Castro', 'martin.castro'],
            ['Gabriela Ramos', 'gabriela.ramos'],
            ['Sebastián Ortega', 'sebastian.ortega'],
            ['Natalia Vargas', 'natalia.vargas'],
            ['Eduardo Mendoza', 'eduardo.mendoza'],
            ['Daniela Reyes', 'daniela.reyes'],
            ['Francisco Guerrero', 'francisco.guerrero'],
            ['Paola Delgado', 'paola.delgado'],
            ['Tomás Aguilar', 'tomas.aguilar'],
            ['Valeria Campos', 'valeria.campos'],
            ['Ricardo Vega', 'ricardo.vega'],
            ['Mónica Peña', 'monica.pena'],
            ['José Ríos', 'jose.rios'],
            ['Claudia Santos', 'claudia.santos'],
            ['Ernesto Molina', 'ernesto.molina'],
            ['Patricia Silva', 'patricia.silva'],
            ['Miguel Flores', 'miguel.flores'],
            ['Andrea Cruz', 'andrea.cruz'],
            ['Héctor Peralta', 'hector.peralta'],
            ['Sandra León', 'sandra.leon'],
            ['Jorge Salinas', 'jorge.salinas'],
            ['Elena Cabrera', 'elena.cabrera'],
            ['Rafael Ibáñez', 'rafael.ibanez'],
            ['Teresa Montes', 'teresa.montes'],
            ['Alberto Serrano', 'alberto.serrano'],
            ['Norma Fuentes', 'norma.fuentes'],
            ['Rodrigo Cortés', 'rodrigo.cortes'],
            ['Beatriz Espinoza', 'beatriz.espinoza'],
            ['Ignacio Parra', 'ignacio.parra'],
            ['Carmen Valdés', 'carmen.valdes'],
            ['Raúl Contreras', 'raul.contreras'],
            ['Verónica Lara', 'veronica.lara'],
            ['Gustavo Miranda', 'gustavo.miranda'],
        ];

        $roles    = ['admin', 'editor', 'viewer'];
        $statuses = ['active', 'inactive'];
        $domains  = ['gmail.com', 'hotmail.com', 'outlook.com', 'yahoo.com', 'empresa.com'];

        foreach ($users as $i => [$name, $slug]) {
            $domain = $domains[$i % count($domains)];
            $role   = $roles[$i % count($roles)];
            $status = $i % 5 === 0 ? 'inactive' : 'active';

            User::create([
                'name'       => $name,
                'email'      => "{$slug}@{$domain}",
                'role'       => $role,
                'status'     => $status,
                'created_at' => now()->subDays(rand(1, 365)),
                'updated_at' => now(),
            ]);
        }
    }
}
