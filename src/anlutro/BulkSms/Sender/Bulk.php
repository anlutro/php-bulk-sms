<?php
/**
 * BulkSMS PHP implementation
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   anlutro/bulk-sms
 */

namespace anlutro\BulkSms\Sender;

use anlutro\cURL\cURL;
use anlutro\BulkSms\Message;

/**
 * Class for sending messages in bulk.
 */
class Bulk
{
	/**
	 * The URL the call should go to.
	 *
	 * @var string
	 */
	protected $url = 'http://bulksms.vsms.net:5567/eapi/submission/send_batch/1/1.0';

	/**
	 * The cURL instance.
	 *
	 * @var anlutro\cURL\cURL
	 */
	protected $curl;

	/**
	 * @param string $username BulkSMS username
	 * @param string $password BulkSMS password
	 * @param anlutro\cURL\cURL $curl  (optional) If you have an existing
	 *   instance of my cURL wrapper, you can pass it.
	 */
	public function __construct($username, $password, cURL $curl = null)
	{
		$this->username = $username;
		$this->password = $password;
		$this->curl = $curl ?: new cURL;
	}

	/**
	 * Add a message to the batch.
	 *
	 * @param Message $message
	 */
	public function addMessage(Message $message)
	{
		$this->messages[] = $message;
	}

	/**
	 * Add several messages at once to the batch.
	 *
	 * @param array $messages
	 */
	public function addMessages(array $messages)
	{
		$filteredMessages = array_filter($messages, function($message) {
			return ($message instanceof Message);
		});

		$this->messages =+ $filteredMessages;
	}

	/**
	 * Send the queued messages.
	 *
	 * @return mixed
	 */
	public function send()
	{
		if (empty($this->messages)) {
			return;
		}

		$data = [
			'username' => $this->username,
			'password' => $this->password,
			'batch_data' => $this->generateCSV(),
		];

		return $this->curl->post($this->url, $data);
	}

	/**
	 * Generate the CSV to send.
	 *
	 * @return string
	 */
	protected function generateCSV()
	{
		$str = "msisdn,message";

		foreach ($this->messages as $message) {
			$str .=  "\n";
			$recipient = $message->getRecipient();
			$message = $message->getMessage();
			$str .= '"'.$recipient.'","'.$message.'"';
		}

		return $str;
	}
}
