var parseQueryStr = function(query) {
    query = query.substring(query.indexOf('?') + 1).split('&');

    var params = {}, pair, d = decodeURIComponent;

    for (var i = query.length - 1; i >= 0; i--) {
        pair = query[i].split('=');
        params[ d( pair[0] ) ] = d(pair[1]);
    }

    return params;
};

jQuery(document).ready(function() {

    var $params = parseQueryStr(self.location.search);
    if ($params.id && $params.showLastChanges) {
        $('<div />')
            .css({'text-align': 'center'})
            .addClass('alert alert-success fade in')
            .append(
                $('<div />')
                    .css({'font-weight':'bold', 'cursor':'pointer'})
                    .text('Ваши данные успешно сохранены')
                    .on('click', function() { $(this).alert('close') })
            )
            .alert()
            .prependTo('div.layout_main');
    }

});