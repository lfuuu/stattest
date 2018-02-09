<?php

namespace app\modules\uu\forms;

use app\modules\uu\models\TariffVm;
use InvalidArgumentException;
use Yii;

class TariffVmEditForm extends TariffVmForm
{
    /**
     * Конструктор
     *
     * @throws InvalidArgumentException
     * @throws \yii\db\Exception
     */
    public function init()
    {
        if ($this->id === null) {
            throw new InvalidArgumentException(Yii::t('common', 'Wrong ID'));
        }

        parent::init();
    }

    /**
     * @return TariffVm
     * @throws \InvalidArgumentException
     */
    public function getTariffVmModel()
    {
        /** @var TariffVm $tariffVm */
        $tariffVm = TariffVm::find()
            ->where(['id' => $this->id])
            ->one();
        if (!$tariffVm) {
            throw new InvalidArgumentException(Yii::t('common', 'Wrong ID'));
        }

        return $tariffVm;
    }
}