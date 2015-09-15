<?php
echo $this->render(
    $operator->operator . '/forms/' . $action . '.php', [
        'operator' => $operator,
        'model' => $model,
    ]
);