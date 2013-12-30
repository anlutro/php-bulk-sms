<?php
/**
 * BulkSMS PHP implementation
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   anlutro/bulk-sms
 */

namespace anlutro\BulkSms;

use anlutro\cURL\cURL;

/**
 * The main API class.
 */
class BulkSmsService
{
	/**
	 * BulkSMS username
	 *
	 * @var string
	 */
	protected $username;

	/**
	 * BulkSMS password
	 *
	 * @var string
	 */
	protected $password;

	/**
	 * Meaning of response status codes.
	 *
	 * @var array
	 */
	protected static $statusMessages = array(
		0 => 'In progress',
		1 => 'Scheduled',
		22 => 'Internal fatal error',
		23 => 'Authentication error',
		24 => 'Data validation failed',
		25 => 'Insufficient credits',
		26 => 'Upstream credits not available',
		27 => 'Daily quota exceeded',
		28 => 'Upstream quota exceeded',
		40 => 'Temporarily unavailable',
		201 => 'Maximum batch size exceeded',
	);

	/**
	 * @param string $username BulkSMS username
	 * @param string $password BulkSMS password
	 * @param anlutro\cURL\cURL $curl  (optional) If you have an existing
	 *   instance of my cURL wrapper, you can pass it.
	 */
	public function __construct($username, $password, $curl = null)
	{
		$this->username = $username;
		$this->password = $password;
		$this->curl = $curl ?: new cURL;
	}

	/**
	 * Send a single message.
	 *
	 * @param  string $recipient
	 * @param  string $message
	 *
	 * @return mixed
	 */
	public function sendMessage($recipient, $message)
	{
		$sender = $this->createMessageSender();

		$msg = $this->createMessage($recipient, $message);

		$sender->setMessage($msg);

		return $this->parseResponse($sender->send());
	}

	/**
	 * Send messages in bulk.
	 *
	 * @param  array  $messages associative array of recipient => message
	 *
	 * @return mixed
	 */
	public function sendBulkMessages(array $messages)
	{
		$sender = $this->createBulkSender();

		foreach ($messages as $recipient => $message) {
			$msg = $this->createMessage($recipient, $message);
			$sender->addMessage($msg);
		}

		return $this->parseResponse($sender->send());
	}

	/**
	 * Parse a response from the API.
	 *
	 * @param  anlutro\cURL\Response $response
	 *
	 * @return boolean
	 */
	public function parseResponse($response)
	{
		if ($response->code !== '200 OK') {
			throw new BulkSmsException('BulkSMS API responded with HTTP status code ' . $response->code);
		}

		$parts = explode('|', $response->body);

		if (!is_numeric($parts[0])) {
			throw new \UnexpectedValueException('Unknown response code: ' . $parts[0] . ' - full response: ' . $response->body);
		}

		$code = (int) $parts[0];

		if ($code === 0 || $code === 1) {
			return true;
		} else {
			$message = array_key_exists($code, static::$statusMessages)
				? static::$statusMessages[$code]
				: $parts[1];
			throw new BulkSmsException('BulkSMS API responded with code: ' . $code . ' - ' . $message);
		}
	}

	/**
	 * Create a message instance.
	 *
	 * @param  string $recipient
	 * @param  string $message
	 *
	 * @return anlutro\BulkSms\Message
	 */
	protected function createMessage($recipient, $message)
	{
		$msg = new Message;
		
		$msg->recipient($recipient);
		$msg->message($message);
		
		return $msg;
	}

	/**
	 * Create a message sender instance.
	 *
	 * @return anlutro\BulkSms\Sender\Single
	 */
	protected function createMessageSender()
	{
		return new Sender\Single($this->username, $this->password, $this->curl);
	}

	/**
	 * Create a message sender instance.
	 *
	 * @return anlutro\BulkSms\Sender\Bulk
	 */
	protected function createBulkSender()
	{
		return new Sender\Bulk($this->username, $this->password, $this->curl);
	}
}
