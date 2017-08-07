<?php

namespace app\modules\transfer\forms\services\decorators;

use app\models\ClientAccount;

/**
 * @property-read int $id
 * @property-read string $value
 * @property-read string $description
 * @property-read string $extendsData
 */
interface ServiceDecoratorInterface
{

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getValue();

    /**
     * @return string
     */
    public function getClientAccountUIDField();

    /**
     * @param ClientAccount $clientAccount
     * @return mixed
     */
    public function getClientAccountUID(ClientAccount $clientAccount);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return string - JSON
     */
    public function getExtendsData();

}