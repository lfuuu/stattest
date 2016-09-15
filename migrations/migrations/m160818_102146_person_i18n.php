<?php

use app\models\Language;
use app\models\PersonI18N;
use app\models\Person;

class m160818_102146_person_i18n extends \app\classes\Migration
{
    public function up()
    {
        $tableName = PersonI18N::tableName();

        $this->createTable(
            $tableName,
            [
                'person_id' => $this->integer(11),
                'lang_code' => $this->string(5)->notNull()->defaultValue(Language::LANGUAGE_DEFAULT),
                'field' => $this->string(255),
                'value' => $this->string(255)
            ],
            'ENGINE=InnoDB CHARSET=utf8'
        );

        $this->addForeignKey(
            'fk-' . $tableName . '-person_id',
            $tableName,
            'person_id',
            Person::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-' . $tableName . '-lang_code',
            $tableName,
            'lang_code',
            Language::tableName(),
            'code',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex(
            'person_id-lang_code-field',
            $tableName,
            [
                'person_id',
                'lang_code',
                'field'
            ],
            $isUnique = true
        );

        $persons  = Person::find()->all();
        $insert = [];

        foreach ($persons as $person) {
            $insert[] = [
                $person->getPrimaryKey(),
                Language::LANGUAGE_DEFAULT,
                'name_nominative',
                $person->name_nominative,
            ];
            $insert[] = [
                $person->getPrimaryKey(),
                Language::LANGUAGE_DEFAULT,
                'name_genitive',
                $person->name_genitive,
            ];
            $insert[] = [
                $person->getPrimaryKey(),
                Language::LANGUAGE_DEFAULT,
                'post_nominative',
                $person->post_nominative,
            ];
            $insert[] = [
                $person->getPrimaryKey(),
                Language::LANGUAGE_DEFAULT,
                'post_genitive',
                $person->post_genitive,
            ];
        }

        if (count($insert)) {
            $chunks = array_chunk($insert, 1000);

            foreach ($chunks as $chunk) {
                $this->batchInsert($tableName, ['person_id', 'lang_code', 'field', 'value'], $chunk);
            }
        }

        $personTable = Person::tableName();
        $this->dropColumn($personTable, 'name_nominative');
        $this->dropColumn($personTable, 'name_genitive');
        $this->dropColumn($personTable, 'post_nominative');
        $this->dropColumn($personTable, 'post_genitive');
    }

    public function down()
    {
        $personTable = Person::tableName();

        $this->addColumn($personTable, 'name_nominative', $this->string(250));
        $this->addColumn($personTable, 'name_genitive', $this->string(150));
        $this->addColumn($personTable, 'post_nominative', $this->string(150));
        $this->addColumn($personTable, 'post_genitive', $this->string(250));

        foreach (PersonI18N::find()->where(['lang_code' => Language::LANGUAGE_DEFAULT])->each() as $record) {
            Yii::$app->db->createCommand(
                'UPDATE ' . $personTable . ' SET
                    ' . $record->field . ' = :value
                WHERE
                    id = :person_id
                ', [
                    'value' => $record->value,
                    'person_id' => $record->person_id,
                ]
            )->execute();
        }

        $tableName = PersonI18N::tableName();
        $this->dropTable($tableName);
    }
}