<?php

header('location: ' . substr($_SERVER['REQUEST_URI'] , 9));
exit;