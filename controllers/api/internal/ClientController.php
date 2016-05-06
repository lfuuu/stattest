<?php

namespace app\controllers\api\internal;

use Yii;
use Exception;
use app\classes\ApiInternalController;
use app\models\ClientSuper;
use app\exceptions\web\BadRequestHttpException;
use app\exceptions\api\internal\PartnerNotFoundException;
use app\forms\client\ClientCreateExternalForm;

class ClientController extends ApiInternalController
{
    /**
     * @SWG\Definition(
     *   definition="contract",
     *   type="object",
     *   required={"id","number","accounts"},
     *   @SWG\Property(
     *     property="id",
     *     type="integer",
     *     description="Идентификатор договора"
     *   ),
     *   @SWG\Property(
     *     property="number",
     *     type="string",
     *     description="Номер договора"
     *   ),
     *   @SWG\Property(
     *     property="accounts",
     *     type="array",
     *     description="Массив идентификаторов лицевых счетов",
     *     @SWG\Items(
     *       type="integer"
     *     )
     *   )
     * ),
     * @SWG\Definition(
     *   definition="contragent",
     *   type="object",
     *   required={"id","name","contracts"},
     *   @SWG\Property(
     *     property="id",
     *     type="integer",
     *     description="Идентификатор контрагента"
     *   ),
     *   @SWG\Property(
     *     property="name",
     *     type="string",
     *     description="Название контрагента"
     *   ),
     *   @SWG\Property(
     *     property="contracts",
     *     type="array",
     *     description="Массив договоров",
     *     @SWG\Items(
     *       ref="#/definitions/contract"
     *     )
     *   )
     * ),
     * @SWG\Definition(
     *   definition="client",
     *   type="object",
     *   required={"id","name","contragents"},
     *   @SWG\Property(
     *     property="id",
     *     type="integer",
     *     description="Идентификатор супер-клиента"
     *   ),
     *   @SWG\Property(
     *     property="name",
     *     type="string",
     *     description="Название супер-клиента"
     *   ),
     *   @SWG\Property(
     *     property="contragents",
     *     type="array",
     *     description="Массив контрагентов",
     *     @SWG\Items(
     *       ref="#/definitions/contragent"
     *     )
     *   )
     * ),
     * @SWG\Post(
     *   tags={"Работа с клиентами"},
     *   path="/internal/client/",
     *   summary="Получение данных по клиенту",
     *   operationId="Получение данных по клиенту",
     *   @SWG\Parameter(name="client_id",type="integer",description="идентификатор супер-клиента",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="данные о клиенте",
     *     @SWG\Schema(
     *       ref="#/definitions/client"
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
    public function actionIndex()
    {
        $superId = isset($this->requestData['client_id']) ? $this->requestData['client_id'] : null;

        if (!$superId) {
            throw new BadRequestHttpException;
        }

        if ($superId && ($super = ClientSuper::findOne(['id' => $superId]))
        ) {

            $contragents = [];
            foreach ($super->contragents as $c) {
                $contracts = [];
                foreach ($c->contracts as $cc) {
                    $accounts = [];
                    foreach ($cc->accounts as $a) {
                        $accounts[] = $a->id;
                    }
                    $contracts[] = ['id' => $cc->id, 'number' => $cc->number, 'accounts' => $accounts];
                }

                $contragents[] = [
                    'id' => $c->id,
                    'name' => $c->name,
                    'contracts' => $contracts
                ];
            }

            $data = [
                'name' => $super->name,
                'id' => $super->id,
                'contragents' => $contragents
            ];

            return $data;
        } else {
            throw new BadRequestHttpException;
        }
    }

    /**
     * @SWG\Post(
     *   tags={"Работа с клиентами"},
     *   path="/internal/client/create/",
     *   summary="Создание клиента",
     *   operationId="Создание клиента",
     *   @SWG\Parameter(name="company",type="string",description="название клиента",in="formData",default="Клиент без названия"),
     *   @SWG\Parameter(name="address",type="string",description="адрес",in="formData"),
     *   @SWG\Parameter(name="partner_id",type="integer",description="идентификатор партнёра",in="formData"),
     *   @SWG\Parameter(name="contact_phone",type="string",description="контактный телефон",in="formData"),
     *   @SWG\Parameter(name="official_phone",type="string",description="телефон организации",in="formData"),
     *   @SWG\Parameter(name="fio",type="string",description="Ф.И.О. контактного лица",in="formData"),
     *   @SWG\Parameter(name="fax",type="string",description="факс",in="formData"),
     *   @SWG\Parameter(name="email",type="string",description="email",in="formData",required=true),
     *   @SWG\Parameter(name="comment",type="string",description="комментарий к заказу",in="formData"),
     *   @SWG\Parameter(name="timezone",type="string",description="Таймзона лицевого счета",in="formData",default="Europe/Moscow"),
     *   @SWG\Parameter(name="country_id",type="integer",description="Код страны подключения (ISO)",in="formData",default="643"),
     *   @SWG\Parameter(name="site_name",type="string",description="С какого сайта пришел клиент",in="formData"),
     *   @SWG\Parameter(name="vats_tariff_id",type="integer",description="идентификатор тарифа для ВАТС",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="данные о созданном клиенте",
     *     @SWG\Schema(
     *       type="object",
     *       required={"id","name","contragents"},
     *       @SWG\Property(
     *         property="client_id",
     *         type="integer",
     *         description="Идентификатор супер-клиента"
     *       ),
     *       @SWG\Property(
     *         property="is_created",
     *         type="boolean",
     *         description="Создан ли клиент"
     *       )
     *     )
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
    public function actionCreate()
    {
        $form = new ClientCreateExternalForm;
        $form->setAttributes($this->requestData);

        if ($form->validate()) {
            if ($form->create()) {
                return [
                    'client_id' => $form->super_id,
                    'is_created' => $form->isCreated,
                ];
            }
        } else {
            $fields = array_keys($form->errors);
            if ($fields[0] == 'partner_id') {
                throw new PartnerNotFoundException();
            } else {
                throw new Exception($form->errors[$fields[0]][0], 400);
            }
        }
    }
}
