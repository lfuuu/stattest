<?php

namespace app\models;

use app\classes\model\HistoryActiveRecord;

/**
 * @property int $contragent_id
 * @property string $last_name
 * @property string $first_name
 * @property string $middle_name
 * @property string $passport_date_issued
 * @property string $passport_serial
 * @property string $passport_number
 * @property string $passport_issued
 * @property string $registration_address
 * @property string $mother_maiden_name
 * @property string $birthplace
 * @property string $birthday
 * @property string $other_document
 */
class ClientContragentPerson extends HistoryActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'client_contragent_person';
    }

    public function attributeLabels()
    {
        return (new ClientContragent())->attributeLabels() + [
            'contragent_id' => 'Контрагент',
            'last_name' => 'Фамилия',
            'first_name' => 'Имя',
            'middle_name' => 'Отчество',
            'passport_date_issued' => 'Паспорт выдан',
            'passport_serial' => 'Паспорт серия',
            'passport_number' => 'Паспорт номер',
            'passport_issued' => 'Паспорт выдан',
            'registration_address' => 'Адрес прописки',
            'mother_maiden_name' => 'Имя матери',
            'birthplace' => 'Место рождения',
            'birthday' => 'День рождения',
            'other_document' => 'Другой документ',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::class,
        ];
    }

    public function getFullName(): ?string
    {
        $n = [];

        if ($this->last_name) {
            $n[] = $this->last_name;
        }

        if ($this->first_name) {
            $n[] = $this->first_name;
        }

        if ($this->middle_name) {
            $n[] = $this->middle_name;
        }

        return implode(' ', $n) ?: null;
    }

    public static function normalizeName(?string $name): ?string
    {
        if (!$name) {
            return $name;
        }

        $name = trim($name);

        if (!$name) {
            return $name;
        }

        $firstLetter = mb_substr($name, 0, 1);

        if ($firstLetter != mb_strtoupper($firstLetter)) {
            $firstLetter = mb_strtoupper($firstLetter);
        }

        if (mb_strlen($name) == 1) {
            return $firstLetter;
        }

        $woFirstLetter = mb_substr($name,  1);
        $twoLetter = mb_substr($woFirstLetter,  0, 1);


        if ($twoLetter != mb_strtolower($twoLetter)) {
            $woFirstLetter = mb_strtolower($woFirstLetter);
        }

        return $firstLetter.$woFirstLetter;
    }
}
