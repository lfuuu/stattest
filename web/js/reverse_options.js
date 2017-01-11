/**
 * Функция меняет местами текст и значение элемента списка, заданного селектором
 */

function reverseOptions (selector)
{
    selector.each( function () {
        var val = $(this).val(),
            text = $(this).text();
        $(this).val(text).text(val);
    })
}
