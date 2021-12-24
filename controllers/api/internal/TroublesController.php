<?php

namespace app\controllers\api\internal;

use ActiveRecord\RecordNotFound;
use app\classes\ApiInternalController;
use app\classes\DynamicModel;
use app\classes\validators\AccountIdValidator;
use app\classes\validators\FormFieldValidator;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\ClientContact;
use app\models\Lead;
use app\models\Trouble;
use app\models\TroubleRoistat;
use app\models\TroubleState;
use app\models\TroubleType;
use app\models\User;
use DateTime;
use DateTimeZone;
use InvalidArgumentException;

class TroublesController extends ApiInternalController
{

    const allowTimeSec = 2678400;// 3600*24*31;
    const allowTimeMin = 44640;// 60*24*31;
    /**
     * @SWG\Get(tags = {"Troubles"}, path = "/internal/troubles/get-changed-clients-for-roistat", summary = "Список созданных клиентов", operationId = "GetChangedClientsForRoistat",
     *   @SWG\Parameter(name = "unixtime", type = "integer", description = "За какой интервал получить актуальные данные", in = "query", default = "", required = true),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
     * @param int $unixtime
     * @return array
     */
    public function actionGetChangedClientsForRoistat($unixtime)
    {
        $unixtime = (int)$unixtime;

        $unixtime = $unixtime < time() - self::allowTimeSec ? time() - self::allowTimeSec : $unixtime;

        $data = [];
        $clients = ClientAccount::find()
            ->innerJoin([
                'subquery' => Trouble::find()
                    ->select('client')
                    ->where(['>', 'date_creation', date('Y-m-d H:i:s', $unixtime)])
                    ->groupBy('client')
            ], 'clients.client = subquery.client');
        foreach ($clients->each() as $client) {
            /** @var ClientAccount $client */
            $contacts = [
                ClientContact::TYPE_EMAIL => [],
                ClientContact::TYPE_PHONE => [],
            ];
            foreach ($client->contacts as $contact) {
                switch ($contact->type) {
                    case ClientContact::TYPE_EMAIL:
                        $contacts[ClientContact::TYPE_EMAIL][] = $contact->data;
                        break;
                    case ClientContact::TYPE_PHONE:
                        $contacts[ClientContact::TYPE_PHONE][] = $contact->data;
                        break;
                }
            }
            $data[] = [
                'id' => $client->id,
                'name' => '',
                'phone' => implode(', ', $contacts[ClientContact::TYPE_PHONE]),
                'email' => implode(', ', $contacts[ClientContact::TYPE_EMAIL]),

            ];
        }
        return ['clients' => $data];
    }

