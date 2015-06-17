<?php
/** @var $versions array */

$links = [
    'ClientAccount' => '/client/edit?id=',
    'ClientContragent' => '/contragent/edit?id=',
    'ClientContragentPerson' => '/contragent/edit?id=',
    'ClientContract' => '/contract/edit?id=',
];
?>
<?php if (!$versions) : ?>
    Версий не найдено
<?php else : ?>
    <ul>
        <?php
        $last = count($versions) - 1;
        foreach ($versions as $k => $version) {
            echo '<li>';
            echo '<a href="' . $links[$version->model] . $version->model_id . ($last === $k ? '' : '&date=' . $version->date) . '">' . $version->date . '</a>';
            echo '</li>';
        }
        ?>
    </ul>


<?php endif; ?>