# PHP IPv4 Library

You can use IP-addresses and subnet masks in any format you like: dotted decimal, unsigned long, signed long, hex, dotted hex, dotted octal and cidr.

## Requirements

PHP 5.4+, GMP extension, Yii2 framework

## License

Copyright (c) 2013 Olegs Capligins under the MIT license.<br />

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist dautkom/yii2-ipv4 "*"
```

or add

```
"dautkom/yii2-ipv4": "*"
```

to the require section of your `composer.json` file.

## Usage:

```php
<?php

$net = new \dautkom\ipv4\IPv4();

// Check if IP-address is valid
$net->address('10.0.11.22')->isValid(); // true

// Check if subnet is valid
$net->mask('255.255.255.0')->isValid(); // true

// Check if IP:Mask is valid
$net->address('192.168.0.2')->mask('255.255.255.0')->isValid(); // true
$net->address('192.168.0.2')->mask('255.255.0.255')->isValid(); // false
$net->address('10.0.11.992')->mask('255.255.255.0')->isValid(); // false

// Check if Subnet:Mask is valid
$net->address('192.168.0.0')->mask('255.255.255.0')->isValid(1); // true
$net->address('192.168.0.2')->mask('255.255.255.0')->isValid(1); // false
$net->address('192.168.0.0')->mask('255.255.0.255')->isValid(1); // false

// Retrieve format
// Supported formats: HumanReadable, Hex, Oct, Bin, Dec
$net->address('255.255.255.255')->getFormat(); // HumanReadable
$net->address('0xffffff00')->getFormat(); // Hex

// For subnets one more format is supported: CIDR.
// Slash is mandatory for CIDR format. Other formats are similar to IP-address.
// This method doesn't check if argument is a valid netmask. Use mask($arg)->isValid() instead.
$net->mask('/22')->getFormat(); // Cidr. For CIDR netmask slash is mandatory.
$net->mask('0xff.0xff.0xff.0x00')->getFormat(); // Hex

// Convert IP-address to a human-readable format
$net->address('0300.0250.0001.0031')->getHumanReadable(); // 192.168.1.25
$net->mask('0xffffffff')->getHumanReadable(); // 255.255.255.255

// Other convertations
$net->address('0xc0.0xA8.0xFF.0x6D')->convertTo('Long'); // 3232300909
$net->mask('255.255.255.0')->convertTo('Cidr'); // 24

// Get subnet address from ip and mask
$net->address('192.168.1.2')->mask('255.255.255.0')->getAddress(); // 192.168.1.0

// Check if given address is a subnet address
$net->address('192.168.1.255')->mask('255.255.255.0')->isSubnet(); // false

// Get IP range from subnet address and mask.
// Be sure to provide valid data
$net->address('192.168.13.2')->mask('255.255.255.0')->getRange(); // null
$net->address('192.168.12.0')->mask('255.255.255.0')->getRange(); // array( 0=>"192.168.12.0", 1=>"192.168.12.255" )

// Count amount of avaliable hosts in subnet.
// Be sure to provide valid data
$net->address('192.168.12.128')->mask('255.255.255.0')->countHosts(); // null
$net->address('192.168.12.128')->mask('255.255.255.192')->countHosts(); // 62

// Retrieve broadcast address from ip and mask
$net->address('10.1.2.2')->mask('255.255.255.0')->getBroadcast(); // 10.1.2.255

// Check if given address is a broadcast address
$net->address('10.0.0.39')->mask('255.255.255.0')->isBroadcast(); // false
$net->address('10.0.0.127')->mask('255.255.255.128')->isBroadcast(); // true

// Check if address belongs to a specified network.
// It's possible to set not only subnet address, but a regular ip-address
// to check if ip in the last argument belong to the same network.
$net->address('192.168.13.8')->mask('255.255.255.128')->has('192.168.13.99'); // true
$net->address('172.16.142.0')->mask('255.255.255.192')->has('172.16.142.199'); // false
$net->address('3232235520')->mask('0xff.0xff.0x00.0x00')->has('0300.0250.0377.0155'); // true

// Retrieve maximal CIDR block that requested subnet fits into.
// For a host IP the result will always be 32 as expected.
$net->address('62.85.192.0')->getMaxBlock(); // 18
$net->address('192.168.13.77')->getMaxBlock(); // 32

```