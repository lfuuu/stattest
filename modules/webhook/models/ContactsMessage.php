<?php

namespace app\modules\webhook\models;

use app\models\ClientContact;

/**
 * Обертка результата, содержащая статус и контакты
 *
 * Если $dids найдены через модель:
 * - ClientContact, то поле $isOrigin является true
 * - AccountTariff, то поле $isOrigin является false
 *
 * В зависимости от переменной $isOrigin происходит отображение комментария (первое поле)
 * во всплывающем уведомлении
 *
 * @link /stat/modules/webhook/views/api/message.php
 */
class ContactsMessage
{
    /**
     * @var ClientContact[]
     */
    public $contacts = [];

    /**
     * @var bool
     */
    public $isOrigin = null;
}