<?php

/** @var \app\models\ClientContragent $contragent */
/** @var \app\models\ClientSuper $superClient */
?>

<form>
    <table border="0" width="95%" align="center">
        <thead>
            <tr>
                <th>
                    <h2>Контрагент успешно перемещен</h2>
                    <hr size="1" />
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    Контрагент <b><?= $contragent->name; ?></b>
                    перемещен к супер-клиенту <b><?= $superClient->name; ?></b>
                </td>
            </tr>
        </tbody>
    </table>
    <hr />
    <div class="buttons_block text-center">
        <button type="button" id="dialog-close" class="btn btn-primary">Закрыть</button>
    </div>
</form>