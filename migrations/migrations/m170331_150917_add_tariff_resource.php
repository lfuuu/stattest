<?php
use app\classes\uu\model\Resource;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffResource;
use app\exceptions\ModelValidationException;

/**
 * Class m170331_150917_add_tariff_resource
 */
class m170331_150917_add_tariff_resource extends \app\classes\Migration
{
    /**
     * Up
     * Добавить новые ресурсы в тарифы ВАТС
     *
     * @throws ModelValidationException
     * @throws \LogicException
     */
    public function safeUp()
    {
        $tariffQuery = Tariff::find()
            ->where(['service_type_id' => ServiceType::ID_VPBX]);

        /** @var Tariff $tariff */
        foreach ($tariffQuery->each() as $tariff) {

            $tariffResourceMinRoute = null;
            $tariffResourceGeoRoute = null;

            $tariffResources = $tariff->tariffResources;
            foreach ($tariffResources as $tariffResource) {

                if ($tariffResource->resource_id == Resource::ID_VPBX_MIN_ROUTE) {
                    $tariffResourceMinRoute = $tariffResource;
                    continue;
                }

                if ($tariffResource->resource_id == Resource::ID_VPBX_GEO_ROUTE) {
                    $tariffResourceGeoRoute = $tariffResource;
                    continue;
                }
            }

            unset($tariffResources, $tariffResource);

            if (!$tariffResourceMinRoute) {
                $this->_createTariffResource($tariff, Resource::ID_VPBX_MIN_ROUTE);
            }

            if (!$tariffResourceGeoRoute) {
                $this->_createTariffResource($tariff, Resource::ID_VPBX_GEO_ROUTE);
            }
        }
    }

    /**
     * @param Tariff $tariff
     * @param int $resourceId
     * @throws ModelValidationException
     * @throws \LogicException
     */
    private function _createTariffResource(Tariff $tariff, $resourceId)
    {
        $tariffResource = new TariffResource();
        $tariffResource->tariff_id = $tariff->id;
        $tariffResource->resource_id = $resourceId;
        $tariffResource->amount = 0;
        $tariffResource->price_min = 0;

        switch ($tariff->currency_id) {
            case \app\models\Currency::RUB:
                $tariffResource->price_per_unit = 300;
                break;
            case \app\models\Currency::HUF:
                $tariffResource->price_per_unit = 1500;
                break;
            default:
                throw new LogicException('Неподдерживаемая валюта ' . $tariff->currency_id);
        }

        if (!$tariffResource->save()) {
            throw new ModelValidationException($tariffResource);
        }

    }

    /**
     * Down
     */
    public function safeDown()
    {
    }
}
