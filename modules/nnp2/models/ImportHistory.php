<?php

namespace app\modules\nnp2\models;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\modules\nnp\models\Country;
use app\modules\nnp\models\CountryFile;
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
 * @property int $ranges_added
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
    const STATE_READ = 10;
    const STATE_PREPARED = 20;
    const STATE_RELATIONS_SAVED = 30;
    const STATE_READY = 40;
    const STATE_OLD_UPDATED = 50;
    const STATE_RELATIONS_CHECKED = 60;
    const STATE_NEW_ADDED = 70;
    const STATE_UPDATED_FIXED = 80;
    const STATE_FINISH = 100;
    const STATE_ERROR = 200;

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
            'ranges_added' => 'Диапазонов добавлено',

            'state' => 'Статус',

            'started_at' => 'Запущен',
            'finished_at' => 'Завершён',
        ];
    }

    protected static array $stateNames = [
        self::STATE_NEW => 'Новый',
        self::STATE_READ => 'Прочитан',
        self::STATE_PREPARED => 'Подготовлен',
        self::STATE_RELATIONS_SAVED => 'Связи сохранены',
        self::STATE_READY => 'Готов к записи',

        self::STATE_OLD_UPDATED => 'Обнрвлены старые',
        self::STATE_RELATIONS_CHECKED => 'Связи проверены',
        self::STATE_NEW_ADDED => 'Добавлены новые',
        self::STATE_UPDATED_FIXED => 'Обновленные поправлены',

        self::STATE_FINISH => 'Завершён',
        self::STATE_ERROR => 'Ошибка',
    ];

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
            [['version', 'country_file_id', 'lines_load', 'lines_processed', 'ranges_before', 'ranges_updated', 'ranges_added', 'state'], 'integer'],
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

    public function getCacheKey()
    {
        return sprintf('%s.%s.state', __CLASS__, $this->id);
    }

    public function getStateName()
    {
        $state = $this->state;

        /** @var yii\redis\Cache $redis */
        $redis = \Yii::$app->cache;

        $cacheKey = $this->getCacheKey();
        if ($redis->exists($cacheKey)) {
            $state = $redis->get($cacheKey);
        }

        return self::$stateNames[$state] ?? '-';
    }

    /**
     * @param int $state
     */
    public function setState($state)
    {
        /** @var yii\redis\Cache $redis */
        $redis = \Yii::$app->cache;

        $redis->set($this->getCacheKey(), $state);

        $this->state = $state;
    }

    /**
     * @param CountryFile $countryFile
     * @param int|null $version
     * @return ImportHistory
     * @throws ModelValidationException
     */
    public static function startFile(CountryFile $countryFile, $version = null)
    {
        $importHistory = new self();
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

    public function markRead()
    {
        $this->setState(self::STATE_READ);

        if (!$this->save()) {
            throw new ModelValidationException($this);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws ModelValidationException
     */
    public function markPrepared()
    {
        $this->setState(self::STATE_PREPARED);

        if (!$this->save()) {
            throw new ModelValidationException($this);
        }

        return $this;
    }


    public function markRelations()
    {
        $this->setState(self::STATE_RELATIONS_SAVED);

        if (!$this->save()) {
            throw new ModelValidationException($this);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws ModelValidationException
     */
    public function markReady()
    {
        $this->setState(self::STATE_READY);

        if (!$this->save()) {
            throw new ModelValidationException($this);
        }

        return $this;
    }

    /**
     * @param $i int
     * @param $total int
     * @return $this
     * @throws ModelValidationException
     */
    public function markInserted($i, $total)
    {
        $progress = intval(floor($i*10/$total));

        $state = self::STATE_READY + $progress;
        $this->setState($state);

        if (!$this->save()) {
            throw new ModelValidationException($this);
        }

        return $this;
    }

    public function markOldUpdated()
    {
        $this->setState(self::STATE_OLD_UPDATED);

        if (!$this->save()) {
            throw new ModelValidationException($this);
        }

        return $this;
    }

    public function markRelationsChecked()
    {
        $this->setState(self::STATE_RELATIONS_CHECKED);

        if (!$this->save()) {
            throw new ModelValidationException($this);
        }

        return $this;
    }

    public function markNewAdded()
    {
        $this->setState(self::STATE_NEW_ADDED);

        if (!$this->save()) {
            throw new ModelValidationException($this);
        }

        return $this;
    }

    public function markUpdatedFixed()
    {
        $this->setState(self::STATE_UPDATED_FIXED);

        if (!$this->save()) {
            throw new ModelValidationException($this);
        }

        return $this;
    }

    public function finish($isOk = false)
    {
        $this->setState($isOk ? self::STATE_FINISH : self::STATE_ERROR);

        $this->finished_at = (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW)))
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);

        /** @var yii\redis\Cache $redis */
        $redis = \Yii::$app->cache;
        $redis->delete($this->getCacheKey());

        if (!$this->save()) {
            throw new ModelValidationException($this);
        }

        return $this;
    }
}
