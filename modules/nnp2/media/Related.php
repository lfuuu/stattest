<?php

namespace app\modules\nnp2\media;

use app\classes\Connection;
use app\modules\nnp2\media\related\Transformer;
use app\modules\nnp2\models\Region;

abstract class Related
{
    const CHUNK_SIZE_INSERT = 5000;
    const CHUNK_SIZE_LOAD = 5000;

    public $countryCode;

    /** @var Connection */
    protected $db = null;

    // name=>id
    protected $list = [];
    // name
    protected $toAdd = [];

    /**
     * @var Transformer
     */
    protected $transformer;

    /**
     * Related constructor.
     * @param Connection $db
     * @param int $countryCode
     */
    public function __construct($db, $countryCode)
    {
        $this->db = $db;
        $this->countryCode = $countryCode;

        $this->list = array_flip(
            Region::getList($isWithEmpty = false,
                $isWithNullAndNotNull = false,
                $countryCode,
                $isMainOnly = false)
        );

        $this->init();
    }

    /**
     * @return mixed
     */
    protected abstract function init();

    /**
     * Проверить, что значение является натуральным числом
     *
     * @param string|int|null $value Пустое привести к null, непустое к int
     * @param bool $isEmptyAllowed Что возвращать для пустых
     * @param bool $isConvertToInt
     * @return bool
     */
    protected function checkNatural(&$value, $isEmptyAllowed, $isConvertToInt = true)
    {
        $value = trim($value);
        if (!$value) {
            $value = null;
            return $isEmptyAllowed;
        }

        if (!preg_match('/^\d+$/', $value)) {
            return false;
        }

        if ($isConvertToInt) {
            $value = (int)$value;
        }

        return true;
    }

    /**
     * Проверить, что значение является строкой. Можно пустой
     *
     * @param string $value
     * @return bool
     */
    protected function checkString(&$value)
    {
        $value = trim($value);
        return $value === '' || !is_numeric($value);
    }

    /**
     * @return array
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @param $value
     * @return string
     */
    public function prepareValue($value)
    {
        if (!$this->transformer) {
            return $value;
        }

        return $this->transformer->transformValue($value);
    }

    /**
     * @param array $data
     * @param int $level
     * @param int $i
     * @return int
     */
    public function countRecursive(array $data, $level = 1, $i = 0)
    {
        $level = max(0, $level - 1);
        foreach ($data as $value) {
            if($level && is_array($value)){
                $i += $this->countRecursive($value, $level);
            } else {
                $i++;
            }
        }

        return $i;
    }

    /**
     * @throws \yii\db\Exception
     */
    public function addNew()
    {
        if ($this->toAdd) {
            $this->processInsertValues($this->prepareInsertValues());

            $this->loadNew();

            $this->toAdd = [];
        }
    }

    /**
     * @param $insertValues
     * @throws \yii\db\Exception
     */
    protected function processInsertValues($insertValues)
    {
        if (!count($insertValues)) {
            return;
        }

        foreach (array_chunk($insertValues, static::CHUNK_SIZE_INSERT) as $chunk) {
            $this->batchInsertValues($chunk);
        }
    }

    /**
     * @return array
     */
    abstract protected function prepareInsertValues();

    /**
     * @param $batchInsertValues
     * @param string $logComment
     * @throws \yii\db\Exception
     */
    abstract protected function batchInsertValues($batchInsertValues);

    /**
     * @param array|null $listToLoad
     */
    abstract public function loadNew($listToLoad = null);
}