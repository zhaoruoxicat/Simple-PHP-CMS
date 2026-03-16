<?php
require_once __DIR__ . '/../core.php';
session_unset();
session_destroy();
redirect('login.php');
