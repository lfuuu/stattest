<?php

namespace app\helpers\usages;

use app\models\UsageVoip;
use yii\base\Object;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use app\classes\Html;
use app\models\usages\UsageInterface;
use app\models\UsageVirtpbx;

class UsageVirtpbxHelper extends Object implements UsageHelperInterface
{

    use UsageHelperTrait;

    private $usage;

    public function __construct(UsageInterface $usage)
    {
        $this->usage = $usage;
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Виртуальная АТС';
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        $value = $this->usage->tariff ? $this->usage->tariff->description : 'Описание';
        $description = [];
        $checkboxOptions = [];

        $numbers = $this->usage->clientAccount->voipNumbers;
        $enabledNumbers = [];

        foreach ($numbers as $number => $options) {
            if ($options['type'] !== 'vpbx' || $options['stat_product_id'] != $this->usage->id) {
                continue;
            }
            $enabledNumbers[] = $number;
        }

        $usages = UsageVoip::find()->where(['IN', 'E164', $enabledNumbers]);

        if ($usages->count()) {
            foreach ($usages->each() as $usage) {
                $description[] =
                    Html::tag(
                        'div',
                        Html::tag('small', $usage->id) . ': ' . reset($usage->helper->description),
                        ['style' => 'margin-left: 10px;']
                    );
            }
        }

        return [$value, implode('', $description), $checkboxOptions];
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return Html::tag(
            'div',
            'ВАТС переносится только с подключенными номерами. ' .
            'Отключить номера можно в настройках ВАТС',
            [
                'style' => 'background-color: #F9F0DF; font-size: 11px; font-weight: bold; padding: 5px; margin-top: 10px;',
            ]
        );
    }

    /**
     * @return string
     */
    public function getEditLink()
    {
        return Url::toRoute(['/pop_services.php', 'table' => UsageVirtpbx::tableName(), 'id' => $this->usage->id]);
    }

    /**
     * @return ActiveRecord
     */
    public function getTransferedFrom()
    {
        return UsageVirtpbx::findOne($this->usage->prev_usage_id);
    }

}