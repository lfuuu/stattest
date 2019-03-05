<?php

namespace app\models;

use app\classes\grid\ExportGridView;
use app\models\billing\CallsRaw;
use app\models\filter\CallsRawFilter;
use app\classes\model\ActiveRecord;
use app\classes\behaviors\CreatedAt;
use app\exceptions\ModelValidationException;
use Closure;
use DateTime;
use Exception;
use Yii;
use app\models\billing\Server;
use app\models\billing\Trunk;
use app\models\billing\Destination;
use app\models\billing\Geo;
use app\models\billing\DisconnectCause;
use app\modules\nnp\models\Operator;
use app\modules\nnp\models\City;


/**
 * @property integer $id
 * @property string $filename
 * @property string $params
 * @property integer $status
 * @property string status_text
 * @property integer $user_id
 * @property string $created_at
 * @property string $downloaded_at
 * @property string $tmp_files
 */
class DeferredTask extends ActiveRecord
{
    const STATUS_EXCEPTION = -4;
    const STATUS_SAVING_DOCUMENT_ERROR = -3;
    const STATUS_DOCUMENT_NOT_EXISTING = -2;
    const STATUS_EMPTY_DATA = -1;
    const STATUS_WAITING_FOR_DOWNLOAD = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_READY = 2;
    const STATUS_IN_REMOVING = 3;

