<?php
use Mockery as m;

class BulkSmsServiceTest extends PHPUnit_Framework_TestCase
{
	public function tearDown()
	{
		m::close();
	}

	public function testSendSingleSuccess()
	{
		$expectedPostData = array(
			'username' => 'foo', 'password' => 'bar',
			'message' => 'hello', 'msisdn' => '4712345678',
		);
		$mockResponse = new StdClass;
		$mockResponse->code = '200 OK';
		$mockResponse->body = '0|IN_PROGRESS|12345678';
		$curl = $this->mockCurl();
		$curl->shouldReceive('post')->once()->with('http://bulksms.vsms.net:5567/eapi/submission/send_sms/2/2.0', $expectedPostData)->andReturn($mockResponse);
		$bsms = $this->makeService('foo', 'bar', $curl);

		$this->assertTrue($bsms->sendMessage('12345678', 'hello'));
	}

	/**
	 * @expectedException anlutro\BulkSms\BulkSmsException
	 */
	public function testSendSingleHttpFail()
	{
		$expectedPostData = array(
			'username' => 'foo', 'password' => 'bar',
			'message' => 'hello', 'msisdn' => '4712345678',
		);
		$mockResponse = new StdClass;
		$mockResponse->code = '500';
		$curl = $this->mockCurl();
		$curl->shouldReceive('post')->once()->andReturn($mockResponse);
		$bsms = $this->makeService('foo', 'bar', $curl);

		$bsms->sendMessage('12345678', 'hello');
	}

	/**
	 * @expectedException anlutro\BulkSms\BulkSmsException
	 */
	public function testSendSingleApiError()
	{
		$expectedPostData = array(
			'username' => 'foo', 'password' => 'bar',
			'message' => 'hello', 'msisdn' => '4712345678',
		);
		$mockResponse = new StdClass;
		$mockResponse->code = '200 OK';
		$mockResponse->body = '99|ERROR|12345678';
		$curl = $this->mockCurl();
		$curl->shouldReceive('post')->once()->andReturn($mockResponse);
		$bsms = $this->makeService('foo', 'bar', $curl);

		$bsms->sendMessage('12345678', 'hello');
	}

	public function testSendSingleConcat()
	{
		$message = str_repeat('x', 200);
		$expectedPostData = array(
			'username' => 'foo', 'password' => 'bar',
			'message' => $message, 'msisdn' => '4712345678',
			'allow_concat_text_sms' => 1, 'concat_text_sms_max_parts' => 2,
		);
		$mockResponse = new StdClass;
		$mockResponse->code = '200 OK';
		$mockResponse->body = '0|IN_PROGRESS|12345678';
		$curl = $this->mockCurl();
		$curl->shouldReceive('post')->once()->with('http://bulksms.vsms.net:5567/eapi/submission/send_sms/2/2.0', $expectedPostData)->andReturn($mockResponse);
		$bsms = $this->makeService('foo', 'bar', $curl);

		$this->assertTrue($bsms->sendMessage('12345678', $message));
	}

	public function makeService($username, $password, $curl = null)
	{
		return new anlutro\BulkSms\BulkSmsService($username, $password, $curl);
	}

	public function mockCurl()
	{
		return m::mock('anlutro\cURL\cURL');
	}
}
