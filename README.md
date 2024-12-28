# Laravel Klevio API Package

This package provides a simple and elegant way to interact with the Klevio API v2 in your Laravel application. It handles JWT authentication and RPC calls to manage smart locks and access control.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/klevio/klevio-api.svg?style=flat-square)](https://packagist.org/packages/klevio/klevio-api)
[![Total Downloads](https://img.shields.io/packagist/dt/klevio/klevio-api.svg?style=flat-square)](https://packagist.org/packages/klevio/klevio-api)
![GitHub Actions](https://github.com/klevio/klevio-api/actions/workflows/main.yml/badge.svg)

## Installation

You can install the package via composer:

```bash
composer require klevio/klevio-api
```

## Configuration

Add the following environment variables to your `.env` file:

```env
KLEVIO_CLIENT_ID=your-client-id
KLEVIO_API_KEY=your-api-key
KLEVIO_PRIVATE_KEY=your-private-key
KLEVIO_PUBLIC_KEY=your-public-key
KLEVIO_API_URL=https://api.klevio.com/v2
```

Note: The private and public keys should be in PEM format. Make sure to properly escape newlines in your .env file.

## Usage

```php
use Klevio\KlevioApi\Facades\KlevioApi;

// Grant key access to a user with metadata
$response = KlevioApi::grantKey(
    'property-123',    // Property ID
    'user@example.com', // User email
    '2024-01-01T00:00:00Z', // From date
    '2024-01-07T23:59:59Z', // To date
    [
        'reservationId' => 'reservation-123',
        'guestName' => 'John Doe',
        'roomNumber' => '101'
    ]
);

// Grant key access without metadata
$response = KlevioApi::grantKey(
    'property-123',
    'user@example.com',
    '2024-01-01T00:00:00Z',
    '2024-01-07T23:59:59Z'
);

// Get all keys for a property
$keys = KlevioApi::getKeys('property-123');

// Use a key (lock/unlock)
$response = KlevioApi::useKey('key-123');
```

## Error Handling

The package will throw exceptions for any API errors. You can catch these using standard Laravel exception handling:

```php
use GuzzleHttp\Exception\RequestException;

try {
    $response = KlevioApi::useKey('key-123');
} catch (RequestException $e) {
    // Handle the error
    $errorMessage = $e->getMessage();
}
```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

- Never commit your API credentials to version control
- Store your private and public keys securely
- Use environment variables for all sensitive configuration
- If you discover any security issues, please email mehedihasansagor.cse@gmail.com

## Credits

-   [Mehedi Hasan Sagor](https://github.com/klevio )
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
