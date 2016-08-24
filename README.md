# BulkSMS API - PHP implementation
A simple implementation of BulkSMS for PHP.

Includes functionality to send single or batch messages.

## Installation

This package requires PHP 5.4 because I'm too lazy to type `array()`. Sorry.

Using composer: `composer require anlutro/bulk-sms` - list of versions is available through GitHub's tag list.

### Laravel

The package includes files to make usage super easy in Laravel 4 and higher.

1. Add `anlutro\BulkSms\Laravel\BulkSmsServiceProvider` to the list of providers in `app/config/app.php`.
2. Run `php artisan config:publish anlutro/bulk-sms`. Edit the config file in `app/config/packages/anlutro/bulk-sms` and fill in your username and password.
3. (optional) Add an alias for the facade by adding `'BulkSms' => 'anlutro\BulkSms\Laravel\BulkSms'` to aliases in `app/config/app.php`.

## Credentials

To use this library you need create an account with Bulksms. They support several sub-sites for specific regions.

1. Username : Bulksms login
2. Password : Bulksms login password
3. Baseurl : Bulksms sub-site to connect to (e.g. 'http://bulksms.com' or 'http://bulksms.de')

## Usage

Send a single message:

```php
$bulkSms = new anlutro\BulkSms\BulkSmsService('username', 'password', 'baseurl');
$bulkSms->sendMessage('12345678', 'Hello there!');
```

Send more than one message at the same time by providing an array of messages:

```php
$message1 = new \anlutro\BulkSms\Message('12345678', 'Hi there');
$message2 = new \anlutro\BulkSms\Message('12345678', 'Hello again');
$bulkSms = new anlutro\BulkSms\BulkSmsService('username', 'password', 'baseurl');
$bulkSms->sendMessage(array($message1,$message2));
```

Get the status of a batch of messages:

```php
$bulkSms = new anlutro\BulkSms\BulkSmsService('username', 'password', 'baseurl');
$bulkSms->getStatusForBatchId(693099785);
```

## Sending unicode messages

In order to send unicode messages, make sure your message is UTF-16, convert
them to hexadecimal, and specify the 'dca' parameter:

```php
$text = 'السلام عليكم';
$encodedMessage = bin2hex(mb_convert_encoding($text, 'utf-16', 'utf-8')) ; 
$bulkSms->sendMessage('12345678', $encodedMessage, ['dca' => '16bit']);
```

## Send test messages

BulkSms suports test modes (SUCCESS and FAIL) that validate the message and return defined responses without really sending out SMS. In order to send messages in test mode, run the following:

Send message that will return a success:

```php
$bulkSms = new anlutro\BulkSms\BulkSmsService('username', 'password', 'baseurl');
$bulkSms->setTestMode(\anlutro\BulkSms\BulkSmsService::TEST_ALWAYS_SUCCEED);
$bulkSms->getStatusForBatchId(693099785);
```

Send message that will return a failure response - and thus trigger a BulkSmsException :

```php
$bulkSms = new anlutro\BulkSms\BulkSmsService('username', 'password', 'baseurl');
$bulkSms->setTestMode(\anlutro\BulkSms\BulkSmsService::TEST_ALWAYS_FAIL);
$bulkSms->getStatusForBatchId(693099785);
```

In Laravel, you don't need to construct `$bulkSms`, and you can replace `$bulkSms->` with `BulkSms::` provided you followed the installation steps above.

# Contact
Open an issue on GitHub if you have any problems or suggestions.

# License
The contents of this repository is released under the [MIT license](http://opensource.org/licenses/MIT).