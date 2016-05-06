<?php

namespace app\controllers\api;

use app\forms\lk_wizard\AcceptsForm;
use yii;
use app\models\document\DocumentTemplate;
use app\models\LkWizardState;
use app\models\ClientDocument;
use app\models\TroubleState;
use app\models\User;
use app\forms\lk_wizard\WizardContragentEurForm;
use app\forms\lk_wizard\ContactForm;

/**
 * Class WizardEurController
 *
 * @package app\controllers\api
 */
class WizardEurController extends WizardBaseController
{
    protected $lastStep = 3;

    /**
     * @SWG\Post(
     *   tags={"Работа с европейским визардом"},
     *   path="/wizard-eur/save/",
     *   summary="Сохранение состояния визарда",
     *   operationId="Сохранение состояния визарда",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Parameter(name="step1",type="array",items="#/definitions/step1",description="информация по первому шагу",in="formData"),
     *   @SWG\Parameter(name="step2",type="array",items="#/definitions/step2",description="информация по второму шагу",in="formData"),
     *   @SWG\Parameter(name="step3",type="array",items="#/definitions/step3",description="информация по третьему шагу",in="formData"),
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
                $stepData['step'] = 1;
                $result = $this->_saveStep1($postData["step1"]);
                break;
            }

            case 2: {
                $stepData = $postData["step2"];
                $stepData['legal_type'] = $postData["step1"]["legal_type"];
                $stepData['step'] = 2;
                $result = $this->_saveStep2($stepData);
                break;
            }

        }

        if ($result === true) {
            if ($step == 1 || $step == 2) {
                if ($step == 1 && $this->wizard->step >= 2) { //если пользователь вернулся назад и пересохранил шаг 1
                    $this->eraseContract();
                }
                $step++;
                $this->wizard->step = $step;
                $this->wizard->state = ($step < $this->lastStep ? LkWizardState::STATE_PROCESS : LkWizardState::STATE_REVIEW);
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

    /**
     * @SWG\Post(
     *   tags={"Работа с европейским визардом"},
     *   path="/wizard-eur/get_contract/",
     *   summary="Получение договора",
     *   operationId="Получение договора",
     *   @SWG\Parameter(name="account_id",type="integer",description="идентификатор лицевого счёта",in="formData"),
     *   @SWG\Response(
     *     response=200,
     *     description="договор в формате HTML или PDF",
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
    public function actionGetContract()
    {
        $this->loadAndSet();

        $contract = ClientDocument::findOne([
            "contract_id" => $this->account->contract->id,
            "user_id" => User::CLIENT_USER_ID
        ]);

        if (!$contract) {
            $clientDocument = new ClientDocument();
            $clientDocument->contract_id = $this->account->contract->id;
            $clientDocument->type = 'contract';
            $clientDocument->contract_no = $this->accountId;
            $clientDocument->contract_date = date("Y-m-d");
            $clientDocument->comment = 'ЛК - wizard';
            $clientDocument->user_id = User::CLIENT_USER_ID;
            $clientDocument->template_id = DocumentTemplate::DEFAULT_WIZARD_EUR;
            $clientDocument->save();

            $contract = ClientDocument::find()
                ->where(["user_id" => User::CLIENT_USER_ID])
                ->contractId($this->account->contract->id)
                ->contract()
                ->one();

        }

        if (!$contract || !$contract->fileContent) {
            $content = "error";
        } else {
            $content = $contract->fileContent;
        }

        $content = $this->renderPartial("//wrapper_html", ['content' => $content]);

        if (isset($this->postData['as_html'])) {
            return $content;
        }
        return base64_encode($content);
    }

    /**
     * @inheritdoc
     */
    public function makeWizardFull()
    {
        return [
            "step1" => $this->getOrganizationInformation(),
            "step2" => $this->getContract(),
            "state" => $this->getWizardState()
        ];
    }

    /**
     * Данные первого шага. Информация о организации.
     *
     * @return array
     */
    private function getOrganizationInformation()
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
            "middle_name" => ($c->person ? $c->person->middle_name : ""),

            "address_birth" => ($c->person ? $c->person->birthplace : ""),
            "birthday" => ($c->person ? $c->person->birthday : ""),

            "contact_phone" => $contact->data,
            "is_rules_accept_person" => (bool)$this->wizard->is_rules_accept_person,

            "address" => ($c->person ? $c->person->registration_address : "")
        ];
        return $d;
    }


    /**
     * Данные второго шага. Договор.
     * @return array
     */
    private function getContract()
    {
        return [
            "link_contract" => "/lk/wizard/contract",
            "is_contract_accept" => (bool)$this->wizard->is_contract_accept
        ];
    }

    /**
     * Сохранение первого шага
     *
     * @param array $stepData
     * @return array|bool
     * @throws yii\db\Exception
     */
    private function _saveStep1($stepData)
    {
        $form = new WizardContragentEurForm();
        $contactForm = new ContactForm();
        $acceptForm = new AcceptsForm();

        $contactForm->setScenario('eur');

        $stepData['contact_fio'] = (isset($stepData['fio']) ? $stepData['fio'] : '');

        if (!$form->load($stepData, "") || !$contactForm->load($stepData, "") || !$acceptForm->load($stepData, "")) {
            return ['errors' => ['' => 'load error']];
        }

        if (!$form->validate() || !$contactForm->validate() || !$acceptForm->validate()) {
            return $this->getFormErrors($form->getErrors() + $contactForm->getErrors() + $acceptForm->getErrors());
        } else {
            $transaction = Yii::$app->getDb()->beginTransaction();
            if (
                $form->saveInContragent($this->account)
                && $contactForm->save($this->account)
                && $acceptForm->save($this->wizard)
            ) {
                $transaction->commit();
                return true;
            }

            $transaction->rollBack();

            return ['errors' => ['' => 'save error']]; //imposible... but can by
        }
    }

    /**
     * Сохранение второй шага
     *
     * @param array $stepData
     * @return array|bool
     */
    private function _saveStep2($stepData)
    {
        $form = new AcceptsForm();

        $form->load($stepData, "");

        if (!$form->validate()) {
            return $this->getFormErrors($form);
        } else {
            return $form->save($this->wizard);
        }
    }
}
