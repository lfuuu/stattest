<?php

namespace app\modules\uu\forms;

use app\modules\uu\models\TariffStatus;
use InvalidArgumentException;
use Yii;

class TariffStatusEditForm extends TariffStatusForm
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
     * @return TariffStatus
     * @throws \InvalidArgumentException
     */
    public function getTariffStatusModel()
    {
        /** @var TariffStatus $tariffStatus */
        $tariffStatus = TariffStatus::find()
            ->where(['id' => $this->id])
            ->one();
        if (!$tariffStatus) {
            throw new InvalidArgumentException(Yii::t('common', 'Wrong ID'));
        }

        return $tariffStatus;
    }
}