<?php

namespace app\classes\important_events\events\properties;

use yii\helpers\Url;
use app\classes\Html;
use app\models\ClientAccount;
use app\models\important_events\ImportantEvents;

/**
 * @property string $name
 */
class ClientProperty extends UnknownProperty implements PropertyInterface
{

    const PROPERTY_CLIENT_ID = 'client.id';
    const PROPERTY_CLIENT_NAME = 'client.name';

    /** @var ClientAccount|null $clientAccount */
    private
        $clientAccount = null;

    /**
     * @param ImportantEvents $event
     */
    public function __construct(ImportantEvents $event)
    {
        parent::__construct($event);

        $this->clientAccount = ClientAccount::findOne(['id' => $event->client_id]);
    }

    /**
     * @return []
     */
    public static function labels()
    {
        return [
            self::PROPERTY_CLIENT_ID => 'ID клиента',
            self::PROPERTY_CLIENT_NAME => 'Клиент',
        ];
    }

    /**
     * @return []
     */
    public function methods()
    {
        return [
            self::PROPERTY_CLIENT_ID => $this->getValue(),
            self::PROPERTY_CLIENT_NAME => $this->getName(),
        ];
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return (!is_null($this->clientAccount) ? $this->clientAccount->id : 0);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return (!is_null($this->clientAccount) ? $this->clientAccount->contragent->name : '');
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return
            Html::tag('b', 'Клиент: ') .
            Html::a(
                $this->getName(),
                Url::toRoute(['/client/view', 'id' => $this->getValue()]),
                ['target' => '_blank']
            );
    }

}