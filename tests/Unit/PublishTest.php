<?php

namespace VildanBina\LaravelVersions\Tests\Unit;

use VildanBina\LaravelVersions\Tests\DefaultTest;

class PublishTest extends DefaultTest
{
    public function test_publish_draft_updates_original()
    {
        $post = $this->createPost();
        $post->publish();

        $post->title = 'Draft Title';
        $post->save();

        $post->publish();

        $this->assertEquals('Draft Title', $post->title);
        $this->assertTrue($post->is_published);
    }

    public function test_publish_sets_published_at_and_publisher()
    {
        $post = $this->createPost();
        $post->publish();

        $this->assertNotNull($post->published_at);
        $this->assertNotNull($post->publisher);
    }

    public function test_publishing_without_current_draft_does_nothing()
    {
        $post = $this->createPost();
        $post->publish();

        $publishedPost = $post->publish();

        $this->assertEquals($post->id, $publishedPost->id);
        $this->assertTrue($post->is_published);
    }

    public function test_publishing_draft_of_unpublished_post_publishes_post()
    {
        $post = $this->createPost(); // Not published

        $post->title = 'New Title';
        $post->save();

        // Assert that a draft is created
        $this->assertNotNull($post);
        $this->assertFalse($post->is_published);
        $this->assertTrue($post->is_current);

        // Publish the draft
        $post->publish();

        // Verify that the original post is now published with updated attributes
        $this->assertTrue($post->is_published);
        $this->assertEquals('New Title', $post->title);
        $this->assertTrue($post->is_current);
    }

    public function test_drafting_unpublished_post_saves_changes_directly()
    {
        $post = $this->createPost(['title' => 'New Title']); // Not published

        // Assert that no draft was created
        $draft = $post->draftWithoutSelf();
        $this->assertNull($draft);

        // Verify that the post's attributes were updated directly
        $this->assertEquals('New Title', $post->title);
        $this->assertFalse($post->is_published);
        $this->assertTrue($post->is_current);
    }
}
