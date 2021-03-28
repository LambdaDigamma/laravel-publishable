<?php

namespace LaravelPublishable;

use Exception;
use LaravelPublishable\Scopes\PublishableScope;

/**
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder withNotPublished()
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder onlyNotPublished()
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder withoutPublished()
 */
trait Publishable
{
    /**
     * Indicates if the model should use publishes.
     *
     * @var bool
     */
    public $publishes = true;

    /**
     * Boot the archiving trait for a model.
     *
     * @return void
     */
    public static function bootPublishable()
    {
        static::addGlobalScope(new PublishableScope);
    }

    /**
     * Initialize the soft deleting trait for an instance.
     *
     * @return void
     */
    public function initializePublishable()
    {
        if (! isset($this->casts[$this->getPublishedAtColumn()])) {
            $this->casts[$this->getPublishedAtColumn()] = 'datetime';
        }
    }

    /**
     * Publish the model.
     *
     * @return bool|null
     *
     * @throws Exception
     */
    public function publish()
    {
        $this->mergeAttributesFromClassCasts();

        if (is_null($this->getKeyName())) {
            throw new Exception('No primary key defined on model.');
        }

        // If the model doesn't exist, there is nothing to publish.
        if (! $this->exists) {
            return;
        }

        // If the publishing event doesn't return false, we'll continue
        // with the operation.
        if ($this->fireModelEvent('publishing') === false) {
            return false;
        }

        // Update the timestamps for each of the models owners. Breaking any caching
        // on the parents
        $this->touchOwners();

        $this->runPublish();

        // Fire unpublished event to allow hooking into the post-publish operations.
        $this->fireModelEvent('published', false);

        // Return true as the publish is presumably successful.
        return true;
    }

    /**
     * Perform the actual publish query on this model instance.
     *
     * @return void
     */
    public function runPublish()
    {
        $query = $this->setKeysForSaveQuery($this->newModelQuery());

        $time = $this->freshTimestamp();

        $columns = [$this->getPublishedAtColumn() => $this->fromDateTime($time)];

        $this->{$this->getPublishedAtColumn()} = $time;

        if ($this->usesTimestamps() && ! is_null($this->getUpdatedAtColumn())) {
            $this->{$this->getUpdatedAtColumn()} = $time;

            $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
        }

        $query->update($columns);

        $this->syncOriginalAttributes(array_keys($columns));
    }

    public function unpublish()
    {
        // If the unpublishing event return false, we will exit the operation.
        // Otherwise, we will clear the published at timestamp and continue
        // with the operation
        if ($this->fireModelEvent('unpublishing') === false) {
            return false;
        }

        $this->{$this->getPublishedAtColumn()} = null;

        $this->exists = true;

        $result = $this->save();

        $this->fireModelEvent('unpublished', false);

        return $result;
    }

    /**
     * Determine if the model instance has been published.
     *
     * @return bool
     */
    public function isPublished()
    {
        return ! is_null($this->{$this->getPublishedAtColumn()});
    }

    /**
     * Register a "unpublishing" model event callback with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function unpublishing($callback)
    {
        static::registerModelEvent('unpublishing', $callback);
    }

    /**
     * Register a "published" model event callback with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function published($callback)
    {
        static::registerModelEvent('published', $callback);
    }

    /**
     * Register a "publishing" model event callback with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function publishing($callback)
    {
        static::registerModelEvent('publishing', $callback);
    }

    /**
     * Register a "un-published" model event callback with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @return void
     */
    public static function unpublished($callback)
    {
        static::registerModelEvent('unpublished', $callback);
    }

    /**
     * Get the name of the "created at" column.
     *
     * @return string
     */
    public function getPublishedAtColumn()
    {
        return defined('static::PUBLISHED_AT') ? static::PUBLISHED_AT : 'published_at';
    }

    /**
     * Get the fully qualified "created at" column.
     *
     * @return string
     */
    public function getQualifiedPublishedAtColumn()
    {
        return $this->qualifyColumn($this->getPublishedAtColumn());
    }
}
