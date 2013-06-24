<?php

if(isset($_GET["data"]) && $_GET["data"])
{
    include "../libs/qr/qrlib.php";

    QRcode::png(trim($_GET["data"]), false, "H", 4,2);
}
