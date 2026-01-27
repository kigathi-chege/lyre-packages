# Lyre File

Lyre File is a [Lyre](https://packagist.org/packages/lyre/lyre) addon for simple Media Management. It comes with the following predefined models to help you manage your media content:

- File - This defines the actual media file
- Attachment - This is a morph model defining your relationships to your media files

## Installation

```bash
composer require lyre/file
```

### Publish Assets

```bash
php artisan vendor:publish --provider="Lyre\File\Providers\LyreFileServiceProvider"
```

After installation, add the `HasFile` trait to all the relevant models:

```php
use Lyre\File\Concerns\HasFile;

use HasFile;
```

### Dependencies

Lyre Content depends on [Lyre](https://packagist.org/packages/lyre/lyre) and [Laravel Filament](https://filamentphp.com/). To complete installation, especially if your require the functionalities from Laravel Filament, follow these additional commands:

```bash
php artisan filament:install --panels
```

To create an admin user:

```bash
php artisan make:filament-user
```

#### Discover Content Filament Resources

To Discover Lyre File Filament Resources on your Filament dashboard, add the LyreFileFilamentPlugin to your Filament panel like so:

```php
use Lyre\File\Filament\Plugins\LyreFileFilamentPlugin;

$panel->plugins([
    new LyreFileFilamentPlugin(),
]);
```

## SelectFromGallery Custom Field

Lyre File comes with a custom field to select files from gallery. You may define whether or not your model has multiple files. By default, your model will only have one file.

```php
use Lyre\File\Filament\Forms\Components\SelectFromGallery;

SelectFromGallery::make('files')->label('Featured Images')->multiple()
```
