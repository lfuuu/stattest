<?php

namespace app\controllers\api;

use app\forms\lk_wizard\AcceptsForm;
use app\models\ClientContragent;
use yii;
use app\forms\lk_wizard\WizardContragentEuroForm;
use app\forms\lk_wizard\ContactForm;

/**
 * Class WizardSlovakController
 */
class WizardSlovakController extends WizardEuroController
{
    /**
     * @SWG\Definition(definition = "wizard_data_slovak_step2", type = "object",
     *   @SWG\Property(property = "is_contract_accept", type = "boolean", description = "Согласие с договором"),
     *   @SWG\Property(property = "link_contract", type = "string", description = "Ссылка на договор"),
     * ),
     * @SWG\Definition(definition = "wizard_data_slovak_step1", type = "object",
     *   @SWG\Property(property = "name", type = "string", description = "Название организации"),
     *   @SWG\Property(property = "legal_type", type = "string", description = "Тип юр.лица"),
     *   @SWG\Property(property = "inn", type = "string", description = "ИНН"),
     *   @SWG\Property(property = "is_inn", type = "boolean", description = "Есть ИНН"),
     *   @SWG\Property(property = "ogrn", type = "string", description = "ОГРН"),
     *   @SWG\Property(property = "address", type = "string", description = "Адрес"),
     *   @SWG\Property(property = "address_birth", type = "string", description = "Масто рождения"),
     *   @SWG\Property(property = "address_jur", type = "string", description = "Юр адресс"),
     *   @SWG\Property(property = "address_post", type = "string", description = "Почтовый адресс"),
     *   @SWG\Property(property = "birthday", type = "string", description = "Дата рождения"),
     *   @SWG\Property(property = "contact_phone", type = "string", description = "Контактный телефон"),
     *   @SWG\Property(property = "fio", type = "string", description = "ФИО"),
     *   @SWG\Property(property = "is_address_different", type = "string", description = "Различается ли адрес с юридическим"),
     *   @SWG\Property(property = "is_rules_accept_ip", type = "boolean", description = "Согласие для ИП"),
     *   @SWG\Property(property = "is_rules_accept_legal", type = "boolean", description = "Согласие для юр.лица"),
     *   @SWG\Property(property = "is_rules_accept_person", type = "boolean", description = "Согласие для физ.лица"),
     *   @SWG\Property(property = "first_name", type = "string", description = "Имя"),
     *   @SWG\Property(property = "last_name", type = "string", description = "Фамилия"),
     *   @SWG\Property(property = "middle_name", type = "string", description = "Отчество (номер рождения)"),
     *   @SWG\Property(property = "passport_number", type = "string", description = "Номер паспорта"),
     *   @SWG\Property(property = "position", type = "string", description = "Должность подписывающего лица")
     * ),
     * @SWG\Definition(definition = "wizard_data_state", type = "object",
     *   @SWG\Property(property = "good", type = "integer", description = "Пройденых шагов"),
     *   @SWG\Property(property = "step", type = "integer", description = "Текущий шаг"),
     *   @SWG\Property(property = "step_state", type = "string", description = "Состояние последнего шага"),
     *   @SWG\Property(property = "wizard_state", type = "string", description = "Тип визарда"),
     * ),
     * @SWG\Definition(definition = "wizard_data_slovak", type = "object",
     *   @SWG\Property(property = "step1", type = "object", description = "информация по первому шагу", ref="#/definitions/wizard_data_slovak_step1"),
     *   @SWG\Property(property = "step2", type = "object", description = "информация по второму шагу", ref="#/definitions/wizard_data_slovak_step2"),
     *   @SWG\Property(property = "state", type = "object", description = "текущий шаг", ref="#/definitions/wizard_data_state"),
     * ),
     * @SWG\Definition(definition = "wizard_data_slovak_load", type = "object",
     *   @SWG\Property(property = "account_id", type = "integer", description = "ID ЛС"),
     * ),
     * @SWG\Definition(definition = "wizard_data_slovak_save", type = "object",
     *   @SWG\Property(property = "account_id", type = "integer", description = "ID ЛС"),
     *   @SWG\Property(property = "step1", type = "object", description = "информация по первому шагу", ref="#/definitions/wizard_data_slovak_step1"),
     *   @SWG\Property(property = "step2", type = "object", description = "информация по второму шагу", ref="#/definitions/wizard_data_slovak_step2"),
     *   @SWG\Property(property = "state", type = "object", description = "текущий шаг", ref="#/definitions/wizard_data_state"),
     * ),
     * @SWG\Post(tags = {"LkWizard"}, path = "/wizard-slovak/read", summary = "Словацкий визард. Получение данных.", operationId = "wizard-slovak-read",
     *   @SWG\Parameter(name = "", type = "object", description = "идентификатор лицевого счёта", in = "body", @SWG\Schema(ref = "#/definitions/wizard_data_slovak_load")),
     *   @SWG\Response(response = 200, description = "информация по визарду", @SWG\Schema(ref = "#/definitions/wizard_data_slovak")),
     *   @SWG\Response(response = "default", description = "Ошибки", @SWG\Schema(ref = "#/definitions/error_result"))
     * )
     */

