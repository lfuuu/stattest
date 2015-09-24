<?php

echo $this->render(
    $operator->operator . '/default.php', [
        'operator' => $operator,
        'report' => $report,
        'filter' => $filter,
    ]
);