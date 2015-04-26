<?php
use Mockery as m;

class ParseRecipientTest extends PHPUnit_Framework_TestCase
{
    protected $message;

    public function testParseWithCountryCode()
    {
        $this->message = new \anlutro\BulkSms\Message("4712345678", "text");
        $this->assertRecipientEquals('4712345678');
    }

    protected function assertRecipientEquals($expected)
    {
        $this->assertEquals($expected, $this->message->getRecipient());
    }

    public function testParseWithCountryCodeAndPlus()
    {
        $this->message = new \anlutro\BulkSms\Message('+4712345678', "text");
        $this->assertRecipientEquals('4712345678');
    }

    public function testParseWithCountryCodeAndSpace()
    {
        $this->message = new \anlutro\BulkSms\Message('47 12345678', "text");
        $this->assertRecipientEquals('4712345678');
    }

    public function testParseWithCountryCodeAndPlusSpace()
    {
        $this->message = new \anlutro\BulkSms\Message('+47 12345678', "text");
        $this->assertRecipientEquals('4712345678');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testParseWithoutCountryCode()
    {
        $this->message = new \anlutro\BulkSms\Message('12345678', "text");
        $this->assertRecipientEquals('4712345678');
    }

    public function testParseIntNumber()
    {
        $this->message = new \anlutro\BulkSms\Message(4712345678, "text");
        $this->assertRecipientEquals('4712345678');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testParseTooShortNumber()
    {
        $this->message = new \anlutro\BulkSms\Message(1234567, "text");
    }
}
