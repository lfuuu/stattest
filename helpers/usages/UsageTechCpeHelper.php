<?php

namespace app\helpers\usages;

use yii\base\InvalidParamException;
use yii\base\BaseObject;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use app\models\UsageTechCpe;

class UsageTechCpeHelper extends BaseObject implements UsageHelperInterface
{

    use UsageHelperTrait;

    /** @var UsageTechCpe  */
    private $_usage;

    /**
     * @param UsageTechCpe $usage
     */
    public function __construct(UsageTechCpe $usage)
    {
        $this->_usage = $usage;
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Клиентские устройства';
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
        return [$this->_usage->model->vendor . ' ' . $this->_usage->model->model, '', ''];
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
        return Url::toRoute(['/', 'module' => 'routers', 'action' => 'd_edit', 'id' => $this->_usage->id]);
    }

    /**
     * @return ActiveRecord
     */
    public function getTransferedFrom()
    {
        return UsageTechCpe::findOne($this->_usage->prev_usage_id);
    }

}