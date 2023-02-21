<?php

namespace app\modules\uu\forms;

use app\exceptions\ModelValidationException;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\ServiceTypeFolder;
use app\modules\uu\models\TariffStatus;
use InvalidArgumentException;
use app\classes\Form;
use Yii;
use yii\db\Exception;

class ServiceFolderForm extends Form
{
    public $service_type_id = null;
    public $serviceType = null;

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function init()
    {
        $this->serviceType = ServiceType::findOne(['id' => $this->service_type_id]);
        if (!$this->serviceType) {
            throw new InvalidArgumentException('Unknown service type');
        }

        $this->loadFromPost();
    }

    public function getPriceLevelStatuses()
    {
        return ServiceTypeFolder::find()
            ->where(['service_type_id' => $this->service_type_id])
            ->indexBy('price_level_id');
//            ->select('tariff_status_id')
//            ->indexBy('price_level_id')
//            ->column();
    }

    public function getModel()
    {
        return new ServiceTypeFolder();
    }

    public function getServiceType()
    {
        return $this->serviceType;
    }

    private function loadFromPost()
    {
        if (!\Yii::$app->request->isPost) {
            return;
        }

        $isWithPackage = array_key_exists($this->service_type_id, ServiceType::$serviceToPackage);

        $transaction = Yii::$app->db->beginTransaction();

        try {
            ServiceTypeFolder::deleteAll(['service_type_id' => $this->service_type_id]);
            $post = Yii::$app->request->post('ServiceTypeFolder');

            foreach ($post as $priceLevelId => $data) {

                if ($data['tariff_status_main_id'] == TariffStatus::ID_PUBLIC && (!$isWithPackage || $data['tariff_status_package_id'] == TariffStatus::ID_PUBLIC)) {
                    continue;
                }

                $f = new ServiceTypeFolder([
                        'service_type_id' => $this->service_type_id,
                        'price_level_id' => $priceLevelId,
                    ] + $data);

                if (!$f->save()) {
                    throw new ModelValidationException($f);
                }
            }


            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            throw $e;
        }

    }
}