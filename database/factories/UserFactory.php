<?php

namespace Database\Factories;
//use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

/*$factory->define(App\User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'remember_token' => str_random(10),
    ];
});*/

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => bcrypt('password'), // por ejemplo, usa una contraseña segura
        'remember_token' => Str::random(10),
    ];
});

// Estado 'admin'
$factory->state(User::class, 'admin', function (Faker $faker) {
    return [
        'type' => 'admin',
    ];
});

// Estado 'guest'
$factory->state(User::class, 'guest', function (Faker $faker) {
    return [
        'type' => 'guest',
    ];
});

// Otros estados o modificaciones adicionales según necesites

