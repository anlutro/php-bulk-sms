<?php
/**
 * BulkSMS PHP implementation
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   anlutro/bulk-sms
 */

namespace anlutro\BulkSms\Laravel;

use Illuminate\Support\ServiceProvider;

/**
 * Bootstrap an instance of BulkSmsService so that it can be accessed through
 * a facade and register the config.
 */
class BulkSmsServiceProvider extends ServiceProvider
{
    /**
     * Whether the service provider should be deferred or not.
     *
     * @var boolean
     */
    protected $defer = false;

    /**
     * Register the service on the IoC container.
     *
     * @return void
     */
    public function register()
    {
        $this->app[ 'bulksms' ] = $this->app->share(
            function ($app) {
                $username  = $app[ 'config' ]->get('bulk-sms::username');
                $password  = $app[ 'config' ]->get('bulk-sms::password');
                $baseurl        = $app[ 'config' ]->get('bulk-sms::baseurl');

                if (isset($app[ 'curl' ])) {
                    return new BulkSmsService($username, $password, $baseurl, $app[ 'curl' ]);
                } else {
                    return new BulkSmsService($username, $password, $baseurl, null);
                }
            }
        );
    }

    /**
     * Load the package config files.
     *
     * @return void
     */
    public function boot()
    {
        $path = realpath($this->guessPackagePath() . '/..');
        $this->package('anlutro/bulk-sms', 'bulk-sms', $path);
    }

    /**
     * The services provided.
     *
     * @return array
     */
    public function provides()
    {
        return ['bulksms'];
    }
}
