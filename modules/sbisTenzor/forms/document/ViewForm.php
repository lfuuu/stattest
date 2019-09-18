<?php

namespace app\modules\sbisTenzor\forms\document;

use app\exceptions\ModelValidationException;
use app\modules\sbisTenzor\classes\SBISDocumentStatus;
use app\modules\sbisTenzor\models\SBISDocument;
use yii\base\InvalidArgumentException;

class ViewForm extends \app\classes\Form
{
    /** @var int */
    protected $clientId;

    /** @var SBISDocument */
    protected $document;

    /**
     * View form constructor.
     * @param int $id
     */
    public function __construct($id = 0)
    {
        parent::__construct();

        if (!( $this->document = SBISDocument::findOne(['id' => $id]) )) {
            throw new InvalidArgumentException('Неверный пакет документов');
        }

        $this->clientId = $this->document->client_account_id;
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
     * Показывать ли кнопку Отмена
     *
     * @return bool
     */
    public function getShowCancelButton()
    {
        return $this->document->state == SBISDocumentStatus::CREATED;
    }

    /**
     * Показывать ли кнопку Отправить
     *
     * @return bool
     */
    public function getShowSendButton()
    {
        return $this->document->state == SBISDocumentStatus::CREATED;
    }

    /**
     * Показывать ли кнопку Восстановить
     *
     * @return bool
     */
    public function getShowRestoreButton()
    {
        return $this->document->state == SBISDocumentStatus::CANCELLED;
    }

    /**
     * @return string
     */
    public function getCancelUrl()
    {
        return '/sbisTenzor/document/cancel?id=' . $this->document->id;
    }

    /**
     * @return string
     */
    public function getRestoreUrl()
    {
        return '/sbisTenzor/document/restore?id=' . $this->document->id;
    }

    /**
     * @return string
     */
    public function getStartUrl()
    {
        return '/sbisTenzor/document/start?id=' . $this->document->id;
    }

    /**
     * Cancel document
     *
     * @param $id
     * @return int
     * @throws ModelValidationException
     * @throws \Exception
     */
    public static function cancel($id)
    {
        $document = SBISDocument::findOne(['id' => $id]);
        if (!$document) {
            throw new InvalidArgumentException('Неверный пакет документов');
        }

        /** @var SBISDocument $document */
        if ($document->state != SBISDocumentStatus::CREATED) {
            throw new InvalidArgumentException(
                'Пакет документов в статусе ' . $document->stateName . ' не может быть отменён.'
            );
        }

        $document->setState(SBISDocumentStatus::CANCELLED);
        if (!$document->save()) {
            throw new ModelValidationException($document);
        }

        return $id;
    }

    /**
     * Restore document
     *
     * @param $id
     * @return int
     * @throws ModelValidationException
     * @throws \Exception
     */
    public static function restore($id)
    {
        $document = SBISDocument::findOne(['id' => $id]);
        if (!$document) {
            throw new InvalidArgumentException('Неверный пакет документов');
        }

        /** @var SBISDocument $document */
        if ($document->state != SBISDocumentStatus::CANCELLED) {
            throw new InvalidArgumentException(
                'Пакет документов в статусе ' . $document->stateName . ' не может быть восстановлен.'
            );
        }

        $document->setState(SBISDocumentStatus::CREATED);
        if (!$document->save()) {
            throw new ModelValidationException($document);
        }

        return $id;
    }

    /**
     * Start document
     *
     * @param $id
     * @return int
     * @throws ModelValidationException
     * @throws \Exception
     */
    public static function start($id)
    {
        $document = SBISDocument::findOne(['id' => $id]);
        if (!$document) {
            throw new InvalidArgumentException('Неверный пакет документов');
        }

        /** @var SBISDocument $document */
        if ($document->state != SBISDocumentStatus::CREATED) {
            throw new InvalidArgumentException(
                'Пакет документов в статусе ' . $document->stateName . ' не может быть запущен в работу.'
            );
        }

        $document->setState(SBISDocumentStatus::PROCESSING);
        if (!$document->save()) {
            throw new ModelValidationException($document);
        }

        return $id;
    }
}