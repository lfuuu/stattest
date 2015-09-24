<?php

$params = get_defined_vars();

echo $this->render(
    $operator->operator . '/forms/' . $action . '.php',
    $params['_params_']
);