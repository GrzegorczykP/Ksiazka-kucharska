<?php
error_reporting(E_ERROR);
session_start();
session_unset();
session_destroy();
require_once('functions.php');

infoPage('Wylogowano pomyślnie.');