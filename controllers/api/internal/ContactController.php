<?php

namespace app\controllers\api\internal;

use app\classes\ApiInternalController;
use app\classes\DynamicModel;
use app\exceptions\web\BadRequestHttpException;
use app\exceptions\web\NotImplementedHttpException;
use app\models\ClientContact;

/**
 * Class ContactController
 */
class ContactController extends ApiInternalController
{
    /**
     * @throws NotImplementedHttpException
     */
    public function actionIndex()
    {
        throw new NotImplementedHttpException;
    }

    /**
     * @SWG\GET(
     *   tags={"Список контактов лицевого счета"},
     *   path="/internal/contact/search-client-by-email",
     *   summary="Поиск клиента по email'у",
     *   operationId="Поиск клиента по email'у",
     *   @SWG\Parameter(name="email",type="string",description="Email",in="query",required=true),
     *   @SWG\Parameter(name="is_official",type="boolean",description="Емайл официальный",default=true,in="query"),
     *   @SWG\Response(
     *     response=200,
     *     description="Результат работы метода",
     *     @SWG\Definition(
     *       type="object",
     *       @SWG\Property(property="is_found", type="boolean", description="Найден контакт"),
     *       @SWG\Property(property="client_id", type="integer", description="ID клиента"),
     *       @SWG\Property(property="account_id", type="integer", description="ID ЛС"),
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
    /**
     * Поиск клиента по email'у
     *
     * @param string $email
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionSearchClientByEmail($email)
    {
        $form = DynamicModel::validateData($this->requestData, [
            [['email'], 'required'],
        ]);

        if ($form->hasErrors()) {
            $errors = $form->getFirstErrors();
            throw new BadRequestHttpException(reset($errors));
        }

        /** @var ClientContact $contact */
        $contact = ClientContact::find()
            ->where([
                'type' => ClientContact::TYPE_EMAIL,
                'data' => $form->email,
                'is_official' => 1
            ])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if (!$contact) {
            return [
                'is_found' => false
            ];
        }

        return [
            'is_found' => true,
            'client_id' => $contact->client->super_id,
            'account_id' => $contact->client_id,
        ];
    }
}
