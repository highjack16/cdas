<?php
require_once __DIR__ . '/auth.php';
session_destroy();
redirect('/cdas/index.php');
