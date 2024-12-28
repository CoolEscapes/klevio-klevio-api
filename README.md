# 🔐 Laravel Klevio API Package

<div align="center">



[![Latest Version on Packagist](https://img.shields.io/packagist/v/klevio/klevio-api.svg?style=for-the-badge)](https://packagist.org/packages/klevio/klevio-api)
[![Total Downloads](https://img.shields.io/packagist/dt/klevio/klevio-api.svg?style=for-the-badge)](https://packagist.org/packages/klevio/klevio-api)
[![License](https://img.shields.io/github/license/klevio/klevio-api?style=for-the-badge)](LICENSE.md)

A powerful Laravel package for seamless integration with [Klevio's](https://klevio.com/) smart access control system. Manage digital keys, access rights, and smart locks with enterprise-grade security.

[Documentation](#documentation) •
[Installation](#installation) •
[Usage Examples](#usage) •
[Contributing](#contributing)

</div>

## ✨ Features

- 🔒 **Secure Authentication** - JWT-based API authentication
- 🔑 **Key Management** - Grant and revoke access rights
- 🏢 **Property Control** - Manage multiple properties
- 📱 **Smart Lock Integration** - Remote lock/unlock capabilities
- 🔄 **Real-time Operations** - Instant access management
- 🛡️ **Enterprise Security** - ES256 encryption

## 📚 Documentation

Comprehensive documentation is available:

- [API Documentation (PDF)](assets/Klevio%20Public%20API%20Docs%20(V2).pdf)
- [Official Klevio Website](https://klevio.com/)

## 🚀 Quick Start

### Installation

```bash
composer require klevio/klevio-api
```

### Getting Started

1️⃣ **Sign Up for API Access**
   - Visit [Klevio Scheduler Portal](https://operations.klevio.net/scheduler/signup)
   - Complete the registration process
   - Obtain your API credentials

   ![Klevio API Integration](assets/image.png)

2️⃣ **Generate Security Keys**
   ```bash
   # Generate ES256 Private Key
   openssl ecparam -genkey -name secp256k1 -out private_key.pem
   
   # Extract Public Key
   openssl ec -in private_key.pem -pubout -out public_key.pem
   ```

3️⃣ **Configure Environment**

Add to your `.env` file:
```env
KLEVIO_CLIENT_ID="your_client_id"
KLEVIO_API_KEY="your_api_key"
KLEVIO_API_URL="https://api.klevio.com/v2"

# Keys should be properly formatted with escaped newlines
KLEVIO_PRIVATE_KEY="-----BEGIN EC PRIVATE KEY-----\nMIGHAgEA...\n-----END EC PRIVATE KEY-----"
KLEVIO_PUBLIC_KEY="-----BEGIN PUBLIC KEY-----\nMFkwEwYH...\n-----END PUBLIC KEY-----"
```

## 💡 Usage Examples

### Managing Access Keys

```php
use Klevio\KlevioApi\Facades\KlevioApi;

// Grant Access with Metadata
$response = KlevioApi::grantKey(
    propertyId: 'property-123',
    email: 'guest@example.com',
    from: '2024-01-01T00:00:00Z',
    to: '2024-01-07T23:59:59Z',
    metadata: [
        'reservationId' => 'booking-123',
        'guestName' => 'John Doe',
        'roomNumber' => '101'
    ]
);

// List Property Keys
$keys = KlevioApi::getKeys('property-123');

// Control Lock
$response = KlevioApi::useKey('key-123');
```

### Error Handling

```php
use GuzzleHttp\Exception\RequestException;

try {
    $response = KlevioApi::useKey('key-123');
} catch (RequestException $e) {
    Log::error('Klevio API Error', [
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
}
```

## 🛡️ Security Best Practices

- Store credentials securely using environment variables
- Never commit sensitive keys to version control
- Regularly rotate API keys
- Monitor API access logs
- Use HTTPS for all API communications

## 🧪 Testing

```bash
composer test
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📝 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 🔗 Links

- [Klevio Website](https://klevio.com)
- [API Documentation](assets/Klevio%20Public%20API%20Docs%20(V2).pdf)
- [Support](mailto:mehedihasansagor.cse@gmail.com)

---

<div align="center">
Made with ❤️ by <a href="mailto:mehedihasansagor.cse@gmail.com">Mehedi Hasan Sagor</a>
</div>
