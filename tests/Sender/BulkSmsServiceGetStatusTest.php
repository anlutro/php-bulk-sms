<?php
use Mockery as m;

class BulkSmsServiceGetStatusTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testSingleResponseSuccess()
    {
        $expectedGetData    = array(
            'username' => 'foo',
            'password' => 'bar',
            'batch_id' => '123445566'
        );
        $mockResponse       = m::mock('anlutro\cURL\Response');
        $mockResponse->code = '200 OK';
        $mockResponse->body = "0|Returns to follow\n\n";
        $mockResponse->body .= "1212121|11\n";
        $curl = $this->mockCurl();
        $curl->shouldReceive('get')->once()->with(
            'http://bulksms.vsms.net:5567/eapi/status_reports/get_report/2/2.0',
            $expectedGetData
        )->andReturn($mockResponse);
        $bsms = $this->makeService('foo', 'bar', 'http://bulksms.vsms.net:5567', 2, $curl);
        $this->assertEquals(
            array(array('msisdn' => '1212121', 'status_code' => '11')),
            $bsms->getStatusForBatchId('123445566')
        );
    }

    public function mockCurl()
    {
        return m::mock('anlutro\cURL\cURL');
    }

    public function makeService($username, $password, $baseurl, $routingGroup = 2, $curl = null)
    {
        return new anlutro\BulkSms\BulkSmsService($username, $password, $baseurl, $routingGroup, $curl);
    }

    public function testMultipleResponseSuccess()
    {
        $expectedGetData    = array(
            'username' => 'foo',
            'password' => 'bar',
            'batch_id' => '123445566'
        );
        $mockResponse       = m::mock('anlutro\cURL\Response');
        $mockResponse->code = '200 OK';
        $mockResponse->body = "0|Returns to follow\n\n";
        $mockResponse->body .= "1212121|11\n";
        $mockResponse->body .= "1212122|12\n";
        $curl = $this->mockCurl();
        $curl->shouldReceive('get')->once()->with(
            'http://bulksms.vsms.net:5567/eapi/status_reports/get_report/2/2.0',
            $expectedGetData
        )->andReturn($mockResponse);
        $bsms = $this->makeService('foo', 'bar', 'http://bulksms.vsms.net:5567', 2, $curl);
        $this->assertEquals(
            array(
                array('msisdn' => '1212121', 'status_code' => '11'),
                array('msisdn' => '1212122', 'status_code' => '12')
            ),
            $bsms->getStatusForBatchId('123445566')
        );
    }

    public function testNoResponseSuccess()
    {
        $expectedGetData    = array(
            'username' => 'foo',
            'password' => 'bar',
            'batch_id' => '123445566'
        );
        $mockResponse       = m::mock('anlutro\cURL\Response');
        $mockResponse->code = '200 OK';
        $mockResponse->body = "0|Returns to follow\n\n";
        $mockResponse->body .= "\n";
        $curl = $this->mockCurl();
        $curl->shouldReceive('get')->once()->with(
            'http://bulksms.vsms.net:5567/eapi/status_reports/get_report/2/2.0',
            $expectedGetData
        )->andReturn($mockResponse);
        $bsms = $this->makeService('foo', 'bar', 'http://bulksms.vsms.net:5567', 2, $curl);
        $this->assertEquals(array(), $bsms->getStatusForBatchId('123445566'));
    }

    /**
     * @expectedException anlutro\BulkSms\BulkSmsException
     */
    public function testSendSingleHttpFail()
    {
        $expectedGetData    = array(
            'username' => 'foo',
            'password' => 'bar',
            'batch_id' => '123445566'
        );
        $mockResponse       = m::mock('anlutro\cURL\Response');
        $mockResponse->code = '500';
        $mockResponse->body = "0|Returns to follow\n\n";
        $mockResponse->body .= "1212121|11\n";
        $curl = $this->mockCurl();
        $curl->shouldReceive('get')->once()->with(
            'http://bulksms.vsms.net:5567/eapi/status_reports/get_report/2/2.0',
            $expectedGetData
        )->andReturn($mockResponse);
        $bsms = $this->makeService('foo', 'bar', 'http://bulksms.vsms.net:5567', 2, $curl);
        $bsms->getStatusForBatchId('123445566');
    }

    /**
     * @expectedException anlutro\BulkSms\BulkSmsException
     */
    public function testSendSingleApiError()
    {
        $expectedGetData    = array(
            'username' => 'foo',
            'password' => 'bar',
            'batch_id' => '123445566'
        );
        $mockResponse       = m::mock('anlutro\cURL\Response');
        $mockResponse->code = '200 OK';
        $mockResponse->body = "23|invalid credentials (username was: XXXXXXX)|\n";
        $curl               = $this->mockCurl();
        $curl->shouldReceive('get')->once()->with(
            'http://bulksms.vsms.net:5567/eapi/status_reports/get_report/2/2.0',
            $expectedGetData
        )->andReturn($mockResponse);
        $bsms = $this->makeService('foo', 'bar', 'http://bulksms.vsms.net:5567', 2, $curl);
        $this->assertTrue($bsms->getStatusForBatchId('123445566'));
    }

    public function testSendSingleSuccessCustomURL()
    {
        $expectedGetData    = array(
            'username' => 'foo',
            'password' => 'bar',
            'batch_id' => '123445566'
        );
        $mockResponse       = m::mock('anlutro\cURL\Response');
        $mockResponse->code = '200 OK';
        $mockResponse->body = "0|Returns to follow\n\n";
        $mockResponse->body .= "1212121|11\n";
        $curl = $this->mockCurl();
        $curl->shouldReceive('get')->once()->with(
            'http://bulksms.de/eapi/status_reports/get_report/2/2.0',
            $expectedGetData
        )->andReturn($mockResponse);
        $bsms = $this->makeService('foo', 'bar', 'http://bulksms.de', 2, $curl);
        $this->assertEquals(
            array(array('msisdn' => '1212121', 'status_code' => '11')),
            $bsms->getStatusForBatchId('123445566')
        );
    }
}
