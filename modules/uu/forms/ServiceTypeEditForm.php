<?php

namespace app\modules\uu\forms;

use app\modules\uu\models\ServiceType;

class ServiceTypeEditForm extends ServiceTypeForm
{
    /**
     * Конструктор
     */
    public function init()
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException(\Yii::t('common', 'Wrong ID'));
        }

        parent::init();
    }

    /**
     * @return ServiceType
     */
    public function getServiceTypeModel()
    {
        /** @var ServiceType $serviceType */
        $serviceType = ServiceType::find()
            ->where(['id' => $this->id])
            ->one();
        if (!$serviceType) {
            throw new \InvalidArgumentException(\Yii::t('common', 'Wrong ID'));
        }

        return $serviceType;
    }

}