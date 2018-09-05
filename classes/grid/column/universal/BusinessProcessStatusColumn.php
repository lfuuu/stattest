<?php

namespace app\classes\grid\column\universal;

use app\models\BusinessProcessStatus;

class BusinessProcessStatusColumn extends DropdownColumn
{
    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = BusinessProcessStatus::getList(true, false);
    }
}