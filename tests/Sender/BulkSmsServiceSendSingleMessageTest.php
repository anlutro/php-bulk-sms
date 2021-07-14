<?php
use Mockery as m;

class BulkSmsServiceSendSingleMessageTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testSendSingleSuccess()
    {
        $expectedPostData   = array(
            'username' => 'foo',
            'password' => 'bar',
            'message'  => 'hello',
            'msisdn'   => '4712345678',
        );
        $mockResponse       = m::mock('anlutro\cURL\Response');
        $mockResponse->statusCode = 200;
        $mockResponse->body = '0|IN_PROGRESS|4712345678';
        $curl               = $this->mockCurl();
        $curl->shouldReceive('post')->once()->with(
            'http://bulksms.vsms.net:5567/eapi/submission/send_sms/2/2.0',
            $expectedPostData
        )->andReturn($mockResponse);
        $bsms = $this->makeService('foo', 'bar', 'http://bulksms.vsms.net:5567', $curl);

        $this->assertEquals(
            array('status_code' => 0, 'status_description' => "IN_PROGRESS", 'batch_id' => 4712345678),
            $bsms->sendMessage('4712345678', 'hello')
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
     * @expectedException anlutro\BulkSms\BulkSmsException
     */
    public function testSendSingleHttpFail()
    {
        $expectedPostData   = array(
            'username' => 'foo',
            'password' => 'bar',
            'message'  => 'hello',
            'msisdn'   => '4712345678',
        );
        $mockResponse       = m::mock('anlutro\cURL\Response');
        $mockResponse->code = '500';
        $curl               = $this->mockCurl();
        $curl->shouldReceive('post')->once()->andReturn($mockResponse);
        $bsms = $this->makeService('foo', 'bar', 'http://bulksms.vsms.net:5567', $curl);

        $bsms->sendMessage('4712345678', 'hello');
    }

    /**
     * @expectedException anlutro\BulkSms\BulkSmsException
     */
    public function testSendSingleApiError()
    {
        $expectedPostData   = array(
            'username' => 'foo',
            'password' => 'bar',
            'message'  => 'hello',
            'msisdn'   => '4712345678',
        );
        $mockResponse       = m::mock('anlutro\cURL\Response');
        $mockResponse->statusCode = 200;
        $mockResponse->body = '99|ERROR|12345678';
        $curl               = $this->mockCurl();
        $curl->shouldReceive('post')->once()->andReturn($mockResponse);
        $bsms = $this->makeService('foo', 'bar', 'http://bulksms.vsms.net:5567', $curl);

        $bsms->sendMessage('23712345678', 'hello');
    }

    public function testSendSingleConcat()
    {
        $message            = str_repeat('x', 200);
        $expectedPostData   = array(
            'username'                  => 'foo',
            'password'                  => 'bar',
            'message'                   => $message,
            'msisdn'                    => '4712345678',
            'allow_concat_text_sms'     => 1,
            'concat_text_sms_max_parts' => 2,
        );
        $mockResponse       = m::mock('anlutro\cURL\Response');
        $mockResponse->statusCode = 200;
        $mockResponse->body = '0|IN_PROGRESS|4712345678';
        $curl               = $this->mockCurl();
        $curl->shouldReceive('post')->once()->with(
            'http://bulksms.vsms.net:5567/eapi/submission/send_sms/2/2.0',
            $expectedPostData
        )->andReturn($mockResponse);
        $bsms = $this->makeService('foo', 'bar', 'http://bulksms.vsms.net:5567', $curl);

        $this->assertEquals(
            array('status_code' => 0, 'status_description' => "IN_PROGRESS", 'batch_id' => 4712345678),
            $bsms->sendMessage('4712345678', $message)
        );
    }

    public function testSendSingleConcatCustomURL()
    {
        $message            = str_repeat('x', 200);
        $expectedPostData   = array(
            'username'                  => 'foo',
            'password'                  => 'bar',
            'message'                   => $message,
            'msisdn'                    => '4712345678',
            'allow_concat_text_sms'     => 1,
            'concat_text_sms_max_parts' => 2,
        );
        $mockResponse       = m::mock('anlutro\cURL\Response');
        $mockResponse->statusCode = 200;
        $mockResponse->body = '0|IN_PROGRESS|4712345678';
        $curl               = $this->mockCurl();
        $curl->shouldReceive('post')->once()->with(
            'http://bulksms.de/eapi/submission/send_sms/2/2.0',
            $expectedPostData
        )->andReturn($mockResponse);
        $bsms = $this->makeService('foo', 'bar', 'http://bulksms.de', $curl);
        $this->assertEquals(
            array('status_code' => 0, 'status_description' => "IN_PROGRESS", 'batch_id' => 4712345678),
            $bsms->sendMessage('4712345678', $message)
        );
    }

    public function testExtraParamsAreAppended()
    {
        $message            = str_repeat('x', 200);
        $expectedPostData   = array(
            'username'                  => 'foo',
            'password'                  => 'bar',
            'message'                   => $message,
            'msisdn'                    => '4712345678',
            'allow_concat_text_sms'     => 1,
            'concat_text_sms_max_parts' => 2,
            'routing_group'             => 1,
        );
        $mockResponse       = m::mock('anlutro\cURL\Response');
        $mockResponse->statusCode = 200;
        $mockResponse->body = '0|IN_PROGRESS|4712345678';
        $curl               = $this->mockCurl();
        $curl->shouldReceive('post')->once()->with(
            'http://bulksms.de/eapi/submission/send_sms/2/2.0',
            $expectedPostData
        )->andReturn($mockResponse);
        $bsms = $this->makeService('foo', 'bar', 'http://bulksms.de', $curl);
        $this->assertEquals(
            array('status_code' => 0, 'status_description' => "IN_PROGRESS", 'batch_id' => 4712345678),
            $bsms->sendMessage('4712345678', $message, array('routing_group' => 1))
        );
    }
}
