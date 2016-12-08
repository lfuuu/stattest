<?php
/**
 * Layout for Pjax load data preloader
 */
?>

<img id="preloader" src="/images/preloader.png" style="position: fixed; z-index: 9999; left: calc(50% - 32px); top: calc(50% - 32px); display: none">
<script>
    $(function ()
    {
        $(document)
            .on('pjax:send', function ()
            {
                $('#preloader').show();
            })
            .on('pjax:complete', function ()
            {
                $('#preloader').hide();
            });
    });
</script>