<?php

include "../php/functions.php";
include "../Database.php";
session_start();

check_settings();

$id = issetget('id', '');

header('Content-Type: text/html; charset=UTF-8');
echo projectEditorMarkup($id);
