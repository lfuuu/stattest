<?php

class Sync1C
{
    public $utWsdlUrl;
    public $utLogin;
    public $utPassword;
    protected $utSoapUrl;
    protected $statToken;
    protected $statSoapUrl;
    protected $statWsdlUrl;

    private static $instance;
    private static $client;

    private function __construct()
    {
        $this->utSoapUrl = defined('SYNC1C_UT_SOAP_URL') ? SYNC1C_UT_SOAP_URL : '';
        $this->utLogin = defined('SYNC1C_UT_LOGIN') ? SYNC1C_UT_LOGIN : '';
        $this->utPassword = defined('SYNC1C_UT_PASSWORD') ? SYNC1C_UT_PASSWORD : '';
        $this->statToken = defined('SYNC1C_STAT_TOKEN') ? SYNC1C_STAT_TOKEN : '';

        $auth = $this->utLogin
            ? $this->utLogin . ($this->utPassword ? ':' . $this->utPassword : '') . '@'
            : '';
        $this->utWsdlUrl = str_replace('://', '://' . $auth, $this->utSoapUrl . '?wsdl');

        $this->statSoapUrl = WEB_ADDRESS . WEB_PATH . "1c/service.php?token=" . $this->statToken;
        $this->statWsdlUrl = WEB_ADDRESS . WEB_PATH . "1c/service.php?wsdl&amp;token=" . $this->statToken;
    }

    /**
     * @return Sync1C
     */
    public static function me()
    {
        return self::$instance ? self::$instance : self::$instance = new Sync1C();
    }

    /**
    * @return Sync1CClient
     */
    public static function getClient()
    {
        if (!self::$client) {

            $params = array('encoding'=>'UTF-8','trace'=>1);
            $login = self::me()->utLogin;
            $pass = self::me()->utPassword;
            if($login && $pass){
                $params['login'] = $login;
                $params['password'] = $pass;
            }
            if (self::me()->utWsdlUrl == '?wsdl') return false;
            $soapClient = new SoapClient(self::me()->utWsdlUrl, $params);
            $soapHandler = new Sync1CClientSoapHandler($soapClient);
            self::$client = new Sync1CClient($soapHandler);
        }
        return self::$client;
    }

    public function serverGetWsdl()
    {
        $originalWsdl = file_get_contents($this->utWsdlUrl);
        return str_replace($this->utSoapUrl, $this->statSoapUrl, $originalWsdl);
    }

    public function serverProcessRequest()
    {
        $server = new SoapServer($this->statWsdlUrl, array('encoding' => 'UTF-8'));
        $server->setObject(new Sync1CServer());
        $server->handle();
    }
 }


class Sync1CClientSoapHandler
{
    protected $soap;

    public function __construct($soapClient)
    {
        $this->soap = $soapClient;
    }

    public function __call($method, $args){
        $translated_args = $args;

        $result = call_user_func_array(array($this->soap, $method), $translated_args);

        return $result;
    }
}
