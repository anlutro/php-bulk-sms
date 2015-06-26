<?php
/**
 * BulkSMS PHP implementation
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   anlutro/bulk-sms
 */

namespace anlutro\BulkSms\Sender;

use anlutro\cURL\cURL;
use anlutro\cURL\Response;
use Respect\Validation\Validator as v;

abstract class AbstractSender
{
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
     * The endpoint the call should go to.
     *
     * @var string
     */
    protected $endpoint;

    /**
     * The base URL of the API.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * The cURL instance.
     *
     * @var cURL
     */
    protected $curl;

    /**
     * Additional BulkSMS params
     *
     * @var array
     */
    protected $params = array();

    /**
     * @param string $username BulkSMS username
     * @param string $password BulkSMS password
     * @param        $baseUrl
     * @param cURL   $curl
     */
    public function __construct($username, $password, $baseUrl, cURL $curl = null)
    {
        v::url()->setName("Base Bulksms URL")->check($baseUrl);
        $this->baseUrl  = $baseUrl;
        $this->username = $username;
        $this->password = $password;
        $this->curl     = $curl ?: new cURL();
    }

    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * Extract response from Sender - depends on sender
     *
     * @param Response $response
     *
     * @return mixed
     */
    abstract public function extractResponse(Response $response);

    /**
     * Get the full URL for the request.
     *
     * @return string
     */
    protected function getUrl()
    {
        return $this->baseUrl . $this->endpoint;
    }
}
