<?php

namespace Database\Seeders;

use App\Models\Login;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LoginTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = array(
            array(
               "name" => "Sonic CV",
               "email" => "soniccv@soniccv.com",
               "password"=>Hash::make("sc123"),
               "role_id"=> 1,
               "expdate"=>"01/03/2021",
            )
        );
        Login::insert($data);
    }
}
