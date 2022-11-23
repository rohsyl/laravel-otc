# Laravel One Time Code Authentication

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-otc.svg?style=flat-square)](https://packagist.org/packages/rohsyl/laravel-otc)
[![Build Status](https://img.shields.io/travis/spatie/laravel-otc/master.svg?style=flat-square)](https://travis-ci.org/rohsyl/laravel-otc)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-otc.svg?style=flat-square)](https://scrutinizer-ci.com/g/rohsyl/laravel-otc)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-otc.svg?style=flat-square)](https://packagist.org/packages/rohsyl/laravel-otc)


Laravel One Time Code Authentication allow you to send by mail an one time code to auth your users.

## Installation

You can install the package via composer:

```bash
composer require rohsyl/laravel-otc
```

Run the installer

```bash
php artisan otc:install
```

## Configuration

Edit `config/otc.php`
```
<?php
return [
    'notifier_class' => \Illuminate\Support\Facades\Notification::class,
    'notification_class' => \rohsyl\LaravelOtc\Notifications\OneTimeCodeNotification::class,

    'authenticatables' => [
        'user' => [
            'model' => \App\Models\User::class,
            'identifier' => 'email',
        ]
    ]
];
```

## Usage

### Check 

Check if the user is authentified
``` php
Otc::check()
```

If the user is not authentified you can return an error
```php
return Otc::unauthorizedResponse($lease);
```
This response will return 401 http error with the following body.
```
{
    "request_code_url": "http://localhost:8001/vendor/rohsyl/laravel-otc/auth/request-code",
    "request_code_body": {
        "type": "lease",
        "identifier": "LS-41203"
    }
}
```
You must use the `request_code_url` as the url to request a code (ye seem obvious) and you must pass the `request_code_body` as the body in json format !

### Request a code
Send a post request
```
POST /vendor/rohsyl/laravel-otc/auth/request-code
{
    "type": "user",
    "identifier": "test@test.com"
}
```
> You need to send the `type` and the `identifier` of your authenticatables entity

An email will be sent to the corresponding entity if available. The email will contain the code.

### Request bearer token
Send a post request
```
POST /vendor/rohsyl/laravel-otc/auth/code
{
    "type": "user",
    "identifier": "test@test.com"
    "code": <code>
}
```
> You need to send the `code` that should have been retrieved from the user through a form or anything else.

You will recieve a token back
```
{
    "token": "9vov6FjW47v6JjH...4iPzPH0PwpwdE"
}
```

And you can use this token for every further request.

### Authentified request

When you have the token, you can send it with you request to be authentified.

Pass it in the headers 
```
Authorization: Bearer <token>
```

Or in the query string
```
?token=<token>
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Credits

- [rohsyl](https://github.com/rohsyl)
- [All Contributors](../../contributors)


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
