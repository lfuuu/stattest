<h2>Договора</h2>
<div id=add_from style="display:none;">
    <FORM action="?" method=post>
        <input type=hidden name=module value=tarifs>
        <input type=hidden name=action value=contracts>
        <input type=hidden name=do value=open>
        <input type=hidden name=new value=true>
        <table border=0 width=90%>
            <tr>
                <td>Договор:</td>
                <td>
                    <select class="select2" style="width: 200px;" name=contract_template_group>
                        {foreach from=$templates key=k item=item}
                            <option value="{$k}"{if $contract_template_group == $k} selected{/if}>{$folders[$k]}</option>
                        {/foreach}
                    </select>
                    <input type=text name=contract_template_add>
                    <input type=submit value="Создать">
                    <input type=button value="Отмена" onclick="toggle(true);">
                </td>
            </tr>
        </table>
    </form>
</div>

<div id=edit_from style="display:block;">
    <table border=0 width=90%>
        <tr>
            <td>Договор:</td>
            <td>
                <table width=100% border=0>
                    <tr>
                        <td>
                            <FORM action="?" method=post>
                                <input type=hidden name=module value=tarifs>
                                <input type=hidden name=action value=contracts>
                                <input type=hidden name=do value=open>
                                <select class="select2" style="width:200px;" name=contract_template_group
                                        id="contract_template_group" onChange="do_change_template_group(this)">
                                    {foreach from=$templates key=k item=item}
                                        <option value="{$k}"{if $contract_template_group == $k} selected{/if}>{$folders[$k]}</option>
                                    {/foreach}
                                </select>

                                <select name=contract_template id="contract_template" class="text select2"
                                        style="width:300px;">
                                    {foreach from=$templates[$contract_template_group] item=item}
                                        <option value="{$item}"{if $contract_template == $item} selected{/if}>{$item}</option>
                                    {/foreach}
                                </select> <input type=submit value="Открыть">
                            </form>
                        </td>
                        <td><img src="./images/icons/add.gif" align=absmiddle style="cursor: pointer;"
                                 onclick="toggle(false);"></td>
                        <td align=right>{$info}</td>
                    </tr>
                </table>
                </form>
            </td>
        </tr>
        {if $is_opened}
            <tr>
                <td colspan=2>
                    <FORM action="?" method=post name=form1 id=form1>
                        <input type=hidden name=module value=tarifs>
                        <input type=hidden name=action value=contracts>
                        <input type=hidden name=do value=open>
                        <input type=hidden name="contract_template_group" value="{$contract_template_group}">
                        <input type=hidden name="contract_template" value={$contract_template}>

                        Тип
                        документа: {html_options name="contract_type" options=$contract_types selected=$contract_type}
                        <textarea id="text" name="text"
                                  style="width: 100%; margin: 0px; height: 600px;">{$contract_body}</textarea>
                        <script type="text/javascript">
                            {literal}
                            $(document).ready(function () {
                                tinymce.init({
                                    selector: "textarea",
                                    convert_urls: false,
                                    plugins: [
                                        "advlist autolink lists link image charmap print preview anchor",
                                        "searchreplace visualblocks code fullscreen",
                                        "insertdatetime media table contextmenu paste"
                                    ],
                                    toolbar: "insertfile undo redo | styleselect fontsizeselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
                                });
                            });

                            {/literal}
                        </script>

                </td>
            </tr>
            <tr>
                <td colspan=2 align=left>
                    <input type=submit value="Сохранить" name="save_text" style="width: 100%">
                    <br>

                    {literal}
                    <table border="1" >
                        <tr>
                            <th>Переменная</th>
                            <th>Описание</th>
                        </tr>
                        <tr>
                            <td>{$position}</td>
                            <td>Должность Исполнительного органа</td>
                        </tr>
                        <tr>
                            <td>{$fio}</td>
                            <td>Фио Исполнительного органа</td>
                        </tr>
                        <tr>
                            <td>{$name}</td>
                            <td>Краткое наименование контрагента</td>
                        </tr>
                        <tr>
                            <td>{$name_full}</td>
                            <td>Полное наименование контрагента</td>
                        </tr>
                        <tr>
                            <td>{$address_jur}</td>
                            <td>Адрес юридический</td>
                        </tr>
                        <tr>
                            <td>{$bank_properties}</td>
                            <td>Банковские реквизиты</td>
                        </tr>
                        <tr>
                            <td>{$bik}</td>
                            <td>БИК</td>
                        </tr>
                        <tr>
                            <td>{$address_post_real}</td>
                            <td>Действительный почтовый адрес</td>
                        </tr>
                        <tr>
                            <td>{$address_post}</td>
                            <td>Почтовый адрес</td>
                        </tr>
                        <tr>
                            <td>{$corr_acc}</td>
                            <td>К/С</td>
                        </tr>
                        <tr>
                            <td>{$pay_acc}</td>
                            <td>Р/С</td>
                        </tr>
                        <tr>
                            <td>{$inn}</td>
                            <td>ИНН</td>
                        </tr>
                        <tr>
                            <td>{$kpp}</td>
                            <td>КПП</td>
                        </tr>
                        <tr>
                            <td>{$stamp}</td>
                            <td>Печатать штамп (1 | 0)</td>
                        </tr>
                        <tr>
                            <td>{$legal_type}</td>
                            <td>Тип контрагента(ip | legal | person)</td>
                        </tr>
                        <tr>
                            <td>{$old_legal_type}</td>
                            <td>Тип контрагента( priv | org)</td>
                        </tr>
                        <tr>
                            <td>{$address_connect}</td>
                            <td>Предполагаемый адрес подключения</td>
                        </tr>
                        <tr>
                            <td>{$account_id}</td>
                            <td>id ЛС</td>
                        </tr>
                        <tr>
                            <td>{$bank_name}</td>
                            <td>Название банка</td>
                        </tr>
                        <tr>
                            <td>{$credit}</td>
                            <td>Разрешить кредит</td>
                        </tr>

                        <tr>
                            <td>{$contract_no}</td>
                            <td>№ договора</td>
                        </tr>
                        <tr>
                            <td>{$contract_date}</td>
                            <td>Дата договора</td>
                        </tr>
                        <tr>
                            <td>{$contract_dop_date}</td>
                            <td>Дополнительная дата договора</td>
                        </tr>
                        <tr>
                            <td>{$contract_dop_no}</td>
                            <td>Дополнительный № договора</td>
                        </tr>

                        <tr>
                            <td>{$contact}</td>
                            <td>ФИО контактного лица</td>
                        </tr>
                        <tr>
                            <td>{$emails}</td>
                            <td>Email</td>
                        </tr>
                        <tr>
                            <td>{$phones}</td>
                            <td>Телефон</td>
                        </tr>
                        <tr>
                            <td>{$faxes}</td>
                            <td>Факс</td>
                        </tr>

                        <tr>
                            <td>{$organization_firma}</td>
                            <td>Организация( mcn | mcn-telecom | ... )</td>
                        </tr>
                        <tr>
                            <td>{$organization_director_post}</td>
                            <td>Почтовый адрес директора</td>
                        </tr>
                        <tr>
                            <td>{$organization_director}</td>
                            <td>ФИО директора</td>
                        </tr>
                        <tr>
                            <td>{$organization_name}</td>
                            <td>Название организации( ООО «МСН Телеком» | ООО «Эм Си Эн» | ... )</td>
                        </tr>
                        <tr>
                            <td>{$organization_address}</td>
                            <td>Адрес организации</td>
                        </tr>
                        <tr>
                            <td>{$organization_inn}</td>
                            <td>ИНН организации</td>
                        </tr>
                        <tr>
                            <td>{$organization_kpp}</td>
                            <td>КПП организации</td>
                        </tr>
                        <tr>
                            <td>{$organization_corr_acc}</td>
                            <td>К/С организации</td>
                        </tr>
                        <tr>
                            <td>{$organization_bik}</td>
                            <td>БИК организации</td>
                        </tr>
                        <tr>
                            <td>{$organization_bank}</td>
                            <td>Банк организации</td>
                        </tr>
                        <tr>
                            <td>{$organization_phone}</td>
                            <td>Телефон организации</td>
                        </tr>
                        <tr>
                            <td>{$organization_email}</td>
                            <td>Email организации</td>
                        </tr>
                        <tr>
                            <td>{$organization_pay_acc}</td>
                            <td>Р/С организации</td>
                        </tr>
                        <tr>
                            <td>{$firm_detail_block}</td>
                            <td>
                                Платежные реквизиты организации: <br/>
                                <pre>
