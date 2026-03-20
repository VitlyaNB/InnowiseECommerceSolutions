<?php

use DG\BypassFinals;

require_once dirname(__DIR__).'/vendor/autoload.php';

if (class_exists('DG\BypassFinals')) {
    BypassFinals::enable();
}
