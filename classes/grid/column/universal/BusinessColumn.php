<?php

namespace app\classes\grid\column\universal;

use app\models\Business;

class BusinessColumn extends DropdownColumn
{
    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = Business::getList(true, false);
    }
}