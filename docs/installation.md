# Installation

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

## Applications that use Symfony Flex


Open a command console, enter your project directory and execute:

```bash
$ composer require svc/totp-bundle
```

## Applications that don't use Symfony Flex

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require svc/totp-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Svc\UtilBundle\SvcTotpBundle::class => ['all' => true],
];
```

## Additional steps

The bundle "endroid/qr-code-bundle" is used to display the QR code. This is installed during the installation.

The following question must be answered with "y":
```console
endroid/installer contains a Composer plugin which is currently not in your allow-plugins config. See https://getcomposer.org/allow-plugins
Do you trust "endroid/installer" to execute code and wish to enable it now? (writes "allow-plugins" to composer.json) [y,n,d,?] y
```