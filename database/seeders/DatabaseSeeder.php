<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(RolesTableSeeder::class);
        $this->call(UsersTableSeeder::class);
//        $this->call(PermissionsTableSeeder::class);
//        $this->call(ConnectRelationshipsSeeder::class);

        Model::reguard();
    }
}
