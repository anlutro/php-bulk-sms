<?php
namespace anlutro\BulkSms;

use anlutro\cURL\cURL;

class MessageSender
{
	protected $url = 'http://bulksms.vsms.net:5567/eapi/submission/send_sms/2/2.0';

	protected $message;

	public function __construct(BulkSmsManager $manager)
	{
		$this->manager = $manager;
	}

	public function setMessage(Message $message)
	{
		$this->message = $message;
	}

	public function send()
	{
		
	}
}
