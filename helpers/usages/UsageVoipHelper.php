<?php

namespace app\helpers\usages;

use yii\base\Object;
use yii\helpers\Url;
use app\classes\Html;
use app\models\Usage;
use app\models\UsageVoip;
use app\models\UsageVirtpbx;

class UsageVoipHelper extends Object implements UsageHelperInterface
{

    private $usage;

    public function __construct(Usage $usage)
    {
        $this->usage = $usage;
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Телефония номера';
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        $value = $this->usage->E164 . ' (линий ' . $this->usage->no_of_lines . ')';
        $description = '';
        $checkboxOptions = [];

        $numbers = $this->usage->clientAccount->voipNumbers;
        if (isset($numbers[$this->usage->E164]) && $numbers[$this->usage->E164]['type'] == 'vpbx') {
            if (($usage = UsageVirtpbx::findOne($numbers[$this->usage->E164]['stat_product_id'])) instanceof Usage) {
                $description = $usage->currentTariff->description . ' (' . $usage->id . ')';
            }
            $checkboxOptions['disabled'] = 'disabled';
        }
        if ($this->usage->type_id == 'line') {
            $number7800 = UsageVoip::findOne(['line7800_id' => $this->usage->id]);
            if ($number7800 instanceof UsageVoip) {
                $description = 'Перенос только вместе с ID: ' . Html::a($number7800->id, 'javascript:void(0)', ['data-linked' => $number7800->id]);
                $checkboxOptions['disabled'] = 'disabled';
            }
        }

        return [$value, $description, $checkboxOptions];
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return Html::tag(
            'div',
            'Заблокированные номера подключены на ВАТС,<br >' .
            'перенос возможен только совместно с ВАТС.<br />' .
            'Отключить номер от ВАТС можно в ЛК',
            [
                'style' => 'background-color: #F9F0DF; font-size: 11px; font-weight: bold; padding: 5px; margin-top: 10px; white-space: nowrap;',
            ]
        );
    }

    /**
     * @return string
     */
    public function getEditLink()
    {
        return Url::toRoute(['/usage/voip/edit', 'id' => $this->usage->id]);
    }

}