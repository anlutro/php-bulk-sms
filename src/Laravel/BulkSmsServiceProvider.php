<?php
/**
 * BulkSMS PHP implementation
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   anlutro/bulk-sms
 */

namespace anlutro\BulkSms\Laravel;

use Illuminate\Foundation\Application;
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
                if (version_compare(Application::VERSION, '5.0', '>=')) {
                    $username = $app[ 'config' ]->get('bulk-sms.username');
                    $password = $app[ 'config' ]->get('bulk-sms.password');
                    $baseurl  = $app[ 'config' ]->get('bulk-sms.baseurl');
                } else {
                    $username = $app[ 'config' ]->get('bulk-sms::username');
                    $password = $app[ 'config' ]->get('bulk-sms::password');
                    $baseurl  = $app[ 'config' ]->get('bulk-sms::baseurl');
                }

                if (isset($app[ 'curl' ])) {
                    return new BulkSmsService($username, $password, $baseurl, $app[ 'curl' ]);
                } else {
                    return new BulkSmsService($username, $password, $baseurl, null);
                }
            }
        );

        if (version_compare(Application::VERSION, '5.0', '>=')) {
            $dir = dirname(dirname(__DIR__)) . '/resources';
            $this->mergeConfigFrom($dir . '/config.php', 'bulk-sms');
        }
    }

    /**
     * Load the package config files.
     *
     * @return void
     */
    public function boot()
    {
        $dir = dirname(dirname(__DIR__)) . '/resources';

        if (version_compare(Application::VERSION, '5.0', '>=')) {
            $this->publishes(
                [
                    $dir . '/config.php' => config_path('bulk-sms.php')
                ],
                'config'
            );
        } else {
            $this->app[ 'config' ]->package('bulk-sms', $dir, 'bulk-sms');
        }
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
