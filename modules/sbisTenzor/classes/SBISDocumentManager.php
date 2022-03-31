<?php

namespace app\modules\sbisTenzor\classes;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\Invoice;
use app\modules\sbisTenzor\classes\SBISDocumentManager\SBISFile;
use app\modules\sbisTenzor\helpers\SBISDataProvider;
use app\modules\sbisTenzor\helpers\SBISInfo;
use app\modules\sbisTenzor\helpers\SBISUtils;
use app\modules\sbisTenzor\models\SBISAttachment;
use app\modules\sbisTenzor\models\SBISDocument;
use app\modules\sbisTenzor\models\SBISOrganization;
use DateTime;
use DateTimeZone;
use Exception;
use yii\base\InvalidConfigException;
use yii\base\InvalidValueException;

class SBISDocumentManager
{
    /** @var ClientAccount */
    protected $client;

    /** @var Invoice */
    protected $invoice;

    /** @var SBISDocument */
    protected $document;

    /** @var SBISOrganization */
    protected $sbisOrganization;
    /** @var SBISFile[] */
    protected $files = [];
    protected $attachmentsCount = 0;

    /**
     * Document manager constructor
     *
     * @param ClientAccount $client
     * @param Invoice|null $invoice
     * @throws Exception
     */
    public function __construct(ClientAccount $client, Invoice $invoice = null)
    {
        $this->client = $client;
        $this->invoice = $invoice;

        $this->checkClient();
        $this->initDocument();
        if ($this->invoice) {
            $this->loadInvoiceData();
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
     * @param SBISFile[] $files
     * @return SBISDocumentManager
     */
    public function setFiles($files)
    {
        $this->files = $files;
        $this->attachmentsCount = count($files);

        return $this;
    }

    /**
     * Проверка клиента
     *
     * @throws \Exception
     */
    protected function checkClient()
    {
        $organization = $this->invoice ? $this->invoice->organization : null;

        $contractorInfo = ContractorInfo::get($this->client, $organization);
        if ($error = $contractorInfo->getFullErrorText()) {
            throw new \Exception($error);
        }

        $this->sbisOrganization = SBISDataProvider::getSBISOrganizationByClient($this->client, $organization);
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
        $document->state = SBISDocumentStatus::CREATED;
        $document->addCreateEvent();

        $document->sbis_organization_id = $this->sbisOrganization->id;
        $document->populateRelation('sbisOrganization', $this->sbisOrganization);

        $document->client_account_id = $this->client->id;
        $document->populateRelation('clientAccount', $this->client);

        $document->comment = 'Пакет документов для ' . $document->clientAccount->contragent->name;

        $this->document = $document;
    }

    /**
     * Подстановка данных из POST
     *
     * @param array $post
     * @return bool
     */
    public function loadData(array $post)
    {
        return $this->document->load($post);
    }

    /**
     * Подстановка данных из закрывающего документа
     *
     * @throws Exception
     */
    protected function loadInvoiceData()
    {
        if (!$this->client->exchange_group_id) {
            throw new InvalidConfigException('Группа обмена не указана!');
        }

        $invoice = $this->invoice;

        $this->document->number = $invoice->number;
        $invoiceDate = new DateTime($invoice->getInitialDate(), new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));
        $this->document->date = $invoiceDate->format('Y-m-d');
        $this->document->comment = 'Пакет закрывающих документов для ' . $this->client->contragent->name;

        foreach ($this->client->exchangeGroup->getExchangeFiles() as $exchangeFile) {
            if ($exchangeFile->isXML()) {
                // check if valid
                $xmlFile = XmlGenerator::createXmlGenerator($exchangeFile->form, $invoice);
                if ($errorText = $xmlFile->getErrorText()) {
                    throw new InvalidValueException($errorText);
                }

                $files[] = new SBISFile(null, $xmlFile);
            }

            if ($exchangeFile->isPdf()) {
                $files[] = new SBISFile(null, null, $invoice->getFilePath($exchangeFile->form->type));
            }
        }
        $this->setFiles($files);

        $this->document->state = SBISDocumentStatus::CREATED_AUTO;
    }

    /**
     * Сохранение документа
     *
     * @return bool
     * @throws ModelValidationException
     * @throws \Exception
     */
    public function saveDocument()
    {
        if (empty($this->files)) {
            throw new InvalidConfigException('Список файлов не указан!');
        }

        $document = $this->document;

        $document->external_id = SBISUtils::generateUuid();
        $document->type = SBISDocumentType::SHIPPED_OUT;
        $document->priority = 0;
        $document->tries = 0;

        if (!$document->validate()) {
            throw new ModelValidationException($document);
        }

        $fileNames = $this->saveAttachments($document);
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
     * @return array
     * @throws \Exception
     */
    protected function saveAttachments(SBISDocument $document)
    {
        $fileNames = [];
        $attachments = [];
        $i = 0;
        foreach ($this->files as $file) {
            $attachment = new SBISAttachment();
            $attachment->populateRelation('document', $document);

            $attachment->external_id = SBISUtils::generateUuid();
            $attachment->number = ++$i;
            $attachment->file_name = $file->getFileName();
            $attachment->is_sign_needed = $document->sbisOrganization->is_sign_needed;
            $attachment->is_signed = '0';

            $fileName = $attachment->getFullStoredPath($file->getOfficialFileName(), $this->attachmentsCount);
            if (!$file->saveAs($fileName)) {
                $this->removeFiles($fileNames);

                throw new Exception(sprintf('Не удалось сохранить файл %s, путь %s', $attachment->file_name, $fileName));
            }
            $attachment->stored_path = $fileName;

            $fileNames[] = $fileName;
            $attachments[] = $attachment;
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