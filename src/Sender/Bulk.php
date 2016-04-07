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
 * Class for sending messages in bulk.
 */
class Bulk extends AbstractSender
{
    /**
     * The endpoint the call should go to.
     *
     * @var string
     */
    protected $endpoint = '/eapi/submission/send_batch/1/1.0';

    /**
     * Message container
     *
     * @var array
     */
    protected $messages;

    /**
     * Add a message to the batch.
     *
     * @param Message $message
     */
    public function addMessage(Message $message)
    {
        $this->messages[] = $message;
    }

    /**
     * Send the queued messages.
     *
     * @param bool $testmode Testmode to use
     *
     * @return mixed
     */
    public function send($testmode = false)
    {
        if (empty($this->messages)) {
            return false;
        }

        $data = array_replace($this->params, [
            'username'   => $this->username,
            'password'   => $this->password,
            'batch_data' => $this->generateCSV(),
        ]);

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
     * Generate the CSV to send.
     *
     * @return string
     */
    protected function generateCSV()
    {
        $str = "msisdn,message";

        foreach ($this->messages as $message) {
            $str .= "\n";
            $recipient = $message->getRecipient();
            $message   = $message->getMessage();
            $str .= '"' . $recipient . '","' . $message . '"';
        }

        return $str;
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
