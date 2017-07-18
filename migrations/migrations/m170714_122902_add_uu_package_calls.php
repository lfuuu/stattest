<?php
use app\exceptions\ModelValidationException;
use app\modules\uu\models\Resource;
use app\modules\uu\models\ServiceType;

/**
 * Class m170714_122902_add_uu_package_calls
 */
class m170714_122902_add_uu_package_calls extends \app\classes\Migration
{
    /**
     * Up
     *
     * @throws ModelValidationException
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    public function safeUp()
    {
        // Обязательно через модель, чтобы сработали триггеры
        $resource = new Resource;
        $resource->id = Resource::ID_VOIP_PACKAGE_CALLS;
        $resource->name = 'Звонки';
        $resource->unit = Resource::DEFAULT_UNIT;
        $resource->min_value = 0;
        $resource->max_value = null;
        $resource->service_type_id = ServiceType::ID_VOIP_PACKAGE;
        $resource->fillerPricePerUnit = 1;
        if (!$resource->save()) {
            throw new ModelValidationException($resource);
        }

        $resource = Resource::findOne(['id' => 8]); // константа уже выпилена
        if (!$resource->delete()) {
            throw new ModelValidationException($resource);
        }
    }

    /**
     * Down
     *
     * @throws ModelValidationException
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    public function safeDown()
    {
        // Обязательно через модель, чтобы сработали триггеры
        $resource = new Resource;
        $resource->id = 8; // константа уже выпилена
        $resource->name = 'Звонки';
        $resource->unit = Resource::DEFAULT_UNIT;
        $resource->min_value = 0;
        $resource->max_value = null;
        $resource->service_type_id = ServiceType::ID_VOIP;
        $resource->fillerPricePerUnit = 1;
        if (!$resource->save()) {
            throw new ModelValidationException($resource);
        }

        $resource = Resource::findOne(['id' => Resource::ID_VOIP_PACKAGE_CALLS]);
        if (!$resource->delete()) {
            throw new ModelValidationException($resource);
        }
    }
}
