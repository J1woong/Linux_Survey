<?php
// 데이터베이스 연결
$servername = "localhost";
$username = "surveyuser"; // 새로 만든 사용자명
$password = "wldndEhd1!"; // 비밀번호
$dbname = "survey_db";

// MySQL 연결
$conn = new mysqli($servername, $username, $password, $dbname);

// 연결 확인
if ($conn->connect_error) {
    exit(); // 스크립트 중단
}

// AJAX 삭제 요청 처리
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_sql = "DELETE FROM survey_responses WHERE id = $delete_id";
    
    if ($conn->query($delete_sql) === TRUE) {
        // 삭제 후 업데이트된 통계 정보 가져오기
        $stats_sql = "SELECT * FROM survey_responses ORDER BY id ASC";
        $stats_result = $conn->query($stats_sql);
        
        $total_responses = $stats_result->num_rows;
        $color_stats = array();
        $coffee_stats = array();
        
        if ($total_responses > 0) {
            while($row = $stats_result->fetch_assoc()) {
                // 색상 통계
                if(isset($color_stats[$row['favorite_color']])) {
                    $color_stats[$row['favorite_color']]++;
                } else {
                    $color_stats[$row['favorite_color']] = 1;
                }
                
                // 커피 통계
                if(isset($coffee_stats[$row['coffee_per_day']])) {
                    $coffee_stats[$row['coffee_per_day']]++;
                } else {
                    $coffee_stats[$row['coffee_per_day']] = 1;
                }
            }
            
            $popular_color = array_keys($color_stats, max($color_stats))[0];
            $popular_coffee = array_keys($coffee_stats, max($coffee_stats))[0];
        } else {
            $popular_color = "데이터 없음";
            $popular_coffee = "0";
        }
        
        echo json_encode([
            'status' => 'success', 
            'message' => '삭제되었습니다.',
            'stats' => [
                'total_responses' => $total_responses,
                'popular_color' => $popular_color,
                'popular_coffee' => $popular_coffee
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => '삭제 중 오류가 발생했습니다.']);
    }
    $conn->close();
    exit();
}

