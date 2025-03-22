<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $users = array(
            "西 怜奈" => "reina.n",
            "山田 太郎" => "taro.y",
            "増田 一世" => "issei.m",
            "山本 啓吉" => "keikichi.y",
            "秋田 朋美" => "tomomi.a",
            "中西 教夫" => "norio.n",
        );

        foreach($users as $name => $email){
            if ($name === "中西 教夫") {
                DB::table('users')->insert([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'role' => "admin",
                ]);
            }
            else {
                DB::table('users')->insert([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'role' => "user",
                ]);
            }   
        }
    }
}
