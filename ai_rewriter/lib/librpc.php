<?php
// Protect against hack attempts
if (!defined('NGCMS')) die('HAL');

// Ensure core functions are loaded and RPC registered
@include_once extras_dir . '/ai_rewriter/ai_rewriter.php';
