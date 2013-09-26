<?php
namespace anlutro\BulkSms;

class Message
{
	protected $recipient;
	protected $message;

	public function __construct(array $options = array())
	{
		$defaults = [];

		// defaults appended to options, doesn't overwrite
		$options += $defaults;
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
		while (substr($number, 0, 1) === '0') {
			$number = substr($number, 1);
		}

		// we should at this point have a normal number
		if (!is_numeric($number)) {
			throw new \InvalidArgumentException("Invalid SMS recipient: $number");
		}

		// is phone number is less than 9 characters, assume we need to append
		// the norwegian country code (47)
		if (strlen($number) >= 8) {
			$number = '47' . $number;
		}

		// phone number should be 11 or 10 characters at this point
		$len = strlen($number);
		if ($len > 11 || $len < 10) {
			throw new \InvalidArgumentException("Invalid SMS recipient: $number");
		}

		return $number;
	}

	protected function encodeMessage($message)
	{
		$replaceCharacters = array(
			'Δ'=>'0xD0', 'Φ'=>'0xDE', 'Γ'=>'0xAC', 'Λ'=>'0xC2', 'Ω'=>'0xDB',
			'Π'=>'0xBA', 'Ψ'=>'0xDD', 'Σ'=>'0xCA', 'Θ'=>'0xD4', 'Ξ'=>'0xB1',
			'¡'=>'0xA1', '£'=>'0xA3', '¤'=>'0xA4', '¥'=>'0xA5', '§'=>'0xA7',
			'¿'=>'0xBF', 'Ä'=>'0xC4', 'Å'=>'0xC5', 'Æ'=>'0xC6', 'Ç'=>'0xC7',
			'É'=>'0xC9', 'Ñ'=>'0xD1', 'Ö'=>'0xD6', 'Ø'=>'0xD8', 'Ü'=>'0xDC',
			'ß'=>'0xDF', 'à'=>'0xE0', 'ä'=>'0xE4', 'å'=>'0xE5', 'æ'=>'0xE6',
			'è'=>'0xE8', 'é'=>'0xE9', 'ì'=>'0xEC', 'ñ'=>'0xF1', 'ò'=>'0xF2',
			'ö'=>'0xF6', 'ø'=>'0xF8', 'ù'=>'0xF9', 'ü'=>'0xFC',
		);

		$message = utf8_decode($message);
		$message = str_replace('"', '\"', $message);
		$message = strtr($message, $replaceCharacters);
		return $message;
	}
}
