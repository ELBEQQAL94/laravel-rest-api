<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;

use App\Product;
use App\User;
use App\Mail\UserCreated;
use App\Mail\UserMailChanged;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        // send email automaticly for created user
        User::created(function($user) {
            retry(5, function() use ($user) {
                Mail::to($user)->send(new UserCreated($user));
            }, 100);
        });

        // send email automaticly for updated email
        User::updated(function($user) {
            // check if email changed
            if($user->isDirty('email')) {
                retry(5, function() use ($user) {
                    Mail::to($user)->send(new UserMailChanged($user));
                }, 100);
            }
         });

        // update product automaticly
        Product::updated(function($product) {
            if($product->quantity == 0 && $product->isAvailable()) {
                $product->status = Product::UNAVAILABLE_PRODUCT;
                $product->save();
            };
        });
    }
}
