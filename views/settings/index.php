
<div class="well text-center" style="width: 500px;">
    <?php if ($isLogOn) : ?>
        <a class="btn btn-info" href="/settings/set-log?isOn=0" role="button">Отключить логи AAA</a>
    <?php else: ?>
        <a class="btn btn-primary" href="/settings/set-log?isOn=1" role="button">Включить логи AAA</a>
    <?php endif; ?>
</div>