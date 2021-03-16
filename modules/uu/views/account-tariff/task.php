<script>
    function render(d) {
        let bgClass = 'info progress-bar-animated'; // status = plan
        if (d.status == 'run') {
            bgClass = 'warning';
        } else if (d.status == 'stoped') {
            bgClass = 'danger';
        } else if (d.status == 'done') {
            bgClass = 'success';
        }

        let perc = d.status == 'plan' ? 100 : Math.round(d.count_done / (d.count_all / 100));
        let percView = perc;

        if (percView < 10) {
            percView = 10;
        }

        $('#task_header').html(
            '<div class="progress">\n' +
            '  <div class="progress-bar progress-bar-' + bgClass + '" role="progressbar" style="width: ' + percView + '%" aria-valuenow="' + d.count_done + '" aria-valuemin="0" aria-valuemax="' + d.count_all + '">' + (d.status != 'plan' ? '<b>' + perc + '% </b><span style=\'padding-left: 5px;\'>' + d.count_done + ' / ' + d.count_all + '</span>' : 'Запланировано') + '</div>\n' +
            '</div>');
        $('#task_cont').html(d.progress
            .replace(/(success)/g, '<b style=\'color: green;\'>Выполнено</b>')
            .replace(/(error)/g, '<b style=\'color: #c40000;\'>Ошибка</b>')
        );
        $('#task_cont')[0].scrollTop = $('#task_cont')[0].scrollHeight;
    }

    function tick(taskid) {
        $.get('/task/get', {id: taskid}, function (answer, status) {
            if (status != 'success') {
                alert(status);
                return;
            }

            if (!answer) {
                return;
            }

            if (answer.status == 'run' || answer.status == 'plan') {
                setTimeout(function () {
                    tick(taskid);
                }, 1000);
            }

            render(answer);
        });
    }

    $(document).ready(function () {
        tick(<?=$taskId?>);
    });
</script>
<div id="task_contener">
    <div id="task_header"></div>
    <div id="task_cont" style="max-height: 200px; overflow-y: scroll;"></div>
</div>