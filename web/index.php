<?php

use SlyDevil\Site\Login;

include_once(__DIR__ . "/../includes/init.inc.php");

$login = new Login();
$login->setPasswordSeed(PASSWORD_SEED);
$login->handle('dashboard', '/dashboard/');