    /**
     * @return array
     */
    public static function getStatusLabels()
    {
        return [
            static::STATUS_EXCEPTION => 'Exception',
            static::STATUS_SAVING_DOCUMENT_ERROR => 'Ошибка сохранения документа',
            static::STATUS_DOCUMENT_NOT_EXISTING => 'Excel: документ не существует',
            static::STATUS_EMPTY_DATA => 'Ничего не найдено',
            static::STATUS_WAITING_FOR_DOWNLOAD => 'Запланировано',
            static::STATUS_IN_PROGRESS => 'Выполняется',
            static::STATUS_READY => 'Завершено',
            static::STATUS_IN_REMOVING => 'В очереди на удаление'
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'deferred_task';
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status'=> 'Статус',
            'created_at'=> 'Дата создания',
            'params' => 'Параметры'
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['filename', 'params', 'status_text'], 'string'],
            [['status', 'user_id'], 'integer'],
            ['params', 'unique', 'on' => 'insert', 'message' => 'Уже есть отчет с такими параметрами.']
        ];
    }

    /**
     * Поведение модели
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'createdAt' => CreatedAt::class,
        ];
    }

    /**
     * @throws Exception
     */
    public function afterDelete()
    {
        parent::afterDelete();
        $path = ExportGridView::getPath();
        $filename = $this->filename;
        if ($filename && is_file($path . $filename) && is_writable($path . $filename)) {
            unlink($path . $filename);
        }
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->status == DeferredTask::STATUS_READY ||
                $this->status == DeferredTask::STATUS_IN_PROGRESS ||
                $this->status == DeferredTask::STATUS_WAITING_FOR_DOWNLOAD
            ) {
                $this->status_text = '';
            }
            return true;
        }
        return false;
    }

    /**
     * Связка с пользователем
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserModel()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @param array $bunch
     * @return mixed
     */
    public static function parseBunch($bunch)
    {
        static $serverList = null;
        if ($serverList === null) {
            $serverList = Server::getList(true);
        }
        static $superClientList = null;
        if ($superClientList === null) {
            $superClientList = UsageTrunk::getSuperClientList(true);
        }
        static $trunkList = null;
        if ($trunkList === null) {
            $trunkList = Trunk::dao()->getList([], true);
        }
        static $usageTrunks = null;
        if ($usageTrunks === null) {
            $usageTrunks = UsageTrunk::getList(null, true);
        }
        static $destinations = null;
        if ($destinations === null) {
            $destinations = Destination::getList(true);
        }
        static $geo = null;
        if ($geo === null) {
            $geo = Geo::getList(true);
        }
        static $disconnectCause = null;
        if ($disconnectCause === null) {
            $disconnectCause = DisconnectCause::getList(true);
        }
        static $countries = null;
        if ($countries === null) {
            $countries = Country::getList(true, 'prefix');
        }
        static $clientVersions = null;
        if ($clientVersions === null) {
            $clientVersions = ClientAccount::$versions;
        }
        static $operators = null;
        if ($operators === null) {
            $operators = Operator::getList(true);
        }
        static $regions = null;
        if ($regions === null) {
            $regions = Region::getList(true);
        }
        static $cities = null;
        if ($cities === null) {
            $cities = City::getList(true);
        }


        $bunch['server_id'] = (isset($serverList[$bunch['server_id']]))
            ? $serverList[$bunch['server_id']]
            : '';
        $bunch['trunk_ids'] = ($bunch['trunk_id'] && isset($superClientList[$bunch['trunk_id']]))
            ? $superClientList[$bunch['trunk_id']]
            : '';

        $bunch['trunk_id'] = isset($trunkList[$bunch['trunk_id']]) ? $trunkList[$bunch['trunk_id']]->name : '';

        $bunch['trunk_service_id'] = isset($usageTrunks[$bunch['trunk_service_id']]) ? $usageTrunks[$bunch['trunk_service_id']] : '';

        $bunch['destination_id'] = isset($destinations[$bunch['destination_id']]) ? $destinations[$bunch['destination_id']] : '';

        $bunch['geo_id'] = isset($geo[$bunch['geo_id']]) ? $geo[$bunch['geo_id']] : '';

        $bunch['orig'] = ($bunch['orig']) ? 'Оригинация' : 'Терминация';

        $bunch['mob'] = ($bunch['mob']) ? 'Мобильные' : 'Стационарные';

        $bunch['disconnect_cause'] = isset($disconnectCause[$bunch['disconnect_cause']]) ? $disconnectCause[$bunch['disconnect_cause']] : '';

        $bunch['nnp_country_prefix'] = isset($countries[$bunch['nnp_country_prefix']]) ? $countries[$bunch['nnp_country_prefix']] : '';

        $bunch['account_version'] = isset($clientVersions[$bunch['account_version']]) ? $clientVersions[$bunch['account_version']] : '';

        $bunch['nnp_operator_id'] = isset($operators[$bunch['nnp_operator_id']]) ? $operators[$bunch['nnp_operator_id']] : '';

        $bunch['nnp_region_id'] = isset($regions[$bunch['nnp_region_id']]) ? $regions[$bunch['nnp_region_id']] : '';

        $bunch['nnp_city_id'] = isset($cities[$bunch['nnp_city_id']]) ? $cities[$bunch['nnp_city_id']] : '';

        $bunch['stats_nnp_package_minute'] = $bunch['stats_nnp_package_minute_id'] . PHP_EOL .
            ($bunch['nnp_package_minute_id'] ? 'минуты' : '') .
            ($bunch['nnp_package_price_id'] ? 'прайс' : '') .
            ($bunch['nnp_package_pricelist_id'] ? 'прайслист' : '');

        return $bunch;
    }

    /**
     * @param int $status
     * @throws ModelValidationException
     */
    public function setStatus($status)
    {
        $this->status = $status;
        if (!$this->save()) {
            throw new ModelValidationException($this);
        }
    }

    /**
     * @param string $message
     * @throws ModelValidationException
     */
    public function setStatusText($message)
    {
        $this->status_text = $message;
        if (!$this->save()) {
            throw new ModelValidationException($this);
        }
    }

    /**
     * Получить строку состояния. Показывает сколько процентов уже скачалось.
     *
     * @return string
     */
    public function getProgressString()
    {
        $downloadedPartsAmount = $this->getDownloadedPartsAmount();
        $totalPartsAmount = $this->getTotalPartsAmount();
        if ($totalPartsAmount == 1) {
            return '';
        }

        return 'Выполнено ' . round($downloadedPartsAmount / $totalPartsAmount, 2) * 100 . '%';
    }

    /**
     * Получить количество скачанных частей
     *
     * @return int
     */
    public function getDownloadedPartsAmount()
    {
        return count(json_decode($this->tmp_files, true));
    }

    /**
     * Получить количество частей, из которых будет составлен отчет
     *
     * @return int
     */
    public function getTotalPartsAmount()
    {
        if (empty($params = json_decode($this->params, true))) {
            return 0;
        }

        $dateTimeFrom = new DateTime($params['connect_time_from']);
        $dateTimeTo = new DateTime($params['connect_time_to']);
        $amount = 1;

        if ($dateTimeFrom->diff($dateTimeTo)->m > 1) {
            $amount = $dateTimeFrom->diff($dateTimeTo)->m;
            $dateTimeFrom->diff($dateTimeTo)->d > 1 && ++$amount;
        }
        return $amount;
    }

    /**
     * @param string $tmpFiles
     * @throws ModelValidationException
     */
    public function setTmpFiles($tmpFiles)
    {
        $this->tmp_files = $tmpFiles;
        if (!$this->save()) {
            throw new ModelValidationException($this);
        }
    }
}
