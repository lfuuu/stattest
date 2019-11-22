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
     * Показывать ли кнопку Отмена для атоматически созданного пакета
     *
     * @return bool
     */
    public function getShowCancelAutoButton()
    {
        return $this->document->state == SBISDocumentStatus::CREATED_AUTO;
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
     * Показывать ли кнопку Обновить
     *
     * @return bool
     */
    public function getShowRefreshButton()
    {
        return
            $this->document->state >= SBISDocumentStatus::PROCESSING &&
            $this->document->state != SBISDocumentStatus::ACCEPTED
        ;
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
     * Показывать ли кнопку Восстановить для атоматически созданного пакета
     *
     * @return bool
     */
    public function getShowRestoreAutoButton()
    {
        return $this->document->state == SBISDocumentStatus::CANCELLED_AUTO;
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
    public function getCancelAutoUrl()
    {
        return '/sbisTenzor/document/cancel-auto?id=' . $this->document->id;
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
    public function getRestoreAutoUrl()
    {
        return '/sbisTenzor/document/restore-auto?id=' . $this->document->id;
    }

    /**
     * @return string
     */
    public function getStartUrl()
    {
        return '/sbisTenzor/document/start?id=' . $this->document->id;
    }

    /**
     * Изменение статуса пакета документов с проверкой
     *
     * @param int $id
     * @param int $stateFrom
     * @param int $stateTo
     * @param string $errorMessage
     * @return int
     * @throws ModelValidationException
     * @throws \Exception
     */
    protected static function changeDocumentState($id, $stateFrom, $stateTo, $errorMessage)
    {
        $document = SBISDocument::findOne(['id' => $id]);
        if (!$document) {
            throw new InvalidArgumentException('Неверный пакет документов');
        }

        /** @var SBISDocument $document */
        if ($document->state != $stateFrom) {
            $errorMessage = strtr($errorMessage, ['{state}' => $document->stateName]);

            throw new InvalidArgumentException($errorMessage);
        }

        $document->setState($stateTo);
        if (!$document->save()) {
            throw new ModelValidationException($document);
        }

        return $id;
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
        return self::changeDocumentState(
            $id,
            SBISDocumentStatus::CREATED,
            SBISDocumentStatus::CANCELLED,
            'Пакет документов в статусе {state} не может быть отменён.'
        );
    }

    /**
     * Cancel document created automatically
     *
     * @param $id
     * @return int
     * @throws ModelValidationException
     * @throws \Exception
     */
    public static function cancelAuto($id)
    {
        return self::changeDocumentState(
            $id,
            SBISDocumentStatus::CREATED_AUTO,
            SBISDocumentStatus::CANCELLED_AUTO,
            'Пакет документов в статусе {state} не может быть отменён.'
        );
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
        return self::changeDocumentState(
            $id,
            SBISDocumentStatus::CANCELLED,
            SBISDocumentStatus::CREATED,
            'Пакет документов в статусе {state} не может быть восстановлен.'
        );
    }

    /**
     * Restore document created automatically
     *
     * @param $id
     * @return int
     * @throws ModelValidationException
     * @throws \Exception
     */
    public static function restoreAuto($id)
    {
        return self::changeDocumentState(
            $id,
            SBISDocumentStatus::CANCELLED_AUTO,
            SBISDocumentStatus::CREATED_AUTO,
            'Пакет документов в статусе {state} не может быть восстановлен.'
        );
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
        return self::changeDocumentState(
            $id,
            SBISDocumentStatus::CREATED,
            SBISDocumentStatus::PROCESSING,
            'Пакет документов в статусе {state} не может быть запущен в работу.'
        );
    }
}