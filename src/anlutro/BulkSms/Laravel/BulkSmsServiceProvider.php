<?php
namespace anlutro\BulkSms\Laravel;

use Illuminate\Support\ServiceProvider;

class BulkSmsServiceProvider
{
	public function register()
	{
		$this->app['bulksms'] = $this->app->share(function($app) {
			$username = $app['config']->get('bulksms::username');
			$password = $app['config']->get('bulksms::password');
			return new BulkSmsService($username, $password);
		});
	}

	public function provides()
	{
		return ['bulksms'];
	}
}
