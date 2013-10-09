<?php
use Mockery as m;

class MessageConcatTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->msg = new anlutro\BulkSms\Message;
	}

	public function tearDown()
	{
		$this->msg = null;
	}

	public function testSingleMessageConcat()
	{
		$this->msg->message($this->generateStrlen(1));
		$this->assertMsgConcat(1);

		$this->msg->message($this->generateStrlen(140));
		$this->assertMsgConcat(1);

		$this->msg->message($this->generateStrlen(141));
		$this->assertMsgConcat(2);
	}

	public function testDoubleMessageConcat()
	{
		$this->msg->message($this->generateStrlen(161));
		$this->assertMsgConcat(2);

		$this->msg->message($this->generateStrlen(280));
		$this->assertMsgConcat(2);

		$this->msg->message($this->generateStrlen(281));
		$this->assertMsgConcat(3);
	}

	public function testTripleMessageConcat()
	{
		$this->msg->message($this->generateStrlen(321));
		$this->assertMsgConcat(3);

		$this->msg->message($this->generateStrlen(420));
		$this->assertMsgConcat(3);

		$this->msg->message($this->generateStrlen(421));
		$this->assertMsgConcat(4);
	}

	protected function assertMsgConcat($concat)
	{
		$this->assertEquals($concat, $this->msg->getConcatParts());
	}

	protected function generateStrlen($len)
	{
		$str = '';

		for ($i = 0; $i < $len; $i++) {
			$str .= 'x';
		}

		return $str;
	}
}
