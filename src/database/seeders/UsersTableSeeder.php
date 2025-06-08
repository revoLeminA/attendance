<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Faker\Provider\DateTime;

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
            '西 怜奈' => 'reina.n',
            '山田 太郎' => 'taro.y',
            '増田 一世' => 'issei.m',
            '山本 啓吉' => 'keikichi.y',
            '秋田 朋美' => 'tomomi.a',
            '中西 教夫' => 'norio.n',
        );

        foreach($users as $name => $email){
            if ($email === 'norio.n') {
                DB::table('users')->insert([
                    'name' => $name,
                    'email' => $email.'@coachtech.com',
                    'password' => Hash::make('password'),
                    'role' => 'admin',
                    'created_at' => DateTime::dateTimeThisDecade(), // ランダムな過去の日付と時間を挿入
                    'updated_at' => Carbon::now(),
                ]);
            }
            else {
                DB::table('users')->insert([
                    'name' => $name,
                    'email' => $email.'@coachtech.com',
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'created_at' => DateTime::dateTimeThisDecade(), // ランダムな過去の日付と時間を挿入
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
}
