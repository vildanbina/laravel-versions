<?php

namespace VildanBina\LaravelVersions\Tests\Unit;

use VildanBina\LaravelVersions\Tests\DefaultTest;

class DraftTest extends DefaultTest
{
    public function test_create_new_draft()
    {
        $post = $this->createPost();
        $post->publish();

        $post->title = 'Unknown Title';
        $draft = tap($post, fn ($updatedPost) => $updatedPost->save())->draftWithoutSelf();

        $this->assertNotNull($draft);
        $this->assertTrue($draft->is_current);
        $this->assertFalse($draft->is_published);
        $this->assertEquals($post->uuid, $draft->uuid);
    }

    public function test_draft_inherits_attributes_from_original()
    {
        $post = $this->createPost();
        $post->publish();

        $post->title = 'Original Title';
        $draft = tap($post, fn ($updatedPost) => $updatedPost->save())->draftWithoutSelf();

        $this->assertEquals('Original Title', $draft->title);
    }

    public function test_draft_has_separate_id_from_original()
    {
        $post = $this->createPost();
        $post->publish();

        $post->title = 'Unknown Title';
        $draft = tap($post, fn ($updatedPost) => $updatedPost->save())->draftWithoutSelf();

        $this->assertNotEquals($post->id, $draft->id);
        $this->assertEquals($post->uuid, $draft->uuid);
    }

    public function test_drafting_unpublished_post_returns_null_or_handles_appropriately()
    {
        $post = $this->createPost(); // Not published

        $draft = $post->newDraft();

        // Assuming it returns null when trying to create a draft of an unpublished post
        $this->assertNull($draft);
    }

    public function test_drafting_published_post_creates_new_draft()
    {
        $post = $this->createPost();
        $post->publish();

        $post->title = 'Unknown Title';
        $draft = tap($post, fn ($updatedPost) => $updatedPost->save())->draftWithoutSelf();

        $this->assertNotNull($draft);
        $this->assertFalse($draft->is_published);
    }
}
