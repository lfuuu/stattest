<?php

namespace app\modules\sim\models;

use app\classes\DynamicModel;

/**
 * Динамическая модель создана в целях более гибкого обращения к данным и определения статуса ответа
 *
 * Класс-обертка, содержащая в себе api-ответ
 * @see MttApiMvnoConnector::METHOD_GET_ACCOUNT_DATA
 */
class AccountMvno extends DynamicModel
{
    public $isEmpty = true;

    /**
     * @param array $attributes
     * @param array $config
     */
    public function __construct(array $attributes = [], array $config = [])
    {
        if (isset($attributes['result']['data'])) {
            $attributes = $attributes['result']['data'];
            $this->isEmpty= false;
        }

        parent::__construct(array_keys($attributes), $config);
        $this->setAttributes($attributes, false);
    }
}