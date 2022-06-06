<?php

namespace DanPalmieri\LivewireComments\Tests\Support;

use function class_basename;
use function config;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Comments\CommentsServiceProvider;
use Spatie\LaravelRay\RayServiceProvider;
use DanPalmieri\LivewireComments\LivewireCommentsServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Spatie\\LivewireComments\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            CommentsServiceProvider::class,
            LivewireCommentsServiceProvider::class,
            LivewireServiceProvider::class,
            RayServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('app.key', 'base64:LjpSHzPr1BBeuRWrlUcN2n2OWZ36o8+VpTLZdHcdG7Q=');

        config()->set('database.default', 'testing');

        $migration = include __DIR__.'/../../database/migrations/create_comments_tables.php.stub';
        $migration->up();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamps();
        });
    }

    public function registerPolicies(): self
    {
        (new LivewireCommentsServiceProvider($this->app))->registerPolicies();

        return $this;
    }
}
