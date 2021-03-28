<img src="https://banners.beyondco.de/Laravel%20Publishable.png?theme=light&packageManager=composer+require&packageName=lambdadigamma%2Flaravel-publishable&pattern=graphPaper&style=style_2&description=An+publishable+trait+for+Laravel+Eloquent+models&md=1&showWatermark=0&fontSize=150px&images=clock&widths=300&heights=300" alt="Laravel Publishable">

<p align="center">
<a href="https://github.com/lambdadigamma/laravel-publishable/actions"><img src="https://github.com/lambdadigamma/laravel-publishable/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/lambdadigamma/laravel-publishable"><img src="https://img.shields.io/packagist/v/lambdadigamma/laravel-publishable" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/lambdadigamma/laravel-publishable"><img src="https://img.shields.io/packagist/l/lambdadigamma/laravel-publishable" alt="License"></a>
</p>

A simple package for making Laravel Eloquent models 'publishable'.
Not published models are excluded from queries by default but can be queried via extra scope.

This package allows easy publishing and unpublishing of models by combining scopes and macros.

## Installation

You can install the package via composer:

```bash
composer require lambdadigamma/laravel-publishable
```

## Usage

#### Migrations

The `Publishable` trait works similarly to Laravel's `SoftDeletes` trait. This package provides a macro for Laravel's Migration Builder.
Just use the `publishedAt` macro in your migration to get started:

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('text');
    $table->timestamps();
    $table->publishedAt(); // Macro provided by the package
});
```

#### Eloquent Model Trait

After providing a `published_at` timestamp on your model, you can use the `Publishable` trait on your Eloquent model:

```php
namespace App\Models;

use \Illuminate\Database\Eloquent\Model;
use \LaravelPublishable\Publishable;

class Post extends Model {
    use Publishable;
    ...
}
```

#### Extensions

The extensions shipped with this trait include; `publish`, `unpublish`, `withNotPublished`, `withoutPublished`, `onlyPublished` and can be used accordingly:

```php
$post = Post::first();
$post->publish();
$post->unpublish();

$postsWithNotPublished = Post::query()->withNotPublished();
$onlyNotPublishedPosts = Post::query()->onlyNotPublished();
```

When not specifing any additional scopes, all not published models are excluded from the query by default (`withoutNotPublished`) to prevent leaks of not published data.

### Testing

`composer test`

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email github@lambdadigamma.com instead of using the issue tracker.

## Credits

-   [Lennart Fischer](https://github.com/lambdadigamma)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
