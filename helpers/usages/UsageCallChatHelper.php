<?php

namespace app\helpers\usages;

use app\models\UsageCallChat;
use app\models\usages\UsageInterface;
use yii\base\InvalidParamException;
use yii\base\Object;
use yii\db\ActiveRecord;
use yii\helpers\Url;

class UsageCallChatHelper extends Object implements UsageHelperInterface
{

    use UsageHelperTrait;

    /** @var UsageCallChat */
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
        return 'Улуга звонок_чат';
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
        return [($this->_usage->tariff ? $this->_usage->tariff->description : 'Описание'), '', ''];
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
        return Url::toRoute(['/usage/call-chat/edit', 'id' => $this->_usage->id]);
    }

    /**
     * @return ActiveRecord
     */
    public function getTransferedFrom()
    {
        return UsageCallChat::findOne($this->_usage->prev_usage_id);
    }

}