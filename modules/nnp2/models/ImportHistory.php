<?php

namespace app\modules\nnp2\models;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\modules\nnp\models\Country;
use app\modules\nnp\models\CountryFile;
use app\modules\nnp2\Module;
use kartik\base\Config;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * @property int $id
 *
 * @property int $version
 * @property int $country_file_id
 *
 * @property int $lines_load
 * @property int $lines_processed
 *
 * @property int $ranges_before
 * @property int $ranges_updated
 * @property int $ranges_duplicates
 * @property int $ranges_new
 *
 * @property int $state
 *
 * @property string $started_at
 * @property string $finished_at
 *
 * @property-read CountryFile $countryFile
 * @property-read Country $country
 */
class ImportHistory extends ActiveRecord
{
    const STATE_NEW = 0;
    const STATE_READING = 1;
    const STATE_READ = 10;
    const STATE_PREPARING = 11;
    const STATE_PREPARED = 20;
    const STATE_RELATIONS_SAVING = 21;
    const STATE_RELATIONS_SAVED = 30;
    const STATE_GETTING_READY = 31;
    const STATE_READY = 40;
    const STATE_INSERTING = 41;
    const STATE_INSERTED = 50;
    const STATE_OLD_UPDATED = 60;
    const STATE_RELATIONS_CHECKED = 70;
    const STATE_NEW_ADDED = 80;
    const STATE_UPDATED_FIXED = 90;
    const STATE_FINISH = 100;
    const STATE_ERROR = 200;

    const AMOUNT_TO_LOG = 100000;

    protected static array $stateNames = [
        self::STATE_NEW => 'Новый',

        self::STATE_READING => 'Читается',
        self::STATE_READ => 'Прочитан',

        self::STATE_PREPARING => 'Подготавливается',
        self::STATE_PREPARED => 'Подготовлен',

        self::STATE_RELATIONS_SAVING => 'Связи сохраняются',
        self::STATE_RELATIONS_SAVED => 'Связи сохранены',

        self::STATE_GETTING_READY => 'Готовится',
        self::STATE_READY => 'Готов к записи',

        self::STATE_INSERTING => 'Наполнение (вр.табл.)',
        self::STATE_INSERTED => 'Наполнен (вр.табл.)',

        self::STATE_OLD_UPDATED => 'Обновлены старые',
        self::STATE_RELATIONS_CHECKED => 'Связи проверены',
        self::STATE_NEW_ADDED => 'Добавлены новые',
        self::STATE_UPDATED_FIXED => 'Обновленные поправлены',

        self::STATE_FINISH => 'Завершён',
        self::STATE_ERROR => 'Ошибка',
    ];

    protected static array $cachedProperties = [
        'state',
        'lines_load',
        'lines_processed',
    ];

    protected bool $removeCached = false;
    public bool $isLogMemory = false;

    /**
     * Имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',

            'version' => 'Версия импорта',
            'country_file_id' => 'Файл импорта',

            'lines_load' => 'Строк загружено',
            'lines_processed' => 'Строк обработано',

            'ranges_before' => 'Диапазонов было',
            'ranges_updated' => 'Диапазонов обновлено',
            'ranges_duplicates' => 'Дубликатов',
            'ranges_new' => 'Диапазонов новых',

            'state' => 'Статус',

            'started_at' => 'Запущен',
            'finished_at' => 'Завершён',
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'nnp2.import_history';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['version', 'country_file_id', 'lines_load', 'lines_processed', 'ranges_before', 'ranges_updated', 'ranges_duplicates', 'ranges_new', 'state'], 'integer'],
        ];
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgNnp2;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return self::getUrlById($this->id);
    }

    /**
     * @param int $id
     * @return string
     */
    public static function getUrlById($id)
    {
        return Url::to(['/nnp2/import-history/view', 'id' => $id]);
    }

