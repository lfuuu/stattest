<h2>Баланс</h2>
{if $totals.saldo_ts && $totals.saldo_sum}
<div style='font-size:11px;margin-bottom:20px'>Сальдо: установлено на {$totals.saldo_ts} в размере {$totals.saldo_sum}{$totals.sgn}</div>
{/if}
Общая сумма оказанных услуг: <b>{$totals.bill_total|round:2}{$totals.sgn}</b> (из них в {0|mdate:'месяце'} - <b>{$totals.bill_month|round:2}{$totals.sgn}</b>)<br>
Общая сумма полученных платежей: <b>{$totals.payment_total|round:2}{$totals.sgn}</b> (из них в {0|mdate:'месяце'} - <b>{$totals.payment_month|round:2}{$totals.sgn}</b>)<br>
По данным ООО "Эм Си Эн" на {0|mdate:'m.d.Y г.'} задолженность {if abs($totals.debt)<=0.01}отсутствует{elseif $totals.debt<0} в Вашу пользу составляет <b>{$totals.debt|round:2:'-'}{$totals.sgn}</b>{else} в пользу ООО "Эм Си Эн" составляет <b>{$totals.debt|mround:2}{$totals.sgn}</b>{/if}<br>
<br>
Если бы счета за следующий месяц выставлялись прямо сейчас, вам мы бы выставили счёт на <b>{$totals.bill_future|round:2}{$totals.sgn}</b><br>