$f["name"] . "<br /> Юридический адрес: " . $f["address"] .
    (isset($f["post_address"]) ? "<br /> Почтовый адрес: " . $f["post_address"] : "")
    . " ИНН " . $f["inn"] . ", КПП " . $f["kpp"]
    . ($b ?
    "  Банковские реквизиты:"
    . " р/с:&nbsp;" . $f["acc"] . " в " . $f["bank_name"]
    . " к/с:&nbsp;" . $f["kor_acc"]
    . " БИК:&nbsp;" . $f["bik"]
    : '')
    . " телефон: " . $f["phone"]
    . (isset($f["fax"]) && $f["fax"] ? "<br /> факс: " . $f["fax"] : "")
    . " е-mail: " . $f["email"];
                                </pre>

                            </td>
                        </tr>
                        <tr>
                            <td>{$payment_info}</td>
                            <td>

                                Платежные реквизиты контрагента: <br/>
                                <pre>
                                $result = $contragent->name_full . '<br />Адрес: ' . (
                                    $contragent->legal_type == 'person'
                                    ? $contragent->person->registration_address
                                    : $account->address_jur
                                ) . '<br />';

                                if ($contragent->legal_type == 'person') {
                                    if (!empty($account->bank_properties))
                                        return $result . nl2br($account->bank_properties);

                                    return
                                        $result .
                                        'Паспорт серия ' . $contragent->person->passport_serial .
                                        ' номер ' . $contragent->person->passport_number .
                                        '<br />Выдан: ' . $contragent->person->passport_issued .
                                        '<br />Дата выдачи: ' . $contragent->person->passport_date_issued . ' г.';
                                }
                                else {
                                    return
                                        $result .
                                        'Банковские реквизиты: ' .
                                        'р/с ' . ($account->pay_acc ?: '') . '<br />' .
                                        $account->bank_name . ' ' . $account->bank_city  .
                                        ($account->corr_acc ? '<br />к/с ' . $account->corr_acc : '') .
                                        ', БИК ' . $account->bik .
                                        ', ИНН ' . $contragent->inn .
                                        ', КПП ' . $contragent->kpp .
                                        (!empty($account->address_post_real) ? '<br />Почтовый адрес: ' . $account->address_post_real : '');
                                }
                                </pre>
                            </td>
                        </tr>
                    </table>
                    {/literal}
                    <br>
                    <br>
                    <br>
                    <br>

                    Переименовать: <input type=text value="{$contract_template}" name="new_contract_template"><input
                            type=submit value="Go" name="rename"><br>
                    Переложить:

                    <select class="select2" style="width:200px;" name=new_contract_template_group
                            id="new_contract_template_group">
                        {foreach from=$templates key=k item=item}
                            <option value="{$k}"{if $contract_template_group == $k} selected{/if}>{$folders[$k]}</option>
                        {/foreach}
                    </select> <input type=submit value="Go" name="move"><br>
                </td>
                </form>
            </tr>
        {/if}
    </table>
