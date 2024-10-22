[![Latest Stable Version](https://poser.pugx.org/vildanbina/laravel-versions/v)](https://packagist.org/packages/vildanbina/laravel-versions)
[![Total Downloads](https://poser.pugx.org/vildanbina/laravel-versions/downloads)](https://packagist.org/packages/vildanbina/laravel-versions)
[![Latest Unstable Version](https://poser.pugx.org/vildanbina/laravel-versions/v/unstable)](https://packagist.org/packages/vildanbina/laravel-versions)
[![License](https://poser.pugx.org/vildanbina/laravel-versions/license)](https://packagist.org/packages/vildanbina/laravel-versions)
[![PHP Version Require](https://poser.pugx.org/vildanbina/laravel-versions/require/php)](https://packagist.org/packages/vildanbina/laravel-versions)

# Laravel Versions

**Laravel Versions** is a package that adds powerful draft and versioning capabilities to your Eloquent models. With
this package, you can create drafts, manage versions, and publish changes to your models without affecting the currently
published version. When a model is updated, it modifies the existing active draft instead of creating a new one for each
change. If no active draft exists, a new one is created. Once you're ready, you can publish the draft to make it the
active version while maintaining a history of all previous versions.

## Requirements

- PHP >= 8.0
- Laravel 9.x, 10.x, or 11.x

## Installation

You can install the package via Composer:

~~~bash
composer require vildanbina/laravel-versions
~~~

After installation, you need to publish the configuration file:

~~~bash
php artisan vendor:publish --provider="VildanBina\LaravelVersions\VersionsServiceProvider"
~~~

### Database Migrations

The package provides schema macros to add the necessary columns to your tables. You'll need to update your existing
migrations or create new ones to add the drafts columns to your models' tables.

To add the drafts columns to a table (e.g., `posts`), you can use the `drafts()` macro in your migration:

~~~php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDraftsColumnsToPostsTable extends Migration
{
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->versionables();
        });
    }

    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropVersionables();
        });
    }
}
~~~

After updating your migrations, run:

~~~bash
php artisan migrate
~~~

## Configuration

The package includes a configuration file `config/drafts.php` that allows you to customize column names and the
authentication guard. Below is the default configuration:

~~~php
<?php

return [
    'column_names' => [
        'is_current' => 'is_current',
        'is_published' => 'is_published',
        'published_at' => 'published_at',
        'uuid' => 'uuid',
        'publisher_morph_name' => 'publisher',
    ],

    'auth' => [
        'guard' => 'web',
    ],
];
~~~

You can customize these settings as needed.

## Getting Started

Follow these steps to set up versioning for your models:

1. **Install the package via Composer**:

    ~~~bash
    composer require vildanbina/laravel-versions
    ~~~

2. **Publish the configuration file**:

    ~~~bash
    php artisan vendor:publish --provider="VildanBina\LaravelVersions\VersionsServiceProvider"
    ~~~

3. **Add the drafts columns to your database**:

   Create a new migration or update an existing one to include the `drafts()` macro:

    ~~~php
    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class AddDraftsColumnsToPostsTable extends Migration
    {
        public function up()
        {
            Schema::table('posts', function (Blueprint $table) {
                $table->versionables();
            });
        }

        public function down()
        {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropVersionables();
            });
        }
    }
    ~~~

   Then run:

    ~~~bash
    php artisan migrate
    ~~~

4. **Update your model**:

   Implement the `Versionable` interface and use the `HasVersions` trait:

    ~~~php
    <?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use VildanBina\LaravelVersions\Contracts\Versionable;
    use VildanBina\LaravelVersions\Concerns\HasVersions;

    class Post extends Model implements Versionable
    {
        use HasVersions;

        // Optionally, define excluded columns
        protected array $excludedColumns = ['created_at', 'updated_at'];
    }
    ~~~

5. **Use the package in your application**:

   Now you can create, update, and publish drafts.

    ~~~php
    <?php

    // Create a new post (initially a draft)
    $post = Post::create(['title' => 'My First Post', 'content' => 'Hello World']);

    // Publish the postha
   
    $post->publish();

    // Update the post, which modifies the existing draft or creates a new one
    $post->update(['content' => 'Updated content']);

    // Get the current draft
    $draft = $post->draft;

    // Publish the draft
    $draft->publish();
    ~~~

## Usage

To enable versioning for a model, implement the `Versionable` interface and use the `HasVersions` trait provided by the
package.

### Example with a `Post` Model

First, update your `Post` model:

~~~php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use VildanBina\LaravelVersions\Contracts\Versionable;
use VildanBina\LaravelVersions\Concerns\HasVersions;

class Post extends Model implements Versionable
{
    use HasVersions;

    // Optionally, define excluded columns
    protected array $excludedColumns = ['created_at', 'updated_at'];
}
~~~

The `$excludedColumns` property allows you to specify which columns should be excluded from being replaced when creating
drafts or exchanging data between drafts and published versions.

## Core Functionality

### Draft Management Workflow

- **Single Active Draft**: The package maintains a single active draft for each model instance. When you update a model,
  it modifies the existing active draft. If no active draft exists, a new one is created.
- **Publishing**: When you publish a draft, the changes are applied to the main published version. The data from the
  published version is then applied to the current draft to keep it as the previous version.
- **New Draft After Publishing**: After publishing, if you make new changes, a new draft is created. All subsequent
  changes are saved to this active draft until it is published.

### Automatic Draft Creation

- When a model instance is updated, it modifies the existing active draft. If there is no active draft, a new one is
  created automatically.
- The `publish()` method can be used to publish the draft, making it live.

### UUID as Grouping Identifier

The `uuid` column serves as a unique identifier for each set of versions. This means that a published model and all its
related drafts share the same `uuid`, allowing you to group them together and track the history of a specific model
instance.

### Model Events

The package includes model events such as `publishing` and `published`. You can attach custom logic to these events
using observers to handle additional tasks whenever a model is published or a draft is being published.

### Core Methods and Relationships

#### Publishing

- **`publish()`**: Publishes the current draft, updating the original model data and setting `is_published` and
  `is_current` flags accordingly.

#### Retrieving Versions

- **`revisions()`**: Returns a collection of all versions (drafts and published) associated with the model, providing
  easy access to the version history.
- **`draft()`**: Retrieves the current active draft version of the model, if available.
- **`published()`**: Retrieves the published version of the model.

#### Checking Draft Status

- **`isDraft()`**: Returns a boolean indicating whether the current instance is a draft or not.
- **`draftWithoutSelf()`**: Retrieves drafts that are not the same as the current instance, helping you identify other
  drafts for the model.

#### User Associations

- **`publisher()`**: Returns the user or entity that published the current version, allowing you to trace who made
  specific changes.

#### Additional Functionalities

- **`getExcludedColumns()`**: Returns an array of columns that are excluded from being saved or overwritten when
  creating drafts. This ensures certain fields remain unchanged across versions.

### Example Usage

#### Creating and Publishing a Model

~~~php
<?php

// Create a new post (initially a draft)
$post = Post::create(['title' => 'Initial Title', 'content' => 'Initial Content']);

// Publish the post
$post->publish();
~~~

#### Updating a Model and Working with Drafts

~~~php
<?php

// Update the post, which modifies the existing draft or creates a new one
$post->update(['title' => 'Updated Title']);

// Get the current draft
$draft = $post->draft;

// Check if the post is a draft
if ($draft->isDraft()) {
    echo "This post is currently a draft!";
}

// Publish the updated draft
$draft->publish();
~~~

#### Retrieving Version History

~~~php
<?php

// Get all versions (drafts and published)
$revisions = $post->revisions;

// Loop through revisions
foreach ($revisions as $revision) {
    echo $revision->title . ' - ' . ($revision->is_published ? 'Published' : 'Draft');
}
~~~

#### Getting the Publisher

~~~php
<?php

// Get the user who published the post
$publisher = $post->publisher;
~~~

## Query Scopes

The package provides the following query scopes that can be used for querying models:

- **`whereCurrent()`**: Retrieve records where `is_current` is `true`.
- **`wherePublished()`**: Retrieve records where `is_published` is `true`.
- **`withoutCurrent()`**: Retrieve records where `is_current` is `false`.
- **`excludeRevision($revision)`**: Exclude a specific revision from the query.

### Examples

~~~php
<?php

// Retrieve all current posts (either published or drafts that are current)
$currentPosts = Post::whereCurrent()->get();

// Retrieve all published posts
$publishedPosts = Post::wherePublished()->get();

// Retrieve all drafts
$drafts = Post::whereCurrent()->wherePublished(false)->get();
~~~

## Tips & Best Practices

- **Using Transactions**: When performing operations that involve multiple steps, it's recommended to use database
  transactions to ensure data consistency. If any step fails, all changes will be rolled back, preventing any incomplete
  versioning states.

    ~~~php
    <?php

    use Illuminate\Support\Facades\DB;

    DB::transaction(function () use ($post) {
        $post->update(['title' => 'Updated Title']);
        $post->publish();
    });
    ~~~

- **Customizing Excluded Columns**: Use the `$excludedColumns` property in your model to specify columns that should not
  be versioned, such as timestamps or other metadata.

## Extensibility and Customization

The package is designed to be flexible and can be customized to fit your application's needs.

### Overriding Methods

You can extend the functionality by overriding methods in the `HasVersions` trait within your model.

### Custom Events

Leverage the `publishing` and `published` model events to add custom logic when a draft is being published.

### Customizing Authentication Guard

You can change the authentication guard used to associate the publisher by updating the `auth.guard` setting in the
`config/drafts.php` configuration file.

## Observers

The package automatically handles the `creating`, `saving`, and `published` events via the `VersionObserver` to manage
the
draft lifecycle. If you need to customize this behavior, you can create your own observer or extend the existing one.

## To Do

- **Handling Relationships**: Implementing support for all relationships in the versioning process, allowing
  relationships to be included and managed across drafts and published versions.

- **Service for Detecting Changes**: Create a service that can identify and compare all changes between different
  versions of a model, providing a clear history of what has changed across versions.

- **Enable/Disable Versioning**: Add functionality to globally enable or disable the versioning system, for scenarios
  such as when super admins want to bypass the versioning process or temporarily deactivate it.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please e-mail vildanbina@gmail.com to report any security vulnerabilities instead of the issue tracker.

## Credits

- [Vildan Bina](https://github.com/vildanbina)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

