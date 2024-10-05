<?php

namespace VildanBina\LaravelVersions\Contracts;

interface Versionable
{
    /**
     * Get the names of the publisher relation columns.
     *
     * @return array<string, string>
     */
    public function getPublisherColumns(): array;

    /**
     * Get the name of the UUID column.
     */
    public function getUuidColumn(): string;

    /**
     * Get the name of the "is current" column.
     */
    public function getIsCurrentColumn(): string;

    /**
     * Get the name of the "is published" column.
     */
    public function getIsPublishedColumn(): string;

    /**
     * Set the publisher of the model.
     */
    public function setPublisher(): static;

    /**
     * Get the columns to exclude during operations.
     *
     * @return array<int, string>
     */
    public function getExcludedColumns(): array;

    /**
     * Get the name of the "published at" column.
     */
    public function getPublishedAtColumn(): string;

    /**
     * Get the draft version excluding the current instance.
     */
    public function draftWithoutSelf(): ?static;

    /**
     * Determine if the model is a draft.
     */
    public function isDraft(): bool;
}
