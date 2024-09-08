<?php

namespace Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Modules\Application\Models\User;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        $url = config('app.url') . config('eloquent-api.api_prefix');
        config(['app.url' => $url]);
        URL::forceRootUrl($url);

        $this->signIn();

        $this->setFactoriesNamespacing();
    }

    protected function signIn(?User $user = null): User
    {
        $user ??= User::where('email', 'admin@example.com')->first();
        Sanctum::actingAs($user, ['*']);

        return $user;
    }

    protected function setFactoriesNamespacing(): void
    {
        Factory::guessFactoryNamesUsing(function (string $modelFullClass) {
            // Here we are getting the model name from the class namespace
            $modelName = Str::afterLast($modelFullClass, '\\');

            // We can also customise where our factories live too if we want:
            $namespace = Str::before($modelFullClass, 'Models\\' . $modelName) . 'Database\\Factories\\';

            // Finally we'll build up the full class path where
            // Laravel will find our model factory
            return $namespace . $modelName . 'Factory';
        });
    }
}
