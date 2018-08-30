<?php

namespace app\helpers\usages;

use yii\base\InvalidParamException;
use yii\base\BaseObject;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use app\models\usages\UsageInterface;
use app\models\UsageSms;

class UsageSmsHelper extends BaseObject implements UsageHelperInterface
{

    use UsageHelperTrait;

    /** @var UsageSms */
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
        return 'SMS';
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return '';
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        return [$this->_usage->tariff ? $this->_usage->tariff->description : 'Описание', '', ''];
    }

    /**
     * @return array
     */
    public function getExtendsData()
    {
        return [];
    }

    /**
     * @return string
     * @throws InvalidParamException
     */
    public function getEditLink()
    {
        return Url::toRoute(['/pop_services.php', 'table' => UsageSms::tableName(), 'id' => $this->_usage->id]);
    }

    /**
     * @return ActiveRecord
     */
    public function getTransferedFrom()
    {
        return UsageSms::findOne($this->_usage->prev_usage_id);
    }

}