    /**
     * @return ActiveQuery
     */
    public function getCountryFile()
    {
        return $this->hasOne(CountryFile::class, ['id' => 'country_file_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCountry()
    {
        return $this->countryFile->getCountry();
    }

    /**
     * @param string $property
     * @return string
     */
    protected function getCacheKey($property)
    {
        return sprintf('%s.%s.%s', __CLASS__, $this->id, $property);
    }

    /**
     * @return int
     */
    public function getState()
    {
        return $this->getCachedPropertyValue('state');
    }

    /**
     * @return int
     */
    public function getLinesLoad()
    {
        return $this->getCachedPropertyValue('lines_load');
    }

    /**
     * @return int
     */
    public function getLinesProcessed()
    {
        return $this->getCachedPropertyValue('lines_processed');
    }

    /**
     * @param string $property
     * @param bool $isToDelete
     */
    protected function setCachedPropertyValue($property, $isToDelete = false)
    {
        if (!$this->hasAttribute($property)) {
            return;
        }

        /** @var yii\redis\Cache $redis */
        $redis = \Yii::$app->cache;

        if ($isToDelete) {
            $redis->delete($this->getCacheKey($property));
            return;
        }

        if ($value = $this->{$property}) {
            $redis->set($this->getCacheKey($property), $value, 3600);
        }
    }

    /**
     * @param string $property
     * @return mixed
     */
    protected function getCachedPropertyValue($property)
    {
        if (!$this->hasAttribute($property)) {
            return '';
        }

        $value = $this->{$property};

        /** @var yii\redis\Cache $redis */
        $redis = \Yii::$app->cache;

        $cacheKey = $this->getCacheKey($property);
        if ($redis->exists($cacheKey)) {
            $value = $redis->get($cacheKey);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        foreach (self::$cachedProperties as $property) {
            $this->setCachedPropertyValue($property, $this->removeCached);
        }

        if ($this->removeCached && $this->countryFile) {
            // clear preview cache
            $this->countryFile->removeCachedPreviewData();
        }

        return parent::save($runValidation, $attributeNames);
    }

    /**
     * @return mixed|string
     */
    public function getStateName()
    {
        $state = $this->getState();

        $lastDigit = $state % 10;
        if ($lastDigit) {
            $state -= $lastDigit;

            if (isset(self::$stateNames[$state+1])) {
                $state = $state + 1;
            }
        }

        return self::$stateNames[$state] ?? '-';
    }

    /**
     * @param int $state
     * @return bool
     */
    public function tryToSetState($state)
    {
        if ($state != $this->state) {
            $this->state = $state;

            return true;
        }

        return false;
    }

    /**
     * @param CountryFile $countryFile
     * @param bool|null $isLogMemory
     * @param int|null $version
     * @return ImportHistory
     * @throws ModelValidationException
     * @throws \yii\base\InvalidConfigException
     */
    public static function startFile(CountryFile $countryFile, $isLogMemory = null, $version = null)
    {
        if (is_null($isLogMemory)) {
            $country = $countryFile->country;
            $mediaManager = $country->getMediaManager();
            if ($mediaManager->isSmall($countryFile)) {
                $isLogMemory = false;
            } else {
                /** @var Module $module */
                $module = Config::getModule('nnp2');
                $isLogMemory = $module->isLogMemory();
            }
        }

        $importHistory = new self([
            'isLogMemory' => $isLogMemory,
        ]);
        $importHistory->country_file_id = $countryFile->id;
        $importHistory->populateRelation('countryFile', $countryFile);
        if ($version) {
            $importHistory->version = $version;
        }

        $importHistory->state = self::STATE_NEW;

        $importHistory->started_at = (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW)))
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);

        if (!$importHistory->save()) {
            throw new ModelValidationException($importHistory);
        }

        return $importHistory;
    }

    /**
     * @param $i int
     * @param $total int
     * @param string $message
     */
    public function logProgress($i, $total, $message = '')
    {
        if (!$this->isLogMemory) {
            return;
        }

        $message .= sprintf("%s of %s", $i, $total);

        $this->logMemory($message);
    }

    /**
     * @param string $message
     */
    public function logMemory($message = '')
    {
        if (!$this->isLogMemory) {
            return;
        }

        echo date(DateTimeZoneHelper::DATETIME_FORMAT) . ', ';
        if ($message) {
            echo $message . '. ';
        }
        echo 'Memory: ' . sprintf(
                '%4.2f MB (%4.2f MB in peak)',
                memory_get_usage(true) / 1048576,
                memory_get_peak_usage(true) / 1048576
            ) . PHP_EOL;
    }

    /**
     * @param $i int
     * @param $total int
     * @return $this
     * @throws ModelValidationException
     */
    public function markReading($i = 1, $total = 1)
    {
        $diff = self::STATE_READ - self::STATE_NEW;
        $progress = intval(floor($i*$diff/$total));

        $state = self::STATE_NEW + $progress;
        if ($this->tryToSetState($state)) {
            if (!$this->save()) {
                throw new ModelValidationException($this);
            }
        }

        if(($i % self::AMOUNT_TO_LOG) == 0) {
            $this->logProgress($i, $total, 'Step 1. Reading: ');
        }

        return $this;
    }

    /**
     * @return $this
     * @throws ModelValidationException
     */
    public function markPrepared($i = 1, $total = 1)
    {
        $diff = self::STATE_PREPARED - self::STATE_READ;
        $progress = intval(floor($i*$diff/$total));

        $state = self::STATE_READ + $progress;
        if ($this->tryToSetState($state)) {
            if (!$this->save()) {
                throw new ModelValidationException($this);
            }
        }

        if(($i % self::AMOUNT_TO_LOG) == 0) {
            $this->logProgress($i, $total, 'Step 2. Preparing: ');
        }

        return $this;
    }

    /**
     * @return $this
     * @throws ModelValidationException
     */
    public function markRelations($i = 1, $total = 1)
    {
        $diff = self::STATE_RELATIONS_SAVED - self::STATE_PREPARED;
        $progress = intval(floor($i*$diff/$total));

        $state = self::STATE_PREPARED + $progress;
        if ($this->tryToSetState($state)) {
            if (!$this->save()) {
                throw new ModelValidationException($this);
            }
        }

        if(($i % self::AMOUNT_TO_LOG) == 0) {
            $this->logProgress($i, $total, 'Step 3. Checking relations: ');
        }

        return $this;
    }

    /**
     * @param $linesProcessed int
     * @param $i int
     * @param $total int
     * @return $this
     * @throws ModelValidationException
     */
    public function markGettingReady($linesProcessed, $i = 1, $total = 1)
    {
        $diff = self::STATE_READY - self::STATE_RELATIONS_SAVED;
        $progress = intval(floor($i*$diff/$total));

        $state = self::STATE_RELATIONS_SAVED + $progress;
        if ($this->tryToSetState($state)) {
            $this->lines_processed = $linesProcessed;

            if (!$this->save()) {
                throw new ModelValidationException($this);
            }
        }

        if(($i % self::AMOUNT_TO_LOG) == 0) {
            $this->logProgress($i, $total, 'Step 4. Getting ready: ');
        }

        return $this;
    }

    /**
     * @param $i int
     * @param $total int
     * @return $this
     * @throws ModelValidationException
     */
    public function markInserting($i = 1, $total = 1)
    {
        $diff = self::STATE_INSERTED - self::STATE_READY;
        $progress = intval(floor($i*$diff/$total));

        $state = self::STATE_READY + $progress;
        if ($this->tryToSetState($state)) {
            if (!$this->save()) {
                throw new ModelValidationException($this);
            }
        }

        if(($i % self::AMOUNT_TO_LOG) == 0) {
            $this->logProgress($i, $total, 'Step 5. Inserting: ');
        }

        return $this;
    }

    /**
     * @return $this
     * @throws ModelValidationException
     */
    public function markOldUpdated()
    {
        $this->tryToSetState(self::STATE_OLD_UPDATED);

        if (!$this->save()) {
            throw new ModelValidationException($this);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws ModelValidationException
     */
    public function markRelationsChecked()
    {
        $this->tryToSetState(self::STATE_RELATIONS_CHECKED);

        if (!$this->save()) {
            throw new ModelValidationException($this);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws ModelValidationException
     */
    public function markNewAdded()
    {
        $this->tryToSetState(self::STATE_NEW_ADDED);

        if (!$this->save()) {
            throw new ModelValidationException($this);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws ModelValidationException
     */
    public function markUpdatedFixed()
    {
        $this->tryToSetState(self::STATE_UPDATED_FIXED);

        if (!$this->save()) {
            throw new ModelValidationException($this);
        }

        return $this;
    }

    /**
     * @param bool $isOk
     * @return $this
     * @throws ModelValidationException
     */
    public function finish($isOk = false)
    {
        $this->tryToSetState($isOk ? self::STATE_FINISH : self::STATE_ERROR);

        $this->finished_at = (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW)))
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);

        $this->removeCached = true;

        if (!$this->save()) {
            throw new ModelValidationException($this);
        }

        return $this;
    }
}
