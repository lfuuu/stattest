<?php

namespace app\controllers\api\internal;

use app\classes\ApiInternalController;
use app\helpers\DateTimeZoneHelper;
use app\models\Trouble;
use app\models\TroubleType;
use DateTime;
use DateTimeZone;
use yii\base\InvalidParamException;

class TroublesController extends ApiInternalController
{
    /**
     * @SWG\Get(tags = {"Troubles"}, path = "/internal/troubles/get-changed-troubles-list-for-roistat", summary = "Список измененных заявок", operationId = "GetCalltrackingLogs",
     *   @SWG\Parameter(name = "minutes_range", type = "integer", description = "За какой интервал получить актуальные данные", in = "query", default = "30"),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
     * @param int $minutes_range
     * @return array
     */
    public function actionGetChangedTroublesListForRoistat($minutes_range)
    {
        $data = [];
        $troubleTypeConnect = TroubleType::findOne(['pk' => TroubleType::CONNECT]);
        if (!$troubleTypeConnect) {
            throw new InvalidParamException('Тип заявки ' . TroubleType::CONNECT . ' не найден');
        }
        // Формирование запроса на получение заявок, которые были обновлены в течении заданного времени
        $time = new DateTime("{$minutes_range} minutes ago", new DateTimeZone('UTC'));
        $troublesQuery = Trouble::find()
            ->alias('t')
            ->joinWith('troubleRoistat')
            ->where(['t.trouble_type' => $troubleTypeConnect->code])
            ->andWhere(['>', 't.updated_at', $time->format(DateTimeZoneHelper::DATETIME_FORMAT)]);
        // Формирование массива данных, состоящего из данных моделей Trouble и Lead
        foreach ($troublesQuery->each() as $trouble) {
            /** @var Trouble $trouble */
            $cur = $trouble->getAttributes();
            if ($troubleRoistat = $trouble->troubleRoistat) {
                $cur['relations'] = [
                    'trouble_roistat' => $troubleRoistat->getAttributes(),
                ];
            }
            $data[] = $cur;
        }

        return $data;
    }
}