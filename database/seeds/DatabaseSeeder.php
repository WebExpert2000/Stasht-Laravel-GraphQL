<?php

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
        // $this->call(UsersTableSeeder::class);
        DB::table('facebook_users')->insert([
            'facebook_user_id' => '00000001',
            'access_token' => Str::random(40)
        ]);
        DB::table('instagram_users')->insert([
            'instagram_user_id' => '00000001',
            'instagram_user_name' => 'null',
            'access_token' => Str::random(40)
        ]);
    }
}
