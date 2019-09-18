<?php

namespace app\modules\sbisTenzor\models;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
use app\models\Organization;
use app\models\User;
use app\modules\sbisTenzor\classes\SBISDocumentStatus;
use app\modules\sbisTenzor\classes\SBISDocumentType;
use app\modules\sbisTenzor\classes\SBISTensorAPI\SBISDocumentInfo;
use DateTime;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\Url;

/**
 * Пакет документов в системе СБИС
 *
 * @property integer $id
 * @property string $external_id
 * @property integer $sbis_organization_id
 * @property integer $client_account_id
 * @property string $date
 * @property string $number
 * @property string $comment
 * @property integer $type
 * @property integer $state
 * @property integer $external_state
 * @property string $external_state_name
 * @property string $external_state_description
 * @property integer $flags
 * @property string $last_event_id
 * @property string $url_our
 * @property string $url_external
 * @property string $url_pdf
 * @property string $url_archive
 * @property integer $error_code
 * @property string $errors
 * @property integer $priority
 * @property integer $tries
 * @property string $created_at
 * @property string $updated_at
 * @property string $started_at
 * @property string $saved_at
 * @property string $prepared_at
 * @property string $signed_at
 * @property string $sent_at
 * @property string $last_fetched_at
 * @property string $read_at
 * @property string $completed_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property-read string $typeName
 * @property-read string $externalStateName
 * @property-read string $stateName
 * @property-read SBISOrganization $sbisOrganization
 * @property-read Organization $organizationTo
 * @property-read ClientAccount $clientAccount
 * @property-read SBISAttachment[] $attachments
 * @property-read User $createdBy
 * @property-read User $updatedBy
 */
