<?php

namespace app\controllers\api;

use app\helpers\DateTimeZoneHelper;
use app\models\ClientContragent;
use app\models\ClientDocument;
use Yii;
use app\models\document\DocumentTemplate;
use app\models\LkWizardState;
use app\models\TroubleState;
use app\models\User;
use app\forms\lk_wizard\WizardContragentMcnForm;


/**
 * Класс-контроллер работы с российским визардом
 *
 * Class WizardMcnController
 * @package app\controllers\api
 */
class WizardMcnController extends WizardBaseController
{
    protected $lastStep = 3;

    /**
     * @SWG\Post(
     *   tags={"Работа с визардом"},
     *   path="/wizard-mcn/save/",
     *   summary="Сохранение состояния визарда",
     *   operationId="Сохранение состояния визарда",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Parameter(name="step1",type="array",items="#/definitions/step1",description="информация по первому шагу",in="formData"),
     *   @SWG\Parameter(name="step2",type="array",items="#/definitions/step2",description="информация по второму шагу",in="formData"),
     *   @SWG\Parameter(name="step3",type="array",items="#/definitions/step3",description="информация по третьему шагу",in="formData"),
     *   @SWG\Parameter(name="step4",type="array",items="#/definitions/step4",description="информация по четвёртому шагу",in="formData"),
     *   @SWG\Parameter(name="state",type="integer",description="текущий шаг",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="информация по визарду",
     *     @SWG\Schema(
     *       ref="#/definitions/wizard_data"
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
    public function actionSave()
    {
        $postData = $this->loadAndSet();

        $result = true;

        $step = $postData["state"]["step"];

        switch ($step) {
            case 1: {
                $result = $this->_saveStep1($postData["step1"]);
                break;
            }

            case 2: {
                $result = $this->_saveStep2($postData["step2"]);
                break;
            }

            case 3: {
                $result = $this->_saveStep3($postData["step3"]);
                break;
            }
        }

        if ($result === true) {
            if ($step == 1 || $step == 2) {
                $this->wizard->step = $step+1;
                $this->wizard->state = ($step == 1 ? LkWizardState::STATE_PROCESS : ($step+1 == $this->lastStep ? LkWizardState::STATE_REVIEW : LkWizardState::STATE_PROCESS));
                $this->wizard->save();

                if ($this->wizard->step == $this->lastStep && $this->wizard->state == LkWizardState::STATE_REVIEW) {
                    $manager = $this->makeNotify();

                    $this->wizard->trouble->addStage(
                        TroubleState::CONNECT__VERIFICATION_OF_DOCUMENTS,
                        "Клиент ожидает проверки документов. ЛК - Wizard",
                        ($manager ? $manager->id : null),
                        User::LK_USER_ID
                    );
                }
            }
        } else { //error
            return $result;
        }

        return $this->makeWizardFull();
    }

    public function actionNextstep()
    {
        $data = $this->loadAndSet();

        $this->wizard->step = 2;
        $this->wizard->save();

        $legalType = isset($data['legal_type']) && isset(ClientContragent::$defaultOrganization[$data['legal_type']])? $data['legal_type'] : ClientContragent::LEGAL_TYPE;

        $contragent = $this->account->contragent;

        $contragent->legal_type = $legalType;
        $contragent->save();


        return ['result' => true];
    }

    public function actionGetContract()
    {
        $data = $this->loadAndSet();

        $contract = ClientDocument::findOne([
            "contract_id" => $this->account->contract->id,
            "user_id" => User::CLIENT_USER_ID
        ]);

        if ($contract) {
            $contract->erase();
        }


        $content = "error";
        $document = null;

        if (isset($data['type']) && $data['type'] == 'legal') {
            $documentId = DocumentTemplate::DEFAULT_WIZARD_MCN_LEGAL_LEGAL;
        } else {
            $documentId = DocumentTemplate::DEFAULT_WIZARD_MCN_LEGAL_PERSON;
        }

        $contract = new ClientDocument();
        $contract->contract_id = $this->account->contract->id;
        $contract->type = 'contract';
        $contract->contract_no = $this->accountId;
        $contract->contract_date = date(DateTimeZoneHelper::DATE_FORMAT);
        $contract->comment = 'ЛК - wizard - оферта';
        $contract->user_id = User::CLIENT_USER_ID;
        $contract->template_id = $documentId;
        $contract->save();

        if ($contract) {
            $content = $contract->fileContent;
        }

        $content = $this->renderPartial("//wrapper_html", ['content' => $content]);

        if (isset($this->postData['as_html'])) {
            return $content;
        }

        return base64_encode($content);
    }

    public function makeWizardFull()
    {
        return [
            "step1" => $this->getOrganizationInformation(),
            "step2" => $this->getContractAccepts(),
            "step3" => $this->getAccountManager(),
            "state" => $this->getWizardState()
        ];
    }


    private function getOrganizationInformation()
    {
        $c = $this->account->contragent;

        $d = [
            "name" => $c->name,
            "legal_type" => $c->legal_type,
            "address_jur" => $c->address_jur,
            "address_post" => $this->account->address_post,
            "inn" => $c->inn,
            "kpp" => $c->kpp,
            "position" => $c->position,
            "fio" => $c->fio,
            "ogrn" => $c->ogrn,
            "last_name" => ($c->person ? $c->person->last_name : ""),
            "first_name" => ($c->person ? $c->person->first_name : ""),
            "middle_name" => ($c->person ? $c->person->middle_name : ""),
            "passport_serial" => ($c->person ? $c->person->passport_serial : ""),
            "passport_number" => ($c->person ? $c->person->passport_number : ""),
            "passport_date_issued" => ($c->person ? ($c->person->passport_date_issued && $c->person->passport_date_issued != '0000-00-00' ? $c->person->passport_date_issued : '') : ''),
            "passport_issued" => ($c->person ? $c->person->passport_issued : ""),
            "address" => ($c->person ? $c->person->registration_address : ""),
        ];
        return $d;
    }

    private function getContractAccepts()
    {
        return [
            "is_contract_accept" => (bool)$this->wizard->is_contract_accept
        ];
    }

    private function getAccountManager()
    {
        $manager = $this->account->userAccountManager ?: User::findOne(User::DEFAULT_ACCOUNT_MANAGER_USER_ID);

        if (!$manager) {
            return [
                "manager_name" => "",
                "manager_phone" => "(495) 105-99-99"
            ];
        }

        return [
            "manager_name" => $manager->name,
            "manager_phone" => "(495) 105-99-99" . ($manager->phone_work ? " доп. " . $manager->phone_work : "")
        ];
    }

    private function _saveStep1($stepData)
    {
        $form = new WizardContragentMcnForm();

        $form->load($stepData, "");

        if (!$form->validate()) {
            return $this->getFormErrors($form);
        } else {
            return $form->saveInContragent($this->account);
        }
    }

    private function _saveStep2($stepData)
    {
        $this->wizard->is_contract_accept = (int)$stepData['is_contract_accept'];
        $this->wizard->save();

        $this->wizard->refresh();

        return $stepData['is_contract_accept'];
    }

}
