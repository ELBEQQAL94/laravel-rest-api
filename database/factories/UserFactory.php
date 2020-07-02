<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Category;
use App\Product;
use App\Transaction;
use App\Seller;
use App\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

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

$factory->define(User::class, function (Faker $faker) {
    static $password;
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => $password ?: $password = bcrypt('password'),
        'remember_token' => Str::random(10),
        'verified' =>
            $verfied = $faker->
                randomElement([User::VERIFIED_USER, User::UNVERIFIED_USER]),
        'verification_token' =>
            $verfied == User::VERIFIED_USER ? null:
                User::generateVerificationCode(),
        'admin' =>
            $faker->randomElement([User::REGULAR_USER, User::ADMIN_USER]),
    ];
});


$factory->define(Category::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'description' => $faker->paragraph(1),
    ];
});


$factory->define(Product::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'description' => $faker->paragraph(1),
        'quantity' => $faker->numberBetween(1, 10),
        'status' =>
            $faker->randomElement([
                Product::AVAILABLE_PRODUCT,
                Product::UNAVAILABLE_PRODUCT
            ]),
        'image' => $faker->randomElement([
            'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1526947425960-945c6e72858f?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1491553895911-0055eca6402d?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1000&q=80'
            ]),
        'seller_id' => User::all()->random()->id,
        // User::isRandomOrder()->first()->id
    ];
});


$factory->define(Transaction::class, function (Faker $faker) {

    // Check seller if already has products on his/her store
    $seller = Seller::has('products')->get()->random();

    // grep random buyer id except seller id for prevent be buyer and seller the same
    $buyer = User::all()->except($seller->id)->random();

    return [
        'quantity' => $faker->numberBetween(1, 3),
        'buyer_id' => $buyer->id,
        'product_id' => $seller->products->random()->id,
    ];
});

