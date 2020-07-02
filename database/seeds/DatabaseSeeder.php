<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\User;
use App\Category;
use App\Product;
use App\Transaction;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
       DB::statement('SET FOREIGN_KEY_CHECKS=0');

       User::truncate();
       Category::truncate();
       Product::truncate();
       Transaction::truncate();

       DB::table('category_product')->truncate();

       User::flushEventListeners();
       Category::flushEventListeners();
       Product::flushEventListeners();
       Transaction::flushEventListeners();

       // How many user, category, product, transaction, we're going to create
       $usersQuantity = 200;
       $categoriesQuantity = 10;
       $productsQuantity = 1000;
       $transactionsQuantity = 3000;

       // create our seeds into database
       factory(User::class, $usersQuantity)->create();

       factory(Category::class, $categoriesQuantity)->create();

       factory(Product::class, $productsQuantity)->create()->each(
           function ($product) {
               $categories = Category::all()->random(mt_rand(1, 8))->pluck('id');
               $product->categories()->attach($categories);
           }
       );

       factory(Transaction::class, $transactionsQuantity)->create();
    }
}
