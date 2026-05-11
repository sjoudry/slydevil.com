<?php

use SlyDevil\Login;

include_once(__DIR__ . "/../includes/init.inc.php");

Login::handleLogin('dashboard', '/dashboard/');
