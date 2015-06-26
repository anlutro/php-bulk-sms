<?php
/**
 * BulkSMS PHP implementation
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   anlutro/bulk-sms
 */

namespace anlutro\BulkSms\Sender;

use anlutro\BulkSms\BulkSmsException;
use anlutro\BulkSms\Laravel\BulkSmsService;
use anlutro\BulkSms\Message;
use anlutro\cURL\Response;

/**
 * Class for sending single messages.
 */
class Single extends AbstractSender
{
    /**
     * The URL the call should go to.
     *
     * @var string
     */
    protected $endpoint = '/eapi/submission/send_sms/2/2.0';

    /**
     * The message to send.
     *
     * @var anlutro\BulkSms\Message
     */
    protected $message;

    /**
     * Set the message.
     *
     * @param anlutro\BulkSms\Message $message
     */
    public function setMessage(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Send the message.
     *
     * @param bool $testmode Testmode to use
     *
     * @return mixed
     */
    public function send($testmode = false)
    {
        $data = array_replace($this->params, [
            'username' => $this->username,
            'password' => $this->password,
            'message'  => $this->message->getMessage(),
            'msisdn'   => $this->message->getRecipient(),
        ]);

        $concat = $this->message->getConcatParts();

        if ($concat > 1) {
            $data['allow_concat_text_sms']     = 1;
            $data['concat_text_sms_max_parts'] = $concat;
        }

        // add test params if required
        if ($testmode) {
            if ($testmode == BulkSmsService::TEST_ALWAYS_SUCCEED) {
                $data['test_always_succeed'] = 1;
            } elseif ($testmode == BulkSmsService::TEST_ALWAYS_FAIL) {
                $data['test_always_fail'] = 1;
            }
        }

        return $this->curl->post($this->getUrl(), $data);
    }

    /**
     * Extract response from Sender - depends on sender
     *
     * @param Response $response
     *
     * @return array('status_code', 'status_description', 'batch_id')
     * @throws BulkSmsException
     */
    public function extractResponse(Response $response)
    {
        $expected = array('status_code', 'status_description', 'batch_id');
        $parts    = explode('|', $response->body);
        $it       = new \ArrayIterator($parts);
        if (count($expected) != $it->count()) {
            throw new BulkSmsException(
                "Count of BulkSMS response does not match expectations!. Return: " . $response->body
            );
        }

        $toreturn = [];
        foreach ($expected as $item) {
            $toreturn[$item] = $it->current();
            $it->next();
        }

        return $toreturn;
    }
}
