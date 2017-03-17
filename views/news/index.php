<div class="row right-indent">
    <div class="col-sm-12" id="newsBlock">

        <?php foreach ($news as $item)
            echo $this->render('_message', ['item' => $item]);
         ?>

    </div>
</div>
<div class="row news-block">
    <div class="col-sm-12">
        <form id="sendMessageForm">
        <div class="row right-indent">
            <div class="col-sm-2">
                <?= \kartik\widgets\Select2::widget([
                    'name' => 'to_user_id',
                    'data' => [0 => 'Всем'] + \app\models\User::getListTrait(
                            $isWithEmpty = false,
                            $isWithNullAndNotNull = false,
                            $indexBy = 'id',
                            $select = 'CONCAT(name, " (", user, ")")',
                            $orderBy = ['name' => SORT_ASC],
                            $where = ['enabled' => 'yes']
                        ),
                    'value' => 0,
                    'options' => ['placeholder' => 'Начните вводить фамилию'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);
                ?>
            </div>
            <div class="col-sm-2">
                <select class="form-control" name="priority" id="news-form-priority">
                    <option value="usual">Средней важности</option>
                    <option value="important">Важное</option>
                    <option value="unimportant">Не важное</option>
                </select>
            </div>
            <div class="col-sm-6">
                <textarea name="message" class="form-control" id="news-form-message"></textarea>
            </div>
            <div class="col-sm-2">
                <button type="submit" id="buttonSave" class="btn btn-primary" name="save">Отправить</button>
            </div>
        </div>
        </form>
    </div>
</div>
