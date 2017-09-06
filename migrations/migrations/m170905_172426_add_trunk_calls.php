<?php
use app\exceptions\ModelValidationException;
use app\modules\uu\models\Resource;
use app\modules\uu\models\ServiceType;

/**
 * Class m170905_172426_add_trunk_calls
 */
class m170905_172426_add_trunk_calls extends \app\classes\Migration
{
    const ID_TRUNK_CALLS = 21; // Транк. Звонки

    /**
     * Up
     *
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        $oldResource = Resource::findOne(['id' => self::ID_TRUNK_CALLS]);
        $oldResource && $oldResource->deleteTariffResource();

        $resource = new Resource();
        $resource->id = Resource::ID_TRUNK_PACKAGE_ORIG_CALLS;
        $resource->name = 'Звонки';
        $resource->unit = '¤';
        $resource->min_value = 0;
        $resource->service_type_id = ServiceType::ID_TRUNK_PACKAGE_ORIG;
        if (!$resource->save()) {
            throw new ModelValidationException($resource);
        }

        $resource->addTariffResource($amount = 0, $pricePerUnit = 1.0);
    }

    /**
     * Down
     *
     * @throws \app\exceptions\ModelValidationException
     * @throws \Exception
     */
    public function safeDown()
    {
        $oldResource = Resource::findOne(['id' => Resource::ID_TRUNK_PACKAGE_ORIG_CALLS]);
        $oldResource && $oldResource->deleteTariffResource();

        // восстанавливать self::ID_TRUNK_CALLS лень
    }
}
