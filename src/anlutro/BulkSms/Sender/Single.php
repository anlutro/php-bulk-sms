<?php
namespace anlutro\BulkSms\Sender;

use anlutro\cURL\cURL;
use anlutro\BulkSms\Message;

class Single
{
	protected $url = 'http://bulksms.vsms.net:5567/eapi/submission/send_sms/2/2.0';

	protected $curl;
	protected $message;

	public function __construct($username, $password, $curl = null)
	{
		$this->username = $username;
		$this->password = $password;
		$this->curl = $curl ?: new cURL;
	}

	public function setMessage(Message $message)
	{
		$this->message = $message;
	}

	public function send()
	{
		$data = [
			'username' => $this->username,
			'password' => $this->password,
			'message' => $this->message->getMessage(),
			'msisdn' => $this->message->getRecipient(),
			'concat_text_sms_max_parts' => $this->message->getConcatParts(),
		];

		return $this->curl->post($this->url, [], $data);
	}
}
