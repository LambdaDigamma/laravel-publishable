<?php

namespace LaravelPublishable\Tests;

use Illuminate\Support\Carbon;
use Spatie\TestTime\TestTime;

class PublishableTest extends TestCase
{
    /** @test */
    public function a_model_can_be_published()
    {
        $model = PublishableModel::factory()->create();

        $this->assertNull($model->fresh()->published_at);

        $model->publish();

        $this->assertNotNull($model->fresh()->published_at);
    }

    /** @test */
    public function a_model_can_be_published_in_the_future()
    {
        TestTime::freeze('Y-m-d H:i:s', '2021-03-31 20:30:00');

        $model = PublishableModel::factory()->create();
        
        $this->assertNull($model->fresh()->published_at);

        $model->publish(Carbon::parse('2021-03-31 21:00:00'));

        $this->assertEquals('2021-03-31 21:00:00', $model->fresh()->published_at->toDateTimeString());
    }

    /** @test */
    public function a_model_can_be_published_in_the_future_and_only_be_accessed_after_that()
    {
        TestTime::freeze('Y-m-d H:i:s', '2021-03-31 20:30:00');

        $model = PublishableModel::factory()->create();
        
        $this->assertNull($model->fresh()->published_at);

        $model->publishAt(Carbon::parse('2021-03-31 21:00:00'));

        $this->assertCount(0, PublishableModel::all());
        $this->assertEquals('2021-03-31 21:00:00', $model->fresh()->published_at->toDateTimeString());

        TestTime::freeze('Y-m-d H:i:s', '2021-03-31 21:00:00');

        $this->assertCount(1, PublishableModel::all());
    }

    /** @test */
    public function a_model_can_be_unpublished()
    {
        $model = PublishableModel::factory()->published()->create();

        $this->assertNotNull($model->fresh()->published_at);

        $model->unpublish();

        $this->assertNull($model->fresh()->published_at);
    }

    /** @test */
    public function a_model_cannot_be_queried_normally_when_published()
    {
        PublishableModel::factory()->published()->create();

        PublishableModel::factory()->create();

        $this->assertDatabaseCount('publishable_models', 2);

        $this->assertCount(1, PublishableModel::all());
    }

    /** @test */
    public function all_models_can_be_found_with_the_withNotPublished_scope()
    {
        PublishableModel::factory()->published()->create();
        PublishableModel::factory()->create();

        $this->assertCount(1, PublishableModel::all());
        $this->assertCount(2, PublishableModel::withNotPublished()->get());
    }

    /** @test */
    public function only_not_published_models_can_be_found_with_the_onlyNotPublished_scope()
    {
        PublishableModel::factory()->published()->create();
        PublishableModel::factory()->create();

        $this->assertCount(1, PublishableModel::onlyNotPublished()->get());
    }

    /** @test */
    public function models_without_the_publishable_trait_are_not_scoped()
    {
        RegularModel::factory()->create();

        $this->assertCount(1, RegularModel::all());
    }
}