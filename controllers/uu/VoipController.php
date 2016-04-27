<?php
/**
 * Вспомогательные сервисы для телефонии
 */

namespace app\controllers\uu;

use app\classes\BaseController;
use app\classes\Html;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffPeriod;
use app\models\City;
use app\models\DidGroup;
use app\models\filter\FreeNumberFilter;
use app\models\Number;
use app\models\NumberType;
use app\models\UsageVoip;
use Yii;
use yii\web\Response;


class VoipController extends BaseController
{
    const FORMAT_JSON = 'json';
    const FORMAT_OPTIONS = 'options';

    /**
     * Вернуть массив типов номера в зависимости от страны
     * Используется для динамической подгрузки select2 или selectbox
     *
     * @param int $countryId
     * @param bool $isWithEmpty
     * @param string $format
     */
    public function actionGetNumberTypes($countryId = null, $isWithEmpty = false, $format = null)
    {
        if (!$countryId) {
            throw new \InvalidArgumentException('Wrong countryId');
        }
        $numberTypes = Tariff::getVoipTypesByCountryId($countryId, $isWithEmpty);
        $this->returnFormattedValues($numberTypes, $format);
    }

    /**
     * Вернуть массив городов в зависимости от страны
     * Используется для динамической подгрузки select2 или selectbox
     *
     * @param int $countryId
     * @param bool $isWithEmpty
     * @param string $format
     */
    public function actionGetCities($countryId = null, $isWithEmpty = false, $format = null)
    {
        if (!$countryId) {
            throw new \InvalidArgumentException('Wrong countryId');
        }
        $numberTypes = City::dao()->getList($isWithEmpty, $countryId);
        $this->returnFormattedValues($numberTypes, $format);
    }

    /**
     * Вернуть массив красивости номера в зависимости от города
     * Используется для динамической подгрузки select2 или selectbox
     *
     * @param int $cityId
     * @param bool $isWithEmpty
     * @param string $format
     */
    public function actionGetDidGroups($cityId = null, $isWithEmpty = false, $format = null)
    {
        if (!$cityId) {
            throw new \InvalidArgumentException('Wrong cityId');
        }
        $numberTypes = DidGroup::dao()->getList($isWithEmpty, $cityId);
        $this->returnFormattedValues($numberTypes, $format);
    }

    /**
     * Вернуть массив свободных номеров по городу и красивости номера
     *
     * @param int $didGroupId
     */
    public function actionGetFreeNumbers($cityId = null, $didGroupId = null, $rowClass = 6, $orderByField = null, $orderByType = null, $mask = '', $limit = 0, $numberType = '')
    {
        $numbers = new FreeNumberFilter;

        switch ($numberType) {
            case Tariff::NUMBER_TYPE_NUMBER:
                $numbers->getNumbers();
                break;
            case Tariff::NUMBER_TYPE_7800:
                $numbers->getNumbers7800();
                break;
            case Tariff::NUMBER_TYPE_LINE:
                $number = UsageVoip::dao()->getNextLineNumber();
                return Html::checkbox('numberIds[]', true, [
                    'value' => $number,
                    'label' => $number,
                    'disabled' => 'disabled',
                ]);
                break;
            default:
                throw new \InvalidArgumentException('Wrong numberType');
        }

        if (!$cityId) {
            throw new \InvalidArgumentException('Wrong cityId');
        }

        $numbers->setCity($cityId);
        $didGroupId && $numbers->setDidGroup($didGroupId);

        // если ['LIKE', 'number', $mask], то он заэскейпит спецсимволы и добавить % в начало и конец. Подробнее см. \yii\db\QueryBuilder::buildLikeCondition
        if ($mask &&
            ($mask = strtr($mask, ['.' => '_', '*' => '%'])) &&
            preg_match('/^[\d_%]+$/', $mask)
        ) {
            $numbers->setNumberMask($mask);
        }

        $orderByField && $orderByType && $numbers->orderBy([$orderByField => (int)$orderByType]);
        $limit = (int)$limit;

        return $this->renderPartial('getFreeNumbers', [
            'numbers' => $numbers->each()->result($limit ?: 100),
            'rowClass' => $rowClass,
        ]);
    }

    /**
     * Вернуть массив тарифов в зависимости от города
     * Используется для динамической подгрузки select2 или selectbox
     *
     * @param int $serviceTypeId
     * @param string $currency
     * @param int $cityId
     * @param int $isWithEmpty
     * @param string $format
     */
    public function actionGetTariffPeriods($serviceTypeId, $currency, $cityId = null, $isWithEmpty = 0, $format = null)
    {
        if (!$cityId) {
            throw new \InvalidArgumentException('Wrong cityId');
        }

        $tariffPeriods = TariffPeriod::getList($defaultTariffPeriodId, $serviceTypeId, $currency, $cityId, $isWithEmpty);
        $this->returnFormattedValues($tariffPeriods, $format, $defaultTariffPeriodId);
    }

    /**
     * Вернуть массив в нужном формате
     *
     * @param string[] $values
     * @param string $format
     */
    private function returnFormattedValues($values, $format, $defaultValue = '')
    {
        $response = Yii::$app->getResponse();

        switch ($format) {
            case self::FORMAT_OPTIONS:
                $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
                $response->format = Response::FORMAT_HTML;
                echo Html::renderSelectOptions($defaultValue, $values);
                break;

            case self::FORMAT_JSON:
            default:
                $response->headers->set('Content-Type', 'application/json');
                $response->format = Response::FORMAT_JSON;
                echo json_encode($values);
                break;
        }
        Yii::$app->end();
    }
}