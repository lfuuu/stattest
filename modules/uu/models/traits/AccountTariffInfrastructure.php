<?php

namespace app\modules\uu\models\traits;

use app\classes\traits\GetListTrait;

trait AccountTariffInfrastructure
{
    public static $infrastructureProjectMgmn = 1; // МГМН
    public static $infrastructureProjectMus = 2; // МУС (местный узел связи)
    public static $infrastructureProjectBi = 3; // БИ

    public static $infrastructureLevelLocal = 1; // Местный
    public static $infrastructureLevelZone = 2; // Зоновый
    public static $infrastructureLevelMg = 3; // МГ
    public static $infrastructureLevelMn = 4; // МН

    /**
     * Вернуть список всех доступных значений Проекта
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @return string[]
     */
    public static function getInfrastructureProjectList($isWithEmpty = false, $isWithNullAndNotNull = false)
    {
        $list = [
            self::$infrastructureProjectMgmn => 'МГМН',
            self::$infrastructureProjectMus => 'МУС',
            self::$infrastructureProjectBi => 'БИ',
        ];

        return GetListTrait::getEmptyList($isWithEmpty, $isWithNullAndNotNull) + $list;
    }

    /**
     * Вернуть список всех доступных значений Уровня
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @return string[]
     */
    public static function getInfrastructureLevelList($isWithEmpty = false, $isWithNullAndNotNull = false)
    {
        $list = [
            self::$infrastructureLevelLocal => 'Местный',
            self::$infrastructureLevelZone => 'Зоновый',
            self::$infrastructureLevelMg => 'МГ',
            self::$infrastructureLevelMn => 'МН',
        ];

        return GetListTrait::getEmptyList($isWithEmpty, $isWithNullAndNotNull) + $list;
    }

    /**
     * @return string
     */
    public function getProjectName()
    {
        if (!$this->infrastructure_project) {
            return '';
        }

        $list = self::getInfrastructureProjectList();
        return $list[$this->infrastructure_project];
    }

    /**
     * @return string
     */
    public function getLevelName()
    {
        if (!$this->infrastructure_level) {
            return '';
        }

        $list = self::getInfrastructureLevelList();
        return $list[$this->infrastructure_level];
    }
}