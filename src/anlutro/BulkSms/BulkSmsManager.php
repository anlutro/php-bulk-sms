<?php
namespace anlutro\BulkSms;

use anlutro\cURL\cURL;

class BulkSmsManager
{
	protected $username;
	protected $password;

	public function __construct($curl = null)
	{
		$this->curl = $curl ?: new cURL;
		self::$instance = $this;
	}

	public function sendMessage($recipient, $message)
	{
		# code...
	}

	public function sendBulkMessages(array $messages)
	{
		$sender = new BulkSender($this->curl);

		foreach ($messages as $recipient => $message) {
			$msg = $this->createMessage($recipient, $message);
			$sender->addMessage($msg);
		}

		$sender->send();
	}

	protected function createMessage($recipient, $message)
	{
		$msg = new Message;

		$msg->sender($this->sender)
			->recipient($recipient)
			->message($message);
		
		return $msg;
	}

	protected function createMessageSender()
	{
		return new MessageSender($this);
	}

	protected function createBulkSender()
	{
		return new BulkSender($this);
	}

	protected static $instance;

	public static function getInstance()
	{
		return self::$instance;
	}
}
