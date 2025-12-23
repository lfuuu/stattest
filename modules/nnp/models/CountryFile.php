<?php

namespace app\modules\nnp\models;

use app\classes\model\ActiveRecord;
use app\modules\nnp\media\CountryMedia;
use app\modules\nnp\media\ImportServiceUploaded;
use app\modules\nnp2\media\ImportServiceUploadedNew;
use app\modules\nnp2\models\ImportHistory;
use Yii;
use yii\base\InvalidParamException;

/**
 * @property int $id
 * @property int $country_code
 * @property string $name
 * @property string $comment
 * @property int $user_id
 * @property string $ts
 * @property int $is_active
 *
 * @property-read Country $country
 * @property-read CountryMedia $mediaManager
 *
 * @method static CountryFile findOne($condition)
 * @method static CountryFile[] findAll($condition)
 */
class CountryFile extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    /**
     * Имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'country_code' => 'Страна',
            'name' => 'Имя исходного файла',
            'comment' => 'Комментарий',
            'user_id' => 'Кто',
            'ts' => 'Когда',
            'is_active' => 'Активен',
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.country_files';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgNnp;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['code' => 'country_code']);
    }

    /**
     * @return CountryMedia
     */
    public function getMediaManager()
    {
        return new CountryMedia($this->country);
    }

    /**
     * Импортировать файл
     *
     * @param int $countryFileId
     * @param bool $old
     * @param bool $new
     * @return string Лог
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public static function importById($countryFileId, $old = true, $new = true)
    {
        $countryFile = CountryFile::findOne([
            'id' => $countryFileId,
            'is_active' => 1,
        ]);
        if (!$countryFile) {
            throw new InvalidParamException('Неправильный файл');
        }

        $country = $countryFile->country;
        $mediaManager = $country->getMediaManager();

        // import v1
        $logOld = '';
        $doneOld = true;
        if ($old) {
            $importHistoryOld = ImportHistory::startFile($countryFile);
            $importOld = new ImportServiceUploaded([
                'countryCode' => $country->code,
                'url' => $mediaManager->getUnzippedFilePath($countryFile),
                'delimiter' => ';',
            ]);
            $doneOld = $importOld->run($importHistoryOld);
            $importHistoryOld->finish($doneOld);
            $logOld = $importOld->getLogAsString();
        }
        //

        // import v2
        $log = '';
        $done = true;
        if ($new) {
            if ($logOld) {
                $logOld .= PHP_EOL . PHP_EOL . '******************************************' . PHP_EOL;
            }

            $importHistory = ImportHistory::startFile($countryFile, null, 2);
            $import = new ImportServiceUploadedNew([
                'countryCode' => $country->code,
                'url' => $mediaManager->getUnzippedFilePath($countryFile),
                'delimiter' => ';',
            ]);
            $done = $import->run($importHistory);
            $importHistory->finish($done);
            $log = $import->getLogAsString();
        }
        //

        $logFull = $logOld . $log;
        if (!$doneOld || !$done) {
            throw new \RuntimeException($logFull);
        }

        return [$logFull, $country->code];
    }

    /**
     * @return string
     */
    protected function getPreviewCacheKey()
    {
        return sprintf('nnp_import_preview_%s', $this->id);
    }

    protected function getPreviewEventCacheKey()
    {
        return sprintf('nnp_import_preview_event_%s', $this->id);
    }

    public function getCachedPreviewData()
    {
        /** @var yii\redis\Cache $redis */
        $redis = \Yii::$app->cache;

        return $redis->get($this->getPreviewCacheKey());
    }

    /**
     * @param string $data
     */
    public function setCachedPreviewData($data = '')
    {
        /** @var yii\redis\Cache $redis */
        $redis = \Yii::$app->cache;

        $redis->set($this->getPreviewCacheKey(), $data, 3600 * 24);
    }

    public function rememberPreviewEventId(?int $eventId): void
    {
        /** @var yii\redis\Cache $redis */
        $redis = \Yii::$app->cache;

        $redis->set($this->getPreviewEventCacheKey(), $eventId, 3600 * 24);
    }

    public function getCachedPreviewEventId(): ?int
    {
        /** @var yii\redis\Cache $redis */
        $redis = \Yii::$app->cache;

        $cached = $redis->get($this->getPreviewEventCacheKey());

        return $cached === false ? null : (int)$cached;
    }

    /**
     *
     */
    public function removeCachedPreviewData()
    {
        /** @var yii\redis\Cache $redis */
        $redis = \Yii::$app->cache;

        $redis->delete($this->getPreviewCacheKey());
        $redis->delete($this->getPreviewEventCacheKey());
    }

}