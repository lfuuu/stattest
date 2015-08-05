<?php
use kartik\grid\GridView;
use app\classes\grid\account\AccountGridFolder;

/** @var AccountGridFolder $activeFolder */

?>

<div class="row">
    <div class="col-sm-12">
        <ul class="nav nav-pills">
            <?php foreach ($activeFolder->grid->getFolders() as $folder): ?>
                <?php $urlParams = array_merge(Yii::$app->request->get(), ['client/grid2', 'folderId' => $folder->getId()]); ?>
                <li class="<?= $activeFolder->getId() == $folder->getId() ? 'active' : '' ?>">
                    <a href="<?= \yii\helpers\Url::toRoute($urlParams) ?>">
                        <?php
                            echo $folder->getName();
                            $count = $folder->getCount();
                            if ($count !== null) {
                                echo " ($count)";
                            }
                        ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <div style="height: 1px;background-color: #e7e7e7; margin: 5px 0px;"></div>
        <?php
        echo GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $activeFolder,
                'columns' => $activeFolder->getColumns(),
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