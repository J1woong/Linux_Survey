<?php
// MySQL 데이터베이스 연결 설정
$servername = "localhost";
$username = "root";
$password = ""; // 기본 MySQL 비밀번호 설정
$dbname = "survey_db"; // 사용할 데이터베이스 이름

// MySQL 데이터베이스 연결
$conn = new mysqli($servername, $username, $password, $dbname);

// 연결 확인
if ($conn->connect_error) {
  die("연결 실패: " . $conn->connect_error);
}

// POST로 받은 데이터 처리
$favorite_color = $_POST['favorite_color'];
$coffee_per_day = $_POST['coffee_per_day'];
$weekend_activity = isset($_POST['weekend_activity']) ? implode(", ", $_POST['weekend_activity']) : '';

// 데이터베이스에 설문 데이터 삽입
$sql = "INSERT INTO survey_responses (favorite_color, coffee_per_day, weekend_activity)
VALUES ('$favorite_color', '$coffee_per_day', '$weekend_activity')";

if ($conn->query($sql) === TRUE) {
  echo "설문조사 결과가 저장되었습니다!";
} else {
  echo "오류: " . $sql . "<br>" . $conn->error;
}

// 연결 종료
$conn->close();
?>
