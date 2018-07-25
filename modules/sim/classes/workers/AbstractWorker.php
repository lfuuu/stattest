<?php

namespace app\modules\sim\classes\workers;

abstract class AbstractWorker
{
    /**
     * @var array Ответ для котроллера с последующей отправкой по ajax
     */
    protected $response = [];

    /**
     * @var array Журналирование процесса с последующим логгированием в Graylog
     */
    protected $journal = [];
}