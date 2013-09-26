<?php
namespace anlutro\BulkSms\Laravel;

use anlutro\BulkSms\BulkSmsService;
use Illuminate\Support\ServiceProvider;

class BulkSmsServiceProvider extends ServiceProvider
{
	protected $defer = false;

	public function register()
	{
		$this->app['bulksms'] = $this->app->share(function($app)
		{
			$username = $app['config']->get('bulk-sms::username');
			$password = $app['config']->get('bulk-sms::password');

			if (isset($app['curl'])) {
				return new BulkSmsService($username, $password, $app['curl']);
			} else {
				return new BulkSmsService($username, $password);
			}
		});
	}

	public function boot()
	{
		$path = realpath( $this->guessPackagePath() . '/..' );
		$this->package('anlutro/bulk-sms', 'bulk-sms', $path);
	}

	public function provides()
	{
		return ['bulksms'];
	}
}