// POST 데이터 처리
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 설문 데이터 받기
    $favorite_color = isset($_POST['favorite_color']) ? $_POST['favorite_color'] : null;
    $coffee_per_day = isset($_POST['coffee_per_day']) ? $_POST['coffee_per_day'] : null;
    $weekend_activity = isset($_POST['weekend_activity']) ? implode(", ", $_POST['weekend_activity']) : null;
    
    // SQL 쿼리 준비
    $sql = "INSERT INTO survey_responses (favorite_color, coffee_per_day, weekend_activity)
            VALUES ('$favorite_color', '$coffee_per_day', '$weekend_activity')";
    
    // 쿼리 실행
    if ($conn->query($sql) === TRUE) {
        // 기존 설문 결과 조회
        $result_sql = "SELECT * FROM survey_responses ORDER BY id ASC";
        $result = $conn->query($result_sql);
        
        // 성공 메시지와 설문 결과 표시
        echo "<!DOCTYPE html>
        <html lang='ko'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>설문조사 완료</title>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 20px;
                    min-height: 100vh;
                }
                .container {
                    max-width: 1200px;
                    margin: 0 auto;
                }
                .success-container {
                    background-color: white;
                    padding: 40px;
                    border-radius: 10px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    text-align: center;
                    margin-bottom: 30px;
                }
                .success-icon {
                    font-size: 60px;
                    color: #4CAF50;
                    margin-bottom: 20px;
                }
                h1 {
                    color: #4CAF50;
                    margin-bottom: 20px;
                }
                p {
                    color: #666;
                    font-size: 16px;
                    line-height: 1.5;
                }
                .back-button {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 10px 20px;
                    background-color: #4CAF50;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    transition: background-color 0.3s;
                }
                .back-button:hover {
                    background-color: #45a049;
                }
                .results-container {
                    background-color: white;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                }
                .results-title {
                    color: #333;
                    margin-bottom: 25px;
                    text-align: center;
                    border-bottom: 2px solid #4CAF50;
                    padding-bottom: 10px;
                }
                .results-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                .results-table th {
                    background-color: #4CAF50;
                    color: white;
                    padding: 12px;
                    text-align: left;
                    font-weight: bold;
                }
                .results-table td {
                    padding: 12px;
                    border-bottom: 1px solid #ddd;
                }
                .results-table tr:nth-child(even) {
                    background-color: #f9f9f9;
                }
                .results-table tr:hover {
                    background-color: #f5f5f5;
                }
                .no-results {
                    text-align: center;
                    color: #666;
                    font-style: italic;
                    padding: 20px;
                }
                .stats-container {
                    display: flex;
                    gap: 20px;
                    margin-bottom: 20px;
                    flex-wrap: wrap;
                }
                .stat-box {
                    flex: 1;
                    background-color: #f8f9fa;
                    padding: 15px;
                    border-radius: 8px;
                    text-align: center;
                    min-width: 150px;
                }
                .stat-number {
                    font-size: 24px;
                    font-weight: bold;
                    color: #4CAF50;
                }
                .stat-label {
                    color: #666;
                    font-size: 14px;
                }
                .delete-btn {
                    background-color: #f44336;
                    color: white;
                    border: none;
                    padding: 6px 12px;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 12px;
                    transition: background-color 0.3s;
                }
                .delete-btn:hover {
                    background-color: #da190b;
                }
                .delete-btn:disabled {
                    background-color: #ccc;
                    cursor: not-allowed;
                }
                .alert {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 15px 20px;
                    border-radius: 5px;
                    color: white;
                    font-weight: bold;
                    z-index: 1000;
                    display: none;
                }
                .alert.success {
                    background-color: #4CAF50;
                }
                .alert.error {
                    background-color: #f44336;
                }
            </style>
        </head>
        <body>
            <div class='alert' id='alert'></div>
            <div class='container'>
                <div class='success-container'>
                    <div class='success-icon'>✓</div>
                    <h1>설문조사가 완료되었습니다!</h1>
                    <p>소중한 의견을 주셔서 감사합니다.<br>
                    귀하의 답변이 성공적으로 저장되었습니다.</p>
                    <a href='javascript:history.back()' class='back-button'>새 설문 작성하기</a>
                </div>
                
                <div class='results-container'>
                    <h2 class='results-title'>📊 설문조사 전체 결과</h2>";
        
        if ($result && $result->num_rows > 0) {
            // 통계 정보 계산
            $total_responses = $result->num_rows;
            $color_stats = array();
            $coffee_stats = array();
            $activity_stats = array();
            
            // 통계를 위해 데이터를 다시 조회
            $stats_result = $conn->query($result_sql);
            while($row = $stats_result->fetch_assoc()) {
                // 색상 통계
                if(isset($color_stats[$row['favorite_color']])) {
                    $color_stats[$row['favorite_color']]++;
                } else {
                    $color_stats[$row['favorite_color']] = 1;
                }
                
                // 커피 통계
                if(isset($coffee_stats[$row['coffee_per_day']])) {
                    $coffee_stats[$row['coffee_per_day']]++;
                } else {
                    $coffee_stats[$row['coffee_per_day']] = 1;
                }
                
                // 활동 통계
                if(isset($activity_stats[$row['weekend_activity']])) {
                    $activity_stats[$row['weekend_activity']]++;
                } else {
                    $activity_stats[$row['weekend_activity']] = 1;
                }
            }
            
            // 통계 박스 표시
            $popular_color = array_keys($color_stats, max($color_stats))[0];
            $popular_coffee = array_keys($coffee_stats, max($coffee_stats))[0];
            
            // 색상 한글 변환
            $color_korean = '';
            switch($popular_color) {
                case 'Red': $color_korean = '빨강'; break;
                case 'Blue': $color_korean = '파랑'; break;
                case 'Green': $color_korean = '초록'; break;
                default: $color_korean = $popular_color; break;
            }
            
            echo "<div class='stats-container'>
                    <div class='stat-box'>
                        <div class='stat-number' id='total-responses'>$total_responses</div>
                        <div class='stat-label'>총 응답 수</div>
                    </div>
                    <div class='stat-box'>
                        <div class='stat-number' id='popular-color'>$color_korean</div>
                        <div class='stat-label'>인기 색상</div>
                    </div>
                    <div class='stat-box'>
                        <div class='stat-number' id='popular-coffee'>{$popular_coffee}잔</div>
                        <div class='stat-label'>평균 커피 섭취량</div>
                    </div>
                  </div>";
            
            // 테이블 표시
            echo "<table class='results-table' id='resultsTable'>
                    <thead>
                        <tr>
                            <th>번호</th>
                            <th>좋아하는 색상</th>
                            <th>하루 커피 섭취량</th>
                            <th>주말 활동</th>
                            <th>응답 시간</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody>";
            
            $counter = 1;
            // 데이터를 다시 조회해서 테이블에 표시
            $table_result = $conn->query($result_sql);
            while($row = $table_result->fetch_assoc()) {
                $color_korean = '';
                switch($row['favorite_color']) {
                    case 'Red': $color_korean = '빨강'; break;
                    case 'Blue': $color_korean = '파랑'; break;
                    case 'Green': $color_korean = '초록'; break;
                    default: $color_korean = $row['favorite_color']; break;
                }
                
                $activity_korean = '';
                switch($row['weekend_activity']) {
                    case 'Sports': $activity_korean = '운동하기'; break;
                    case 'Movie': $activity_korean = '영화 보기'; break;
                    case 'Reading': $activity_korean = '책 읽기'; break;
                    default: $activity_korean = $row['weekend_activity']; break;
                }
                
                $created_at = isset($row['created_at']) ? date('Y-m-d H:i', strtotime($row['created_at'])) : '시간 정보 없음';
                
                echo "<tr id='row-{$row['id']}'>
                        <td>$counter</td>
                        <td>$color_korean</td>
                        <td>{$row['coffee_per_day']}잔</td>
                        <td>$activity_korean</td>
                        <td>$created_at</td>
                        <td>
                            <button class='delete-btn' onclick='deleteResponse({$row['id']})'>삭제</button>
                        </td>
                      </tr>";
                $counter++;
            }
            echo "</tbody></table>";
        } else {
            echo "<div class='no-results'>아직 설문 결과가 없습니다.</div>";
        }
        
        echo "      </div>
            </div>
            
            <script>
                function deleteResponse(id) {
                    if (confirm('정말로 이 응답을 삭제하시겠습니까?')) {
                        // 삭제 버튼 비활성화
                        const button = event.target;
                        button.disabled = true;
                        button.textContent = '삭제 중...';
                        
                        // AJAX 요청
                        fetch('?delete_id=' + id)
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    // 행 삭제 애니메이션
                                    const row = document.getElementById('row-' + id);
                                    row.style.transition = 'opacity 0.3s ease';
                                    row.style.opacity = '0';
                                    
                                    setTimeout(() => {
                                        row.remove();
                                        showAlert(data.message, 'success');
                                        updateRowNumbers();
                                        updateStatistics(data.stats);
                                    }, 300);
                                } else {
                                    showAlert(data.message, 'error');
                                    button.disabled = false;
                                    button.textContent = '삭제';
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                showAlert('네트워크 오류가 발생했습니다.', 'error');
                                button.disabled = false;
                                button.textContent = '삭제';
                            });
                    }
                }
                
                function updateStatistics(stats) {
                    // 총 응답 수 업데이트
                    document.getElementById('total-responses').textContent = stats.total_responses;
                    
                    // 인기 색상 업데이트 (한글로 변환)
                    let colorKorean = '';
                    switch(stats.popular_color) {
                        case 'Red': colorKorean = '빨강'; break;
                        case 'Blue': colorKorean = '파랑'; break;
                        case 'Green': colorKorean = '초록'; break;
                        default: colorKorean = stats.popular_color; break;
                    }
                    document.getElementById('popular-color').textContent = colorKorean;
                    
                    // 인기 커피 섭취량 업데이트
                    document.getElementById('popular-coffee').textContent = stats.popular_coffee + '잔';
                    
                    // 모든 데이터가 삭제된 경우 처리
                    if (stats.total_responses === 0) {
                        document.getElementById('popular-color').textContent = '데이터 없음';
                        document.getElementById('popular-coffee').textContent = '0잔';
                        
                        // 테이블을 '데이터 없음' 메시지로 교체
                        const tableContainer = document.querySelector('.results-container');
                        const table = document.getElementById('resultsTable');
                        if (table) {
                            table.innerHTML = \"<div class='no-results'>아직 설문 결과가 없습니다.</div>\";
                        }
                    }
                }
                
                function showAlert(message, type) {
                    const alert = document.getElementById('alert');
                    alert.textContent = message;
                    alert.className = 'alert ' + type;
                    alert.style.display = 'block';
                    
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 3000);
                }
                
                function updateRowNumbers() {
                    const rows = document.querySelectorAll('#resultsTable tbody tr');
                    rows.forEach((row, index) => {
                        row.cells[0].textContent = index + 1;
                    });
                }
            </script>
        </body>
        </html>";
        
    } else {
        // SQL 쿼리 오류 메시지 출력
        echo "SQL 오류: " . $conn->error . "<br>";
        echo "실행된 쿼리: " . $sql . "<br>";
    }
    
    // 연결 종료
    $conn->close();
}
?>