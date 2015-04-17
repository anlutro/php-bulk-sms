# BulkSMS API - PHP implementation
A simple implementation of BulkSMS for PHP.

Includes functionality to send single or batch messages.

## Installation

This package requires PHP 5.4 because I'm too lazy to type `array()`. Sorry.

Using composer: `composer require anlutro/bulk-sms` - list of versions is available through GitHub's tag list.

### Laravel 4

The package includes files to make usage super easy in Laravel 4.

1. Add `anlutro\BulkSms\Laravel\BulkSmsServiceProvider` to the list of providers in `app/config/app.php`.
2. Run `php artisan config:publish anlutro/bulk-sms`. Edit the config file in `app/config/packages/anlutro/bulk-sms` and fill in your username and password.
3. (optional) Add an alias for the facade by adding `'BulkSms' => 'anlutro\BulkSms\Laravel\BulkSms'` to aliases in `app/config/app.php`.

## Usage

Send Single Message
```php
$bulkSms = new anlutro\BulkSms\BulkSmsService('username', 'password', 'baseurl');
$bulkSms->sendMessage('12345678', 'Hello there!');
```

Send Bulk Message
```php
$message1 = new \anlutro\BulkSms\Message("12345678", "Hi there");
$message2 = new \anlutro\BulkSms\Message("12345678", "Hello again");
$bulkSms = new anlutro\BulkSms\BulkSmsService('username', 'password', 'baseurl');
$bulkSms->sendMessage(array($message1,$message2));
```

Get Status report
```php
$bulkSms = new anlutro\BulkSms\BulkSmsService('username', 'password', 'baseurl');
$bulkSms->getStatusForBatchId(693099785);
```


In Laravel 4, you don't need to construct `$bulkSms`, and you can replace `$bulkSms->` with `BulkSms::` provided you followed the installation steps above.

# Contact
Open an issue on GitHub if you have any problems or suggestions.

If you have any questions or want to have a chat, look for anlutro @ chat.freenode.net.

# License
The contents of this repository is released under the [MIT license](http://opensource.org/licenses/MIT).