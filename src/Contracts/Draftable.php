<?php

namespace VildanBina\LaravelVersions\Contracts;

interface Draftable
{
    /**
     * Create a new draft.
     */
    public function createDraft(bool $checkDirty = true): ?Versionable;

    /**
     * Publish the draft.
     */
    public function publish(): Versionable;
}
