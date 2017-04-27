<?php

namespace app\helpers\usages;

use app\modules\uu\models\Tariff;
use yii\base\Object;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use app\classes\Html;
use app\models\usages\UsageInterface;
use app\models\UsageVoip;

class UsageVoipHelper extends Object implements UsageHelperInterface
{

    use UsageHelperTrait;

    private $_usage;

    /**
     * @param UsageInterface $usage
     */
    public function __construct(UsageInterface $usage)
    {
        $this->_usage = $usage;
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
        $value = $this->_usage->E164 . ' (линий ' . $this->_usage->no_of_lines . ')';
        $description = '';
        $checkboxOptions = [];

        if ($this->_usage->type_id === Tariff::NUMBER_TYPE_7800) {
            $line = UsageVoip::findOne(['id' => $this->_usage->line7800_id]);
            if ($line instanceof UsageVoip) {
                $description = Html::tag(
                    'div',
                    Html::tag('small', $line->id) . ': ' . reset($line->helper->description),
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
        return '';
    }

    /**
     * @return string
     */
    public function getEditLink()
    {
        return Url::toRoute(['/usage/voip/edit', 'id' => $this->_usage->id]);
    }

    /**
     * @return ActiveRecord
     */
    public function getTransferedFrom()
    {
        return UsageVoip::findOne($this->_usage->prev_usage_id);
    }

}