<?php

namespace app\helpers\tariffs;

use yii\base\Object;
use yii\helpers\Url;
use app\models\tariffs\TariffInterface;

class TariffVirtpbxHelper extends Object implements TariffHelperInterface
{

    private $tariff;

    /**
     * @param TariffInterface $tariff
     */
    public function __construct(TariffInterface $tariff)
    {
        $this->tariff = $tariff;
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->tariff->description;
    }

    /**
     * @return string
     */
    public function getEditLink()
    {
        return Url::toRoute([
            '/index.php',
            'module' => 'tarifs',
            'action' => 'edit',
            'm' => 'virtpbx',
            'id' => $this->tariff->id
        ]);
    }

}