<div class="row" style="margin-right: 0; padding: 3px 0;" data-id="<?= $item->id ?>">
    <div class="col-sm-2" style="text-align: right">
        <?= $item->user->name ?><br/>
        (<?= $item->date ?>)
    </div>
    <div class="col-sm-10" style="border: 1px solid #cdcdcd; background: #f5f5f5; border-radius: 5px; padding: 10px; ">
        <?= $item->message ?>
    </div>
</div>