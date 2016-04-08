<?php
namespace app\models\tariffs;

use app\helpers\tariffs\TariffHelperInterface;

interface TariffInterface
{

    /**
     * @return TariffHelperInterface
     */
    public function getHelper();
}