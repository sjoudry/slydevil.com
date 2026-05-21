<?php

use SlyDevil\Site\Main;

include_once(__DIR__ . '/../../includes/init.inc.php');

$main = new Main();
$main->getLogin()->clearLogin();
