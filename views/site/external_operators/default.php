<?php
echo $this->render(
    $operator->operator . '/default.php', [
        'currentRange' => $currentRange,
        'operator' => $operator,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
        'filter' => $filter,
    ]
);