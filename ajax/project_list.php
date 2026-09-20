<?php

include "../php/functions.php";
include "../Database.php";
session_start();

check_settings();

$selected = issetget('selected', '');

header('Content-Type: text/html; charset=UTF-8');
echo renderOpenProjectsList($selected);
