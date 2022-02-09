<?php

namespace app\controllers\api;

use app\classes\validators\FormFieldValidator;
use app\helpers\DateTimeZoneHelper;
use app\models\ContractType;
use app\models\Country;
use app\models\EquipmentUser;
use app\models\HistoryChanges;
use Yii;
use app\classes\validators\AccountIdValidator;
use app\exceptions\ModelValidationException;
use app\classes\ApiController;
use app\classes\DynamicModel;
use app\models\ClientDocument;
use app\models\ClientAccount;
use yii\db\Query;

class LkDocsController extends ApiController
{
    private $accountId = 0;
    private $account = null;

    private function validateAccountId()
    {
        $form = DynamicModel::validateData(
            Yii::$app->request->bodyParams,
            [
                ['account_id', AccountIdValidator::class],
            ]
        );

        if (!$form->hasErrors()) {
            $this->accountId = $form->account_id;
            $this->account = ClientAccount::findOne(['id' => $this->accountId]);;

            $countryCode = $this->account->getUuCountryId() ?: Country::RUSSIA;
            $country = Country::findOne(['code' => $countryCode]) ?: Country::findOne(['code' => Country::RUSSIA]);


            \Yii::$app->language = $country->language;

            return true;
        } else {
            throw new \Exception("Account not found");
        }
    }

    /**
     * @SWG\Post(
     *   tags={"Работа с документами"},
     *   path="/lk-docs/sections/",
     *   summary="Получение списка документов ЛС",
     *   operationId="Получение списка документов ЛС",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="Получение списка документов ЛС",
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
    public function actionSections()
    {
        $this->validateAccountId();

        $data = [[
            'type' => 'client_card',
            'id' => 0
        ]];

        $account = ClientAccount::findOne($this->accountId);

        // Договора показываем только в России
        if (!$account || !$account->contragent || $account->contragent->country_id != Country::RUSSIA) {
            return $data;
        }

        $contract = ClientDocument::find()
            ->contractId($account->contract_id)
            ->active()
            ->contract()
            ->last();

        if ($contract) {
            $data[] = [
                'type' => ClientDocument::DOCUMENT_CONTRACT_TYPE,
                'id' => $contract->id,
                'no' => $contract->contract_no,
                'date' => strtotime($contract->contract_date)
            ];

            if ($contract->blank) {
                $data[] = [
                    'type' => ClientDocument::DOCUMENT_BLANK_TYPE,
                    'id' => $contract->blank->id,
                ];
            }

            foreach ($contract->agreements as $agreement) {
                $data[] = [
                    'type' => ClientDocument::DOCUMENT_AGREEMENT_TYPE,
                    'id' => $agreement->id,
                    'no' => $agreement->contract_dop_no,
                    'date' => strtotime($agreement->contract_dop_date)
                ];
            }
        }

        return $data;
    }

    public function getProperty()
    {
        $a = ClientAccount::findOne($this->accountId);
        $c = $a->contragent;

        $return = ["error" => "Error"];

        if ($c->legal_type == "legal") {
            $return = [
                'company_name' => $c->name,
                'address_jur' => $c->address_jur,
                'inn' => $c->inn,
                'kpp' => $c->kpp,
                'signer_position' => $c->position,
                'signer_fio' => $c->fio,
            ];
        } else {
            if ($c->legal_type == "ip") {
                $return = [
                    'company_name' => $c->name,
                    'address_jur' => $c->address_jur,
                    'last_name' => $c->person->last_name,
                    'first_name' => $c->person->first_name,
                    'inn' => $c->inn,
                    'ogrn' => $c->ogrn,
                ];
            } else { //person

                $passportSerial = $c->person->passport_serial;
                $passportSerial =
                    strlen($passportSerial) > 2
                        ? substr($passportSerial, 0, 2) . preg_replace("/\d/", "*", substr($passportSerial, 2))
                        : $passportSerial;

                $passportNumber = $c->person->passport_number;
                $passportNumber =
                    strlen($passportNumber) > 2
                        ? substr($passportNumber, 0, 1) . preg_replace("/\d/", "*",
                            substr($passportNumber, 1, strlen($passportNumber) - 2)) . substr($passportNumber,
                            strlen($passportNumber) - 1)
                        : $passportNumber;

                $return = [
                    'last_name' => $c->person->last_name,
                    'first_name' => $c->person->first_name,
                    'middle_name' => $c->person->middle_name,
                    'passport_serial' => $passportSerial,
                    'passport_number' => $passportNumber,
                    'passport_date_issued' => date(DateTimeZoneHelper::DATE_FORMAT,
                        ($c->person->passport_date_issued == "0000-00-00" ? 0 : strtotime($c->person->passport_date_issued))),
                    'passport_issued' => $c->person->passport_issued ? "***" : "",
                    'address' => $c->person->registration_address,
                ];
            }
        }

        if ($a->allContacts) {
            $allEmail = [];
            $officialEmails = [];

            foreach ($a->allContacts as $contact) {
                if ($contact->type == "email") {
                    $email = trim($contact->data);
                    $allEmail[$email] = 1;

                    if ($contact->is_official) {
                        $officialEmails[$email] = 1;
                    }
                }
            }

            $return["emails"] = array_keys($officialEmails);
            $return["all_emails"] = array_keys($allEmail);

            sort($return["emails"]);
            sort($return["all_emails"]);
        }

        $data = [];
        $counter = 1;
        foreach ($return as $title => $value) {
            $data[] = [
                "id" => $counter++,
                "title" => $title,
                "value" => $value,
                "is_show" => $title != "all_emails",
                "tips" => false
            ];
        }

        return $data;
    }

    /**
     * @SWG\Post(
     *   tags={"Работа с документами"},
     *   path="/lk-docs/document/",
     *   summary="Получение текста документа",
     *   operationId="Получение текста документа",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Parameter(name="id",type="integer",description="идентификатор документа",in="formData",default="0"),
     *   @SWG\Response(
     *     response="default",
     *     description="Ошибки",
     *     @SWG\Schema(
     *       ref="#/definitions/error_result"
     *     )
     *   )
     * )
     */
    public function actionDocument()
    {
        $form = DynamicModel::validateData(
            Yii::$app->request->bodyParams,
            [
                ['account_id', AccountIdValidator::class],
                [["id"], "required"],
            ]
        );

        if ($form->hasErrors()) {
            throw new ModelValidationException($form);
        }

        $this->accountId = $form->account_id;

        if ($form->id == 0) {
            return $this->getProperty();
        }

        $contractId = ClientAccount::findOne($this->accountId)->contract_id;
        $document = ClientDocument::find()
            ->contractId($contractId)
            ->active()
            ->andWhere(["id" => $form->id])
            ->select(["id", "type", "contract_id", "contract_no", "contract_date", "contract_dop_no", "contract_dop_date"])
            ->one();


        if (!$document) {
            $form->addError("id", "Document not ready");
            throw new ModelValidationException($form);
        }

        $result = $document->toArray();
        $result["content"] = $document->fileContent;

        if ($document->type != ClientDocument::DOCUMENT_AGREEMENT_TYPE) {
            unset($result["contract_dop_no"], $result["contract_dop_date"]);
        }

        unset($result["client_id"]);

        return $result;
    }

