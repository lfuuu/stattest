<?php

namespace tests\codeception\unit\usage\sync;


trait _ApiTrait
{
    private $_callStack = [];

    public function clearStack()
    {
        $this->_callStack = [];
    }

    protected function _exec($action, $data, $isSendPost = true)
    {
        $this->_callStack[] = ['action' => $action, 'data' => $data, 'is_send_post' => $isSendPost];
    }

    public function getCallStack()
    {
        return $this->_callStack;
    }
}