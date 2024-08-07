<?php
/**
 * Универсальные тарифы
 */

namespace app\modules\uu\controllers;

use app\classes\Assert;
use app\classes\BaseController;
use app\models\Currency;
use app\modules\uu\filter\TariffFilter;
use app\modules\uu\forms\TariffAddForm;
use app\modules\uu\forms\TariffEditForm;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffPeriod;
use kartik\base\Config;
use Yii;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;


class TariffController extends BaseController
{
    const EDITABLE_NONE = 0;
    const EDITABLE_LIGHT = 1;
    const EDITABLE_FULL = 2;

    /**
     * Права доступа
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => ['tarifs.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['edit'],
                        'verbs' => ['GET'],
                        'roles' => ['tarifs.read'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['new', 'edit', 'edit-by-tariff-period', 'download'],
                        'roles' => ['tarifs.edit'],
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Список
     *
     * @param int $serviceTypeId
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex($serviceTypeId = ServiceType::ID_VPBX)
    {
        $getData = Yii::$app->request->get();
        if (\Yii::$app->isRus() && !isset($getData['TariffFilter'])) {
            $getData['TariffFilter']['currency_id'] = Currency::RUB;
        }
        $filterModel = new TariffFilter($serviceTypeId);
        $filterModel->load($getData);
        $filterModel->initExtraValues();

        return $this->render('index', ['filterModel' => $filterModel]);
    }

    /**
     * Создать
     *
     * @param int $serviceTypeId
     * @param int $countryId
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionNew($serviceTypeId, $countryId = null)
    {
        /** @var TariffAddForm $formModel */
        $formModel = new TariffAddForm([
            'serviceTypeId' => $serviceTypeId,
            'tariffCountries' => [$countryId => true],
            'tariffVoipCountries' => [$countryId => true],
        ]);

        // сообщение об ошибке
        if ($formModel->validateErrors) {
            Yii::$app->session->setFlash('error', $formModel->validateErrors);
        }

        if ($formModel->isSaved) {
            Yii::$app->session->setFlash('success', Yii::t('common', 'The object was created successfully'));
            return $this->redirect(['edit', 'id' => $formModel->id]);
        }

        return $this->render('edit', ['formModel' => $formModel]);
    }

    /**
     * Редактировать
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionEdit()
    {
        $query = Yii::$app->getRequest()
            ->getQueryParams();
        try {
            /** @var TariffEditForm $formModel */
            $formModel = new TariffEditForm([
                'id' => (isset($query['id']) ? (int)$query['id'] : null),
                'tariffCountries' => [
                    (isset($query['countryId']) ? $query['countryId'] : null) => true
                ],
                'tariffVoipCountries' => [
                    (isset($query['countryId']) ? $query['countryId'] : null) => true
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());

            return $this->render('//layouts/empty', ['content' => '']);
        }

        // сообщение об ошибке
        if ($formModel->validateErrors) {
            Yii::$app->session->setFlash('error', $formModel->validateErrors);
        }

        if ($formModel->isSaved) {
            if ($formModel->id) {
                Tariff::deleteCacheById($formModel->id);
                Yii::$app->session->setFlash('success', Yii::t('common', 'The object was saved successfully'));
                return $this->redirect(['edit', 'id' => $formModel->id]);
            }

            Yii::$app->session->setFlash('success', Yii::t('common', 'The object was dropped successfully'));
            // Очищаем данные о текущем тарифе из параметров, сохраняя остальные, и производим редирект на index
            unset($query['id']);
            return $this->redirect(array_merge(['index'], $query));
        }

        return $this->render('edit', ['formModel' => $formModel, 'clientAccount' => $this->getFixClient()]);
    }

    /**
     * @param int $tariffPeriodId
     */
    public function actionEditByTariffPeriod($tariffPeriodId)
    {
        /** @var TariffPeriod $tariffPeriod */
        $tariffPeriod = TariffPeriod::findOne(['id' => $tariffPeriodId]);
        Assert::isObject($tariffPeriod);

        $this->redirect(['edit', 'id' => $tariffPeriod->tariff_id]);
    }

    /**
     * Развернуть в префиксы и скачать
     *
     * @param int $id
     * @return string
     * @throws \LogicException
     * @throws \yii\web\RangeNotSatisfiableHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \BadMethodCallException
     * @throws \yii\base\InvalidParamException
     */
    public function actionDownload($id)
    {
        $id = (int)$id;
        if (!$id) {
            throw new InvalidParamException('Не указан id');
        }

        $tariff = Tariff::findOne(['id' => $id]);
        if (!$tariff) {
            throw new InvalidParamException('Неправильный id');
        }

        $packagePrices = $tariff->packagePrices;
        if (!$packagePrices) {
            throw new \LogicException('Нет цен по направлениям');
        }

        $content = '';

        /** @var \app\modules\nnp\Module $nnpModule */
        $nnpModule = Config::getModule('nnp');
        foreach ($packagePrices as $packagePrice) {

            $destinationName = $packagePrice->destination->name;
            $price = $packagePrice->price;
            $price = str_replace('.', ',', $price);

            $prefixList = $nnpModule->getPrefixListByDestinationID($packagePrice->destination_id);
            foreach ($prefixList as $prefix) {
                $content .= $prefix . ';' . $destinationName . ';' . $price . PHP_EOL;
            }
        }

        Yii::$app->response->sendContentAsFile($content, $tariff->name . '.csv');
        Yii::$app->end();
    }
}