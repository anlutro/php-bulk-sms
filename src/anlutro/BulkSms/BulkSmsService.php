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

	public function sendMessage($recipient, $message)
	{
		$sender = $this->createMessageSender();

		$msg = $this->createMessage($recipient, $message);

		$sender->setMessage($msg);

		return $sender->send();
	}

	public function sendBulkMessages(array $messages)
	{
		$sender = $this->createBulkSender();

		foreach ($messages as $recipient => $message) {
			$msg = $this->createMessage($recipient, $message);
			$sender->addMessage($msg);
		}

		return $sender->send();
	}

	protected function createMessage($recipient, $message)
	{
		$msg = new Message;
		
		$msg->recipient($recipient);
		$msg->message($message);
		
		return $msg;
	}

	protected function createMessageSender()
	{
		return new Sender\Single($this->username, $this->password, $this->curl);
	}

	protected function createBulkSender()
	{
		return new Sender\Bulk($this->username, $this->password, $this->curl);
	}
}
