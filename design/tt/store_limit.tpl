<H2>Оповещение о минимальном остатке товаров на складе</H2>

<FORM action="?" method=get name="get_form">
    <input type=hidden name=module value=tt>
    <input type=hidden name=action value=store_limit>
    <TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TR>
            <TD class=left>
                <label for="region">Пользователь:</label>
            </TD>
            <TD>
                {assign var="has_email" value="0"}
                <SELECT name=user onchange='document.forms["get_form"].submit();'>
                    {foreach from=$users item=item key=k name=us}
                        {if !$usergroup || $usergroup != $item->usergroup}
                            {if !$smarty.foreach.us.first}
                                </optgroup>
                            {/if}
                            <optgroup label="{$item->ugroup}">
                            {assign var="usergroup" value=$item->usergroup}
                        {/if}
                        <option value='{$item->id}'{if $user==$item->id} selected {if $item->email}{assign var="has_email" value="1"}{/if}{/if}>
                            {$item->name} ({$item->user})
                        </option>
                    {/foreach}
                    </optgroup>
                </select>
                {if !$has_email}
                    <span style="color: red;font-weight: bold;">У пользователя нет адреса электронной почты</span>
                {/if}
            </TD>
        </TR>
    </TABLE>
</form>
<FORM action="?" method=post name="post_store_limit">
    <input type=hidden name=module value=tt>
    <input type=hidden name=action value=save_limits>
    <input type=hidden name=user_id value={$user}>
    <table width="100%" id="products" class="price">
        <tr align="left">
                <td class="header" width="5%">Код товара</td>
                <td class="header" width="10%">Артикл</td>
                <td class="header" width="50%">Наименование товара</td>
                <td class="header" width="10%">Склад</td>
                <td class="header" width="5%">Количество на складе</td>
                <td class="header" width="10%">Лимит</td>
                <td class="header" width="10%">Удалить</td>
        </tr>
        {if $data}
            {foreach from=$data item=d}
                <tr id=product_{$d->good_id}_{$d->store_id}>
                    <td>{$d->num_id}</td>
                    <td>{$d->art}</td>
                    <td>{$d->name}</td>
                    <td>{$d->store_name}</td>
                    <td>{if $d->qty_free}{$d->qty_free}{else}0{/if}</td>
                    <td><input type="text" class="text" size=5 value="{$d->limit_value}" name="products[{$d->good_id}][{$d->store_id}]"></td>
                    <td>
                        <a onclick="$('#product_{$d->good_id}_{$d->store_id}').remove();document.forms['post_store_limit'].submit();">
                            <img class=icon src='{$IMAGES_PATH}icons/disable.gif' alt="Удалить">
                        </a>
                    </td>
                </tr>
            {/foreach}
        {/if}
        
    </table>
    <table width="100%">
        <tr>
                <td width="20%">
                    Склад
                </td>
                <td>
                    Продукт
                </td>
        </tr>
        <tr>
            
            <td>
                <select name="store_id" id="store_id" onChange="report_limit.findProduct(event)" onKeyUp="report_limit.findProduct(event)">
                    {foreach from=$store_list item="s"}
                        <option value={$s->id} {if $s->id == $store_id} selected{/if}>{$s->name}</option>
                    {/foreach}
                </select>
            </td>

            <td>
                <input type="text" onKeyUp="report_limit.findProduct(event)" style="width: 100%" name="new[name]" id="new_item_name" AUTOCOMPLETE=OFF />
            </td>
        </tr>
        <tr><td colspan=2 id="product_list_pane"></td></tr>
    </table>
    <DIV align=center style="padding-top: 30px;"><hr><INPUT class=button type=submit value="Сохранить"></DIV>
</FORM>
<div style="display: none;">
    <img id="tmp_image" class=icon src='{$IMAGES_PATH}icons/disable.gif' alt="Удалить">
</div>
<script src="{$PATH_TO_ROOT}js/store_limit.js"></script>