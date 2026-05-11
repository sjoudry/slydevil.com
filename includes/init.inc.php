<?php

use SlyDevil\Env;
use SlyDevil\Session;

include_once(__DIR__ . '/autoload.inc.php');

Session::continueSession();
Env::loadEnv();
