<?php

namespace app\controllers\api;

use app\classes\Assert;
use app\classes\DynamicModel;
use app\classes\validators\AccountIdValidator;
use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\models\LkWizardState;
use app\models\media\ClientFiles;
use app\models\User;
use yii\db\ActiveQuery;

/**
 * Class WizardAustriaController
 */
class WizardAustriaController extends WizardEuroController
{
    protected $wizardType = LkWizardState::TYPE_AUSTRIA;

    private $_filePassportName = 'passport_scan';

    /**
     * Данные первого шага. Информация о организации.
     *
     * @return array
     */
    protected function _getOrganizationInformation()
    {
        $contact = $this->getContact();

        $c = $this->account->contragent;
        $d = [
            "name" => $c->name,
            "legal_type" => $c->legal_type,

            "inn" => $c->inn,
            "is_inn" => !empty($c->inn),

            "address_jur" => $c->address_jur,
            "address_post" => $this->account->address_post,
            "is_address_different" => ($c->address_jur != $this->account->address_post),

            "position" => $c->position,
            "fio" => $c->fio,
            "is_rules_accept_legal" => (bool)$this->wizard->is_rules_accept_legal,

            "last_name" => ($c->person ? $c->person->last_name : ""),
            "first_name" => ($c->person ? $c->person->first_name : ""),

            "address_birth" => ($c->person ? $c->person->birthplace : ''),
            "birthday" => ($c->person ? $this->getValidedDateStr($c->person->birthday) : ''),

            "contact_phone" => $contact->data,
            "is_rules_accept_person" => (bool)$this->wizard->is_rules_accept_person,

            "address" => ($c->person ? $c->person->registration_address : ""),

            "passport_number" => $c->comment,
            "is_file_uploaded" => $this->_getFileQuery($this->account->contract_id)->exists(),

        ];
        return $d;
    }

    public function actionSavePassport()
    {
        $form = DynamicModel::validateData(
            \Yii::$app->request->bodyParams,
            [
                ['account_id', AccountIdValidator::class],
            ]
        );

        if ($form->hasErrors()) {
            throw new ModelValidationException($form);
        }

        /** @var ClientAccount $account */
        $account = ClientAccount::findOne(["id" => $form->account_id]);
        Assert::isObject($account);

        $data = \Yii::$app->request->bodyParams;

        if (!isset($data["file_extension"]) || !isset($data["content"]) || !$data["content"]) {
            throw new \InvalidArgumentException("data_error");
        }

        // удаляем предыдущий скан
        $savedScan = $this->_getFileQuery($account->contract_id)->one();

        if ($savedScan) {
            $savedScan->delete();
        }

        $file = $account->contract->mediaManager->addFileFromParam(
            $name = $this->_filePassportName . '.' . $data["file_extension"],
            $content = base64_decode($data["content"]),
            $comment = "ЛК - wizard",
            $userId = User::CLIENT_USER_ID,
            $isShowInLk = true
        );

        if ($file) {
            return [
                "file_name" => $file->name,
                "file_id" => $file->id,
            ];
        } else {
            return [
                "errors" => [
                    [
                        "code" => "error upload file"
                    ]
                ]
            ];
        }
    }

    /**
     * @param $contract_id
     * @return ActiveQuery
     */
    private function _getFileQuery($contract_id)
    {
        /** @var ClientFiles $savedScan */
        return ClientFiles::find()
            ->where([
                'contract_id' => $contract_id,
                'user_id' => User::CLIENT_USER_ID,
            ])
            ->andWhere(['like', 'name', $this->_filePassportName.'.%', false]);
        
    }


}
