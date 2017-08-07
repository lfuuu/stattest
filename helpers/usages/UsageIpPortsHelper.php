<?php

namespace app\helpers\usages;

use yii\base\InvalidParamException;
use yii\base\Object;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use app\models\usages\UsageInterface;
use app\models\UsageIpPorts;

class UsageIpPortsHelper extends Object implements UsageHelperInterface
{

    use UsageHelperTrait;

    /** @var UsageIpPorts  */
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
        return 'Интернет';
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
        return [$this->_usage->address, '', ''];
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
        return Url::toRoute(['/pop_services.php', 'table' => UsageIpPorts::tableName(), 'id' => $this->_usage->id]);
    }

    /**
     * @return ActiveRecord
     */
    public function getTransferedFrom()
    {
        return UsageIpPorts::findOne($this->_usage->prev_usage_id);
    }

}