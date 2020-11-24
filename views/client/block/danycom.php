<h1>Заявка на портирование</h1>
<table border="0">
    <tr>
        <td valign="top">
            <table border="0">
                <?php
                foreach (\app\models\danycom\Number::find()->where(['account_id' => $account->id])->all() as $number) {
                    echo "<tr><td><b>" . $number->number . "<b></td><td>" . $number->region . "</td><td>" . $number->operator . "</td></tr>";
                }
                ?>
            </table>
        </td>
        <td valign="top">
            <?php $info = \app\models\danycom\Info::findOne(['account_id' => $account->id]); ?>
            <?php if ($info) : ?>
                Временный номер: <?= $info->temp ?><br>
                Тариф: <?= $info->tariff ?><br>
                Доставка: <?= $info->delivery_type ?><br>
            <?php
                if($info->file_link) {
                    $fileName = $info->file_link;
                    $urlInfo = parse_url($info->file_link);
                    if ($urlInfo) {
                        $e = explode('/', $urlInfo['path']);
                        $fileName = $e[count($e)-1];;
                    }
                    echo \app\classes\Html::a($fileName, $info->file_link);
                } ?>
            <?php endif; ?>
        </td>
        <td valign="top">
            <?php foreach (\app\models\danycom\Address::findAll(['account_id' => $account->id]) as $address) : ?>
            Адрес: <?=$address->address . ($address->post_code ? '('.$address->post_code.')' : '')?><br>
            <?php endforeach;?>
        </td>
    </tr>
</table>