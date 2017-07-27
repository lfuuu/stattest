<?php

namespace app\models\tariffs;

use app\helpers\tariffs\TariffHelperInterface;

interface TariffInterface
{
    const STATUS_PUBLIC = 'public';
    const STATUS_SPECIAL = 'special';
    const STATUS_ARCHIVE = 'archive';
    const STATUS_TEST = 'test';

    /**
     * @return TariffHelperInterface
     */
    public function getHelper();
}