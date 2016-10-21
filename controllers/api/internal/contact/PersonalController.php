<?php

namespace app\controllers\api\internal\contact;

use app\classes\ApiInternalController;
use app\classes\DynamicModel;
use app\exceptions\web\BadRequestHttpException;
use app\models\ClientAccount;
use app\models\ClientContactType;
use app\models\ClientContactPersonal;
use app\models\ClientContract;

class PersonalController extends ApiInternalController
{

    public function actionIndex()
    {
        return $this->actionGet();
    }

    /**
     * @SWG\GET(
     *   tags={"Работа с персональными контактами"},
     *   path="/internal/contact/personal/",
     *   summary="Получение списка персональных контактов лицевого счета",
     *   operationId="Получение списка персональных контактов лицевого счета",
     *   @SWG\Parameter(name="contract_id",type="integer",description="ID контракта",in="query",required=true),
     *   @SWG\Response(
     *     response=200,
     *     description="результат работы метода",
     *     @SWG\Definition(
     *       type="object",
     *       @SWG\Property(
     *          property="id",
     *          type="integer",
     *          description="ID персонального контакта"
     *       ),
     *       @SWG\Property(
     *          property="type",
     *          type="string",
     *          description="Тип контакта",
     *          enum={"phone", "email"}
     *       ),
     *       @SWG\Property(
     *          property="contact",
     *          type="string",
     *          description="Контакт"
     *       ),
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
    public function actionGet()
    {
        $contract = $this->getContract();

        return array_map(
            function (ClientContactPersonal $contact) {
                return [
                    'id' => $contact->id,
                    'type' => $contact->type->code,
                    'contact' => $contact->contact
                ];
            },
            ClientContactPersonal::find()->where(['contract_id' => $contract->id])->with('type')->all()
        );
    }

    /**
     * @SWG\POST(
     *   tags={"Работа с персональными контактами"},
     *   path="/internal/contact/personal/add",
     *   summary="Добавление персонального контакта лицевого счета",
     *   operationId="Добавление персонального контакта лицевого счета",
     *   @SWG\Parameter(name="contract_id",type="integer",description="ID контракта",in="query",required=true),
     *   @SWG\Parameter(name="type",type="string",enum={"phone", "email"},description="Тип контакта",in="query",required=true),
     *   @SWG\Parameter(name="contact",type="string",description="Контакт",in="query",required=true),
     *   @SWG\Response(
     *     response=200,
     *     description="результат работы метода",
     *     @SWG\Definition(
     *       type="object",
     *       @SWG\Property(
     *          property="id",
     *          type="integer",
     *          description="ID персонального контакта"
     *       ),
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
    public function actionAdd()
    {
        $contract = $this->getContract();

        $form = DynamicModel::validateData($this->requestData, [
            [['contract_id', 'type', 'contact'], 'required'],
            [
                'type',
                'in',
                'range' =>  [
                        ClientContactType::TYPE_PHONE,
                        ClientContactType::TYPE_EMAIL
                    ]
            ],
            ['contact', 'trim'],
            [
                'contact',
                'email',
                'when' => function ($model) {
                    return $model['type'] == ClientContactType::TYPE_EMAIL;
                }
            ]
        ]);

        if ($form->hasErrors()) {
            $errors = $form->getFirstErrors();
            throw new BadRequestHttpException(reset($errors));
        }

        $typeId = ClientContactType::find()->where(['code' => $this->requestData['type']])->select('id')->scalar();
        $contactField = trim($this->requestData['contact']);

        $contact = ClientContactPersonal::findOne([
            'contract_id' => $contract->id,
            'type_id' => $typeId,
            'contact' => $contactField
        ]);

        if ($contact) {
            return [
                'id' => $contact->id
            ];
        }

        $contact = new ClientContactPersonal();
        $contact->contract_id = $contract->id;
        $contact->type_id = $typeId;
        $contact->contact = $contactField;

        if ($contact->validate() && $contact->save()) {
            $contact->refresh();

            return [
                'id' => $contact->id
            ];
        }

        $errors = $contact->getFirstErrors();
        throw new BadRequestHttpException(reset($errors));
    }

    /**
     * @SWG\POST(
     *   tags={"Работа с персональными контактами"},
     *   path="/internal/contact/personal/delete",
     *   summary="Удаление персонального контакта лицевого счета",
     *   operationId="Удаление персонального контакта лицевого счета",
     *   @SWG\Parameter(name="contract_id",type="integer",description="ID контракта",in="query",required=true),
     *   @SWG\Parameter(name="id",type="integer",description="ID персонального контакта",in="query",required=true),
     *   @SWG\Response(
     *     response=200,
     *     description="результат работы метода",
     *     @SWG\Definition(
     *       type="boolean",
     *       description="Успешно ли удаление"
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
    public function actionDelete()
    {
        $contract = $this->getContract();

        $contactId = isset($this->requestData['id']) ? $this->requestData['id'] : null;

        if (!($contact = ClientContactPersonal::findOne(['id' => $contactId, 'contract_id' => $contract->id]))) {
            return false;
        }

        return (bool)$contact->delete();
    }

    /**
     * Получает и проверяет id контракта
     * @return ClientContract
     * @throws BadRequestHttpException
     */
    private function getContract()
    {
        $contractId = isset($this->requestData['contract_id']) ? $this->requestData['contract_id'] : null;

        if (!$contractId) {
            throw new BadRequestHttpException;
        }

        $contract = ClientContract::findOne(['id' => $contractId]);

        if (!$contract) {
            throw new BadRequestHttpException;
        }

        return $contract;
    }
}
