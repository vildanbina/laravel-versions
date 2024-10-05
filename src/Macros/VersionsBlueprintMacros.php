<?php

namespace VildanBina\LaravelVersions\Macros;

use Illuminate\Database\Schema\Blueprint;

class VersionsBlueprintMacros
{
    public static function register(): void
    {
        Blueprint::macro('versionables', function (
            ?string $uuid = null,
            ?string $publishedAt = null,
            ?string $isPublished = null,
            ?string $isCurrent = null,
            ?string $publisherMorphName = null,
        ): void {
            /** @var Blueprint $this */
            $uuid ??= config('versions.column_names.uuid', 'uuid');
            $publishedAt ??= config('versions.column_names.published_at', 'published_at');
            $isPublished ??= config('versions.column_names.is_published', 'is_published');
            $isCurrent ??= config('versions.column_names.is_current', 'is_current');
            $publisherMorphName ??= config('versions.column_names.publisher_morph_name', 'publisher');

            $this->uuid($uuid)->nullable();
            $this->timestamp($publishedAt)->nullable();
            $this->boolean($isPublished)->default(false);
            $this->boolean($isCurrent)->default(false);
            $this->nullableMorphs($publisherMorphName);

            $this->index([$uuid, $isPublished, $isCurrent]);
        });

        Blueprint::macro('dropVersionables', function (
            ?string $uuid = null,
            ?string $publishedAt = null,
            ?string $isPublished = null,
            ?string $isCurrent = null,
            ?string $publisherMorphName = null,
        ): void {
            /** @var Blueprint $this */
            $uuid ??= config('versions.column_names.uuid', 'uuid');
            $publishedAt ??= config('versions.column_names.published_at', 'published_at');
            $isPublished ??= config('versions.column_names.is_published', 'is_published');
            $isCurrent ??= config('versions.column_names.is_current', 'is_current');
            $publisherMorphName ??= config('versions.column_names.publisher_morph_name', 'publisher_morph_name');

            $this->dropIndex([$uuid, $isPublished, $isCurrent]);
            $this->dropMorphs($publisherMorphName);

            $this->dropColumn([
                $uuid,
                $publishedAt,
                $isPublished,
                $isCurrent,
            ]);
        });
    }
}
