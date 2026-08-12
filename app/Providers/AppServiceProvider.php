<?php

namespace App\Providers;

use App\Models\Person;
use App\Models\Property;
use App\Models\PropertyDocument;
use Illuminate\Routing\Route as BindingRoute;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Scoped binding: resolve only records belonging to the authenticated user
        Route::bind('person', function (string $value) {
            return Person::where('user_id', auth()->id())->findOrFail($value);
        });

        Route::bind('property', function (string $value) {
            return Property::where('user_id', auth()->id())->findOrFail($value);
        });

        Route::bind('document', function (string $value, BindingRoute $route) {
            $property = $route->parameter('property');
            $propertyId = $property instanceof Property ? $property->id : $property;

            return PropertyDocument::query()
                ->where('property_id', $propertyId)
                ->findOrFail($value);
        });
    }
}
