<?php

namespace LaravelPublishable\Tests;

use Illuminate\Support\Carbon;
use Spatie\TestTime\TestTime;

class AllTraitsTest extends TestCase
{
    /* --- Publishable Tests --- */

    /** @test */
    public function a_model_can_be_published()
    {
        $model = AllTraitsModel::factory()->create();

        $this->assertNull($model->fresh()->published_at);

        $model->publish();

        $this->assertNotNull($model->fresh()->published_at);
    }

    /** @test */
    public function a_model_can_be_published_in_the_future()
    {
        TestTime::freeze('Y-m-d H:i:s', '2021-03-31 20:30:00');

        $model = AllTraitsModel::factory()->create();

        $this->assertNull($model->fresh()->published_at);

        $model->publish(Carbon::parse('2021-03-31 21:00:00'));

        $this->assertEquals('2021-03-31 21:00:00', $model->fresh()->published_at->toDateTimeString());
    }

    /** @test */
    public function a_model_can_be_published_in_the_future_and_only_be_accessed_after_that()
    {
        TestTime::freeze('Y-m-d H:i:s', '2021-03-31 20:30:00');

        $model = AllTraitsModel::factory()->create();

        $this->assertNull($model->fresh()->published_at);

        $model->publishAt(Carbon::parse('2021-03-31 21:00:00'));

        $this->assertCount(0, AllTraitsModel::all());
        $this->assertEquals('2021-03-31 21:00:00', $model->fresh()->published_at->toDateTimeString());
        $this->assertNull($model->fresh()->expired_at);

        TestTime::freeze('Y-m-d H:i:s', '2021-03-31 21:10:00');

        $this->assertCount(1, AllTraitsModel::all());
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

    /* --- Expirable Tests --- */

    /** @test */
    public function a_model_can_be_expired()
    {
        $model = AllTraitsModel::factory()->create();

        $this->assertNull($model->fresh()->expired_at);

        $model->expire();

        $this->assertNotNull($model->fresh()->expired_at);
    }

    /** @test */
    public function a_model_can_be_expired_in_the_future()
    {
        TestTime::freeze('Y-m-d H:i:s', '2021-03-31 20:30:00');

        $model = AllTraitsModel::factory()->create();

        $this->assertNull($model->fresh()->expired_at);

        $model->expire(Carbon::parse('2021-03-31 21:00:00'));

        $this->assertEquals('2021-03-31 21:00:00', $model->fresh()->expired_at->toDateTimeString());
    }

    /** @test */
    public function a_model_can_be_expired_in_the_future_and_only_be_accessed_before_that()
    {
        TestTime::freeze('Y-m-d H:i:s', '2021-03-31 20:30:00');

        $model = AllTraitsModel::factory()->published()->create();

        $this->assertNull($model->fresh()->expired_at);

        $model->expireAt(Carbon::parse('2021-03-31 21:00:00'));
        $this->assertEquals('2021-03-31 21:00:00', $model->fresh()->expired_at->toDateTimeString());
        $this->assertCount(1, AllTraitsModel::all());

        TestTime::freeze('Y-m-d H:i:s', '2021-03-31 21:10:00');

        $this->assertCount(0, AllTraitsModel::all());
    }

    /** @test */
    public function a_model_can_be_unexpired()
    {
        $model = AllTraitsModel::factory()->expired()->create();

        $this->assertNotNull($model->fresh()->expired_at);

        $model->unexpire();

        $this->assertNull($model->fresh()->expired_at);
    }

    /** @test */
    public function a_model_cannot_be_queried_normally_when_not_expired()
    {
        TestTime::freeze('Y-m-d H:i:s', '2021-03-31 20:30:00');

        AllTraitsModel::factory()->published()->expired()->create();
        AllTraitsModel::factory()->published()->create();

        TestTime::freeze('Y-m-d H:i:s', '2021-03-31 21:00:00');

        $this->assertDatabaseCount('all_traits_models', 2);
        $this->assertCount(1, AllTraitsModel::all());
    }

    /** @test */
    public function all_models_can_be_found_with_the_withExpired_scope()
    {
        TestTime::freeze('Y-m-d H:i:s', '2021-03-31 20:30:00');

        AllTraitsModel::factory()->published()->expired()->create();
        AllTraitsModel::factory()->published()->create();

        TestTime::freeze('Y-m-d H:i:s', '2021-03-31 21:30:00');

        $this->assertCount(1, AllTraitsModel::all());
        $this->assertCount(2, AllTraitsModel::withExpired()->get());
    }

    /** @test */
    public function only_expired_models_can_be_found_with_the_onlyExpired_scope()
    {
        TestTime::freeze('Y-m-d H:i:s', '2021-03-31 20:30:00');

        AllTraitsModel::factory()->published()->expired()->create();
        AllTraitsModel::factory()->published()->create();

        TestTime::freeze('Y-m-d H:i:s', '2021-03-31 21:30:00');

        $this->assertCount(1, AllTraitsModel::onlyExpired()->get());
    }

    /** @test */
    public function accessor_isPublished_returns_true_if_published()
    {
        $this->travelTo(Carbon::parse('2021-03-31 20:30:00'));
        $model = PublishableModel::factory()->create([
            'published_at' => Carbon::parse('2021-03-31 21:30:00'),
        ]);

        $this->assertFalse($model->isPublished());

        $this->travelTo(Carbon::parse('2021-03-31 21:35:00'));
        $this->assertTrue($model->isPublished());
    }

}
