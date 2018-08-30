<?php

namespace app\helpers\usages;

use app\classes\Html;
use app\models\usages\UsageInterface;
use app\models\UsageVoip;
use app\modules\nnp\models\NdcType;
use yii\base\InvalidParamException;
use yii\base\BaseObject;
use yii\db\ActiveRecord;
use yii\helpers\Url;

class UsageVoipHelper extends BaseObject implements UsageHelperInterface
{

    use UsageHelperTrait;

    /** @var UsageVoip */
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
     * @return string
     */
    public function getValue()
    {
        return $this->_usage->E164;
    }

    /**
     * @return array
     */
    public function getExtendsData()
    {
        return [
            'didGroupId' => $this->_usage->voipNumber->did_group_id,
        ];
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        $value = $this->_usage->E164 . ' (линий ' . $this->_usage->no_of_lines . ')';
        $description = '';
        $checkboxOptions = [];

        if ($this->_usage->ndc_type_id === NdcType::ID_FREEPHONE) {
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
     * @throws InvalidParamException
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