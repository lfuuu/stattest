<?php

class m151223_083334_important_events_variants extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            INSERT INTO `important_events_names`
                (`code`, `value`, `group_id`)
            VALUES
                ('new_account', 'Создание: Клиент', 1),
                ('account_changed', 'Изменение: Клиент', 1),
                ('extend_account_contract', 'Создание: Доп. контракт', 1),
                ('contract_transfer', 'Перемешение: Контракт', 1),
                ('account_contract_changed', 'Изменение: Контракт', 1),
                ('transfer_contragent', 'Перемещение: Контрагент', 1),

                ('created_trouble', 'Создание: Заявка', 1),
                ('closed_trouble', 'Закрытие: Заявка', 1),
                ('set_state_trouble', 'Изменение: Статус заявки', 1),
                ('set_responsible_trouble', 'Изменение: Ответственного за заявку', 1),
                ('new_comment_trouble', 'Создание: Комментарий к заявке', 1),

                ('enabled_usage', 'Подключено: Услуга', 1),
                ('disabled_usage', 'Отключено: Услуга', 1),
                ('created_usage', 'Создание: Услуга', 1),
                ('updated_usage', 'Изменение: Услуга', 1),
                ('deleted_usage', 'Удаление: Услуга', 1),
                ('transfer_usage', 'Перемещение: Услуга', 1);
        ");
    }

    public function down()
    {
        echo "m151223_083334_important_events_variants cannot be reverted.\n";

        return false;
    }
}