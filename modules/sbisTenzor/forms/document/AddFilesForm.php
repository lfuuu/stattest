<?php

namespace app\modules\sbisTenzor\forms\document;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\modules\sbisTenzor\classes\SBISDocumentManager\SBISFile;
use app\modules\sbisTenzor\classes\SBISDocumentStatus;
use app\modules\sbisTenzor\helpers\SBISUtils;
use app\modules\sbisTenzor\models\SBISAttachment;
use app\modules\sbisTenzor\models\SBISDocument;
use DateTime;
use DateTimeZone;
use Exception;
use Yii;
use yii\web\UploadedFile;

class AddFilesForm extends \app\classes\Form
{
    /** @var SBISDocument */
    protected $document;

    /**
     * Add files form constructor
     *
     * @param SBISDocument $document
     * @throws Exception
     */
    public function __construct(SBISDocument $document)
    {
        parent::__construct();

        $this->document = $document;
        $this->checkDocument();
    }

    /**
     * Проверка пакета документов
     *
     * @throws \Exception
     */
    protected function checkDocument()
    {
        if ( !$this->document->isJustCreated() ) {
            throw new \Exception(
                sprintf(
                    'Пакет документов в неподходящем статусе: %s (%s)',
                    SBISDocumentStatus::getById($this->document->state),
                    $this->document->state
                )
            );
        }
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
        if (Yii::$app->request->isPost) {
            return $this->saveAttachments();
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
    protected function saveAttachments()
    {
        $document = $this->document;

        $files = [];
        for ($i = 1; $i <= SBISDocument::MAX_ATTACHMENTS; $i++) {
            if ($file = UploadedFile::getInstance($document, 'filename[' . $i . ']')) {
                $files[] = new SBISFile($file);
            }
        }

        return $this->addFiles($files);
    }

    /**
     * Добавляет вложения в пакет
     *
     * @param SBISFile[] $files
     * @return true
     * @throws Exception
     */
    protected function addFiles(array $files)
    {
        $document = $this->document;

        $fileNames = [];
        $attachments = $document->attachments;

        $attachmentsPath = '';
        if ($existed = count($document->attachments)) {
            $firstAttachment = current($document->attachments);
            $attachmentsPath = dirname($firstAttachment->stored_path) . DIRECTORY_SEPARATOR;
        }

        $i = $existed;
        foreach ($files as $file) {
            $attachment = new SBISAttachment();
            $attachment->populateRelation('document', $document);

            $attachment->sbis_document_id = $document->id;
            $attachment->external_id = SBISUtils::generateUuid();
            $attachment->number = ++$i;
            $attachment->file_name = $file->getFileName();
            $attachment->is_sign_needed = $document->sbisOrganization->is_sign_needed;
            $attachment->is_signed = '0';

            if ($attachmentsPath) {
                $fileName = $attachmentsPath . $attachment->generateFileName();
            } else {
                $fileName = $attachment->getFullStoredPath($file->getOfficialFileName(), count($files));
            }
            if (!$file->saveAs($fileName)) {
                $this->removeFiles($fileNames);

                throw new Exception(sprintf('Не удалось сохранить файл %s, путь %s', $attachment->file_name, $fileName));
            }
            $attachment->stored_path = $fileName;

            $fileNames[] = $fileName;
            $attachments[$i] = $attachment;
        }

        $document->updated_at = new DateTime('now', new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));
        $document->populateRelation('attachments', $attachments);

        if (!$document->save()) {
            $this->removeFiles($fileNames);

            throw new ModelValidationException($document);
        }

        return true;
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