    public function actionPassportList()
    {
        $this->validateAccountId();

        $userQuery = EquipmentUser::find()
            ->where(['client_account_id' => $this->accountId]);

        $result = [];

        /** @var EquipmentUser $user */
        foreach ($userQuery->each() as $user) {
            $result[] = $this->_getPassportRow($user);
        }

        return $result;
    }

    public function actionPassportDelete()
    {
        $this->validateAccountId();

        $form = DynamicModel::validateData(
            Yii::$app->request->bodyParams,
            [
                [['id'], 'required'],
                [['id'], 'exist', 'skipOnError' => true, 'targetClass' => EquipmentUser::class, 'filter' => ['client_account_id' => $this->accountId]],
            ]
        );

        $form->validateWithException();

        $equipUser = EquipmentUser::findOne(['id' => $form->id, 'client_account_id' => $this->accountId]);

        return ['success' => $equipUser ? $equipUser->delete() : 0];
    }

    /**
     * @param EquipmentUser $user
     * @return array
     */
    protected function _getPassportRow(EquipmentUser $user)
    {
        return [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'date_of_birth' => (new \DateTime($user->birth_date))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED),
            'passport_num_and_series' => $user->passport,
            'passport_issued_by' => $user->passport_ext,
            'updated' => (new \DateTime($user->updated_at))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED),
        ];
    }

    public function actionPassportSave()
    {
        $this->validateAccountId();

        $form = DynamicModel::validateData(
            Yii::$app->request->bodyParams,
            [
                [['id'], 'exist', 'skipOnEmpty' => true, 'targetClass' => EquipmentUser::class, 'filter' => ['client_account_id' => $this->accountId]],
                [['date_of_birth', 'full_name', 'passport_issued_by', 'passport_num_and_series'], 'required'],
                [['date_of_birth', 'full_name', 'passport_issued_by', 'passport_num_and_series'], 'string'],
                ['date_of_bird', 'date', 'format' => 'd.m.Y']
            ]
        );

        $form->validateWithException();

        if ($form->id) {
            $equipUser = EquipmentUser::findOne(['id' => $form->id, 'client_account_id' => $this->accountId]);
        } else {
            $equipUser = new EquipmentUser();
            $equipUser->client_account_id = $this->accountId;
        }

        $equipUser->setAttributes([
            'birth_date' => (new \DateTime($form->date_of_birth))->format(DateTimeZoneHelper::DATE_FORMAT),
            'full_name' => $form->full_name,
            'passport' => $form->passport_num_and_series,
            'passport_ext' => $form->passport_issued_by,x
        ]);

        if (!$equipUser->save()) {
            throw new ModelValidationException($equipUser);
        }

        return ['success' => 1];
    }

    public function actionPassportsHistory()
    {
        $this->validateAccountId();

        $historyQuery = HistoryChanges::find()
            ->where([
                'model' => EquipmentUser::class,
                'parent_model_id' => $this->accountId
            ])
            ->orderBy([
                'created_at' => SORT_DESC
            ])
            ->limit(500);

        $result = [];
        
        /** @var HistoryChanges $hist */
        foreach ($historyQuery->each() as $hist) {
            $new = json_decode($hist->data_json, true);
            $old = json_decode($hist->prev_data_json, true);

            $result[] = [
                'updated' => (new \DateTime($hist->created_at))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED),
                'new' => $this->_getHistoryRow($new),
                'old' => $this->_getHistoryRow($old),
                'json' => [
                    'new' => $new,
                    'old' => $old,
                ]
            ];
        }

        return $result;
    }

    protected function _getHistoryRow($row)
    {
        if (!$row) {
            return '';
        }

        $data = [];
        foreach(["full_name", "birth_date", "passport","passport_ext"] as $field) {
            $data[] = isset($row[$field]) ? $row[$field] : '';
        }

        return implode(' / ', $data);
    }
}
