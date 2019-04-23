<table style="border-collapse: separate; border-spacing: 0px;" width="94%">
    <tbody>
    <tr style="page-break-inside: avoid;">
        <td style="border: 1px solid black; padding-left: 10px; width: 2%; height: 34px;">
            <p><strong>№</strong></p>
        </td>
        <td style="border: 1px solid black; padding-left: 10px; width: 11%; height: 34px;">
            <p>Абонентский номер, номер SIM карты (ICCID)</p>
        </td>
        <td style="border: 1px solid black; padding-left: 10px; width: 27%; height: 34px;">
            <p>ФИО</p>
        </td>
        <td style="border: 1px solid black; padding-left: 10px; width: 60%; height: 34px;">
            <p>Серия и номер паспорта, дата выдачи, кем выдан, код подразделения, адрес регистрации</p>
        </td>
    </tr>
    {assign var=idx value=1}
    {foreach from=$sims item=sim}
        <tr style="page-break-inside: avoid;">
            <td style="border: 1px solid black; padding-left: 10px; width: 2%; height: 102px;">
                <p>{$idx}.</p>
            </td>
            <td style="border: 1px solid black; padding-left: 10px; width: 11%; height: 102px;">
                <p>{$sim.msisdn}<br/>{$sim.iccid}</p>
            </td>
            <td style="border: 1px solid black; padding-left: 10px; width: 27%; height: 102px;">
                <p>&nbsp;</p>
            </td>
            <td style="border: 1px solid black; padding-left: 10px; width: 60%; height: 102px;">&nbsp;</td>
        </tr>
        {assign var=idx value=$idx+1}
    {/foreach}
    {foreach from=$numbers item=number}
        <tr style="page-break-inside: avoid;">
            <td style="border: 1px solid black; padding-left: 10px; width: 2%; height: 102px;">
                <p>{$idx}.</p>
            </td>
            <td style="border: 1px solid black; padding-left: 10px; width: 11%; height: 102px;">
                <p>{$number}<br/>-----</p>
            </td>
            <td style="border: 1px solid black; padding-left: 10px; width: 27%; height: 102px;">
                <p>&nbsp;</p>
            </td>
            <td style="border: 1px solid black; padding-left: 10px; width: 60%; height: 102px;">&nbsp;</td>
        </tr>
        {assign var=idx value=$idx+1}
    {/foreach}
    </tbody>
</table>