<?php

namespace VildanBina\LaravelVersions\Handlers;

use Arr;
use VildanBina\LaravelVersions\Contracts\Draftable;
use VildanBina\LaravelVersions\Contracts\Versionable;

class DraftService implements Draftable
{
    public function __construct(
        protected Versionable $model,
    ) {}

    /**
     * Create a new draft of the model.
     */
    public function createDraft(bool $checkDirty = true): ?Versionable
    {
        if ($checkDirty && ! $this->model->isDirty()) {
            return null;
        }

        $excludedColumns = $this->model->getExcludedColumns();
        $uuidColumn = $this->model->getUuidColumn();

        $draft = $this->model->newInstance(array_merge(
            Arr::except($this->model->toArray(), $excludedColumns),
            [
                $uuidColumn => $this->model->{$uuidColumn},
                $this->model->getIsCurrentColumn() => true,
                $this->model->getIsPublishedColumn() => false,
            ]
        ));

        $draft->saveQuietly();

        return $draft;
    }

    /**
     * Publish the draft model.
     */
    public function publish(): Versionable
    {
        $published = $this->model->published ?? $this->model; // In case of creating
        /* @var Versionable $draft */
        $draft = $published->draftWithoutSelf();

        $draftData = $draft?->toArray() ?? [];
        $publishedData = $published->toArray();

        if ($draft) {
            $draft->fill(array_merge(
                $publishedData,
                [
                    $this->model->getIsCurrentColumn() => false,
                    $this->model->getIsPublishedColumn() => false,
                ],
            ));
            $draft->setPublisher();
            $draft->saveQuietly();
        }

        $published->fill(array_merge(
            $draftData,
            [
                $this->model->getIsCurrentColumn() => true,
                $this->model->getIsPublishedColumn() => true,
                $this->model->getPublishedAtColumn() => now(),
            ],
        ));

        $published->setPublisher();
        $published->saveQuietly();

        $this->model->setRelation('published', $published->withoutRelations());

        return $published;
    }
}
