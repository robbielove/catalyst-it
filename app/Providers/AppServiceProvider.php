<?php

namespace App\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

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
        Collection::macro('validate', function (array $rules) {
            /** @var $this Collection */
            return $this->values()->filter(function ($item) use ($rules) {
                return Validator::make($item->all(), $rules)->passes();
            });
        });
    }
}