    /**
     * @SWG\Post(tags = {"LkWizard"}, path = "/wizard-slovak/save", summary = "Словацкий визард. Сохранение состояния визарда", operationId = "wizard-slovak-save",
     *   @SWG\Parameter(name = "", type = "object", description = "Данные для сохранения", in = "body", @SWG\Schema(ref = "#/definitions/wizard_data_slovak_save")),
     *   @SWG\Response(response = 200, description = "информация по визарду", @SWG\Schema(ref = "#/definitions/wizard_data_slovak")),
     *   @SWG\Response(response = "default", description = "Ошибки", @SWG\Schema(ref = "#/definitions/error_result"))
     * )
     */

    /**
     * Данные первого шага. Информация о организации.
     *
     * @return array
     */
    protected function _getOrganizationInformation()
    {
        $contact = $this->getContact();

        /** @var ClientContragent $c */
        $c = $this->account->contragent;
        $d = [
            'name' => $c->name,
            'legal_type' => $c->legal_type,

            'inn' => $c->inn,
            'is_inn' => !empty($c->inn),

            'ogrn' => $c->ogrn,
            'address_jur' => $c->address_jur,
            'address_post' => $this->account->address_post,
            'is_address_different' => ($c->address_jur != $this->account->address_post),

            'position' => $c->position,
            'fio' => $c->fio,

            'last_name' => ($c->person ? $c->person->last_name : ''),
            'first_name' => ($c->person ? $c->person->first_name : ''),
            'middle_name' => ($c->person ? $c->person->middle_name : ''),

            'passport_number' => ($c->person ? $c->person->passport_number : ''),

            'address_birth' => ($c->person ? $c->person->birthplace : ''),
            'birthday' => ($c->person ? $this->getValidedDateStr($c->person->birthday) : ''),

            'contact_phone' => $contact->data,
            'address' => ($c->person ? $c->person->registration_address : ''),

            'is_rules_accept_legal' => (bool)$this->wizard->is_rules_accept_legal,
            'is_rules_accept_person' => (bool)$this->wizard->is_rules_accept_person,
            'is_rules_accept_ip' => (bool)$this->wizard->is_rules_accept_ip,

        ];
        return $d;
    }

    /**
     * Сохранение первого шага
     *
     * @param array $stepData
     * @return array
     * @throws yii\db\Exception
     */
    protected function _saveStep1($stepData)
    {
        $form = new WizardContragentEuroForm();
        $contactForm = new ContactForm();
        $acceptForm = new AcceptsForm();

        $form->setScenario('slovak');
        $contactForm->setScenario('slovak');
        $acceptForm->setScenario('slovak');

        $stepData['fio'] = ($stepData['legal_type'] == 'legal' ?
            (isset($stepData['fio']) ? $stepData['fio'] : '') :
            ($stepData['last_name'] . ' ' . $stepData['first_name'])
        );

        if (!$form->load($stepData, "") || !$contactForm->load($stepData, "") || !$acceptForm->load($stepData, "")) {
            return ['errors' => ['' => 'load error']];
        }

        if (!$form->validate() || !$contactForm->validate() || !$acceptForm->validate()) {
            return $this->getFormErrors(($form->getErrors() + $contactForm->getErrors() + $acceptForm->getErrors()));
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

            return ['errors' => ['' => 'save error']]; // imposible... but can by
        }
    }

}
