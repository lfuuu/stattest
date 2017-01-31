<?php
namespace app\commands;

use app\exceptions\ModelValidationException;
use app\models\ClientContact;
use app\models\Region;
use yii\console\Controller;

/**
 * Контакты
 */
class ContactController extends Controller
{

    /**
     * Конвертировать телефоны в E164. 10-30 минут
     *
     * @return int
     * @throws \app\exceptions\ModelValidationException
     */
    public function actionConvertPhonesToE164()
    {
        $clientContactQuery = ClientContact::find()
            ->where([
                'type' => ClientContact::$phoneTypes,
                'is_active' => 1,
            ]);
        /** @var ClientContact $clientContact */
        foreach ($clientContactQuery->each() as $clientContact) {

            list($phoneRemain, $e164Phones) = ClientContact::dao()->getE164(
                $clientContact->data,
                ($clientContact->client && $clientContact->client->region == Region::MOSCOW) ? '495' : ''
            );

            $countE164Phones = count($e164Phones);
            switch ($countE164Phones) {
                case 0:
                    // не распознан телефон - выключить
                    echo '- ';
                    $clientContact->is_validate = 0;
                    if (!$clientContact->data) {
                        $clientContact->data = '.'; // хоть что-нибудь, чтобы не падало
                    }

                    if (!$clientContact->save()) {
                        throw new ModelValidationException($clientContact);
                    }
                    break;

                case 1:
                    // распознан 1 телефон - обновить
                    $e164Phone = reset($e164Phones);
                    if ($clientContact->data == $e164Phone) {
                        echo '. ';
                        // ничего не изменилось
                        break;
                    }

                    echo '+ ';
                    $clientContact->is_validate = 1;
                    $clientContact->data = $e164Phone;
                    if ($phoneRemain) {
                        $clientContact->comment = ' ' . $phoneRemain; // нераспознанный остаток перенести в комментарий
                    }

                    if (!$clientContact->save()) {
                        throw new ModelValidationException($clientContact);
                    }
                    break;

                default:
                    // распознаны несколько телефонов - создать новые
                    $clientContact->is_validate = 0;
                    if (!$clientContact->save()) {
                        throw new ModelValidationException($clientContact);
                    }

                    foreach ($e164Phones as $e164Phone) {
                        echo '++ ';
                        $clientContactNew = new ClientContact;
                        $clientContactNew->client_id = $clientContact->client_id;
                        $clientContactNew->type = $clientContact->type;
                        $clientContactNew->is_official = $clientContact->is_official;
                        $clientContactNew->comment = $clientContact->comment;
                        $clientContactNew->is_active = 1;
                        $clientContactNew->is_validate = 1;
                        $clientContactNew->data = $e164Phone;
                        if (!$clientContactNew->save()) {
                            throw new ModelValidationException($clientContactNew);
                        }
                    }
                    break;
            }
        }

        return Controller::EXIT_CODE_NORMAL;
    }
}
