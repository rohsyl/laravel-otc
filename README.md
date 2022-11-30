# Laravel One Time Code Authentication

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rohsyl/laravel-otc.svg?style=flat-square)](https://packagist.org/packages/rohsyl/laravel-otc)
[![CI](https://github.com/rohsyl/laravel-otc/actions/workflows/ci.yml/badge.svg)](https://github.com/rohsyl/laravel-otc/actions/workflows/ci.yml)
[![Quality Score](https://img.shields.io/scrutinizer/g/rohsyl/laravel-otc.svg?style=flat-square)](https://scrutinizer-ci.com/g/rohsyl/laravel-otc)
[![Total Downloads](https://img.shields.io/packagist/dt/rohsyl/laravel-otc.svg?style=flat-square)](https://packagist.org/packages/rohsyl/laravel-otc)


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
```php
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

### notifier_class
Define what class will be called to send the notification. By default it use the Notification facade of Laravel.
```php
'notifier_class' => \Illuminate\Support\Facades\Notification::class,
```

### notification_class
Define what notification will be sent.
```
'notification_class' => \rohsyl\LaravelOtc\Notifications\OneTimeCodeNotification::class,
```

You can replace this class by any other notification, you will recieve a `OtcToken $token` as constructor parameters
```php
public function __construct(OtcToken $token) {
    $this->token = $token;
}
```

You can access the code that need to be sent from the `$token` variable
```php
$token->code
```

### authenticatables

This array will define a list of entites that can be used to get authentified. It's like a simplified version of laravel guard.
I might move this to guard in the futur. The main goal is to set what model and what column are used to find the model in the database.

- `user` is the name of the "guard"/type
- `model` is the corresponding eloquent model
- `identifier` is the identifier column that will be used to find the corresponding user
```php
'user' => [
    'model' => \App\Models\User::class,
    'identifier' => 'email',
]
```

## Usage

### Check 

Check if the user is authenticated
``` php
Otc::check()
```
> This method will return `true` or `false`.

If the user is not authentified you can return an error
```php
if(!Otc::check()) {
    return Otc::unauthorizedResponse($user);
}
```
This response will return 401 http error with the following body.
```json
{
    "request_code_url": ".../vendor/rohsyl/laravel-otc/auth/request-code",
    "request_code_body": {
        "type": "user",
        "identifier": "test@test.com"
    }
}
```
You must use the `request_code_url` as the url to request a code (ye seem obvious) and you must pass the `request_code_body` as the body in json format !

### Request a code
Send a post request
```
POST /vendor/rohsyl/laravel-otc/auth/request-code
```
with body
```json
{
    "type": "user",
    "identifier": "test@test.com"
}
```
> You need to send the `type` and the `identifier` of your authenticatables entity

An email will be sent to the corresponding entity if available. The email will contain the code.

### Request a token
Send a post request
```
POST /vendor/rohsyl/laravel-otc/auth/code
```
with body
```json
{
    "type": "user",
    "identifier": "test@test.com",
    "code": <code>
}
```
> You need to send the `code` that should have been retrieved from the user through a form or anything else.

You will recieve a token back
```json
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

### Troubleshooting

#### CORS

If you use `fruitcake/laravel-cors` to manage CORS in your app. You will get `CORS error` when doing call to this package endpoints.

You will need to add a new path in your `config/cors.php` in the `paths` array

```
    'paths' => [
        // ...
        'vendor/rohsyl/laravel-otc/*',
    ],
```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please use the issue tracker.

## Credits

- [rohsyl](https://github.com/rohsyl)
- [All Contributors](../../contributors)


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
