<?php
  //세션 설정
  session_start();

  //DB 설정
  require("config/config.php");
  require("lib/db.php");
  $conn = db_init($config["host"], $config["duser"], $config["dpw"], $config["dname"]);
  if (!$conn->set_charset("utf8")) {
    $log = "utf8 문자 세트를 가져오다가 에러가 났습니다 : %s\n";
  } else {
    $log = "굿드";
  }

  //날짜
  $dates = $_POST['dates'];
  $year = substr($dates, 0, 4);
  $month = substr($dates, 4, 2);
  $date = substr($dates, 6, 2);

  //세션
  $id = $_POST['id'];
  $password = $_POST['password'];

  //value
  $valueEvent = mysqli_real_escape_string($conn, $_POST['event']);
  $valueWork = mysqli_real_escape_string($conn, $_POST['work']);
  $valueCheck = $_POST['check'];

  //$sql 조회
  $sqlSlt = "SELECT user_key FROM tb_user WHERE id = '".$id."' AND password = '".$password."'";
  $resultSlt = mysqli_query($conn, $sqlSlt);
  $row = mysqli_fetch_assoc($resultSlt);
  $userKey = $row['user_key'];

  //$sql 등록
  $sqlUdt = array();
  $sqlUdt[0]  = "REPLACE INTO tb_calendar (year,month,date,type,context,user_key) "
               ."VALUES('".$year."','".$month."','".$date."','event','".$valueEvent."',".$userKey.")";
  $sqlUdt[1]  = "REPLACE INTO tb_calendar (year,month,date,type,context,user_key) "
               ."VALUES('".$year."','".$month."','".$date."','work','".$valueWork."',".$userKey.")";
  for( $i=0 ; $i<count($valueCheck) ; $i++ ) { 
    $sqlUdt[$i+2] = "REPLACE INTO tb_calendar (year,month,date,type,context,user_key) "
                   ."VALUES('".$year."','".$month."','".$date."','check".($i+1)."','".$valueCheck[$i]."',".$userKey.")";
  }
 
  //update
  for( $i=0 ; $i<count($sqlUdt) ; $i++ ) { 
    $resultUdt = mysqli_query($conn, $sqlUdt[$i]);
  }
  
  $sql = "SELECT context FROM tb_calendar WHERE year = '".$year."' AND month = '".$month."' AND date = '".$date."' AND type ='event'";
  $result = mysqli_query($conn, $sql);
  $row2 = mysqli_fetch_assoc($result);
  $context = $row['context'];
  $enc1 = mb_detect_encoding($valueEvent, "EUC-KR, UTF-8, ASCII");
  $enc2 = mb_detect_encoding($context, "EUC-KR, UTF-8, ASCII");
  $enc3 = mb_detect_encoding($sqlUdt[0], "EUC-KR, UTF-8, ASCII");

  echo "ajax success : ".$enc1.", ".$enc2.", ".$enc3.", ".$log;
?>
