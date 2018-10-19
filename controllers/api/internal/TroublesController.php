<?php

namespace app\controllers\api\internal;

use ActiveRecord\RecordNotFound;
use app\classes\ApiInternalController;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Lead;
use app\models\Trouble;
use app\models\TroubleRoistat;
use app\models\TroubleState;
use app\models\TroubleType;
use DateTime;
use DateTimeZone;
use InvalidArgumentException;

class TroublesController extends ApiInternalController
{
    const TYPE_NO_UPDATE_REQUIRED = 0;
    const TYPE_SUCCESS = 1;

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
        $response = [];
        // Получение типа подключения для заявки - лида
        $troubleTypeConnect = TroubleType::findOne(['pk' => TroubleType::CONNECT]);
        if (!$troubleTypeConnect) {
            throw new RecordNotFound(sprintf('Couldn\'t find %s with pk=%d', TroubleType::class, TroubleType::CONNECT));
        }
        // Получение статусов заявки
        $response['statuses'] = array_reduce(TroubleState::find()
            ->where([
                '&', 'pk', TroubleType::find()
                    ->select('states')
                    ->where(['code' => Trouble::TYPE_CONNECT])
                    ->scalar()
            ])
            ->all(), function($sum, $item) {
            /** @var TroubleState $item */
            $sum[] = ['id' => $item->id, 'name' => $item->name,];
            return $sum;
        }, []);
        // Получение заявок, которые были обновлены в течении заданного времени
        $time = new DateTime("{$minutes_range} minutes ago", new DateTimeZone('UTC'));
        $troubleQuery = Trouble::find()
            ->alias('t')
            ->joinWith('troubleRoistat')
            ->where(['t.trouble_type' => $troubleTypeConnect->code])
            ->andWhere(['>', 't.updated_at', $time->format(DateTimeZoneHelper::DATETIME_FORMAT)]);
        foreach($troubleQuery->each() as $trouble) {
            /** @var Trouble $trouble */
            $build = [
                'id' => $trouble->id,
                'date_create' => $trouble->date_creation,
            ];
            // Получение последнего актуального статуса текущей заявки
            if ($currentStage = $trouble->currentStage) {
                $build['status'] = $currentStage->state_id;
            }
            // Получение переменной roistat
            if ($troubleRoistat = $trouble->troubleRoistat) {
                $build['roistat'] = $troubleRoistat->roistat_visit;
            }
            $response['orders'][] = $build;
        }
        return $response;
    }

    /**
     * @SWG\Get(tags = {"Troubles"}, path = "/internal/troubles/bind-trouble-to-variables", summary = "Привязать переменную roistat_visit к заявке", operationId = "BindTroubleToVariables",
     *   @SWG\Parameter(name = "json", type = "string", description = "JSON - строка данных, полученных с Roistat", in = "query", required = true, default = ""),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
     * @param string $json
     * @return int
     */
    public function actionBindTroubleToVariables($json)
    {
        if (!$data = json_decode($json, true)) {
            throw new InvalidArgumentException('Invalid argument exception');
        }
        // Попытка получения trouble_id из таблицы lead по номерам телефонов и времени звонка
        $troubleId = Lead::getDb()
            ->createCommand("
                SELECT trouble_id FROM lead WHERE id = (
                  SELECT
                    MAX(id)
                  FROM
                    lead
                  WHERE
                    created_at BETWEEN DATE_SUB(NOW(), INTERVAL 1 HOUR) AND NOW()
                    AND did = '{$data['caller']}' AND did_mcn = '{$data['callee']}'
                );
            ")
            ->queryScalar();
        // Попытка найти Trouble, что бы проверить ее существование и вызвать связанную модель TroubleRoistat
        $trouble = Trouble::findOne(['id' => $troubleId]);
        if (!$trouble) {
            throw new RecordNotFound("Couldn't find Trouble with ID={$troubleId}");
        }
        // Получаем TroubleRoistat
        $troubleRoistat = $trouble->troubleRoistat;
        if (!$troubleRoistat) {
            $troubleRoistat = new TroubleRoistat;
            $troubleRoistat->trouble_id = $trouble->id;
        } else {
            // Если roistat_visit одинаковый, то перезаписывать не надо
            if ($troubleRoistat->roistat_visit == $data['visit_id']) {
                return 'No update required';
            }
        }
        $troubleRoistat->roistat_visit = $data['visit_id'];
        if (!$troubleRoistat->validate() || !$troubleRoistat->save()) {
            throw new ModelValidationException($troubleRoistat);
        }
        return null;
    }
}