    /**
     * @SWG\Get(tags = {"Troubles"}, path = "/internal/troubles/get-changed-troubles-list-for-roistat", summary = "Список измененных заявок", operationId = "GetChangedTroublesListForRoistat",
     *   @SWG\Parameter(name = "minutes_range", type = "integer", description = "За какой интервал получить актуальные данные", in = "query", default = "30"),
     *   @SWG\Response(response = "default", description = "Ошибки",
     *     @SWG\Schema(ref = "#/definitions/error_result")
     *   )
     * )
     *
     * @param int $minutes_range
     * @return array
     */
    public function actionGetChangedTroublesListForRoistat($minutes_range = 60)
    {
        $response = [];
        // Получение типа подключения для заявки - лида
        $troubleTypeConnect = TroubleType::findOne(['pk' => TroubleType::CONNECT]);
        if (!$troubleTypeConnect) {
            throw new RecordNotFound(sprintf('Couldn\'t find %s with pk=%d', TroubleType::class, TroubleType::CONNECT));
        }
        // Получение статусов заявки
        $response['statuses'] = array_reduce(
            TroubleState::find()
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
        $minutes_range = (int)$minutes_range;
        $minutes_range = $minutes_range > self::allowTimeMin ? self::allowTimeMin : $minutes_range;
        $minutes_range = $minutes_range < 0 ? 0 : $minutes_range;

        $time = new DateTime("{$minutes_range} minutes ago", new DateTimeZone('UTC'));
        $troubleQuery = Trouble::find()
            ->alias('t')
            ->with('account', 'stage')
            ->joinWith('troubleRoistat', true, 'INNER JOIN')
            ->where(['t.trouble_type' => $troubleTypeConnect->code])
            ->andWhere(['>', 't.updated_at', $time->format(DateTimeZoneHelper::DATETIME_FORMAT)]);

        foreach($troubleQuery->each() as $trouble) {
            /** @var Trouble $trouble */
            $build = [
                'id' => $trouble->id,
                'date_create' => strtotime($trouble->date_creation),
            ];

            // Получение переменной roistat
            if ($troubleRoistat = $trouble->troubleRoistat) {
                $build['roistat'] = $troubleRoistat->roistat_visit;;
                $build['price'] = $troubleRoistat->roistat_price;
            }

            // Получение клиента
            if ($client = $trouble->account) {
                $build['client_id'] = $client->id;
            }

            // Получение последнего актуального статуса текущей заявки
            if ($currentStage = $trouble->stage) {
                $build['status'] = $currentStage->state_id;
                $manager = $currentStage->user;
                $build['fields'] = [
                    'Менеджер' => $manager ? $manager->name : $currentStage->user_main
                ];
                if (($troubleRoistat && $troubleRoistat->roistat_fields)) {
                    foreach (json_decode($troubleRoistat->roistat_fields, true) as $key => $value) {
                        $build['fields'][$key] = $value;
                    }
                }
            }

            // Добавляем в массив
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
                SELECT trouble_id FROM `lead` WHERE id in (
                  SELECT
                    MAX(id)
                  FROM
                    `lead`
                  WHERE
                    created_at BETWEEN DATE_SUB(UTC_TIMESTAMP, INTERVAL 1 HOUR) AND UTC_TIMESTAMP AND did = '{$data['caller']}'
                )
            ")
            ->queryScalar();
        // Попытка найти Trouble, что бы проверить ее существование и вызвать связанную модель TroubleRoistat
        $trouble = Trouble::findOne(['id' => $troubleId]);
        if (!$trouble) {
            throw new \LogicException("Couldn't find Trouble with caller:{$data['caller']} and callee:{$data['callee']} in the last hour");
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
        if (!$troubleRoistat->save()) {
            throw new ModelValidationException($troubleRoistat);
        }

        return [
            'status' => 'ok',
            'order_id' => $troubleRoistat->trouble_id,
        ];
    }

    /**
    * @SWG\Get(tags = {"Troubles"}, path = "/internal/troubles/create", summary = "Создать заявку", operationId = "TroubleCreate",
    *   @SWG\Parameter(name = "account_id", type = "integer", description = "ЛС", in = "query", required = true, default = ""),
    *   @SWG\Parameter(name = "type", type = "string", description = "Тип заявки (connect, task, trouble)", in = "query", required = true, default = ""),
    *   @SWG\Parameter(name = "text", type = "string", description = "Текст заявки", in = "query", required = true, default = ""),
    *   @SWG\Parameter(name = "user_id", type = "integer", description = "Текст заявки", in = "query", required = true, default = ""),
    *   @SWG\Response(response = "default", description = "Ошибки",
    *     @SWG\Schema(ref = "#/definitions/error_result")
    *   )
    * )
    */
    public function actionCreate()
    {
        $form = DynamicModel::validateData(
            \Yii::$app->request->get(),
            [
                [['account_id', 'type', 'text', 'user_id'], 'required'],
                ['account_id', AccountIdValidator::class],
                [['type', 'text'], 'string'],
                ['text', FormFieldValidator::class],
                ['type', 'in', 'range' => array_keys(Trouble::$types)],
                ['user_id', 'exist', 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            ]
        );

        if ($form->hasErrors()) {
            throw new InvalidArgumentException(reset($form->getFirstErrors()));
        }

        $user = User::find()->where(['id' => $form->user_id, 'enabled' => new \yii\db\Expression('\'yes\'')])->one();

        if (!$user) {
            throw new InvalidArgumentException('user not found');
        }

        return Trouble::dao()->createTrouble(
            $form->account_id,
            $form->type,
            $form->type,
            $form->text,
            User::SYSTEM_USER,
            $user->user,
        );
    }
}