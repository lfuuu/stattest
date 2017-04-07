<?php
/**
 * Вывести help иконкой про допустимые символы в MySQL LIKE. Но в контроллере они все равно должны пройти валидацию и замену.
 *
 * $this->voip_number = strtr($this->voip_number, ['.' => '_', '*' => '%']);
 * $this->voip_number && $query->andWhere(['LIKE', 'voip_number', $this->voip_number, $isEscape = false]);
 */
?>

<?= $this->render('//layouts/_help', [
    'message' => 'Допустимы цифры, _ или . (одна любая цифра), % или * (любая последовательность цифр, в том числе пустая строка)',
    'extraClass' => 'pull-right',
]); ?>
