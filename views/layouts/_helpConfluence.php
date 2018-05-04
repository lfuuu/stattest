<?php
/**
 * Вывести help иконкой со ссылкой
 *
 * @var int $confluenceId
 * @var string $message
 * @var string $extraClass не обязательно
 */
?>

<a href="http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=<?= $confluenceId ?>" target="_blank" title="<?= $message ?>" class="<?= isset($extraClass) ? $extraClass : '' ?>">
    <span class="glyphicon glyphicon-question-sign"></span>
</a>
