<?php
namespace anlutro\BulkSms;

use anlutro\cURL\cURL;

class BulkSmsService
{
	protected $username;
	protected $password;

	public function __construct($username, $password, $curl = null)
	{
		$this->username = $username;
		$this->password = $password;
		$this->curl = $curl ?: new cURL;
		static::$instance = $this;
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
	public function send($view, $data, $recipient)
	{
		$message = \Illuminate\Support\Facades\View::make($view, $data)
			->render();
		return $this->sendMessage($recipient, $message);
	}

	public function sendMessage($recipient, $message)
	{
		$sender = $this->createMessageSender();

		$msg = $this->createMessage($recipient, $message);

		$sender->setMessage($msg);

		dd($msg, $sender);

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

		// $msg->sender($this->sender);
		
		$msg->recipient($recipient)
			->message($message);
		
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

	protected static $instance;

	public static function getInstance()
	{
		return static::$instance;
	}
}
