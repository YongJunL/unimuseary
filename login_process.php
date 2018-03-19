<?php
//세션 설정
session_start();

//DB 설정
//$id = 'admin';
//$pwd = '1388413j';
require("config/config.php");
require("lib/db.php");
$conn = db_init($config["host"], $config["duser"], $config["dpw"], $config["dname"]);

$sql = "SELECT * FROM tb_user WHERE id = '".$_POST['id']."' AND password = '".$_POST['pwd']."'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$rowCnt = mysqli_num_rows($result);
$id = $row['id'];
$pwd = $row['password'];
//로그인 정보 확인
if(!empty($_POST['id']) && !empty($_POST['pwd'])){
    if( $rowCnt == 1 ){
        $_SESSION['is_login'] = true;
        $_SESSION['id'] = $id;
        $_SESSION['password'] = $pwd;
        $_SESSION['row'] = $rowCnt;

        header('Location: ./dungyees.php');
        exit;
    }
}
echo '로그인 하지 못했습니다.';
?>
