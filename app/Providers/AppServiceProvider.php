<?php

namespace App\Providers;

use App\Models\Person;
use App\Models\Property;
use App\Models\PropertyDocument;
use Illuminate\Routing\Route as BindingRoute;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

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
        Route::bind('person', function (string $value) {
            $query = Person::query();

            if (! auth()->user()?->canAccessAllRecords()) {
                $query->where('user_id', auth()->id());
            }

            return $query->findOrFail($value);
        });

        Route::bind('property', function (string $value) {
            $query = Property::query();

            if (! auth()->user()?->canAccessAllRecords()) {
                $query->where('user_id', auth()->id());
            }

            return $query->findOrFail($value);
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
