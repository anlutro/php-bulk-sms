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
    const TEST_ALWAYS_SUCCEED = 1;
    const TEST_ALWAYS_FAIL = 2;

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

    /**
     * Whether test mode is enabled.
     *
     * @var boolean
     */
    protected $testMode = false;

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
     * @var string
     */
    protected $baseUrl;

    /**
     * @param string $username BulkSMS username
     * @param string $password BulkSMS password
     * @param string $baseUrl  Optional - defaults to "http://bulksms.vsms.net:5567"
     * @param cURL   $curl     Optional - a new instance will be constructed if null is passed.
     */
    public function __construct($username, $password, $baseUrl = "http://bulksms.vsms.net:5567", $curl = null)
    {
        v::url()->setName("Base Bulksms URL")->check($baseUrl);
        $this->baseUrl = $baseUrl;
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
        if (BulkSmsService::TEST_ALWAYS_SUCCEED == $mode) {
            $this->testMode = BulkSmsService::TEST_ALWAYS_SUCCEED;
        } elseif (BulkSmsService::TEST_ALWAYS_FAIL == $mode) {
            $this->testMode = BulkSmsService::TEST_ALWAYS_FAIL;
        } else {
            throw new \InvalidArgumentException("Invalid test mode: " . $mode);
        }
    }

    /**
     * Send a single message.
     *
     * @param  string $recipient
     * @param  string $message
     * @param  array  $params
     *
     * @return mixed
     */
    public function sendMessage($recipient, $message, array $params = null)
    {
        $sender = $this->createMessageSender();

        $msg = $this->createMessage($recipient, $message);

        $sender->setMessage($msg);
        if ($params) {
            $sender->setParams($params);
        }
        $response = $sender->send($this->testMode);
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
        return new Sender\Single($this->username, $this->password, $this->baseUrl, $this->curl);
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
        if ($response->statusCode !== 200) {
            throw new BulkSmsException('BulkSMS API responded with HTTP status code ' . $response->statusCode);
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
        }

        $message = array_key_exists($code, static::$statusMessages)
            ? static::$statusMessages[ $code ]
            : $parts[ 1 ];
        throw new BulkSmsException('BulkSMS API responded with code: ' . $code . ' - ' . $message);
    }

    /**
     * Send messages in bulk.
     *
     * @param  Message[] $messages
     * @param  array     $params
     *
     * @return mixed
     */
    public function sendBulkMessages(array $messages, array $params = null)
    {
        $sender = $this->createBulkSender();
        v::notEmpty()->setName("BulkSms Array")->check($messages);

        foreach ($messages as $message) {
            // make sure messages are proper objects
            v::instance('anlutro\BulkSms\Message')->check($message);
            $sender->addMessage($message);
        }

        if ($params) {
            $sender->setParams($params);
        }
        $response = $sender->send($this->testMode);
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
        return new Sender\Bulk($this->username, $this->password, $this->baseUrl, $this->curl);
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
        $response = $sender->getStatusForBatchId($bulksmsid, $this->testMode);
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
        return new Sender\Status($this->username, $this->password, $this->baseUrl, $this->curl);
    }
}
