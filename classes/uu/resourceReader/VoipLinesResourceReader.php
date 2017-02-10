<?php

namespace app\classes\uu\resourceReader;

use app\classes\api\ApiPhone;
use app\classes\api\ApiVpbx;
use app\classes\uu\model\AccountTariff;
use app\exceptions\web\BadRequestHttpException;
use app\helpers\DateTimeZoneHelper;
use DateTimeImmutable;
use Yii;
use yii\base\Object;

class VoipLinesResourceReader extends Object implements ResourceReaderInterface
{

    /**
     * VoipLinesResourceReader constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // чтобы подключить конфиг API VPBX
        define("NO_WEB", 1);
        require_once Yii::$app->basePath . '/stat/conf.php';
    }

    /**
     * Вернуть количество потраченного ресурса
     * https://vpbx.mcn.ru/core/swagger/index.html , voip, /get_did_client_lines
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $dateTime
     * @return float Если null, то данные неизвестны
     */
    public function read(AccountTariff $accountTariff, DateTimeImmutable $dateTime)
    {

        if (!ApiVpbx::isAvailable()) {
            Yii::error('VoipLinesResourceReader. Нет данных по ресурсу. Не настроен API');
            return null;
        }

        try {
            $result = ApiPhone::getResourceVoipLines($accountTariff->clientAccount, $accountTariff->voip_number, $dateTime);
            if (isset($result['result'][0]['cl'])) {
                return (int)$result['result'][0]['cl'];
            }

            throw new BadRequestHttpException(isset($result['errors']) ? $result['errors'] : 'Неправильный ответ. Нет lc');
        } catch (\Exception $e) {
            $date = $dateTime->format(DateTimeZoneHelper::DATE_FORMAT);
            Yii::error(sprintf('VoipLinesResourceReader. Нет данных по ресурсу. AccountTariffId = %d, дата = %s. %s', $accountTariff->id, $date, $e->getMessage()));
            return null;
        }
    }

    /**
     * Как считать PricePerUnit - указана за месяц или за день
     * true - за месяц (при ежедневном расчете надо разделить на кол-во дней в месяце)
     * false - за день (при ежедневном расчете так и оставить)
     *
     * @return bool
     */
    public function getIsMonthPricePerUnit()
    {
        return true;
    }
}