<?php

namespace app\classes\contragent\importer\lk;


use app\classes\contragent\importer\lk\typeFactory\CoreLkContragentTypeDefault;
use app\classes\contragent\importer\lk\typeFactory\CoreLkContragentTypeFactory;
use app\classes\Utils;
use app\models\ClientContragent;
use app\models\User;

class CoreLkContragent
{
    public array $row = [];
    protected ClientContragent $statContragent;

    public function __construct($dbRow)
    {
        $this->row = $dbRow;//['contragent_id' => $dbRow['contragent_id'], 'name' => $dbRow['name']];
    }

    public function getOrgType(): string
    {
        $name = $this->row['name'] ?? '';

        if ($this->row['opf'] == CoreLkContragentTypeDefault::OPF_IP || strpos($name, 'ИП ') === 0) {
            return CoreLkContragentTypeDefault::ORG_TYPE_INDIVIDUAL;
        };

        if ($this->row['org_type'] == CoreLkContragentTypeDefault::ORG_TYPE_BUSINESS) {
            switch ($this->row['legal_type']) {
                case ClientContragent::IP_TYPE:
                    return CoreLkContragentTypeDefault::ORG_TYPE_INDIVIDUAL;
                case ClientContragent::LEGAL_TYPE:
                    return CoreLkContragentTypeDefault::ORG_TYPE_LEGAL;
                case ClientContragent::PERSON_TYPE:
                    return CoreLkContragentTypeDefault::ORG_TYPE_PHYSICAL;
                default:
                    throw new \InvalidArgumentException('Unknown contragent type: ' . var_export($this->row['legal_type'], true));
            }
        }

        return $this->row['org_type'] ?? '';
    }

    public function getName(): string
    {
        return $this->row['name'];
    }

    public function getStatus(): string
    {
        return $this->row['status'];
    }

    public function getContragentId(): string
    {
        return $this->row['contragent_id'] ?? false;
    }

    public function getAddressRegistratonIp(): string
    {
        return (string)$this->row['reg_address_ip'];
    }

    public function getAddressPostFilial(): string
    {
        return (string)$this->row['postal_address'];
    }

    public function getAddress(): string
    {
        return (string)$this->row['address'];
    }

    public function setStatContragent(ClientContragent $contragent)
    {
        $this->statContragent = $contragent;
    }

    public function getStatContragent(): ?ClientContragent
    {
        return $this->statContragent;
    }

    private function loadStatContragent()
    {
        $this->setStatContragent(ClientContragent::find()->where(['id' => $this->getContragentId()])->with('personModel')->one());
    }

    public function getTransformatorByType(): CoreLkContragentTypeDefault
    {
        if (!isset($this->statContragent)) {
            $this->loadStatContragent();
        }

        return CoreLkContragentTypeFactory::getTransformer($this);
    }

    public function getDataResponse()
    {
        return Utils::fromJson($this->row['data_response']);
    }

    public function isVerified(): bool
    {
        return $this->row['status'] == 'verified';
    }

    public function isLkFirst(): bool
    {
        return (bool)$this->row['is_lk_first'] ?? 0;
    }

    public static function syncAndUpdate($contragentId): bool
    {
        if (!$contragentId) {
            return false;
        }

        self::syncDbRow($contragentId);
        self::update($contragentId);

        return true;
    }

    public static function syncDbRow($contragentId): bool
    {
        return (new CoreLkContragentDbSyncer($contragentId))->sync();
    }

    public static function update($contragentId = 0)
    {
        $identity = \Yii::$app->user->identity;
        \Yii::$app->user->setIdentity(User::findOne(['id' => User::LK_USER_ID]));

        /** @var CoreLkContragent $obj */
        foreach (DataLoader::getObjectsForSync($contragentId) as $obj) {
            $obj
                ->getTransformatorByType()
                ->update();
        }

        \Yii::$app->user->setIdentity($identity);
    }
}

