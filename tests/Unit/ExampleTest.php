<?php

namespace VildanBina\LaravelVersions\Tests\Unit;

use App\Models\Comment;
use App\Models\Tag;
use Arr;
use VildanBina\LaravelVersions\Tests\DefaultTest;


class ExampleTest extends DefaultTest
{
    public function test_create_new_post()
    {
        $post = $this->createPost();

        $this->assertTrue(
            $post->is_current &&
            ! $post->is_published
        );
    }

    public function test_publish_post(): void
    {
        $post = $this->createPost();
        $post->publish();

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'is_current' => true,
            'is_published' => true,
            'uuid' => $post->uuid,
        ]);

        // check if there's no other drafts
        // since the first post created is published
        $this->assertDatabaseMissing('posts', [
            ['id', '!=', $post->id],
            'uuid' => $post->uuid,
        ]);
    }

    public function test_make_changes_to_published_post(): void
    {
        $post = $this->createPost();
        $publishedPost = $post->publish();

        $post = $post->fresh();
        $post->title = $this->faker->text;
        $post->description = 'Approved';
        $post->save();
        $draftPost = $post->draftWithoutSelf();

        $this->assertDatabaseHas('posts', [
            'id' => $publishedPost->id,
            'is_current' => false,
            'is_published' => true,
            'title' => $publishedPost->title,
            'description' => $publishedPost->description,
            'uuid' => $publishedPost->uuid,
        ]);

        $this->assertDatabaseHas('posts', [
            'id' => $draftPost->id,
            'is_current' => true,
            'is_published' => false,
            'title' => $draftPost->title,
            'uuid' => $publishedPost->uuid,
        ]);
    }
}
