# Console Diff Renderer

[![Latest Version on Packagist](https://img.shields.io/packagist/v/graze/console-diff-renderer.svg?style=flat-square)](https://packagist.org/packages/graze/console-diff-renderer)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/github/actions/workflow/status/graze/console-diff-renderer/ci.yml?branch=master&style=flat-square)](https://github.com/graze/console-diff-renderer/actions/workflows/ci.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/graze/console-diff-renderer.svg?style=flat-square)](https://packagist.org/packages/graze/console-diff-renderer)

Only render things that have changed to the console.

## Usage

```php
$output = new DiffConsoleOutput($existing);

$output->reWrite([
    'first line',
    'second line',
]);

$output->reWrite([
    'first line here',
    'second line',
]);
```

This will navigate the cursor to the end of `first line` and write ` here` then navigate the cursor back to the end.

 - Supports Formatting
 - Supports ANSI control codes
 - Will wrap around based on the terminal size
 - Can trim based on the terminal size
 - Will only write the number of lines that are visible to the user

## Install

Via Composer

``` bash
$ composer require graze/console-diff-renderer
```

## Development

```bash
$ make build
```

### Testing

```bash
$ make test
```

### Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email security@graze.com instead of using the issue tracker.

## Credits

- [Harry Bragg](https://github.com/h-bragg)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
