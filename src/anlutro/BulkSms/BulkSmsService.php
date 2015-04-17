<?php
/**
 * BulkSMS PHP implementation
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   anlutro/bulk-sms
 */

namespace anlutro\BulkSms;

use anlutro\cURL\cURL;
use Respect\Validation\Validator as v;

/**
 * The main API class.
 */
class BulkSmsService
{
    public static $TEST_ALWAYS_SUCCEED = 1;
    public static $TEST_ALWAYS_FAIL = 2;
    /**
     * Meaning of response status codes.


*
     * @var array
     */
    protected static $statusMessages = array(
        0   => 'In progress',
        10 => 'Delivered upstream',
        11 => 'Delivered mobile',
        12 => 'Delivered upstream unacknowledged (presume in progress)',
        1   => 'Scheduled',
        22  => 'Internal fatal error',
        23  => 'Authentication error',
        24  => 'Data validation failed',
        25  => 'Insufficient credits',
        26  => 'Upstream credits not available',
        27  => 'Daily quota exceeded',
        28  => 'Upstream quota exceeded',
        40  => 'Temporarily unavailable',
        201 => 'Maximum batch size exceeded',
    );
    protected $test_mode = false;
    /**
     * BulkSMS username
     *
     * @var string
     */
    protected $username;
    /**
     * BulkSMS password
     *
     * @var string
     */
    protected $password;
    /**
     * @var null
     */
    protected $baseurl;

    /**
     * @param string            $username BulkSMS username
     * @param string            $password BulkSMS password
     * @param string            $baseurl
     * @param anlutro\cURL\cURL $curl     (optional) If you have an existing
     *                                    instance of my cURL wrapper, you can pass it.
     */
    public function __construct($username, $password, $baseurl = "http://bulksms.vsms.net:5567", $curl = null)
    {
        v::url()->setName("Base Bulksms URL")->check($baseurl);
        $this->baseurl  = $baseurl;
        $this->username = $username;
        $this->password = $password;
        $this->curl     = $curl ?: new cURL();
    }

    /**
     * Set test mode
     *
     * @param $mode
     */
    public function setTestMode($mode)
    {
        if (BulkSmsService::$TEST_ALWAYS_SUCCEED == $mode) {
            $this->test_mode = BulkSmsService::$TEST_ALWAYS_SUCCEED;

            return;
        } elseif (BulkSmsService::$TEST_ALWAYS_FAIL == $mode) {
            $this->test_mode = BulkSmsService::$TEST_ALWAYS_FAIL;

            return;
        }
        throw new \InvalidArgumentException("Invalid test mode: " . $mode);
    }

    /**
     * Send a single message.
     *
     * @param  string $recipient
     * @param  string $message
     *
     * @return mixed
     */
    public function sendMessage($recipient, $message)
    {
        $sender = $this->createMessageSender();

        $msg = $this->createMessage($recipient, $message);

        $sender->setMessage($msg);
        $response = $sender->send($this->test_mode);
        $this->validateResponse($response);

        return $sender->extractResponse($response);
    }

    /**
     * Create a message sender instance.
     *
     * @return anlutro\BulkSms\Sender\Single
     */
    protected function createMessageSender()
    {
        return new Sender\Single($this->username, $this->password, $this->baseurl, $this->curl);
    }

    /**
     * Create a message instance.
     *
     * @param  string $recipient
     * @param  string $message
     *
     * @return anlutro\BulkSms\Message
     */
    protected function createMessage($recipient, $message)
    {
        return new Message($recipient, $message);
    }

    /**
     * Validate a response from the API to check for errors
     *
     * @param  anlutro\cURL\Response $response
     *
     * @return bool
     * @throws BulkSmsException
     */
    public function validateResponse($response)
    {
        if ($response->code !== '200 OK') {
            throw new BulkSmsException('BulkSMS API responded with HTTP status code ' . $response->code);
        }

        $parts = explode('|', $response->body);

        if (!is_numeric($parts[ 0 ])) {
            throw new \UnexpectedValueException(
                'Unknown response code: ' . $parts[ 0 ] . ' - full response: ' . $response->body
            );
        }

        $code = (int) $parts[ 0 ];

        if ($code === 0 || $code === 1) {
            return true;
        } else {
            $message = array_key_exists($code, static::$statusMessages)
                ? static::$statusMessages[ $code ]
                : $parts[ 1 ];
            throw new BulkSmsException('BulkSMS API responded with code: ' . $code . ' - ' . $message);
        }
    }

    /**
     * Send messages in bulk.
     *
     * @param  Message[] $messages
     *
     * @return mixed
     */
    public function sendBulkMessages(array $messages)
    {
        $sender = $this->createBulkSender();
        v::notEmpty()->setName("BulkSms Array")->check($messages);

        foreach ($messages as $message) {
            // make sure messages are proper objects
            v::instance('anlutro\BulkSms\Message')->check($message);
            $sender->addMessage($message);
        }
        $response = $sender->send($this->test_mode);
        $this->validateResponse($response);

        return $sender->extractResponse($response);
    }

    /**
     * Create a message sender instance.
     *
     * @return anlutro\BulkSms\Sender\Bulk
     */
    protected function createBulkSender()
    {
        return new Sender\Bulk($this->username, $this->password, $this->baseurl, $this->curl);
    }

    /**
     * Check status for single id
     *
     * @param  string $bulksmsid
     *
     * @return mixed
     */
    public function getStatusForBatchId($bulksmsid)
    {
        $sender   = $this->createBulkStatusSender();
        $response = $sender->getStatusForBatchId($bulksmsid, $this->test_mode);
        $this->validateResponse($response);

        return $sender->extractResponse($response);
    }

    /**
     * Create a message sender instance.
     *
     * @return anlutro\BulkSms\Sender\Status
     */
    protected function createBulkStatusSender()
    {
        return new Sender\Status($this->username, $this->password, $this->baseurl, $this->curl);
    }
}
