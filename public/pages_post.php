<?php
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: '.base_url('index.php')); exit; }
check_csrf();

$do = $_POST['do'] ?? '';

switch ($do) {
    case 'login':
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { flash('error', 'Invalid email.'); header('Location: '.base_url('index.php?view=login')); exit; }
        // check if exists
        $st = $pdo->prepare('SELECT * FROM users WHERE email=? LIMIT 1');
        $st->execute([$email]);
        $user = $st->fetch();
        if ($user) {
            if (!password_verify($password, $user['password_hash'])) {
                flash('error', 'Wrong password.'); header('Location: '.base_url('index.php?view=login')); exit;
            }
        } else {
            // Create on first login
            $st = $pdo->prepare('INSERT INTO users (email, password_hash, created_at) VALUES (?,?,NOW())');
            $st->execute([$email, password_hash($password, PASSWORD_BCRYPT)]);
            $id = $pdo->lastInsertId();
            $st = $pdo->prepare('SELECT * FROM users WHERE id=?');
            $st->execute([$id]); $user = $st->fetch();
        }
        $_SESSION['user'] = $user;
        flash('success', 'Welcome back!');
        header('Location: '.base_url('index.php'));
        exit;

    case 'save_account':
        require_login();
        $u = current_user();
        $st = $pdo->prepare('UPDATE users SET first_name=?, zipcode=?, include_location=?, enable_email_comm=?, enable_phone_comm=? WHERE id=?');
        $st->execute([
            trim($_POST['first_name'] ?? ''),
            trim($_POST['zipcode'] ?? ''),
            isset($_POST['include_location']) ? 1 : 0,
            isset($_POST['enable_email_comm']) ? 1 : 0,
            isset($_POST['enable_phone_comm']) ? 1 : 0,
            $u['id']
        ]);
        // Refresh session
        $st = $pdo->prepare('SELECT * FROM users WHERE id=?'); $st->execute([$u['id']]); $_SESSION['user'] = $st->fetch();
        flash('success', 'Account saved.');
        header('Location: '.base_url('index.php?view=account'));
        exit;

    case 'create_request':
        require_login();
        $u = current_user();
        $content = trim($_POST['content'] ?? '');
        if ($content === '' || mb_strlen($content) > 1000) { flash('error','Invalid request.'); header('Location: '.base_url('index.php?view=request')); exit; }
        $st = $pdo->prepare('INSERT INTO prayer_requests (user_id, content, is_anonymous, is_approved, is_answered, like_count, comments_count, created_at) VALUES (?,?,?,?,?,?,?,NOW())');
        $st->execute([$u['id'], $content, isset($_POST['is_anonymous'])?1:0, 0, 0, 0, 0]);
        flash('success','Your prayer has been submitted for review.');
        header('Location: '.base_url('index.php'));
        exit;

    case 'pray':
        $rid = (int)($_POST['request_id'] ?? 0);
        if ($rid <= 0) { header('Location: '.base_url('index.php')); exit; }
        $uid = current_user()['id'] ?? null;
        if ($uid) {
            // prevent duplicate by same user
            $st = $pdo->prepare('SELECT 1 FROM prayer_prays WHERE request_id=? AND user_id=?');
            $st->execute([$rid, $uid]);
            if (!$st->fetch()) {
                $st = $pdo->prepare('INSERT INTO prayer_prays (request_id, user_id, ip_address, created_at) VALUES (?,?,?,NOW())');
                $st->execute([$rid, $uid, $_SERVER['REMOTE_ADDR'] ?? '']);
                $pdo->prepare('UPDATE prayer_requests SET like_count = like_count + 1 WHERE id=?')->execute([$rid]);
            }
        } else {
            // guest allowed
            $st = $pdo->prepare('INSERT INTO prayer_prays (request_id, user_id, ip_address, created_at) VALUES (?,?,?,NOW())');
            $st->execute([$rid, null, $_SERVER['REMOTE_ADDR'] ?? '']);
            $pdo->prepare('UPDATE prayer_requests SET like_count = like_count + 1 WHERE id=?')->execute([$rid]);
        }
        header('Location: '.base_url('index.php?view=detail&id='.$rid));
        exit;

    case 'add_comment':
        require_login();
        $rid = (int)($_POST['request_id'] ?? 0);
        $cid = (int)($_POST['canned_id'] ?? 0);
        if ($rid<=0 || $cid<=0) { header('Location: '.base_url('index.php')); exit; }
        $opt = $pdo->prepare('SELECT text FROM canned_comments WHERE id=?');
        $opt->execute([$cid]);
        $t = $opt->fetchColumn();
        if (!$t) { header('Location: '.base_url('index.php?view=detail&id='.$rid)); exit; }
        $st = $pdo->prepare('INSERT INTO prayer_comments (request_id, user_id, comment_text, created_at) VALUES (?,?,?,NOW())');
        $st->execute([$rid, current_user()['id'], $t]);
        $pdo->prepare('UPDATE prayer_requests SET comments_count = comments_count + 1 WHERE id=?')->execute([$rid]);
        flash('success', 'Comment added.');
        header('Location: '.base_url('index.php?view=detail&id='.$rid));
        exit;

    case 'flag':
        require_login();
        $rid = (int)($_POST['request_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        if ($rid<=0 || $reason==='') { header('Location: '.base_url('index.php?view=detail&id='.$rid)); exit; }
        $st = $pdo->prepare('INSERT INTO prayer_flags (request_id, user_id, reason, created_at) VALUES (?,?,?,NOW())');
        $st->execute([$rid, current_user()['id'], $reason]);
        flash('success','Thanks for the report. Our moderators will review it.');
        header('Location: '.base_url('index.php?view=detail&id='.$rid));
        exit;

    case 'approve':
        if (!is_admin()) { die('Forbidden'); }
        $rid = (int)($_POST['request_id'] ?? 0);
        if (isset($_POST['reject'])) {
            $pdo->prepare('DELETE FROM prayer_requests WHERE id=?')->execute([$rid]);
            flash('success', 'Request rejected and removed.');
        } else {
            $pdo->prepare('UPDATE prayer_requests SET is_approved=1 WHERE id=?')->execute([$rid]);
            flash('success', 'Request approved.');
        }
        header('Location: '.base_url('index.php?view=account'));
        exit;

    default:
        header('Location: '.base_url('index.php'));
        exit;
}
