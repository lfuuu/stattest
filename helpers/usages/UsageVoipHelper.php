<?php

namespace app\helpers\usages;

use app\classes\uu\model\Tariff;
use yii\base\Object;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use app\classes\Html;
use app\models\usages\UsageInterface;
use app\models\UsageVoip;

class UsageVoipHelper extends Object implements UsageHelperInterface
{

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

        if ($this->usage->type_id === Tariff::NUMBER_TYPE_LINE) {
            $number7800 = UsageVoip::findOne(['line7800_id' => $this->usage->id]);
            if ($number7800 instanceof UsageVoip) {
                $description =
                    Html::tag(
                        'div',
                        Html::tag('small', $number7800->id) . ': ' . reset($number7800->helper->description),
                        ['style' => 'margin-left: 10px;']
                    );
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

    /**
     * @return ActiveRecord
     */
    public function getTransferedFrom()
    {
        return UsageVoip::findOne($this->usage->prev_usage_id);
    }

}