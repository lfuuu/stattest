<?php if ($messageIdsForClose) : ?>
    <script>
        <?php
        foreach($messageIdsForClose as $closeMessageId) : ?>
        optools.socketPopup.closeMessage('<?= $closeMessageId ?>');
        <?php endforeach; ?>
          optools.socketPopup.checkNeedCloseTooltip();
    </script>
<?php endif; ?>
