<?php
use app\exceptions\ModelValidationException;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\Resource;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffResource;

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
     */
    public function safeUp()
    {
        $this->_deleteResource(self::ID_TRUNK_CALLS);

        $resource = new Resource();
        $resource->id = Resource::ID_TRUNK_PACKAGE_ORIG_CALLS;
        $resource->name = 'Звонки';
        $resource->unit = '¤';
        $resource->min_value = 0;
        $resource->service_type_id = ServiceType::ID_TRUNK_PACKAGE_ORIG;
        if (!$resource->save()) {
            throw new ModelValidationException($resource);
        }

        $tariffs = Tariff::findAll(['service_type_id' => ServiceType::ID_TRUNK_PACKAGE_ORIG]);
        foreach ($tariffs as $tariff) {
            $tariffResource = new TariffResource();
            $tariffResource->amount = 0;
            $tariffResource->price_per_unit = 1;
            $tariffResource->price_min = 0;
            $tariffResource->resource_id = Resource::ID_TRUNK_PACKAGE_ORIG_CALLS;
            $tariffResource->tariff_id = $tariff->id;
            if (!$tariffResource->save()) {
                throw new ModelValidationException($tariffResource);
            }
        }
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->_deleteResource(Resource::ID_TRUNK_PACKAGE_ORIG_CALLS);

        // восстанавливать self::ID_TRUNK_CALLS лень
    }

    /**
     * @param int $resourceId
     */
    private function _deleteResource($resourceId)
    {
        $tariffResources = TariffResource::findAll(['resource_id' => $resourceId]);
        foreach ($tariffResources as $tariffResource) {
            AccountLogResource::deleteAll(['tariff_resource_id' => $tariffResource->id]);
        }

        TariffResource::deleteAll(['resource_id' => $resourceId]);

        AccountTariffResourceLog::deleteAll(['resource_id' => $resourceId]);

        Resource::deleteAll(['id' => $resourceId]);
    }
}
