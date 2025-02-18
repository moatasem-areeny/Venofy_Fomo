# Venofy FOMO - Magento 2 Module

Venofy FOMO is a Magento 2 module that allows you to integrate pixel tracking and social proof notifications on your store.

## Features
- Adds a tracking pixel to all pages.
- Configurable from the Magento admin panel.
- Sends order details (customer name and city) to `https://venofy.com`.
- Retrieves coupon data from Magento.

## Installation
### 1. Install via Composer
```sh
composer require venofy/fomo
php bin/magento module:enable Venofy_Fomo
php bin/magento setup:upgrade
php bin/magento cache:flush
```

### 2. Configuration
1. Go to **Stores > Configuration > Venofy > general**.
2. Enable the module.
3. Enter your Pixel Code.
4. Save the configuration.

## Uninstallation
To remove the module, run:
```sh
php bin/magento module:disable Venofy_Fomo
composer remove venofy/fomo
php bin/magento setup:upgrade
php bin/magento cache:flush
```

## License
This module is licensed under the MIT License.

## Support
For support, visit [Venofy.com](https://venofy.com) or contact support@venofy.com.
