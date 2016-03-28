<?php
/**
 * Вспомогательные сервисы для телефонии
 */

namespace app\controllers\uu;

use app\classes\BaseController;
use app\classes\Html;
use app\classes\uu\model\Tariff;
use app\models\City;
use app\models\DidGroup;
use app\models\Number;
use app\models\UsageVoip;
use Yii;
use yii\web\Response;


class VoipController extends BaseController
{
    const FORMAT_JSON = 'json';
    const FORMAT_OPTIONS = 'options';

    /**
     * Вернуть массив типов номера в зависимости от страны
     * 8800 только для России
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
    public function actionGetFreeNumbers($cityId = null, $didGroupId = null, $rowClass = 6, $orderByField = null, $orderByType = null, $mask = '', $numberType = '')
    {
        switch ($numberType) {
            case Tariff::NUMBER_TYPE_NUMBER:
                break;
            case Tariff::NUMBER_TYPE_7800:
                return '<div class="alert alert-danger">Номера 7800 пока не заведены в базу данных</div>';
                break;
            case Tariff::NUMBER_TYPE_LINE:
                $number = UsageVoip::dao()->getNextLineNumber();
                return Html::checkbox('numberIds[]', true, [
                    'value' => $number,
                    'label' => $number,
                ]);
                break;
            default:
                throw new \InvalidArgumentException('Wrong numberType');
        }

        if (!$cityId) {
            throw new \InvalidArgumentException('Wrong cityId');
        }

        $numberActiveQuery = Number::dao()->getFreeNumbers();
        $numberActiveQuery->andWhere(['city_id' => $cityId]);
        $didGroupId && $numberActiveQuery->andWhere(['did_group_id' => $didGroupId]);

        // если ['LIKE', 'number', $mask], то он заэскейпит спецсимволы и добавить % в начало и конец. Подробнее см. \yii\db\QueryBuilder::buildLikeCondition
        $mask && ($mask = strtr($mask, ['.' => '_', '*' => '%'])) && preg_match('/^[\d_%]+$/', $mask) && $numberActiveQuery->andWhere('number LIKE :mask', [':mask' => $mask]);

        $orderByField && $orderByType && $numberActiveQuery->orderBy([$orderByField => (int)$orderByType]);

        $numberActiveQuery->limit(100);

        return $this->renderPartial('getFreeNumbers', [
            'numberActiveQuery' => $numberActiveQuery,
            'rowClass' => $rowClass,
        ]);
    }

    /**
     * Вернуть массив в нужном формате
     *
     * @param string[] $values
     * @param string $format
     */
    private function returnFormattedValues($values, $format)
    {
        $response = Yii::$app->getResponse();

        switch ($format) {
            case self::FORMAT_OPTIONS:
                $options = '';
                array_walk($values,
                    function ($item, $key) use (&$options) {
                        $options .= sprintf('<option value="%s">%s</option>', $key, $item);
                    }
                );
                $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
                $response->format = Response::FORMAT_HTML;
                echo $options;
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