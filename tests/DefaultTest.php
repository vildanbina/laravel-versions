<?php

namespace VildanBina\LaravelVersions\Tests;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DefaultTest extends TestCase
{
    //    use RefreshDatabase;
    use WithFaker;

    protected User $user;

    protected Post $post;

    public function setUp(): void
    {
        parent::setUp();

        User::query()->delete();

        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'web');
    }

    protected function createPost(array $additionalData = []): Post
    {
        return Post::factory()->create([
            'user_id' => $this->user->id,
            'description' => 'Test',
            ...$additionalData,
        ]);
    }
}
