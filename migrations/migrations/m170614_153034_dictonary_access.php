<?php
use app\models\UserGrantGroups;
use app\models\UserRight;

/**
 * Class m170614_153034_dictonary_access
 */
class m170614_153034_dictonary_access extends \app\classes\Migration
{
    public $dictionary = 'dictionary';
    public $dictionaryRight = 'read,city-billing-method,city,country,entry-point,invoice-settings,public-site,region,tags';

    public $importantEvent = 'dictionary-important-event';
    public $importantEventRight = 'important-events-groups,important-events-names,important-events-sources';

    /**
     * Up
     */
    public function safeUp()
    {
        $this->insert(UserRight::tableName(), [
            'resource' => $this->dictionary,
            'comment' => 'Справочники',
            'values' => $this->dictionaryRight,
            'values_desc' => 'Чтение справочников (всех),' .
                'Редактирование: Методы билингования,' .
                'Редактирование: Города,Редактирование: Страны,' .
                'Редактирование: Точки входа,' .
                'Редактирование: Настройки платежных документов,' .
                'Редактирование: Публичные сайты,' .
                'Редактирование: Регионы,' .
                'Редактирование: Метки',
            'order' => 0
        ]);

        $this->insert(UserRight::tableName(), [
            'resource' => $this->importantEvent,
            'comment' => 'Справочники важных событий',
            'values' => $this->importantEventRight,
            'values_desc' =>
                'Редактирование: Группы событий,' .
                'Редактирование: Названия событий,' .
                'Редактирование: Источники событий',
            'order' => 0
        ]);

        $this->insert(UserGrantGroups::tableName(), [
            'name' => UserGrantGroups::GROUP_ADMIN,
            'resource' => $this->dictionary,
            'access' => $this->dictionaryRight
        ]);

        $this->insert(UserGrantGroups::tableName(), [
            'name' => UserGrantGroups::GROUP_ADMIN,
            'resource' => $this->importantEvent,
            'access' => $this->importantEventRight
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete(UserRight::tableName(), [
            'resource' => $this->dictionary
        ]);

        $this->delete(UserRight::tableName(), [
            'resource' => $this->importantEvent
        ]);

        $this->delete(UserGrantGroups::tableName(), [
            'resource' => $this->dictionary,
        ]);

        $this->delete(UserGrantGroups::tableName(), [
            'resource' => $this->dictionary,
        ]);
    }
}
