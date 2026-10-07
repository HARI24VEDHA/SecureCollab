<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['attachment'])) {
    $pid  = intval($_POST['project_id'] ?? 0);
    $file = $_FILES['attachment'];
    if ($pid > 0 && $file['error'] === UPLOAD_ERR_OK) {
        $orig = basename($file['name']);
        $ext  = pathinfo($orig, PATHINFO_EXTENSION);
        $name = uniqid('file_') . '.' . $ext;
        $dir  = __DIR__ . '/../assets/uploads/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        if (move_uploaded_file($file['tmp_name'], $dir . $name)) {
            $db = get_db();
            $db->prepare("INSERT INTO files (project_id, user_id, filename, original_name, filesize, filetype) VALUES (?,?,?,?,?,?)")
               ->execute([$pid, $_SESSION['user_id'], $name, $orig, $file['size'], $file['type']]);
            log_activity('File Uploaded', $orig);
            header("Location: index.php?project_id=$pid&msg=File+uploaded+successfully.");
            exit;
        }
    }
}
header('Location: index.php');
exit;