<?php

namespace app\modules\sbisTenzor\classes;

use app\exceptions\ModelValidationException;
use app\modules\sbisTenzor\classes\SBISProcessor\SBISFetcher;
use app\modules\sbisTenzor\classes\SBISProcessor\SBISGenerator;
use app\modules\sbisTenzor\classes\SBISProcessor\SBISSender;
use app\modules\sbisTenzor\classes\SBISProcessor\SBISSigner;
use app\modules\sbisTenzor\models\SBISDocument;
use app\modules\sbisTenzor\Module;
use kartik\base\Config;

/**
 * Абстрактный класс обработчика пакетов документов
 */
abstract class SBISProcessor
{
    const TYPE_SENDER = 'sender';
    const TYPE_FETCHER = 'fetcher';
    const TYPE_SIGNER = 'signer';
    const TYPE_GENERATOR = 'generator';

    protected static $classes = [
        self::TYPE_SENDER => SBISSender::class,
        self::TYPE_FETCHER => SBISFetcher::class,
        self::TYPE_SIGNER => SBISSigner::class,
        self::TYPE_GENERATOR => SBISGenerator::class,
    ];

    /** @var SBISTensorAPI[] */
    protected $apiPool = [];

    /**
     * SBISProcessor constructor
     *
     * @throws \Exception
     */
    public function __construct()
    {
        /** @var Module $module */
        $module = Config::getModule('sbisTenzor');

        foreach ($module->getParams() as $id => $sbisOrganization) {
            $this->apiPool[$id] = new SBISTensorAPI($sbisOrganization);
        }
    }

    /**
     * Создание обработчика
     *
     * @param int $typeId
     * @return static
     */
    public static function createProcessor($typeId)
    {
        $instance = new self::$classes[$typeId];
        return $instance;
    }

    /**
     * Получить API-класс взаимодествия со СБИС по документу
     *
     * @param SBISDocument $document
     * @return SBISTensorAPI
     */
    protected function getAPIByDocument(SBISDocument $document)
    {
        return $this->apiPool[$document->sbis_organization_id];
    }

    /**
     * Точка входа обработчика
     *
     * @return int количество обработанный пакетов
     */
    public function run()
    {
        return 0;
    }

    /**
     * Предобработка пакета документов
     *
     * @param SBISDocument $document
     * @return bool
     * @throws ModelValidationException
     */
    protected function beforeProcess(SBISDocument $document)
    {
        $document->tries += 1;

        if (!$document->save()) {
            throw new ModelValidationException($document);
        }

        return true;
    }

    /**
     *
     * Обработка пакета документов
     * @param SBISDocument $document
     * @return bool
     */
    protected function process(SBISDocument $document)
    {
        return true;
    }

    /**
     * Постобработка пакета документов
     *
     * @param SBISDocument $document
     * @param $success
     */
    protected function afterProcess(SBISDocument $document, $success)
    {

    }
}