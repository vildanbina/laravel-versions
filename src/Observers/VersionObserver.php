<?php

namespace VildanBina\LaravelVersions\Observers;

use Illuminate\Support\Str;
use VildanBina\LaravelVersions\Contracts\Versionable;

class VersionObserver
{
    /**
     * Handle the "creating" event.
     */
    public function creating(Versionable $model): void
    {
        $uuidColumn = $model->getUuidColumn();
        $model->{$model->getIsCurrentColumn()} = true;
        $model->{$model->getIsPublishedColumn()} = false;

        if (! $model->{$uuidColumn}) {
            $model->{$uuidColumn} = (string) Str::uuid();
        }
    }

    /**
     * Handle the "updating" event.
     *
     */
    public function updating(Versionable $model): bool
    {
        if ($model->isDirty() && $model->{$model->getIsPublishedColumn()}) {
            $model->fresh()->updateQuietly([$model->getIsCurrentColumn() => false]);

            $draft = $model->newDraft();
            $model->setRelation('draft', $draft);

            return false;
        }

        return true;
    }
}
