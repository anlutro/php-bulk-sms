<?php
/**
 * @package
 * @subpackage
 * @author    Michael Nowag<michael.nowag@maviance.com>
 * @copyright maviance GmbH 2015
 */

namespace anlutro\BulkSms\Sender;

use anlutro\cURL\cURL;
use anlutro\cURL\Response;
use Respect\Validation\Validator as v;

abstract class ASender
{

    /**
     * The endpoint the call should go to.
     *
     * @var string
     */
    protected $endpoint;

    protected $baseurl;
    /**
     * The cURL instance.
     *
     * @var anlutro\cURL\cURL
     */
    protected $curl;

    /**
     * @param string                 $username BulkSMS username
     * @param string                 $password BulkSMS password
     * @param                        $baseurl
     * @param anlutro\cURL\cURL|cURL $curl     (optional) If you have an existing
     *                                         instance of my cURL wrapper, you can pass it.
     */
    public function __construct($username, $password, $baseurl, cURL $curl = null)
    {
        v::url()->setName("Base Bulksms URL")->check($baseurl);
        $this->baseurl  = $baseurl;
        $this->username = $username;
        $this->password = $password;
        $this->curl     = $curl ?: new cURL();
    }

    /**
     * Extract response from Sender - depends on sender
     *
     * @param Response $response
     *
     * @return mixed
     */
    abstract public function extractResponse(Response $response);

    protected function getUrl()
    {
        return $this->baseurl . $this->endpoint;
    }
}
