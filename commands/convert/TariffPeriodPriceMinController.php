<?php

namespace app\commands\convert;

use app\exceptions\ModelValidationException;
use app\modules\nnp\models\Package;
use app\modules\uu\models\Period;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffPeriod;
use yii\console\Controller;

class TariffPeriodPriceMinController extends Controller
{
	/**
	 * Клонирование поля price_min из модели TariffPeriod в модель Package
	 */
	public function actionClonePriceMin()
	{
		$result = Tariff::getDb()
			->createCommand("
				SELECT
					tariff.id,
					period.price_min
				FROM 
					" . Tariff::tableName() . " tariff
				INNER JOIN " . TariffPeriod::tableName() . " period 
					ON tariff.id = period.tariff_id
				WHERE 
					tariff.service_type_id IN (" . implode(',', [ServiceType::ID_VOIP_PACKAGE_CALLS, ServiceType::ID_TRUNK_PACKAGE_ORIG, ServiceType::ID_TRUNK_PACKAGE_TERM]) . ") and
					period.charge_period_id = " . Period::ID_MONTH . " and
					period.price_min > 0
			")
			->queryAll();
		$prices = [];
		foreach ($result as $price) {
			$prices[$price['id']] = $price['price_min'];
		}
		unset($result);

		$packages = Package::find()
			->where(['tariff_id' => array_keys($prices)]);
		foreach ($packages->each() as $package) {
			/** @var Package $package */
			$package->price_min = $prices[$package->tariff_id];
			if (!$package->save()) {
				throw new ModelValidationException($package);
			}
		}
	}
}