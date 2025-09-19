<?php
require_once __DIR__.'/../../includes/db.php';

$pending_count = $pdo->query("SELECT COUNT(*) FROM prayer_requests WHERE is_approved=0 AND deleted!=1")->fetchColumn();
echo $pending_count;
