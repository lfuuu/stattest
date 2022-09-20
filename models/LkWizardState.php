<?php

namespace app\models;

use app\classes\model\ActiveRecord;


/**
 * Class LkWizardState
 *
 * @property int $contract_id
 * @property int $step
 * @property string $state
 * @property int $trouble_id
 * @property string $type
 * @property int $is_bonus_added
 * @property int $is_on
 * @property int $is_rules_accept_legal
 * @property int $is_rules_accept_person
 * @property int $is_rules_accept_ip
 * @property int $is_contract_accept
 * @property int $client_contragent_person
 * @property-read Trouble $trouble
 *
 * @package app\models
 */
class LkWizardState extends ActiveRecord
{
    const TYPE_RUSSIA = 'mcn';
    const TYPE_HUNGARY = 'euro';
    const TYPE_SLOVAK = 'slovak';
    const TYPE_AUSTRIA = 'austria';

    public static $name = [
        self::TYPE_RUSSIA => 'Российский',
        self::TYPE_HUNGARY => 'Венгерский',
        self::TYPE_SLOVAK => 'Словацкий',
        self::TYPE_AUSTRIA => 'Австрйский',
    ];

    const STATE_PROCESS = 'process';
    const STATE_REVIEW = 'review';
    const STATE_APPROVE = 'approve';
    const STATE_REJECTED = 'rejected';

    public static function tableName()
    {
        return 'lk_wizard_state';
    }

    public function getTrouble()
    {
        return $this->hasOne(Trouble::class, ["id" => "trouble_id"]);
    }

    /**
     * Создание состояния визарда
     *
     * @param int $contractId
     * @param int $troubleId
     * @param string $type
     * @return bool
     */
    public static function create($contractId, $troubleId = 0, $type = self::TYPE_RUSSIA)
    {
        $wizard = new self();
        $wizard->contract_id = $contractId;
        $wizard->step = 1;
        $wizard->state = "process";
        $wizard->trouble_id = $troubleId;
        $wizard->type = $type;
        $wizard->is_on = 1;

        return $wizard->save();
    }

    public function getStepName()
    {
        if ($this->type == "mcn") {
            switch ($this->step) {
                case 1:
                    return "Заполнение реквизитов";
                    break;
                case 2:
                    return "Принятие оферты";
                    break;
                case 3:
                    $s = "Ожидание проверки";
                    switch ($this->state) {
                        case 'approve':
                            $s = "Документы проверенны";
                            break;
                        case 'rejected':
                            $s = "Проверка не пройдена";
                            break;
                    }
                    return $s;
            }
        }

        return "Шаг " . $this->step;
    }

    public static function isBPStatusAllow($bpsId, $contractId = 0)
    {
        return (bool)BusinessProcessStatus::find()
            ->where(['id' => $bpsId])
            ->select('is_with_wizard')
            ->scalar();
    }

}
