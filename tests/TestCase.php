<?php

namespace LaravelPublishable\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use LaravelPublishable\LaravelPublishableServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            function (string $modelName) {
                return 'LaravelPublishable\\Tests\\Database\\Factories\\'.class_basename($modelName).'Factory';
            }
        );

        $this->setUpDatabase($this->app);
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelPublishableServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUpDatabase($app)
    {
        $this->loadLaravelMigrations();

        $app['db']
            ->connection()
            ->getSchemaBuilder()
            ->create('publishable_models', function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
                $table->publishedAt();
                $table->softDeletes();
            });

        $app['db']
        ->connection()
        ->getSchemaBuilder()
            ->create('expirable_models', function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
                $table->expiredAt();
                $table->softDeletes();
            });

        $app['db']
            ->connection()
            ->getSchemaBuilder()
            ->create('all_traits_models', function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
                $table->publishedAt();
                $table->expiredAt();
                $table->softDeletes();
            });

        $app['db']
            ->connection()
            ->getSchemaBuilder()
            ->create('regular_models', function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
                $table->softDeletes();
            });
    }
}
