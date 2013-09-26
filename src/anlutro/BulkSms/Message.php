<?php
namespace anlutro\BulkSms;

class Message
{
	protected $sender;
	protected $recipient;
	protected $message;

	public function __construct(array $options = array())
	{
		$defaults = [];

		// defaults appended to options, doesn't overwrite
		$options += $defaults;
	}

	public function sender($sender)
	{
		$this->sender = $sender;
		return $this;
	}

	public function recipient($recipient)
	{
		$this->recipient = $this->parseNumber($recipient);
		return $this;
	}

	public function message($message)
	{
		$this->message = $this->encodeMessage($message);
		return $this;
	}

	public function getRecipient()
	{
		return $this->recipient;
	}

	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * Parse a phone number.
	 *
	 * @param  int|string $number
	 *
	 * @return string
	 */
	protected function parseNumber($number)
	{
		if (is_int($number)) {
			$number = (string) $number;
		}

		// remove whitespaces
		$number = trim($number);
		$number = str_replace(' ', '', $number);

		// remove + in front if exists
		if (substr($number, 0, 1) == '+') {
			$number = substr($number, 1);
		}

		// remove 0s in front if exists
		while (substr($number, 0, 1) == '0') {
			$number = substr($number, 1);
		}

		// we should at this point have a normal number
		if (!is_numeric($number)) {
			throw new InvalidArgumentException("Invalid SMS recipient: $number");
		}

		// is phone number is less than 9 characters, assume we need to append
		// the norwegian country code (47)
		if (strlen($number) >= 8) {
			$number = '47' . $number;
		}

		// phone number should be 11 or 10 characters at this point
		$len = strlen($number);
		if ($len > 11 || $len < 10) {
			throw new InvalidArgumentException("Invalid SMS recipient: $number");
		}

		return $number;
	}

	protected function encodeMessage($message)
	{
		return str_replace('"', '\"', $message);
	}
}
