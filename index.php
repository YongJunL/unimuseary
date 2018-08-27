<?php
  //세션 설정
  session_start();
  if ( !isset($_SESSION['is_login']) ) {
    header("Location: ./login.html");
  }
  $timeout = 100;                        // Set timeout minutes 
  $logout_redirect_url = "./login.html"; // Set logout URL 
  $timeout = $timeout*60;                // Converts minutes to seconds 
  if ( isset($_SESSION['start_time']) ) { 
    $elapsed_time = time()-$_SESSION['start_time']; 
    if ( $elapsed_time >= $timeout ) { 
      session_destroy(); 
      header("Location: $logout_redirect_url");
    } 
  } 
  $_SESSION['start_time'] = time(); 

  //DB 설정
  require("config/config.php");
  require("lib/db.php");
  $conn = db_init($config['host'], $config['duser'], $config['dpw'], $config['dname']);
?>
<!DOCTYPE html>
<html>
<head>
  <title>둥이둥이</title>
  <meta charset="utf-8">
  <link rel="stylesheet" type="text/css" href="main.css" />
  <script type="text/javascript" src="http://code.jquery.com/jquery-3.3.1.min.js"></script>
  <script type="text/javascript" src="calendar.js"></script>
</head>
<body>
  <div class="wrapper">
    <main>
      <div class="toolbar">
        <div class="toggle">
          <div class="toggle__option">week</div>
          <div class="toggle__option toggle__option--selected">month</div>
        </div>
        <script type="text/javascript">
          var temp = document.location.href.split('?');
          if ( temp.length > 1 ) {
            var param = temp[1].replace('date=', '');
            var year = param.substring(0, 4);
            var month = param.substring(4, 6);
            var cal = new Calendar('cal', 'frm', year, month);
          }else {
            var cal = new Calendar('cal', 'frm');
          }
          cal.runScriptYM();
        </script>
        <div class="search-input">
          <input type="text" value="What are you looking for?" />
          <i class="fa fa-search"></i>
        </div> 
      </div>
      <div class="calendar">
        <div class="calendar__header">
          <div>sun</div>
          <div>mon</div>
          <div>tue</div>
          <div>wed</div>
          <div>thu</div>
          <div>fri</div>
          <div>sat</div>
        </div>
        <script type="text/javascript">
          var temp = document.location.href.split('?');
          if( temp.length > 1 ) {
            var param = temp[1].replace('date=', '');
            var year = param.substring(0, 4);
            var month = param.substring(4, 6);
            var cal = new Calendar('cal', 'frm', year, month);
            cal.runScriptDate(param);
          } else {
            var cal = new Calendar('cal', 'frm');
            cal.runScriptDate();
          }
        </script>
      </div>
      <br/><br/><br/>
      <div class="schedule">
        <div class="schedule__header">
          <div>schedule</div>
          <div></div><div></div><div></div><div></div>
          <div class="schedule__modify">modify</div>
          <div class="schedule__save">save</div>
        </div>
        <?php
          //id, password NULL 체크
          if( empty($_SESSION['id']) === true || empty($_SESSION['password']) === true ){
            header ("Location: ./logout.php");
          }

          //데이터 받아오기
          if( empty($_GET['date']) === true ){
            date_default_timezone_set("Asia/Seoul");
            $dates = date("Ymd");
          }else{
            $dates = $_GET['date'];
          }
          $year  = substr($dates, 0, 4);
          $month = substr($dates, 4, 2);
          $date  = substr($dates, 6, 2);
          $sql = "(SELECT cal.year, cal.month, cal.date, cal.type, cal.context FROM tb_calendar AS cal LEFT JOIN tb_user AS user ON cal.user_key=user.user_key "
                ."WHERE user.id='".$_SESSION['id']."' AND user.password='".$_SESSION['password']."' AND cal.type like 'event%' "
                ."AND cal.year='".$year."' AND cal.month='".$month."' AND cal.date='".$date."' ORDER BY type) UNION ALL "
                ."(SELECT cal.year, cal.month, cal.date, cal.type, cal.context FROM tb_calendar AS cal LEFT JOIN tb_user AS user ON cal.user_key=user.user_key "
                ."WHERE user.id='".$_SESSION['id']."' AND user.password='".$_SESSION['password']."' AND cal.type like 'work%' " 
                ."AND cal.year='".$year."' AND cal.month='".$month."' AND cal.date='".$date."' ORDER BY type) UNION ALL "
                ."(SELECT cal.year, cal.month, cal.date, cal.type, cal.context FROM tb_calendar AS cal LEFT JOIN tb_user AS user ON cal.user_key=user.user_key "
                ."WHERE user.id='".$_SESSION['id']."' AND user.password='".$_SESSION['password']."' AND cal.type like 'check%' "
                ."AND cal.year='".$year."' AND cal.month='".$month."' AND cal.date='".$date."' ORDER BY type)";
          $result = mysqli_query($conn, $sql);

          //Event
          $row = mysqli_fetch_assoc($result);
          echo "<div class='schedule__special' id='schedule__special' contenteditable='true' placeholder='Events'>".$row['context']."</div>";
          //Work
          $row = mysqli_fetch_assoc($result);
          echo "<div class='schedule__work' id='schedule__work' contenteditable='true' placeholder='Works'>".$row['context']."</div>";
          
          //Check 배열 생성
          $chkArr = array();
          $cnt = 1;
          while ( $row = mysqli_fetch_assoc($result) ) {
            $chkCnt = (int)substr($row['type'], 5);
            if( $cnt == $chkCnt ){
              $chkArr[$cnt] = $row['context'];
              $cnt++;
            }else{
              while ( $cnt < $chkCnt) {
                $chkArr[$cnt] = "";
                $cnt++;
              }
              $chkArr[$cnt] = $row['context'];
              $cnt++;
            }
          }
          
          //Check 화면 표시
          if( count($chkArr) === 0 ){
            echo "<div class='schedule__check' id='schedule__check' contenteditable='true' placeholder='Check1'></div>";
            echo "<div class='schedule__check' id='schedule__check' contenteditable='true' placeholder='Check2'></div>";
            echo "<div class='schedule__check' id='schedule__check' contenteditable='true' placeholder='Check3'></div>";
          }else{
            for ( $i = 1 ; $i <= count($chkArr) ; $i++ ){ 
              echo "<div class='schedule__check' contenteditable='true' placeholder='Check".$i."'>".$chkArr[$i]."</div>";
            }
          }
        ?>
      </div>
    </main>
    <sidebar>
      <div class="logo">logo</div>
      <div class="avatar">
        <div class="avatar__img">
          <img src="https://picsum.photos/70" alt="avatar">
        </div>
        <div class="avatar__name" onclick="window.location.href='./logout.php';">Yongjun Lee</div>
      </div>
      <nav class="menu">
        <a class="menu__item" href="#">
          <i class="menu__icon"></i>
          <span class="menu__text">overview</span>
        </a>
        <a class="menu__item" href="#">
          <i class="menu__icon"></i>
          <span class="menu__text">messages</span>
        </a>
        <a class="menu__item" href="#">
          <i class="menu__icon"></i>
          <span class="menu__text">workout</span>
        </a>
        <a class="menu__item menu__item--active" href="#">
          <i class="menu__icon"></i>
          <span class="menu__text">calendar</span>
        </a>
        <a class="menu__item" href="#">
          <i class="menu__icon"></i>
          <span class="menu__text">goals</span>
        </a>
        <a class="menu__item" href="#">
          <i class="menu__icon"></i>
          <span class="menu__text">achivements</span>
        </a>
        <a class="menu__item" href="#">
          <i class="menu__icon"></i>
          <span class="menu__text">measurements</span>
        </a>
      </nav>
      <div class="copyright">copyright &copy; 2018</div>
    </sidebar>
  </div>
  <script type="text/javascript">
    //$를 함수의 지역변수로 선언해서 외부에 있을지 모르는 타 라이브러리의 $와의 충돌을 예방
    (function($){
      //월버튼 클릭 이벤트
      $('div.current-month__btn').on('click', function() {
        var value = $('#current-month__dates').text();
        var temp = value.split('.');
        var year = temp[0];
        var month = temp[1];
        if ( month.length < 2 ) {
          month = "0"+month;
        }
        location.href = "index.php?date="+year+month+"01";
      })

      //년월 클릭 이벤트
      $('div.current-month__dates').on('click', function() {
        var value = $('#current-month__dates').text();
        var temp1 = value.match(/[A-Z]/g);
        if ( temp1 == "" || temp1 == null || temp1 == undefined ) {
        } else {
          //클릭시 입력 폼으로 바꿔 주기
          $('*').removeClass('current-month__dates--active');
          $('.current-month__dates').addClass('current-month__dates--active');
          $('.current-month__dates').attr( 'contenteditable', 'true' );
          value = $('#current-month__dates').text();
          
          //입력 텍스트 변환
          var temp2 = value.split(' ');
          var valueYear = temp2[1];
          var valueMonth;
          var eng_months = new Array('Jan.','Feb.','Mar.','Apr.','May.','Jun.','Jul.','Aug','Sep.','Oct.','Nov.','Dec.');
          for (var i = 0 ; i < eng_months.length ; i++) {
            if( eng_months[i] == temp2[0] ){
              valueMonth = i+1;
            }
          }
          value = valueYear + "." + valueMonth
          $('#current-month__dates').text(value);

          //Btn동적 추가
          $('*').removeClass('current-month__btn--active');
          $('.current-month__btn').addClass('current-month__btn--active');
          $('#current-month__btn').text('Btn');
        }
      })

      //달력 클릭 이벤트
      $('div.calendar__day').on('click', function() {
        $this = $(this);
        $('*').removeClass('calendar__day--selected');
        var divCDId = $this.attr('id');
        $('#'+divCDId).addClass('calendar__day calendar__day--selected');
      })

      //schedule__modify 클릭 이벤트
      $('div.schedule__modify').on('click', function() {
        $this = $(this);
        $('*').removeClass('calendar__day--selected');
        //var divCDId = $this.attr('id');
        //$('#'+divCDId).addClass('calendar__day calendar__day--selected');
        
      })

      //schedule__save 클릭 이벤트
      $('div.schedule__save').on('click', function() {
        //dates 값 가져오기
        $href = $(location).attr('href');
        var temp = $href.split('?');
        if ( temp.length > 1 ) {
          var dates = temp[1].replace('date=', '');
        } else {
          var datesObj = new Date();
          var year = datesObj.getFullYear();
          var month = ""+(datesObj.getMonth()+1);
          if ( month.length < 2 ) {
            month = "0"+month;
          }
          var date = "" +datesObj.getDate();
          if ( date.length < 2 ) {
            date = "0"+date;
          }
          dates = ""+year+month+date;
        }

        //schedule value 가져오기
        var valueEvent = $('.schedule__special').text();
        var valueWork = $('.schedule__work').text();
        var valueCheckCnt = $('.schedule').find('div.schedule__check').length;
        var valueCheck = new Array();
        for ( var i = 0 ; i < valueCheckCnt ; i++ ) {
          valueCheck[i] = $('.schedule').find('div.schedule__check').eq(i).text();
        }

        //php세션 받아오기
        var id = '<?= $_SESSION['id'] ?>';
        var password = '<?= $_SESSION['password'] ?>';
        console.log("id, password : " + id + ", " + password);

        //save_process.php 값 전달
        $.ajax({
          url:'./save_process.php',
          type:'post',
          data:{
            dates : dates,
            id : id,
            password : password,
            event : valueEvent,
            work : valueWork,
            check : valueCheck
          },
          success:function(data){
            console.log(data);
          }
        })
       
       for( var i = 0 ; i < 100000 ; i++ ) {
         //db저장시간 동안 돌아라
        for( var j = 0 ; j < 100 ; j++ ) {
          for( var l = 0 ; l < 100 ; l++ ) {}
        }
       }
       window.location.href=window.location.href;
       
      })
    })(jQuery)
  </script>
</body>
</html>

