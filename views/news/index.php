<div class="row" style="margin-right: 0;">
    <div class="col-sm-12" id="newsBlock">

        <?php foreach ($news as $item)
            echo $this->render('_message', ['item' => $item]);
         ?>

    </div>
</div>
<div class="row" style="margin-right: 0; margin-top: 30px;">
    <div class="col-sm-12">
        <form id="sendMessageForm">
        <div class="row" style="margin-right: 0;">
            <div class="col-sm-2">
                <?= \kartik\widgets\Select2::widget([
                    'name' => 'to_user_id',
                    'data' => [0 => 'Всем'] + \yii\helpers\ArrayHelper::map(\app\models\User::find()->where(['enabled' => 'yes'])->all(), 'id', 'name'),
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
                <button type="submit" id="buttonSave" class="btn btn-default" name="save">Отправить</button>
            </div>
        </div>
        </form>
    </div>
</div>
<script>
    function getLastNews()
    {
        var lastId = getLastNewsId();
        $.get('/news/last', {lastId:lastId}, function(data){
            $('#newsBlock').prepend(data);
            $('.layout_main').animate({scrollTop: 0}, 'slow');
        });
        localStorage.setItem("lastNewsId", lastId);
    }

    function setUnreadMessages()
    {
        var lastId = localStorage.getItem("lastNewsId");
        $('#newsBlock>.row').each(function(){
            if($(this).data('id') > lastId)
                $(this).addClass('unread-msg');
            else
                $(this).removeClass('unread-msg');
        });
    }

    function getLastNewsId()
    {
        return $('#newsBlock>.row').first().data('id');
    }

    $(document).ready(function () {
        $('.layout_main').animate({scrollTop: 0}, 'slow');

        setUnreadMessages();
        $('#newsBlock>.row.unread-msg').on('click', function(){
                localStorage.setItem("lastNewsId", $(this).data('id'));
                setUnreadMessages();
            }
        );

        $('#sendMessageForm').on('submit', function(e){
            e.preventDefault();
            e.stopImmediatePropagation();
            var params = {
                message: $('#news-form-message').val(),
                to_user_id: $('#news-form-to-user-id').val(),
                priority: $('#news-form-priority').val(),
            };
            $.post('/news/create',params, function(data){
                if(data['status'] == 'ok') {
                    getLastNews();
                    $('#news-form-message').val('')
                }
            }, 'json');
            return false;
        });
    });
</script>

<style>
    .unread-msg{
        background-color: rgb(168, 255, 248);
    }
</style>

