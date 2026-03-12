<?php
header('Content-Type: application/json; charset=utf-8');
$file = 'mindmap.json';

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'load') {
    if (file_exists($file)) {
        echo file_get_contents($file);
    } else {
        echo json_encode([
            "meta" => ["name" => "mindmap", "version" => "1.0"],
            "format" => "node_tree",
            "data" => ["id" => "root", "topic" => "신규 마인드맵"]
        ]);
    }
} 
elseif ($action === 'save') {
    // 1. 통신 방식 검증 (리다이렉션으로 인해 GET으로 바뀌었는지 확인)
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(["status" => "error", "message" => "요청 방식 오류: " . $_SERVER['REQUEST_METHOD'] . " (http -> https 리다이렉션으로 인해 데이터가 유실되었을 확률이 높습니다)"]);
        exit;
    }

    // 2. 데이터 수신
    $data = file_get_contents('php://input');
    
    // 3. 빈 데이터 검증
    if (empty($data)) {
        echo json_encode(["status" => "error", "message" => "빈 데이터가 서버로 전달되었습니다."]);
        exit;
    }

    // 4. JSON 형태 검증 및 저장
    json_decode($data);
    if (json_last_error() === JSON_ERROR_NONE) {
        $write_result = file_put_contents($file, $data);
        if ($write_result === false) {
            echo json_encode(["status" => "error", "message" => "파일 쓰기 권한이 없습니다. 터미널에서 chmod 666 mindmap.json 을 실행해주세요."]);
        } else {
            echo json_encode(["status" => "success"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "JSON 파싱 에러: " . json_last_error_msg()]);
    }
}
?>