class SBISDocument extends ActiveRecord
{
    const MAX_ATTACHMENTS = 5;
    const MAX_TRIES = 3;
    const LOG_CATEGORY = 'sbis';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sbis_document';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sbis_organization_id', 'client_account_id', 'type', 'state', 'priority', 'tries', 'created_at', 'created_by'], 'required'],
            [['sbis_organization_id', 'client_account_id', 'type', 'state', 'external_state', 'flags', 'error_code', 'priority', 'tries', 'updated_by'], 'integer'],
            [['date', 'external_state_name', 'external_state_description', 'created_at', 'updated_at', 'started_at', 'saved_at', 'prepared_at', 'signed_at', 'sent_at', 'last_fetched_at', 'read_at', 'completed_at'], 'safe'],
            [['errors'], 'string'],
            [['external_id', 'last_event_id'], 'string', 'max' => 36],
            [['number', 'external_state_name'], 'string', 'max' => 64],
            [['comment', 'external_state_description'], 'string', 'max' => 255],
            [['url_our', 'url_external'], 'string', 'max' => 100],
            [['url_pdf'], 'string', 'max' => 1500],
            [['url_archive'], 'string', 'max' => 350],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        return Yii::$app->db;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'external_id' => 'UUID документа в СБИС',
            'sbis_organization_id' => 'Организация-отправитель в СБИС',
            'client_account_id' => 'Клиент-получатель',
            'date' => 'Дата пакета документов',
            'number' => 'Номер пакета документов',
            'comment' => 'Примечание к пакету документов',
            'type' => 'Тип документа',
            'state' => 'Статус',
            'external_state' => 'ID состояния в СБИС',
            'external_state_name' => 'Название состояния в СБИС',
            'external_state_description' => 'Описание состояния в СБИС',
            'flags' => 'Набор флагов состояния объекта',
            'last_event_id' => 'Last event ID',
            'url_our' => 'Ссылка для нашей организации',
            'url_external' => 'Ссылка для контрагента',
            'url_pdf' => 'Ссылка на pdf',
            'url_archive' => 'Ссылка на архив',
            'error_code' => 'Код ошибки',
            'errors' => 'Errors',
            'priority' => 'Приоритет',
            'tries' => 'Кол-во попыток',
            'created_at' => 'Создан',
            'updated_at' => 'Изменён',
            'started_at' => 'Запущен',
            'saved_at' => 'Сохранён',
            'prepared_at' => 'Подготовлен',
            'signed_at' => 'Подписан',
            'sent_at' => 'Отправлен',
            'last_fetched_at' => 'Опрошен',
            'read_at' => 'Прочитан',
            'completed_at' => 'Подтверждён',
            'created_by' => 'Кем создан',
            'updated_by' => 'Кем обновлён',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                // Установить "когда создал" и "когда обновил"
                'class' => TimestampBehavior::class,
                'value' => new Expression("UTC_TIMESTAMP()"), // "NOW() AT TIME ZONE 'utc'" (PostgreSQL) или 'UTC_TIMESTAMP()' (MySQL)
            ],
            [
                // Установить "кто создал" и "кто обновил"
                'class' => AttributeBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_by', 'updated_by'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_by',
                ],
                'value' => Yii::$app->user->getId(),
            ],
        ];
    }

    /**
     * Максимальное число попыток
     *
     * @return int
     */
    public static function getMaxTries()
    {
        return self::MAX_TRIES;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSbisOrganization()
    {
        return $this->hasOne(SBISOrganization::class, ['id' => 'sbis_organization_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::class, ['id' => 'client_account_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttachments()
    {
        return $this->hasMany(SBISAttachment::class, ['sbis_document_id' => 'id'])
            ->inverseOf('document')
            ->indexBy('number');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * @return string
     */
    public function getTypeName()
    {
        return SBISDocumentType::getById($this->type);
    }

    /**
     * @return string
     */
    public function getStateName()
    {
        return SBISDocumentStatus::getById($this->state);
    }

    /**
     * @return string
     */
    public function getExternalStateName()
    {
        return SBISDocumentStatus::getExternalById($this->external_state);
    }

    /**
     * @param SBISAttachment[] $attachments
     * @return SBISDocument
     */
    public function setAttachments(array $attachments)
    {
        $this->populateRelation('attachments', $attachments);
        return $this;
    }

    /**
     * @param int $state
     * @param DateTime|null $dateNow
     * @return SBISDocument
     * @throws \Exception
     */
    public function setState($state, $dateNow = null)
    {
        if ($this->state != $state) {
            if (is_null($dateNow)) {
                $dateNow = new DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));
            }
            $dateNowString = $dateNow->format(DateTimeZoneHelper::DATETIME_FORMAT);

            switch ($state) {
                case SBISDocumentStatus::PROCESSING:
                    $this->started_at = $dateNowString;
                    if ($this->isSigned()) {
                        $state = SBISDocumentStatus::SIGNED;
                        $this->signed_at = $dateNowString;
                    }
                    break;

                case SBISDocumentStatus::SIGNED:
                    $this->signed_at = $dateNowString;
                    break;

                case SBISDocumentStatus::SAVED:
                    $this->saved_at = $dateNowString;
                    break;

                case SBISDocumentStatus::NOT_SIGNED:
                    $this->prepared_at = $dateNowString;
                    if ($this->isSigned()) {
                        $state = SBISDocumentStatus::READY;
                    }
                    break;

                case SBISDocumentStatus::READY:
                    break;

                case SBISDocumentStatus::SENT:
                    if ($this->state == SBISDocumentStatus::NOT_SIGNED) {
                        // подписан при отправке
                        $this->signed_at = $dateNowString;
                    }
                    $this->sent_at = $dateNowString;
                    break;

                case SBISDocumentStatus::DELIVERED:
                    $this->read_at = $dateNowString;
                    break;

                case SBISDocumentStatus::ACCEPTED:
                    if (!$this->read_at) {
                        $this->read_at = $dateNowString;
                    }
                    $this->completed_at = $dateNowString;
                    break;
            }

            $oldState = $this->state;
            // change state
            $this->state = $state;

            $this->raiseEvents($state);
            Yii::info(sprintf('SBISDocument #%s, %s, state changed: %s -> %s', $this->id, $this->external_id, $oldState, $state), self::LOG_CATEGORY);
        }

        return $this;
    }

    /**
     * Параметры для события
     *
     * @return array
     */
    public function getInfo()
    {
        return [
            'client_id' => $this->client_account_id,
            'doc_from' => strval($this->sbisOrganization->organization->name),
            'doc_id' => $this->id,
            'doc_external_id' => $this->external_id,
            'doc_number' => $this->number,
            'doc_date' => $this->date,
            'doc_comment' => $this->comment,
            'doc_files' => implode(
                ', ',
                array_map(function (SBISAttachment $attachment) {
                    return $attachment->file_name;
                }, $this->attachments)
            ),
            'doc_type' => $this->getTypeName(),
            'doc_url' => $this->getUrl(),
            'doc_state_name' => $this->external_state_name,
            'doc_state_description' => $this->external_state_description,
        ];
    }

    /**
     * Создать событие
     *
     * @param int $state новый статус
     * @throws \yii\db\Exception
     */
    protected function raiseEvents($state)
    {
        switch ($state) {
            case SBISDocumentStatus::SENT:
                ImportantEvents::create(
                    ImportantEventsNames::SBIS_DOCUMENT_SENT,
                ImportantEventsSources::SOURCE_STAT,
                    $this->getInfo()
                );
                break;

            case SBISDocumentStatus::ACCEPTED:
                ImportantEvents::create(
                    ImportantEventsNames::SBIS_DOCUMENT_ACCEPTED,
                    ImportantEventsSources::SOURCE_STAT,
                    $this->getInfo()
                );
                break;

            default:
                if (
                    ($state > SBISDocumentStatus::SENT) &&
                    ($state != SBISDocumentStatus::DELIVERED)
                ) {
                    ImportantEvents::create(
                        ImportantEventsNames::SBIS_DOCUMENT_EVENT,
                        ImportantEventsSources::SOURCE_STAT,
                        $this->getInfo()
                    );
                    break;
                }
        }
    }

    /**
     * Возвращает цепочку пройденных статусов
     *
     * @return array
     */
    public function getStatusesChain()
    {
        $chain = [];

        $chain[] = [
            'name' => SBISDocumentStatus::getById(SBISDocumentStatus::CREATED),
            'passed' => true,
        ];

        if ($this->state == SBISDocumentStatus::CANCELLED) {
            $chain[] = [
                'name' => SBISDocumentStatus::getById(SBISDocumentStatus::CANCELLED),
                'passed' => true,
            ];

            return $chain;
        }

        $chain[] = [
            'name' => SBISDocumentStatus::getById(SBISDocumentStatus::PROCESSING),
            'passed' => !empty($this->started_at),
        ];

        $chain[] = [
            'name' => SBISDocumentStatus::getById(SBISDocumentStatus::SIGNED),
            'passed' => !empty($this->signed_at),
        ];

        $chain[] = [
            'name' => SBISDocumentStatus::getById(SBISDocumentStatus::SAVED),
            'passed' => !empty($this->saved_at),
        ];

        $chain[] = [
            'name' => SBISDocumentStatus::getById(SBISDocumentStatus::READY),
            'passed' => !empty($this->prepared_at),
        ];

        $chain[] = [
            'name' => SBISDocumentStatus::getById(SBISDocumentStatus::SENT),
            'passed' => !empty($this->sent_at),
        ];

        $chain[] = [
            'name' => SBISDocumentStatus::getById(SBISDocumentStatus::DELIVERED),
            'passed' => !empty($this->read_at),
        ];

        if (
            ($this->state > SBISDocumentStatus::DELIVERED) &&
            ($this->state != SBISDocumentStatus::ACCEPTED)
        ) {
            $chain[] = [
                'name' => $this->getStateName(),
                'passed' => true,
            ];
        }

        $chain[] = [
            'name' => SBISDocumentStatus::getById(SBISDocumentStatus::ACCEPTED),
            'passed' => !empty($this->completed_at),
        ];

        return $chain;
    }

    /**
     * @param string $insert
     * @param array $changedAttributes
     * @throws ModelValidationException
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            // создание
            $i = 0;
            foreach ($this->attachments as $attachment) {
                // задаём вложениям индекс id пакета
                $attachment->number = ++$i;
                $attachment->sbis_document_id = $this->id;
                $attachment->populateRelation('document', null);
            }
        }

        foreach($this->attachments as $attachment) {
            if (!$attachment->validate() || !$attachment->save()) {
                throw new ModelValidationException($attachment);
            }
        }
    }

    /**
     * @return bool
     */
    public function isSigned()
    {
        if (!$this->sbisOrganization->is_sign_needed) {
            return true;
        }

        $isSigned = true;
        foreach($this->attachments as $attachment) {
            if (!$attachment->is_signed) {
                $isSigned = false;
                break;
            }
        }

        return $isSigned;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Url::to(['/sbisTenzor/document/view', 'id' => $this->id]);
    }

    /**
     * Обновляет данные модели из объекта Документ
     *
     * @param SBISDocumentInfo $documentInfo
     * @return bool if state changed
     */
    public function applyDocumentInfo(SBISDocumentInfo $documentInfo)
    {
        if ($this->external_id != $documentInfo->externalId) {
            return false;
        }

        // links
        if (!is_null($documentInfo->urlExternal)) {
            $this->url_external = $documentInfo->urlExternal;
        }
        if (!is_null($documentInfo->urlOur)) {
            $this->url_our = $documentInfo->urlOur;
        }
        if (!is_null($documentInfo->urlPDF)) {
            $this->url_pdf = $documentInfo->urlPDF;
        }
        if (!is_null($documentInfo->urlArchive)) {
            $this->url_archive = $documentInfo->urlArchive;
        }

        // state info
        if (!is_null($documentInfo->externalStateName)) {
            $this->external_state_name = $documentInfo->externalStateName;
        }
        if (!is_null($documentInfo->externalStateDescription)) {
            $this->external_state_description = $documentInfo->externalStateDescription;
        }
        if (!is_null($documentInfo->lastEventId)) {
            $this->last_event_id = $documentInfo->lastEventId;
        }

        // check if external state changed
        $externalStateChanged = false;
        if (!is_null($documentInfo->externalState) && $this->external_state != $documentInfo->externalState) {
            // внешний статус сменился
            $this->external_state = $documentInfo->externalState;
            $externalStateChanged = true;
        }

        return $externalStateChanged;
    }
}