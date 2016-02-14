<?php

include_once "../../config.php";

header('Content-type: application/ogg');
header('Content-Length: '.filesize($CFG->dataroot.$_GET['audio']));
readfile($CFG->dataroot.$_GET['audio']);

