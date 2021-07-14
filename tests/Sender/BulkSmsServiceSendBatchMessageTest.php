<?php
use Mockery as m;

class BulkSmsServiceSendBatchMessageTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    /**
     * @expectedException \Respect\Validation\Exceptions\NotEmptyException
     * @expectedExceptionMessage BulkSms Array must not be empty
     */
    public function testSendNoMessageSuccess()
    {
        $curl = $this->mockCurl();
        $bsms = $this->makeService('foo', 'bar', 'http://bulksms.vsms.net:5567', $curl);
        $this->assertEquals(
            array('status_code' => 0, 'status_description' => "IN_PROGRESS", 'batch_id' => 4712345678),
            $bsms->sendBulkMessages(array())
        );
    }

    public function mockCurl()
    {
        return m::mock('anlutro\cURL\cURL');
    }

    public function makeService($username, $password, $baseurl, $curl = null)
    {
        return new anlutro\BulkSms\BulkSmsService($username, $password, $baseurl, $curl);
    }

    /**
     * @expectedException Respect\Validation\Exceptions\InstanceException
     * @expectedExceptionMessage must be an instance of "anlutro\\BulkSms\\Message"
     */
    public function testSendWrongMessageClassSuccess()
    {
        $curl = $this->mockCurl();
        $bsms = $this->makeService('foo', 'bar', 'http://bulksms.vsms.net:5567', $curl);
        $this->assertEquals(
            array('status_code' => 0, 'status_description' => "IN_PROGRESS", 'batch_id' => 4712345678),
            $bsms->sendBulkMessages(array(m::mock('anlutro\cURL\Response')))
        );
    }

    public function testSendSingleMessageSuccess()
    {
        $expectedPostData   = array(
            'username'   => 'foo',
            'password'   => 'bar',
            'batch_data' => "msisdn,message\n\"4917610908093\",\"TestText\"",
        );
        $mockResponse       = m::mock('anlutro\cURL\Response');
        $mockResponse->statusCode = 200;
        $mockResponse->body = '0|IN_PROGRESS|4712345678';
        $curl               = $this->mockCurl();
        $curl->shouldReceive('post')->once()->with(
            "http://bulksms.vsms.net:5567/eapi/submission/send_batch/1/1.0",
            $expectedPostData
        )->andReturn($mockResponse);
        $bsms    = $this->makeService('foo', 'bar', 'http://bulksms.vsms.net:5567', $curl);
        $message = new \anlutro\BulkSms\Message("4917610908093", "TestText");
        $this->assertEquals(
            array('status_code' => 0, 'status_description' => "IN_PROGRESS", 'batch_id' => 4712345678),
            $bsms->sendBulkMessages(array($message))
        );
    }

    public function testSendMultipleMessageSuccess()
    {
        $expectedPostData   = array(
            'username'   => 'foo',
            'password'   => 'bar',
            'batch_data' => "msisdn,message\n\"4917610908093\",\"TestText\"\n\"4917610908094\",\"TestText2\"",
        );
        $mockResponse       = m::mock('anlutro\cURL\Response');
        $mockResponse->statusCode = 200;
        $mockResponse->body = '0|IN_PROGRESS|4712345678';
        $curl               = $this->mockCurl();
        $curl->shouldReceive('post')->once()->with(
            "http://bulksms.vsms.net:5567/eapi/submission/send_batch/1/1.0",
            $expectedPostData
        )->andReturn($mockResponse);
        $bsms     = $this->makeService('foo', 'bar', 'http://bulksms.vsms.net:5567', $curl);
        $message1 = new \anlutro\BulkSms\Message("4917610908093", "TestText");
        $message2 = new \anlutro\BulkSms\Message("4917610908094", "TestText2");
        $this->assertEquals(
            array('status_code' => 0, 'status_description' => "IN_PROGRESS", 'batch_id' => 4712345678),
            $bsms->sendBulkMessages(array($message1, $message2))
        );
    }

    public function testExtraParamsAreAppended()
    {
        $expectedPostData   = array(
            'username'      => 'foo',
            'password'      => 'bar',
            'batch_data'    => "msisdn,message\n\"4917610908093\",\"TestText\"\n\"4917610908094\",\"TestText2\"",
            'routing_group' => 1,
        );
        $mockResponse       = m::mock('anlutro\cURL\Response');
        $mockResponse->statusCode = 200;
        $mockResponse->body = '0|IN_PROGRESS|4712345678';
        $curl               = $this->mockCurl();
        $curl->shouldReceive('post')->once()->with(
            "http://bulksms.vsms.net:5567/eapi/submission/send_batch/1/1.0",
            $expectedPostData
        )->andReturn($mockResponse);
        $bsms     = $this->makeService('foo', 'bar', 'http://bulksms.vsms.net:5567', $curl);
        $message1 = new \anlutro\BulkSms\Message("4917610908093", "TestText");
        $message2 = new \anlutro\BulkSms\Message("4917610908094", "TestText2");
        $this->assertEquals(
            array('status_code' => 0, 'status_description' => "IN_PROGRESS", 'batch_id' => 4712345678),
            $bsms->sendBulkMessages(array($message1, $message2), array('routing_group' => 1))
        );
    }
}
