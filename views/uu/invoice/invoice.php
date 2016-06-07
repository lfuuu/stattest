<?php

use app\classes\DateFunction;
use app\classes\uu\model\AccountEntry;
use app\classes\Wordifier;
use app\models\ClientAccount;
use app\models\Organization;
use app\models\Currency;

/** @var AccountEntry[] $accountEntries */
/** @var ClientAccount $clientAccount */
/** @var Organization $organization */

$firstEntry = $accountEntries[0];
$organization = $clientAccount->contract->getOrganization($firstEntry->date);
?>

<div align="center">
    <table border="0" cellpadding="0" cellspacing="15">
        <tr>
            <td colspan="2">
                <p align="center">
                    <strong>
                        СЧЕТ-ФАКТУРА N&nbsp;<?= $firstEntry->bill_id ?> от <?= DateFunction::mdate($firstEntry->date, 'd.m.Y') ?> г. <br />
                        ИСПРАВЛЕНИЕ N ----- от -----
                    </strong>
                </p>
        </tr>
        <tr>
            <td valign="top" width="55%" class="ht">
                Продавец: <strong> <?= $organization->name ?></strong><br />
                Адрес: <strong><?= $organization->legal_address ?></strong><br>
                ИНН/КПП продавца: <strong><?= $organization->tax_registration_id ?> / <?= $organization->tax_registration_reason ?></strong><br />
                Грузоотправитель и его адрес: <strong><?= $organization->name ?> <?= $organization->legal_address ?></strong><br />
                Грузополучатель и его адрес:
                    <?php if ($clientAccount->is_with_consignee && $clientAccount->consignee): ?>
                        <strong><?= $clientAccount->consignee ?></strong><br />
                    <?php else: ?>
                        ------<br />
                    <?php endif; ?>
                К платежно-расчетному документу<br />
                Покупатель: <strong><?= ($clientAccount->head_company ? $clientAccount->head_company : $clientAccount->company_full) ?></strong><br />
                Адрес: <strong><?= ($clientAccount->head_company_address_jur ? $clientAccount->head_company_address_jur : $clientAccount->address_jur) ?></strong><br />
                ИНН/КПП покупателя: <strong><?= $clientAccount->inn ?>&nbsp;/<?= $clientAccount->kpp ?></strong><br />
                Дополнение: <strong>к счету N: <?= $firstEntry->bill_id ?></strong><br />
                Валюта: наименование Российский рубль, код 643
            </td>
            <td align=right valign="top" width="45%">
                <small>
                    Приложение N1<br />
                    к постановлению Правительства<br />
                    Российской Федерации<br />
                    от 28 декабря 2011 г. N 1137
                </small>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div align="center">
                    <table border="1" cellpadding="3" cellspacing="0" width="100%">
                        <tr>
                            <th rowspan="2">
                                Наименование<br />
                                товара<br />
                                (описание выполненных работ, оказанных услуг),<br />
                                имущественного права
                            </th>
                            <th colspan="2">
                                Единица<br />
                                измерения
                            </th>
                            <th rowspan="2">
                                Коли-<br />чество<br/ >
                                (объем)
                            </th>
                            <th rowspan="2">
                                Цена<br />
                                (тариф)<br />
                                за единицу<br />
                                измерения
                            </th>
                            <th rowspan="2">
                                Стоимость<br />
                                товаров (работ,<br />
                                услуг),<br />
                                имущественных<br />прав без налога-<br />
                                всего
                            </th>
                            <th rowspan="2">
                                В том<br />
                                числе<br />
                                сумма<br />
                                акциза
                            </th>
                            <th rowspan="2">
                                Нало-<br />
                                говая<br />
                                ставка
                            </th>
                            <th rowspan="2">
                                Сумма налога,<br />
                                предъяв-<br />
                                ляемая<br />
                                покупа-<br />
                                телю
                            </th>
                            <th rowspan="2">
                                Стоимость товаров<br />
                                (работ, услуг),<br />
                                имущественных <br />
                                прав<br />
                                с налогом - всего
                            </th>
                            <th colspan=2>
                                Страна происхожде-<br />
                                ния товара
                            </th>
                            <th rowspan="2">
                                Номер<br />
                                таможенной<br />
                                декларации
                            </th>
                        </tr>
                        <tr>
                            <th>код</th>
                            <th>условное<br />обозначение<br />(национальное)</th>
                            <th>цифро-<br />вой<br />код</th>
                            <th>краткое<br>наименование</th>
                        </tr>
                        <tr>
                            <td align="center">1</td>
                            <td align="center">2</td>
                            <td align="center">2а</td>
                            <td align="center">3</td>
                            <td align="center">4</td>
                            <td align="center">5</td>
                            <td align="center">6</td>
                            <td align="center">7</td>
                            <td align="center">8</td>
                            <td align="center">9</td>
                            <td align="center">10</td>
                            <td align="center">10а</td>
                            <td align="center">11</td>
                        </tr>

                        <?php
                        $summaryWithoutVat =
                        $summaryVat =
                        $summaryWithVat = 0;

                        foreach ($accountEntries as $accountEntry):
                            $summaryWithoutVat += $accountEntry->price_without_vat;
                            $summaryVat += $accountEntry->vat;
                            $summaryWithVat += $accountEntry->price_with_vat;
                            ?>
                            <tr>
                                <td><?= $accountEntry->accountTariff->getName(false) ?></td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td><?= sprintf('%.2f', $accountEntry->price_without_vat) ?></td>
                                <td>без акциза</td>
                                <td><?= ($accountEntry->vat_rate == 0 ? 'без НДС' : sprintf('%.2f', $accountEntry->vat_rate) . '%') ?></td>
                                <td><?= sprintf('%.2f', $accountEntry->vat) ?></td>
                                <td><?= sprintf('%.2f', $accountEntry->price_with_vat) ?></td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                            </tr>
                        <?php endforeach ?>

                        <tr>
                            <td colspan="5"><b>Всего к оплате<b></td>
                            <td align="center"><?= sprintf('%.2f', $summaryWithoutVat) ?></td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td align="center"><?= sprintf('%.2f', $summaryVat) ?></td>
                            <td align="center"><?= sprintf('%.2f', $summaryWithVat) ?></td>
                            <td colspan="3">&nbsp;</td>
                        </tr>
                    </table>
                </div>
                <br />

                Итого: <?= Wordifier::Make($summaryWithVat, Currency::RUB) ?>

                <div align="center">
                    <table border="0" cellpadding="0" cellspacing="5" align="left">
                        <tr>
                            <td><p align="right">Руководитель&nbsp;организации <br />или иное уполномоченное лицо:</td>
                            <td><br />________________________________<br /><br /></td>
                            <td nowrap>/ <?= $organization->director->name_nominative ?> /</td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td><p align="right">&nbsp;Главный&nbsp;бухгалтер <br />или иное уполномоченное лицо:</td>
                            <td><br />________________________________<br /><br /></td>
                            <td nowrap>/ <?= $organization->accountant->name_nominative ?> /</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td align="center"><small>(подпись)</small></td>
                            <td align="center"><small>(ф.и.о.)</small></td>
                            <td></td>
                            <td></td>
                            <td align="center"><small>(подпись)</small></td>
                            <td align="center"><small>(ф.и.о.)</small></td>
                        </tr>

                        <tr >
                            <td><p align="right">За генерального директора:</td>
                            <td>
                                <br>________________________________<br><br></td>
                            <td></td>

                            <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td><p align="right">За главного&nbsp;бухгалтер:</td>
                            <td>

                                <br>________________________________<br><br></td>
                            <td>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td align="center"><small>(подпись)</small></td>
                            <td align="center"></td>
                            <td></td>
                            <td></td>
                            <td align="center"><small>(подпись)</small></td>
                            <td align="center"></td>
                        </tr>
                        <tr>
                            <td colspan=4><p><br>Индивидуальный предприниматель ___________________&nbsp;&nbsp;&nbsp;&nbsp; _______________________</p>
                            </td>
                            <td colspan=3><p><br>&nbsp; &nbsp;____________________________________________________________________</p></td>
                        </tr>

                        <tr>
                            <td></td><td align=center><small>(подпись)</small></td>
                            <td align=center><small>(ф.и.о.)</small></td>
                            <td></td>
                            <td align=center colspan=3><small>(реквизиты свидетельства о государственной регистрации индивидуального предпринимателя)</small></td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</div>
<small>Примечание: Первый экземпляр - покупателю, второй экземпляр - продавцу.</small>
