<?php
use Mockery as m;

class MessageConcatTest extends PHPUnit_Framework_TestCase
{
    protected $msg;

    public function testSingleMessageConcat()
    {
        $this->msg = new \anlutro\BulkSms\Message(1111111111, $this->generateStrlen(1));
        $this->assertMsgConcat(1);

        $this->msg = new \anlutro\BulkSms\Message(1111111111, $this->generateStrlen(140));
        $this->assertMsgConcat(1);

        $this->msg = new \anlutro\BulkSms\Message(1111111111, $this->generateStrlen(141));
        $this->assertMsgConcat(2);
    }

    protected function generateStrlen($len)
    {
        $str = '';

        for ($i = 0; $i < $len; $i++) {
            $str .= 'x';
        }

        return $str;
    }

    protected function assertMsgConcat($concat)
    {
        $this->assertEquals($concat, $this->msg->getConcatParts());
    }

    public function testDoubleMessageConcat()
    {
        $this->msg = new \anlutro\BulkSms\Message(1111111111, $this->generateStrlen(161));
        $this->assertMsgConcat(2);

        $this->msg = new \anlutro\BulkSms\Message(1111111111, $this->generateStrlen(280));
        $this->assertMsgConcat(2);

        $this->msg = new \anlutro\BulkSms\Message(1111111111, $this->generateStrlen(281));
        $this->assertMsgConcat(3);
    }

    public function testTripleMessageConcat()
    {
        $this->msg = new \anlutro\BulkSms\Message(1111111111, $this->generateStrlen(321));
        $this->assertMsgConcat(3);

        $this->msg = new \anlutro\BulkSms\Message(1111111111, $this->generateStrlen(420));
        $this->assertMsgConcat(3);

        $this->msg = new \anlutro\BulkSms\Message(1111111111, $this->generateStrlen(421));
        $this->assertMsgConcat(4);
    }
}
