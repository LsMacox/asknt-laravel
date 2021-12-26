<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminRole = config('roles.models.role')::where('slug', 'ascntadmin')->first();
        $managerRole = config('roles.models.role')::where('slug', 'ascntmanager')->first();
        $dispRole = config('roles.models.role')::where('slug', 'ascntdisp')->first();

        if (config('roles.models.defaultUser')::where('email', '=', 'admin@admin.com')->first() === null) {
            $newUser = config('roles.models.defaultUser')::create([
                'name'     => 'Admin',
                'email'    => 'admin@admin.com',
                'password' => bcrypt('password'),
            ]);

            $newUser->attachRole($adminRole);
        }

        if (config('roles.models.defaultUser')::where('email', '=', 'manager@manager.com')->first() === null) {
            $newUser = config('roles.models.defaultUser')::create([
                'name'     => 'Manager',
                'email'    => 'manager@manager.com',
                'password' => bcrypt('password'),
            ]);

            $newUser->attachRole($managerRole);
        }

        if (config('roles.models.defaultUser')::where('email', '=', 'disp@disp.com')->first() === null) {
            $newUser = config('roles.models.defaultUser')::create([
                'name'     => 'Disp',
                'email'    => 'disp@disp.com',
                'password' => bcrypt('password'),
            ]);

            $newUser->attachRole($dispRole);
        }
    }
}
