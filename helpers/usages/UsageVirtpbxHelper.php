<?php

namespace app\helpers\usages;

use app\models\UsageVoip;
use yii\base\InvalidParamException;
use yii\base\BaseObject;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use app\classes\Html;
use app\models\usages\UsageInterface;
use app\models\UsageVirtpbx;

class UsageVirtpbxHelper extends BaseObject implements UsageHelperInterface
{

    use UsageHelperTrait;

    /** @var UsageVirtpbx */
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
        return 'Виртуальная АТС';
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
        $value = $this->_usage->tariff ? $this->_usage->tariff->description : 'Описание';
        $description = [];
        $checkboxOptions = [];

        return [$value, implode('', $description), $checkboxOptions];
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
        return Url::toRoute(['/pop_services.php', 'table' => UsageVirtpbx::tableName(), 'id' => $this->_usage->id]);
    }

    /**
     * @return ActiveRecord
     */
    public function getTransferedFrom()
    {
        return UsageVirtpbx::findOne($this->_usage->prev_usage_id);
    }

}