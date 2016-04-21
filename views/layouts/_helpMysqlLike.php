<?php
/**
 * Вывести help иконкой про допустимые символы в MySQL LIKE. Но в контроллере они все равно должны пройти валидацию и замену.
 *
 * // если ['LIKE', 'number', $mask], то он заэскейпит спецсимволы и добавить % в начало и конец. Подробнее см. \yii\db\QueryBuilder::buildLikeCondition
 * $this->src_number !== '' && ($this->src_number = strtr($this->src_number, ['.' => '_', '*' => '%'])) && $query->andWhere('src_number::VARCHAR LIKE :src_number', [':src_number' => $this->src_number]);
 */
?>

<?= $this->render('//layouts/_help', [
    'message' => 'Допустимы цифры, _ или . (одна любая цифра), % или * (любая последовательность цифр, в том числе пустая строка)',
    'extraClass' => 'pull-right',
]); ?>
