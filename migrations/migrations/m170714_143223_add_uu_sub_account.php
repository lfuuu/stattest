<?php
use app\exceptions\ModelValidationException;
use app\modules\uu\models\Resource;
use app\modules\uu\models\ServiceType;

/**
 * Class m170714_143223_add_uu_sub_account
 */
class m170714_143223_add_uu_sub_account extends \app\classes\Migration
{
    /**
     * Up
     *
     * @throws ModelValidationException
     */
    public function safeUp()
    {
        // Обязательно через модель, чтобы сработали триггеры
        $resource = new Resource;
        $resource->id = Resource::ID_VPBX_SUB_ACCOUNT;
        $resource->name = 'Лимиты по субсчетам';
        $resource->unit = '';
        $resource->min_value = 0;
        $resource->max_value = 1;
        $resource->service_type_id = ServiceType::ID_VPBX;
        $resource->fillerPricePerUnit = 100;
        if (!$resource->save()) {
            throw new ModelValidationException($resource);
        }
    }

    /**
     * Down
     *
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    public function safeDown()
    {
        // Обязательно через модель, чтобы сработали триггеры
        $resource = Resource::findOne(['id' => Resource::ID_VPBX_SUB_ACCOUNT]);
        if (!$resource->delete()) {
            throw new ModelValidationException($resource);
        }
    }
}
