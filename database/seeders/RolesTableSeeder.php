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
                'name'        => 'ascnt_disp',
                'slug'        => 'ascntdisp',
                'description' => 'Роль диспетчер',
                'level'       => 0,
            ],
            [
                'name'        => 'ascnt_main_disp',
                'slug'        => 'ascntmaindisp',
                'description' => 'Роль главного диспетчира',
                'level'       => 1,
            ],
            [
                'name'        => 'ascnt_manager',
                'slug'        => 'ascntmanager',
                'description' => 'Роль менеджера',
                'level'       => 2,
            ],
            [
                'name'        => 'ascnt_smanager',
                'slug'        => 'ascntsmanager',
                'description' => 'Роль старшего менеджера',
                'level'       => 3,
            ],
            [
                'name'        => 'ascnt_admin',
                'slug'        => 'ascntadmin',
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
