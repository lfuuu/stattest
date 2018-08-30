<?php

namespace app\helpers\tariffs;

use yii\base\BaseObject;
use yii\helpers\Url;
use app\models\tariffs\TariffInterface;

class TariffVoipHelper extends BaseObject implements TariffHelperInterface
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
        return $this->tariff->name;
    }

    /**
     * @return string
     */
    public function getEditLink()
    {
        return Url::toRoute(['/tariff/voip/edit', 'id' => $this->tariff->id]);
    }

}