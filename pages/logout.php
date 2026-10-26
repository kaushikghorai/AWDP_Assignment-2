<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

session_destroy();
session_start();
setFlashMessage('success', 'You have been logged out successfully.');
redirect(SITE_URL . '/index.php');
