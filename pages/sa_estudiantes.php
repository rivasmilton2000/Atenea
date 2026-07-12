<?php
require 'session.php';
confirm_logged_in();

header('Location: estudiantes.php');
exit;
