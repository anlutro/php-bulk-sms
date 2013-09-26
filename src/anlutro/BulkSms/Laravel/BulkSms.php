<?php
namespace anlutro\BulkSms\Laravel;

use Illuminate\Support\Facades\Facade;

class BulkSms extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'bulksms';
	}
}
