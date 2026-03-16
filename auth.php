<?php
require_once __DIR__ . '/core.php';

if (!is_logged_in()) {
    redirect('login.php');
}
