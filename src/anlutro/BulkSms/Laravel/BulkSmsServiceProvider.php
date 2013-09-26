<?php
namespace anlutro\BulkSms\Laravel;

use Illuminate\Support\ServiceProvider;

class BulkSmsServiceProvider extends ServiceProvider
{
	protected $defer = false;

	public function register()
	{
		$this->app['bulksms'] = $this->app->share(function($app) {
			$username = $app['config']->get('bulk-sms::username');
			$password = $app['config']->get('bulk-sms::password');
			return new BulkSmsService($username, $password);
		});
	}

	public function boot()
	{
		$this->package('anlutro/bulk-sms');
	}

	public function provides()
	{
		return ['bulksms'];
	}
}
