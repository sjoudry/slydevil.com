<?php

use SlyDevil\Site\Main;

include_once(__DIR__ . '/Form/autoload.inc.php');
include_once(__DIR__ . '/Site/autoload.inc.php');

$main = new Main();
$main->getSessionManager()->continueSession();
