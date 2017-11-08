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
     * Whether the Laravel version is 5.x or not.
     *
     * @var boolean
     */
    protected $l5;

    /**
     * Register the service on the IoC container.
     *
     * @return void
     */
    public function register()
    {
        $l5 = $this->l5 = version_compare(Application::VERSION, '5.0', '>=');

        $factory = function ($app) use($l5) {
            $delim = $l5 ? '.' : '::';
            $config = $app['config'];
            $username  = $config->get("bulk-sms{$delim}username");
            $password  = $config->get("bulk-sms{$delim}password");
            $baseurl   = $config->get("bulk-sms{$delim}baseurl");

            $curl = isset($app['curl']) ? $app['curl'] : null;

            return new BulkSmsService($username, $password, $baseurl, $curl);
        };

        if (version_compare(Application::VERSION, '5.4', '>=')) {
            $this->app->singleton('bulksms', $factory);
        } else {
            $this->app['bulksms'] = $this->app->share($factory);
        }


        if ($l5) {
            $dir = dirname(dirname(__DIR__)).'/resources';
            $this->mergeConfigFrom($dir.'/config.php', 'bulk-sms');
        }
    }

    /**
     * Load the package config files.
     *
     * @return void
     */
    public function boot()
    {
        $dir = dirname(dirname(__DIR__)).'/resources';

        if ($this->l5) {
            $this->publishes([
                $dir.'/config.php' => config_path('bulk-sms.php')
            ], 'config');
        } else {
            $this->app['config']->package('bulk-sms', $dir, 'bulk-sms');
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
