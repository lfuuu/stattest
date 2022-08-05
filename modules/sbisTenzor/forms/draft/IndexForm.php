<?php

namespace app\modules\sbisTenzor\forms\draft;

use app\exceptions\ModelValidationException;
use app\models\Bill;
use app\models\ClientAccount;
use app\modules\sbisTenzor\classes\SBISExchangeStatus;
use app\modules\sbisTenzor\classes\SBISGeneratedDraftStatus;
use app\modules\sbisTenzor\classes\XmlGenerator;
use app\modules\sbisTenzor\models\SBISAttachment;
use app\modules\sbisTenzor\models\SBISGeneratedDraft;
use yii\base\InvalidArgumentException;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

class IndexForm extends \app\classes\Form
{
    protected $client;

    /**
     * Index form constructor
     *
     * @param ClientAccount|null $client
     */
    public function __construct(ClientAccount $client = null)
    {
        parent::__construct();

        $this->client = $client;
    }

    /**
     * Получить запрос на выборку сгенерированных черновиков
     *
     * @param int $state
     * @return ActiveDataProvider
     */
    public function getDataProvider($state =  0)
    {
        $query = SBISGeneratedDraft::find();

        if ($state) {
            $query->andWhere(['=', 'state', $state]);
        } else {
            $query
                ->andWhere(['state' => [SBISGeneratedDraftStatus::DRAFT, SBISGeneratedDraftStatus::PROCESSING]]);
        }

        if ($this->client) {
            $query
                ->joinWith('invoice.bill.clientAccountModel as c1')
                ->andWhere(['c1.id' => $this->client->id]);
        }

        $query->orderBy([
            'updated_at' => SORT_DESC,
            'id' => SORT_DESC,
        ]);

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
        ]);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return
            $this->client ?
                sprintf('Сгенерированные черновики пакетов документов для клиента %s', $this->client->contragent->name) :
                'Сгенерированные черновики пакетов документов для отправки в СБИС'
            ;
    }

    /**
     * Изменение статуса черновика документов с проверкой
     *
     * @param int $id
     * @param int $stateFrom
     * @param int $stateTo
     * @param string $errorMessage
     * @return int
     * @throws ModelValidationException
     * @throws \Exception
     */
    protected static function changeDraftState($id, $stateFrom, $stateTo, $errorMessage)
    {
        $draft = SBISGeneratedDraft::findOne(['id' => $id]);
        if (!$draft) {
            throw new InvalidArgumentException('Неверный черновик документов');
        }

        /** @var SBISGeneratedDraft $draft */
        if ($draft->state != $stateFrom) {
            $errorMessage = strtr($errorMessage, ['{state}' => $draft->stateName]);

            throw new InvalidArgumentException($errorMessage);
        }

        $draft->state = $stateTo;
        if (!$draft->save()) {
            throw new ModelValidationException($draft);
        }

        return $id;
    }

    /**
     * Cancel draft
     *
     * @param $id
     * @return int
     * @throws ModelValidationException
     * @throws \Exception
     */
    public static function cancel($id)
    {
        return self::changeDraftState(
            $id,
            SBISGeneratedDraftStatus::DRAFT,
            SBISGeneratedDraftStatus::CANCELLED,
            'Черновик пакета документов в статусе {state} не может быть отменён.'
        );
    }

    /**
     * Restore draft
     *
     * @param $id
     * @return int
     * @throws ModelValidationException
     * @throws \Exception
     */
    public static function restore($id)
    {
        return self::changeDraftState(
            $id,
            SBISGeneratedDraftStatus::CANCELLED,
            SBISGeneratedDraftStatus::DRAFT,
            'Черновик пакета документов в статусе {state} не может быть восстановлен.'
        );

    }
    /**
     * Repeat draft
     *
     * @param $id
     * @return int
     * @throws ModelValidationException
     * @throws \Exception
     */
    public static function repeat($id)
    {
        return self::changeDraftState(
            $id,
            SBISGeneratedDraftStatus::ERROR,
            SBISGeneratedDraftStatus::DRAFT,
            'Черновик пакета документов в статусе {state} не может быть восстановлен.'
        );
    }

    /**
     * Save draft
     *
     * @param $id
     * @return int
     * @throws ModelValidationException
     * @throws \Exception
     */
    public static function save($id)
    {
        return self::changeDraftState(
            $id,
            SBISGeneratedDraftStatus::DRAFT,
            SBISGeneratedDraftStatus::PROCESSING,
            'Черновик пакета документов в статусе {state} не может быть запущен в работу.'
        );
    }

    /**
     * Имя файла черновика
     *
     * @param int $id
     * @param int $number порядковый номер файла
     * @return string
     */
    public static function getAttachmentFileName($id, $number)
    {
        $draft = SBISGeneratedDraft::findOne(['id' => $id]);

        /** @var SBISGeneratedDraft $draft */
        if (!$draft->sbis_document_id) {
            $client = $draft->invoice->bill->clientAccount;
            $exchangeFiles = $client->exchangeGroup->getExchangeFiles();
            if (!array_key_exists($number, $exchangeFiles)) {
                throw new InvalidArgumentException('Не найден файл для генерации с номером ' . $number);
            }
            $exchangeFile = $exchangeFiles[$number];

            if ($exchangeFile->isPdf()) {
                return sprintf('%s-%s-%02d-%s.pdf', $draft->id, $draft->invoice->number, $number + 1, $exchangeFile->form->type);
            }

            return XmlGenerator::createXmlGenerator($exchangeFile->form, $draft->invoice)->getFileName();
        }

        /** @var SBISAttachment $attachment */
        $attachment = SBISAttachment::findOne([
            'sbis_document_id' => $draft->sbis_document_id,
            'number' => $number + 1,
        ]);

        if (!$attachment) {
            throw new InvalidArgumentException('Не найдено вложение с номером ' . $number);
        }

        return basename($attachment->getActualStoredPath());
    }

    /**
     * Контент файла черновика
     *
     * @param int $id
     * @param int $number порядковый номер файла
     * @return false|string
     * @throws \Exception
     */
    public static function getAttachmentContent($id, $number)
    {
        $draft = SBISGeneratedDraft::findOne(['id' => $id]);

        /** @var SBISGeneratedDraft $draft */
        if (!$draft->sbis_document_id) {
            $client = $draft->invoice->bill->clientAccount;
            $exchangeFiles = $client->exchangeGroup->getExchangeFiles();
            if (!array_key_exists($number, $exchangeFiles)) {
                throw new InvalidArgumentException('Не найден файл для генерации с номером ' . $number);
            }
            $exchangeFile = $exchangeFiles[$number];

            if ($exchangeFile->isPdf()) {
                return file_get_contents($draft->invoice->getFilePath($exchangeFile->form->type));
            }

            return XmlGenerator::createXmlGenerator($exchangeFile->form, $draft->invoice)->getContent();
        }

        /** @var SBISAttachment $attachment */
        $attachment = SBISAttachment::findOne([
            'sbis_document_id' => $draft->sbis_document_id,
            'number' => $number + 1,
        ]);

        if (!$attachment) {
            throw new InvalidArgumentException('Не найдено вложение с номером ' . $number);
        }

        return file_get_contents($attachment->getActualStoredPath());
    }

    /**
     * @param bool $isForAll
     * @return string
     */
    public function getProcessConfirmText($isForAll = false)
    {
        return
            $this->client && !$isForAll ?
                sprintf('Создать пакеты для клиента %s?', $this->client->contragent->name) :
                'Создать пакеты по всем подтвержденным клиентам?'
            ;
    }

    /**
     * @param $isForAll
     * @param bool $eagerLoading
     * @return \yii\db\ActiveQuery
     */
    protected function getProcessQuery($isForAll, $eagerLoading = true)
    {
        $query = SBISGeneratedDraft::find();

        $query
            ->where(['=', 'state', SBISGeneratedDraftStatus::DRAFT])
            ->joinWith('invoice.bill.clientAccountModel as c1', $eagerLoading);

        $query
            ->andWhere(['IS NOT', Bill::tableName() . '.id', null]);

        if ($isForAll) {
            $query->andWhere(['c1.exchange_status' => SBISExchangeStatus::$verified]);
            return $query;
        }

        if ($this->client) {
            $query->andWhere(['c1.id' => $this->client->id]);
        } else {
            $query->andWhere(new Expression('0 = 1'));
        }

        return $query;
    }

    /**
     * Возвращает количество черновиков для обработки
     *
     * @param bool $isForAll
     * @return int
     */
    public function getProcessCount($isForAll = false)
    {
        return $this->getProcessQuery($isForAll, false)->count();
    }

    /**
     * Отправляет все подготовленные черновики пакетов документов на обработку
     *
     * @param bool $isForAll
     * @throws ModelValidationException
     */
    public function process($isForAll = false)
    {
        $query = $this->getProcessQuery($isForAll);

        /** @var SBISGeneratedDraft $draft */
        foreach ($query->each() as $draft) {
            $draft->state = SBISGeneratedDraftStatus::PROCESSING;
            if (!$draft->save()) {
                throw new ModelValidationException($draft);
            }
        }
    }
}