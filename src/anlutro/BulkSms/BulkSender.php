<?php
namespace anlutro\BulkSms;

use anlutro\cURL\cURL;

class BulkSender
{
	protected $url = 'http://bulksms.vsms.net:5567/eapi/submission/send_batch/2/2.0';

	protected $curl;

	public function __construct(cURL $curl)
	{
		$this->curl = $curl;
	}

	public function addMessage(Message $message)
	{
		$this->messages[] = $message;
	}

	public function addMessages(array $messages)
	{
		$filteredMessages = array_filter($messages, function($message) {
			return ($message instanceof Message);
		});

		$this->messages =+ $filteredMessages;
	}

	public function send()
	{
		$data = [];

		$this->curl->post($this->url, [], $data);
	}

	protected function generateCSV()
	{
		$str = "msisdn,message";

		foreach ($this->messages as $message) {
			$str .=  "\n";
			$recipient = $message->getRecipient();
			$message = $message->getMessage();
			$str .= '"'.$recipient.'","'.$message.'"';
		}
	}
}
