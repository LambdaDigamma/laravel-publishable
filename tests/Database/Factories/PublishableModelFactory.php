<?php

namespace LaravelPublishable\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelPublishable\Tests\PublishableModel;

class PublishableModelFactory extends Factory
{
    protected $model = PublishableModel::class;

    public function published()
    {
        return $this->state(function (array $attributes) {
            return [
                'published_at' => now(),
            ];
        });
    }

    public function definition()
    {
        return [];
    }
}