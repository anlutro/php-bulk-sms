<?php
use Mockery as m;

class ParseRecipientTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->message = new anlutro\BulkSms\Message;
	}

	public function tearDown()
	{
		$this->message = null;
	}

	public function testParseWithCountryCode()
	{
		$this->message->recipient('4712345678');
		$this->assertRecipientEquals('4712345678');
	}

	public function testParseWithCountryCodeAndPlus()
	{
		$this->message->recipient('+4712345678');
		$this->assertRecipientEquals('4712345678');
	}

	public function testParseWithCountryCodeAndSpace()
	{
		$this->message->recipient('47 12345678');
		$this->assertRecipientEquals('4712345678');
	}

	public function testParseWithCountryCodeAndPlusSpace()
	{
		$this->message->recipient('+47 12345678');
		$this->assertRecipientEquals('4712345678');
	}

	public function testParseWithoutCountryCode()
	{
		$this->message->recipient('12345678');
		$this->assertRecipientEquals('4712345678');
	}

	public function testParseIntNumber()
	{
		$this->message->recipient(12345678);
		$this->assertRecipientEquals('4712345678');
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testParseTooShortNumber()
	{
		$this->message->recipient('1234567');
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testParseTooLongNumber()
	{
		$this->message->recipient('123456789');
	}

	protected function assertRecipientEquals($expected)
	{
		$this->assertEquals($expected, $this->message->getRecipient());
	}
}
