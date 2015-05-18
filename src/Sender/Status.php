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
use anlutro\cURL\Response;

/**
 * Class for getting the message status
 */
class Status extends AbstractSender
{
    /**
     * The URL the call should go to.
     *
     * @var string
     */
    protected $endpoint = '/eapi/status_reports/get_report/2/2.0';

    /**
     * Get status for single batch id
     *
     * @param string $batchid
     *
     * @return \anlutro\cURL\Response|void
     */
    public function getStatusForBatchId($batchid)
    {
        return $this->send($batchid);
    }

    /**
     * Send the status query
     *
     * @param $batchid
     *
     * @return \anlutro\cURL\Response|void
     */
    public function send($batchid)
    {
        if (empty($batchid)) {
            throw new \InvalidArgumentException("Batch Id must not be empty");
        }

        $data = [
            'username' => $this->username,
            'password' => $this->password,
            'batch_id' => $batchid,
        ];

        return $this->curl->get($this->getUrl(), $data);
    }

    /**
     * Extract response from Sender - depends on sender
     *
     * @param Response $response
     *
     * @return array('msisdn', 'status_code')
     * @throws BulkSmsException
     */
    public function extractResponse(Response $response)
    {
        // dump the first 2 lines indicated by the string "0|Returns to follow\n\n"
        $cleaned     = substr($response->body, strlen("0|Returns to follow\n\n"), strlen($response->body));
        $statusitems = explode("\n", $cleaned);

        $siit     = new \ArrayIterator(array_filter($statusitems));
        $toreturn = [];
        foreach ($siit as $item) {
            $expected = array('msisdn', 'status_code');
            $parts    = explode('|', $item);
            $it       = new \ArrayIterator($parts);
            if (count($expected) != $it->count()) {
                throw new BulkSmsException(
                    "Count of BulkSMS response does not match expectations!. Return: " . $response->body
                );
            }

            $status = [];
            foreach ($expected as $statusitem) {
                $status[ $statusitem ] = $it->current();
                $it->next();

            }
            $toreturn[ ] = $status;
        }

        return $toreturn;
    }
}
