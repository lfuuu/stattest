<?php

namespace app\classes\grid\column\universal;

use app\models\BusinessProcess;

class BusinessProcessColumn extends DropdownColumn
{
    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = BusinessProcess::getList(true, false);
    }
}