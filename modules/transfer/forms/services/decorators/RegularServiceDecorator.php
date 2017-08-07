<?php

namespace app\modules\transfer\forms\services\decorators;

use app\classes\Html;
use app\models\ClientAccount;
use app\models\usages\UsageInterface;
use yii\base\InvalidParamException;
use yii\base\Model;
use yii\helpers\Json;

class RegularServiceDecorator extends Model implements ServiceDecoratorInterface
{

    /** @var UsageInterface */
    public $service;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->service->id;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->service->helper->value;
    }

    /**
     * @return string
     */
    public function getClientAccountUIDField()
    {
        return 'client';
    }

    /**
     * @param ClientAccount $clientAccount
     * @return string
     */
    public function getClientAccountUID(ClientAccount $clientAccount)
    {
        return $clientAccount->client;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        list($fulltext, $description) = (array)$this->service->helper->description;

        return Html::a(
            Html::tag('small', $this->service->id) . ': ' . $fulltext,
            $this->service->helper->editLink,
            ['target' => '_blank']
        ) .
        Html::tag('div', $description, ['class' => 'help-block']) .
        Html::tag('div', $this->service->helper->tariffDescription);
    }

    /**
     * @return string - JSON
     * @throws InvalidParamException
     */
    public function getExtendsData()
    {
        return Json::encode($this->service->helper->extendsData);
    }

}