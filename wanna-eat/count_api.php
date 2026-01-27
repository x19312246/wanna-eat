<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

// 請確認 connect.php 的路徑是否正確
require_once './assets/inc/connect.php'; 

$action = $_GET['action'] ?? '';
// 這裡要注意：AjaxData.postJson() 會送出 FormData，所以用 $_POST 接收
$store_id = $_POST['id'] ?? ''; 

if ($action === 'get') {
    $sql = "SELECT id, thumb FROM store";
    $result = connect_mysql($sql);
    $counts = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $counts[$row['id']] = $row['thumb'] ?? 0;
        }
    }
    echo json_encode($counts);
    exit;
}

if ($action === 'increment' && $store_id) {
    if (isset($_SESSION['voted_stores']) && in_array($store_id, $_SESSION['voted_stores'])) {
        echo json_encode(['status' => 'error', 'msg' => '這間店您點過囉！']);
        exit;
    }

    // 1. 更新資料庫
    $sql_update = "UPDATE store SET thumb = IFNULL(thumb, 0) + 1 WHERE id = '$store_id'";
    connect_mysql($sql_update);

    // 2. 取得最新數字
    $sql_get = "SELECT thumb FROM store WHERE id = '$store_id'";
    $res = connect_mysql($sql_get);
    $row = mysqli_fetch_assoc($res);

    if (!isset($_SESSION['voted_stores'])) $_SESSION['voted_stores'] = [];
    $_SESSION['voted_stores'][] = $store_id;

    echo json_encode(['status' => 'success', 'count' => $row['thumb']]);
    exit;
}