# secp256k1_nostr extension for PHP

[![Continuous Integration](https://github.com/1ma/schnorr-php-ext/actions/workflows/build.yml/badge.svg)](https://github.com/1ma/schnorr-php-ext/actions/workflows/build.yml)

`secp256k1_nostr` is a PHP extension to validate [Nostr](https://nostr-resources.com/) events in accordance to [NIP-01](https://github.com/nostr-protocol/nips/blob/master/01.md).

It is implemented as a thick, opinionated wrapper around Bitcoin Core's [libsecp256k1](https://github.com/bitcoin-core/secp256k1).

## Sample Usage

```php
<?php

$event = '
{
  "id": "62fa167369a603b1181a49ecf2e20e7189833417c3fb49666c5644901da27bcc",
  "pubkey": "84fdf029f065438702b011c2002b489fd00aaea69b18efeae8261c44826a8886",
  "created_at": 1689033061,
  "kind": 1,
  "tags": [],
  "content": "This event was created at https://nostrtool.com/ with a throwaway key.",
  "sig": "a67e8d286605e3d7dfd3e0bd1642f85a25bb0cd70ec2ed941349ac879f617868a3ffa2a9040bb43c024594a79e4878429a990298c51ae4d6d20533589f4a04df"
}';

$json = json_decode($event);

var_dump(secp256k1_nostr_verify($json->pubkey, $json->id, $json->sig));

// Mangle last half-byte of the signature on purpose
$json->sig[127] = 'e';

var_dump(secp256k1_nostr_verify($json->pubkey, $json->id, $json->sig));
```

The above script will output:

```
bool(true)
bool(false)
```

## Installation (Linux)

These instructions are tailored for Ubuntu 22.04 LTS.
Nevertheless, only the first step might be a bit different on other Linux distributions that don't use the APT package manager.

1. Install the required dependencies to build both the extension and secp256k1.

    ```shell
    $ sudo apt install autoconf build-essential git libtool php8.1-dev pkgconf
    ```

2. Clone this repository and its submodules.

    ```shell
    $ git clone https://github.com/1ma/secp256k1-nostr-php
    $ cd secp256k1-nostr-php
    $ git submodule init
    $ git submodule update
    ```

3. Build secp256k1 and the extension in one step. Then install the extension (`secp256k1_nostr.so`) in your local PHP.
   The second command will likely require sudo privileges.

    ```shell
    $ make secp256k1 ext
    $ sudo make -C ext install
    ```

4. Append `extension=secp256k1_nostr.so` to your php.ini file. Note that often the PHP CLI and PHP-FPM have
   separate php.ini files, so you need to edit both. Finally, test that the extension loads correctly with `php -m`. 

   ```shell
   $ echo "extension=secp256k1_nostr.so" | sudo tee -a /etc/php/8.1/cli/php.ini
   $ php -m | grep nostr
   secp256k1_nostr

   $ echo "extension=secp256k1_nostr.so" | sudo tee -a /etc/php/8.1/fpm/php.ini
   $ php-fpm8.1 -m | grep nostr
   secp256k1_nostr
   ```

## Full API

See [secp256k1_nostr.stub.php](ext/secp256k1_nostr.stub.php)

## F.A.Q.

### What was the motivation for development this extension?

I recently started working on a toy [Nostr relay in async PHP](https://github.com/1ma/yar), and a central class in that project is the "Event" domain object that verifies its own signature at construction time.

As I wrote the first handful of unit tests I quickly found out that the existing PHP Schnorr libraries are very slow, therefore I built myself this native extension.

It can be used in any context where Nostr events need to be validated in PHP, not just relays.

### How does this extension differ from [secp256k1-php](https://github.com/Bit-Wasp/secp256k1-php)?

`secp256k1-php` is a generic binding to the `libsecp256k1` library that exposes its full API function by function (also known as a "thin wrapper").
In contrast `secp256k1_nostr` implements a "thick wrapper" that is tailored for Nostr event validation, in that case `libsecp256k1` is just an implementation detail.

In fact `secp256k1_nostr` doesn't even need `libsecp256k1`'s shared libraries installed in the system, as I prefer to rely on the latest stable version and link it statically to the PHP extension.

`secp256k1_nostr` could've been written on top of `secp256k1-php` as as regular PHP library, but unfortunately it seems abandoned.
As I write this it hasn't seen activity on the master branch for 4 years, and doesn't even build on PHP 8.0 and up.

If you need a `libsecp256k1` binding that exposes all its functionality you should try to revive that project.

### Are there stubs for the `secp256k1_nostr` functions?

Yes. Simply use Composer to install `uma/secp256k1-nostr` as a development dependency of your project:

```shell
$ composer require --dev uma/secp256k-nostr
```

This isn't a substitute for the real installation steps outlined above.
It will just make the [secp256k1_nostr.stub.php](ext/secp256k1_nostr.stub.php) file visible for your IDE.

### How do I run the test suite?

```shell
$ make check
```

You will need Valgrind on your system to run this command. On Ubuntu that'd be:

```shell
$ sudo apt install valgrind
```

### Does `secp256k1_nostr` follow [semantic versioning](https://semver.org/)?

Yes, but the API may still evolve before the 1.0 release.

```
4. Major version zero (0.y.z) is for initial development. Anything MAY change at any time.
   The public API SHOULD NOT be considered stable.
```

### Is Windows supported?

No, and I don't intend to work on this. But a PR would be welcome.

### Is `secp256k1_nostr` available on PECL?

No :')
