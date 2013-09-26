<?php
/**
 * BulkSMS PHP implementation
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   anlutro/bulk-sms
 */

namespace anlutro\BulkSms\Laravel;

use Illuminate\Support\Facades\Facade;

/**
 * Facade for easy access to a BulkSmsService instance.
 */
class BulkSms extends Facade
{
	/**
	 * The facade accessor.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'bulksms';
	}

	/**
	 * Send a view with data to a recipient. Made to imitate Laravel's
	 * Mail::send syntax.
	 *
	 * @param  string $view
	 * @param  array  $data
	 * @param  string $recipient Phone number
	 *
	 * @return void
	 */
	public static function send($view, $data, $recipient)
	{
		$message = \Illuminate\Support\Facades\View::make($view, $data)
			->render();
		return static::sendMessage($recipient, $message);
	}
}
