<?php

class Sync1CServer
{
    protected $helper;
    protected $handler;

    public function __construct()
    {
        $this->helper = new Sync1CHelper();
        $this->handler = new Sync1CServerHandler($this->helper);
    }

    public function __call($method,$args){
        if (method_exists($this->handler, $method)) {
            try {

                $translated_args = $this->helper->translateToKoi8r($args);

                $result = call_user_func_array(array($this->handler, $method), $translated_args);

                return $this->helper->translateToUtf8($result);

            } catch (SoapFault $e) {
                throw $e;
            } catch (Exception $e) {
                throw new SoapFault('error', $this->helper->translateToUtf8($e->getMessage()));
            }
        }

        require_once INCLUDE_PATH . "1c_integration.php";
        $oldHandler = new _1c\SoapHandler();
        if (method_exists($oldHandler, $method)) {
            return call_user_func_array(array($oldHandler, $method), $args);
        }
    }
}