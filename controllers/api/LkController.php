<?php

namespace app\controllers\api;

use app\models\media\ClientFiles;
use app\models\User;
use Yii;
use app\classes\Assert;
use app\classes\DynamicModel;
use app\classes\ApiController;
use app\classes\validators\AccountIdValidator;
use app\exceptions\FormValidationException;
use app\models\ClientAccount;
use yii\base\InvalidParamException;

/**
 * Class LkController
 * @package app\controllers\api
 */
class LkController extends ApiController
{
    const MAX_UPLOAD_FILES = 10;
    /**
     * @SWG\Post(
     *   tags={"Работа с лицевыми счетами"},
     *   path="/lk/account-info/",
     *   summary="Получение информации о лицевом счёте",
     *   operationId="Получение информации о лицевом счёте",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="информация о лицевом счёте",
     *     @SWG\Definition(
     *       type="object",
     *       required={"id","country_id","connect_point_id","currency"},
     *       @SWG\Property(property="id",type="integer",description="идентификатор лицевого счёта"),
     *       @SWG\Property(property="country_id",type="integer",description="идентификатор страны"),
     *       @SWG\Property(property="connect_point_id",type="integer",description="идентификатор точки подключения"),
     *       @SWG\Property(property="currency",type="integer",description="валюта")
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
    public function actionAccountInfo()
    {
        $form = DynamicModel::validateData(
            Yii::$app->request->bodyParams,
            [
                ['account_id', AccountIdValidator::className()],
            ]
        );

        if ($form->hasErrors()) {
            throw new FormValidationException($form);
        }

        $account = ClientAccount::findOne(["id" => $form->account_id]);
        Assert::isObject($account);

        return [
            'id' => $account->id,
            'country_id' => $account->country_id,
            'connect_point_id' => $account->region,
            'currency' => $account->currency,
            'country_lang' => $account->country->lang,
            'version' => $account->account_version,
        ];
    }

    /**
     * @SWG\Definition(
     *   definition="file_list",
     *   type="object",
     *   required={"name"},
     *   @SWG\Property(
     *     property="name",
     *     type="string",
     *     description="Название файла"
     *   ),
     * ),
     * @SWG\Post(
     *   tags={"Работа с файлами"},
     *   path="/lk/get-files/",
     *   summary="Получение списка файлов",
     *   operationId="Получение списка файлов",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="Список файлов",
     *       @SWG\Items(
     *         ref="#/definitions/file_list"
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
     **/
    public function actionGetFiles()
    {
        $accountId = (int)(isset(Yii::$app->request->bodyParams['account_id']) ? Yii::$app->request->bodyParams['account_id'] : 0);
        $form = DynamicModel::validateData(
            ['account_id' => $accountId],
            [
                ['account_id', AccountIdValidator::className()],
            ]
        );

        if ($form->hasErrors()) {
            throw new FormValidationException($form);
        }


        return $this->getFiles($form->account_id);
    }

    /**
     * Возвращает список файлов, прикрепленных к договору ЛС
     *
     * @param int $accountId id ЛС
     * @return array
     */
    private function getFiles($accountId)
    {
        $account = ClientAccount::findOne(["id" => $accountId]);
        Assert::isObject($account);

        $files = [];
        foreach (ClientFiles::findAll([
            "contract_id" => $account->contract_id,
            "user_id" => User::CLIENT_USER_ID
        ]) as $file) {
            $files[] = ['name' =>$file->name];
        }

        return $files;
    }


    /**
     * @SWG\Definition(
     *   definition="file",
     *   type="object",
     *   required={"name","content"},
     *   @SWG\Property(property="name",type="string",description="название файла"),
     *   @SWG\Property(property="content",type="string",description="содержимое файла"),
     * ),
     * @SWG\Post(
     *   tags={"Работа с файлами"},
     *   path="/lk/save-file/",
     *   summary="Сохранение документа",
     *   operationId="Сохранение документа",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Parameter(name="file",type="file",description="файл",in="formData",@SWG\Schema(ref="#/definitions/file")),
     *   @SWG\Response(
     *     response=200,
     *     description="Загруженный файл",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="file_name",type="string",description="название файла"),
     *       @SWG\Property(property="file_id",type="integer",description="идентификатор файла"),
     *       @SWG\Property(property="is_can_upload",type="integer",description="Возмодно ли ещё загрузить файл"),
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
     **/
    public function actionSaveFile()
    {
        $form = DynamicModel::validateData(
            Yii::$app->request->bodyParams,
            [
                ['account_id', AccountIdValidator::className()],
            ]
        );

        if ($form->hasErrors()) {
            throw new FormValidationException($form);
        }

        /** @var ClientAccount $account */
        $account = ClientAccount::findOne(["id" => $form->account_id]);
        Assert::isObject($account);

        $files = $this->getFiles($account->id);

        if (count($files) >= self::MAX_UPLOAD_FILES) {
            return ["errors" => [["code" => "max upload file limit"]]];
        }

        $data = Yii::$app->request->bodyParams;

        if (!isset($data["file"]) || !isset($data["file"]["name"]) || !$data["file"]["content"]) {
            throw new InvalidParamException("data_error");
        }

        $file = $account->contract->mediaManager->addFileFromParam(
            $data["file"]["name"],
            base64_decode($data["file"]["content"]),
            "ЛК - wizard",
            User::CLIENT_USER_ID
        );

        if ($file) {
            return ["file_name" => $file->name, "file_id" => $file->id, 'is_can_upload' => (count($files)+1 < self::MAX_UPLOAD_FILES)];
        } else {
            return ["errors" => [["code" => "error upload file"]]];
        }
    }
}
