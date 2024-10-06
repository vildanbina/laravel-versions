<?php

namespace VildanBina\LaravelVersions\Concerns;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use JetBrains\PhpStorm\ArrayShape;
use VildanBina\LaravelVersions\Contracts\Draftable;
use VildanBina\LaravelVersions\Facades\LaravelVersions;
use VildanBina\LaravelVersions\Handlers\DraftService;
use VildanBina\LaravelVersions\Observers\VersionObserver;

trait HasVersions
{
    /**
     * Initialize the HasVersions trait for a model.
     */
    public function initializeHasVersions(): void
    {
        $this->addObservableEvents(['publishing', 'published']);

        $this->mergeFillable([
            $this->getPublishedAtColumn(),
            $this->getIsCurrentColumn(),
            $this->getIsPublishedColumn(),
            $this->getUuidColumn(),
        ]);

        $this->mergeCasts([
            $this->getPublishedAtColumn() => 'datetime',
            $this->getIsCurrentColumn() => 'boolean',
            $this->getIsPublishedColumn() => 'boolean',
        ]);

        $this->registerObserver(VersionObserver::class);
    }

    /**
     * Create a new draft version of the model.
     *
     * @return $this|null
     */
    public function newDraft(): ?static
    {
        return $this->draftService()->createDraft();
    }

    /**
     * Get the draft service instance.
     */
    public function draftService(): Draftable
    {
        return new DraftService($this);
    }

    /**
     * Set the publisher of the model.
     *
     * @return $this
     */
    public function setPublisher(): static
    {
        $publisherColumns = $this->getPublisherColumns();

        if ($this->{$publisherColumns['id']} === null && LaravelVersions::getCurrentUser()) {
            $this->publisher()->associate(LaravelVersions::getCurrentUser());
        }

        return $this;
    }

    /**
     * Get the names of the publisher relation columns.
     *
     * @return array<string, string>
     */
    #[ArrayShape(['id' => 'string', 'type' => 'string'])]
    public function getPublisherColumns(): array
    {
        $morphName = config('versions.column_names.publisher_morph_name', 'publisher');

        return [
            'id' => $morphName.'_id',
            'type' => $morphName.'_type',
        ];
    }

    /**
     * Define the publisher morph relation.
     *
     * @return MorphTo
     */
    public function publisher()
    {
        return $this->morphTo(config('versions.column_names.publisher_morph_name', 'publisher'));
    }

    /**
     * Get the name of the "published at" column.
     */
    public function getPublishedAtColumn(): string
    {
        return defined(static::class . '::PUBLISHED_AT')
            ? static::PUBLISHED_AT
            : config('versions.column_names.published_at', 'published_at');
    }

    /**
     * Get the name of the "is current" column.
     */
    public function getIsCurrentColumn(): string
    {
        return defined(static::class . '::IS_CURRENT')
            ? static::IS_CURRENT
            : config('versions.column_names.is_current', 'is_current');
    }

    /**
     * Get the name of the "is published" column.
     */
    public function getIsPublishedColumn(): string
    {
        return defined(static::class . '::IS_PUBLISHED')
            ? static::IS_PUBLISHED
            : config('versions.column_names.is_published', 'is_published');
    }

    /**
     * Get the name of the "UUID" column.
     */
    public function getUuidColumn(): string
    {
        return defined(static::class . '::UUID')
            ? static::UUID
            : config('versions.column_names.uuid', 'uuid');
    }

    /**
     * Publish the model.
     *
     * @return $this
     */
    public function publish(): static
    {
        if ($this->fireModelEvent('publishing') === false) {
            return $this;
        }

        $published = $this->draftService()->publish();

        $published->fireModelEvent('published');

        return $published;
    }

    /**
     * Get the columns to exclude during operations.
     */
    public function getExcludedColumns(): array
    {
        return array_merge(
            array_values($this->getPublisherColumns()),
            [$this->getPublishedAtColumn()],
            property_exists($this, 'excludedColumns') ? $this->excludedColumns : []
        );
    }

    /**
     * Get the fully qualified publisher relation columns.
     */
    public function getQualifiedPublisherColumns(): array
    {
        $columns = $this->getPublisherColumns();

        return [
            'id' => $this->qualifyColumn($columns['id']),
            'type' => $this->qualifyColumn($columns['type']),
        ];
    }

    /**
     * Scope a query to only include current versions.
     */
    public function scopeWhereCurrent(Builder $query): void
    {
        $query->where($this->getIsCurrentColumn(), true);
    }

    /**
     * Scope a query to only include published versions.
     */
    public function scopeWherePublished(Builder $query, bool $value = true): void
    {
        $query->where($this->getIsPublishedColumn(), $value);
    }

    /**
     * Scope a query to exclude current versions.
     */
    public function scopeWithoutCurrent(Builder $query): void
    {
        $query->where($this->getIsCurrentColumn(), false);
    }

    /**
     * Scope a query to exclude a specific revision.
     */
    public function scopeExcludeRevision(Builder $query, int|Model $exclude): Builder
    {
        $excludeId = $exclude instanceof Model ? $exclude->getKey() : $exclude;

        return $query->where($this->getKeyName(), '!=', $excludeId);
    }

    /**
     * Get all revisions of the model.
     *
     * @return HasMany
     */
    public function revisions()
    {
        return $this->hasMany(static::class, $this->getUuidColumn(), $this->getUuidColumn());
    }

    /**
     * Get the published version of the model.
     *
     * @return HasOne
     */
    public function published()
    {
        return $this->hasOne(static::class, $this->getUuidColumn(), $this->getUuidColumn())
            ->where($this->getIsPublishedColumn(), true);
    }

    /**
     * Get the draft version of the model.
     *
     * @return HasOne
     */
    public function draft()
    {
        return $this->hasOne(static::class, $this->getUuidColumn(), $this->getUuidColumn())
            ->where($this->getIsCurrentColumn(), true)
            ->where($this->getIsPublishedColumn(), false);
    }

    /**
     * Get the draft version excluding the current instance.
     *
     * @return $this|null
     */
    public function draftWithoutSelf(): ?static
    {
        return $this->draft()->whereNot($this->getKeyName(), $this->getKey())->first();
    }

    /**
     * Determine if the model is a draft.
     */
    public function isDraft(): bool
    {
        $draft = $this->draft;

        return $draft !== null && $draft->getKey() !== $this->getKey();
    }
}
