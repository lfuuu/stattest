<?php
/**
 * Layout for Pjax load data preloader
 */
?>

<img id="preloader" src="/images/preloader.png">
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