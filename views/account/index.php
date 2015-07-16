<?php
use \kartik\grid\GridView;

?>

<div class="row">
    <div class="col-sm-12">
        <ul class="nav nav-pills">
            <li>
                <a href="index.php?module=tt&amp;action=view_type&amp;type_pk=8&amp;folder=1&amp;filtred=true">
                    Все
                    <span class="badge">699</span>
                </a>
            </li>
        </ul>
        <div style="height: 1px;background-color: #e7e7e7; margin: 15px; margin-top: 5px"></div>
        <?php
        echo GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $model,
                'columns' => $model->getGridSetting()['columns']
            ]
        );
        ?>
    </div>
</div>

<script>
    $(function () {
        var substringMatcherC = function () {
            return function findMatches(q, cb) {
                    $.getJSON('search/index', {
                        search: $("#searchByCompany").val(),
                        searchType: 'clients'
                    }, function (matches) {
                        searchs = true;
                        cb(matches);
                        //$('.tt-dropdown-menu').width($(window).width() - $('#search').offset()['left'] - 50);
                    });
            };
        };

        $('#searchByCompany').typeahead({
                hint: true,
                highlight: true,
                minLength: 3,
                async: true,
            },
            {
                name: 'states',
                source: substringMatcherC(),
                templates: {
                    suggestion: function (obj) {
                            return '<div>'
                                + ' ЛС № ' + obj['id']
                                + ' ' + obj['value']
                                + '</div>';

                    }
                }
            });
    });
</script>