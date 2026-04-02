<?php
require_once __DIR__ . '/includes/helpers.php';

session_destroy();
session_start();
set_flash('success', 'Bạn đã đăng xuất khỏi hệ thống.');
redirect('login.php');