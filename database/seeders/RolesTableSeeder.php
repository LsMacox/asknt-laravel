<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
         * Role Types
         *
         */
        $RoleItems = [
            [
                'name'        => 'Диспетчер',
                'slug'        => 'disp',
                'description' => 'Роль диспетчер',
                'level'       => 0,
            ],
            [
                'name'        => 'Главный диспетчер',
                'slug'        => 'main_disp',
                'description' => 'Роль главного диспетчира',
                'level'       => 1,
            ],
            [
                'name'        => 'Менеджр',
                'slug'        => 'manager',
                'description' => 'Роль менеджера',
                'level'       => 2,
            ],
            [
                'name'        => 'Старший менеджр',
                'slug'        => 's_manager',
                'description' => 'Роль старшего менеджера',
                'level'       => 3,
            ],
            [
                'name'        => 'Админ',
                'slug'        => 'admin',
                'description' => 'Роль админа',
                'level'       => 4,
            ],
        ];

        /*
         * Add Role Items
         *
         */
        foreach ($RoleItems as $RoleItem) {
            $newRoleItem = config('roles.models.role')::where('slug', '=', $RoleItem['slug'])->first();
            if ($newRoleItem === null) {
                $newRoleItem = config('roles.models.role')::create([
                    'name'          => $RoleItem['name'],
                    'slug'          => $RoleItem['slug'],
                    'description'   => $RoleItem['description'],
                    'level'         => $RoleItem['level'],
                ]);
            }
        }
    }
}