</div>
<script>
    var tDogovors = new Array();
    {foreach from=$templates key=group item=item}
    tDogovors["{$group}"] = new Array();
    {foreach from=$item item=dov}
    tDogovors["{$group}"].push('{$dov}');
    {/foreach}
    {/foreach}
    {literal}

    document.onready = function() {
        if (!document.getElementById("contract_template").options.length)
            document.getElementById('contract_template_group').onchange();
    };

    function do_change_template_group(o) {
        var group = o.options[o.selectedIndex].value;

        var oContractTemplate = document.getElementById("contract_template");

        for (i = oContractTemplate.options.length; i >= 1; i--) {
            oContractTemplate.remove(i - 1);
        }

        aIds = tDogovors[group];

        var optNames = new Array();

        if (aIds) {
            for (a in aIds) {
                id = aIds[a];
                optNames.push([id, id])
            }
        }

        optNames.sort(optSort);

        var firstName = "";
        for (a in optNames) {
            var o = optNames[a];
            if (firstName == "") {
                firstName = o[0];
            }
            createOption(oContractTemplate, o[0], o[1]);
        }

        if (firstName != "") {
            $("#contract_template").select2("val", firstName);
        }
    }

    function optSort(i, ii) {
        return i[1] == ii[1] ? 0 : (i[1] > ii[1] ? 1 : -1);
    }

    function toggle(flag) {
        document.getElementById("add_from").style.display = flag ? "none" : "block";
        document.getElementById("edit_from").style.display = !flag ? "none" : "block";
    }

    {/literal}

</script>
