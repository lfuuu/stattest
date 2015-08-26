Вы изменили пароль на сервере <?= Yii::$app->params['PROTOCOL_STRING'] . $_SERVER['HTTP_HOST']; ?><br />
Ваш новый пароль - <?= $form->password; ?><br />
Запишите его в надёжном месте (лучше всего - в голове) и постарайтесь не забывать<br />
<br />
<br />
<br />
You have changed password at <?= Yii::$app->params['PROTOCOL_STRING'] . $_SERVER['HTTP_HOST']; ?> server<br />
Your new password is "<?= $form->password; ?>"<br />
Please, write it in private place and try not to forget.