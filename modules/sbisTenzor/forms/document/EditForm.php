<?php

namespace app\modules\sbisTenzor\forms\document;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\modules\sbisTenzor\classes\SBISDocumentStatus;
use app\modules\sbisTenzor\classes\SBISDocumentType;
use app\modules\sbisTenzor\helpers\SBISUtils;
use app\modules\sbisTenzor\models\SBISAttachment;
use app\modules\sbisTenzor\models\SBISDocument;
use app\modules\sbisTenzor\models\SBISOrganization;
use DateTime;
use DateTimeZone;
use Yii;
use yii\base\Exception;
use yii\web\UploadedFile;

class EditForm extends \app\classes\Form
{
    /** @var ClientAccount */
    protected $client;

    /** @var SBISDocument */
    protected $document;

    /** @var SBISOrganization */
    protected $sbisOrganization;

    /**
     * Edit form constructor
     *
     * @param ClientAccount|null $client
     * @throws \Exception
     */
    public function __construct(ClientAccount $client = null)
    {
        parent::__construct();

        $this->client = $client;

        $this->checkClient();
        $this->initDocument();
    }

    /**
     * Ссылка на страницу со списком документов
     *
     * @param int $clientId
     * @return string
     */
    public static function getIndexUrl($clientId)
    {
        return '/sbisTenzor/document/' . ($clientId ? '?clientId=' . $clientId : '');
    }

    /**
     * Инициализация нового документа
     *
     * @throws \Exception
     */
    protected function checkClient()
    {
        $client = $this->client;
        $this->sbisOrganization = SBISOrganization::findOne([
            'organization_id' => $client->organization->organization_id,
            'is_active' => true,
        ]);

        if (!$this->sbisOrganization) {
            throw new \Exception(
                sprintf('Обслуживающая данного клиента организация %s не настроена для работы со СБИС!', $client->organization->name)
            );
        }

        if (!$client->contragent->inn) {
            throw new \Exception('У контрагента данного клиента не заполнен ИНН!');
        }

        if (!$client->contragent->kpp) {
            throw new \Exception('У контрагента данного клиента не заполнен КПП!');
        }
    }

    /**
     * Инициализация нового документа
     *
     * @throws \Exception
     */
    protected function initDocument()
    {
        $now = new DateTime('now', new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));
        $document = new SBISDocument();

        $document->date = $now->format('Y-m-d');

        $document->sbis_organization_id = $this->sbisOrganization->id;
        $document->populateRelation('sbisOrganization', $this->sbisOrganization);

        $document->client_account_id = $this->client->id;
        $document->populateRelation('clientAccount', $this->client);

        $document->comment = 'Пакет документов для ' . $document->clientAccount->contragent->name_full;

        $this->document = $document;
    }

    /**
     * Получить документ
     *
     * @return SBISDocument
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Попробовать сохранить документ, если POST
     *
     * @return bool
     * @throws ModelValidationException
     */
    public function tryToSave()
    {
        if ($this->document->load(Yii::$app->request->post())) {
            return $this->saveDocument();
        }

        return false;
    }

    /**
     * Сохранение документа
     *
     * @return bool
     * @throws ModelValidationException
     * @throws \Exception
     */
    protected function saveDocument()
    {
        $document = $this->document;

        $dateNow = new DateTime('now', new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));

        $document->external_id = SBISUtils::generateUuid();
        $document->type = SBISDocumentType::SHIPPED_OUT;
        $document->state = SBISDocumentStatus::CREATED;
        $document->priority = 0;
        $document->tries = 0;
        $document->created_at = $dateNow->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $document->created_by = Yii::$app->user->getId();

        if (!$document->validate()) {
            throw new ModelValidationException($document);
        }

        $fileNames = $this->saveAttachments($document, $dateNow);

        if (!$document->save()) {
            $this->removeFiles($fileNames);

            throw new ModelValidationException($document);
        }

        return true;
    }

    /**
     * Сохранение вложений
     *
     * @param SBISDocument $document
     * @param DateTime $dateTime
     * @return array
     * @throws \Exception
     */
    protected function saveAttachments(SBISDocument $document, DateTime $dateTime)
    {
        $fileNames = [];
        $attachments = [];
        for ($i = 1; $i <= SBISDocument::MAX_ATTACHMENTS; $i++) {
            if ($file = UploadedFile::getInstance($document, 'filename[' . $i . ']')) {
                $attachment = new SBISAttachment();
                $attachment->populateRelation('document', $document);

                $attachment->external_id = SBISUtils::generateUuid();
                $attachment->number = $i;
                $attachment->file_name = $file->name;
                $attachment->is_sign_needed = $document->sbisOrganization->is_sign_needed;
                $attachment->is_signed = '0';
                $attachment->created_at = $dateTime->format(DateTimeZoneHelper::DATETIME_FORMAT);

                $fileName = $attachment->getStoredPath();
                if (!$file->saveAs($fileName, $deleteTempFile = true)) {
                    $this->removeFiles($fileNames);

                    throw new Exception(sprintf('Не удалось сохранить файл %s, путь %s', $attachment->file_name, $fileName));
                }
                $attachment->stored_path = $fileName;

                $fileNames[] = $fileName;
                $attachments[] = $attachment;
            }
        }

        $document->populateRelation('attachments', $attachments);
        return $fileNames;
    }

    /**
     * Удаляет временные файлы
     *
     * @param array $fileNames
     */
    protected function removeFiles(array $fileNames)
    {
        foreach ($fileNames as $fileName) {
            unlink($fileName);
        }
    }
}