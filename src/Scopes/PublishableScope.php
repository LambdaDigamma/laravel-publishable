<?php

namespace LaravelPublishable\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class PublishableScope implements Scope
{
    /**
     * All of the extensions to be added to the builder.
     *
     * @var array
     */
    protected $extensions = [
        'Publish', 
        'Unpublish', 
        'WithNotPublished', 
        'WithoutNotPublished', 
        'OnlyNotPublished'
    ];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if (is_callable([$model, 'getQualifiedPublishedAtColumn'], true, $name)) {
            $builder->where(
                $model->getQualifiedPublishedAtColumn(), '<=', now()->toDateTimeString()
            );
        }
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    /**
     * Get the "published at" column for the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return string
     */
    protected function getPublishedAtColumn(Builder $builder)
    {
        if (count($builder->getQuery()->joins) > 0) {
            return $builder->getModel()->getQualifiedPublishedAtColumn();
        }

        return $builder->getModel()->getPublishedAtColumn();
    }

    /**
     * Add the publish extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addPublish(Builder $builder)
    {
        $builder->macro('publish', function (Builder $builder) {
            $column = $this->getPublishedAtColumn($builder);

            return $builder->update([
                $column => $builder->getModel()->freshTimestampString(),
            ]);
        });
    }

    /**
     * Add the publish extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addUnpublish(Builder $builder)
    {
        $builder->macro('unpublish', function (Builder $builder) {
            $builder->withNotPublished();

            $column = $this->getPublishedAtColumn($builder);

            return $builder->update([
                $column => null,
            ]);
        });
    }

    /**
     * Add the with-notpublished extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addWithNotPublished(Builder $builder)
    {
        $builder->macro('withNotPublished', function (Builder $builder, $withNotPublished = true) {
            if (! $withNotPublished) {
                return $builder->withoutNotPublished();
            }

            return $builder->withoutGlobalScope($this);
        });
    }

    /**
     * Add the without-notpublished extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addWithoutNotPublished(Builder $builder)
    {
        $builder->macro('withoutNotPublished', function (Builder $builder) {
            $model = $builder->getModel();

            return $builder->withoutGlobalScope($this)->where(
                $model->getQualifiedPublishedAtColumn(), '<=', now()->toDateTimeString()
            );
        });
    }

    /**
     * Add the only-notpublished extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addOnlyNotPublished(Builder $builder)
    {
        $builder->macro('onlyNotPublished', function (Builder $builder) {
            $model = $builder->getModel();

            $builder->withoutGlobalScope($this)->whereNull(
                $model->getQualifiedPublishedAtColumn()
            );

            return $builder;
        });
    }
}
