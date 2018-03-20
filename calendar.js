/* ------------------------------------------------------------ 
		1. 기본 옵션
   ------------------------------------------------------------ */
// 포멧
var eng_months = new Array('Jan.','Feb.','Mar.','Apr.','May.','Jun.','Jul.','Aug','Sep.','Oct.','Nov.','Dec.');
var currentDate = new Date();
var DOMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];		// Non-Leap year Month days.. 
var lDOMonth = [31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];	// Leap year Month days.. 
var d_formName = "form1";
var d_name = "cal";
var d_tfDate = "tfDate";
/* ------------------------------------------------------------ 
		2. Calendar 객체 생성
   ------------------------------------------------------------ */
// Calendar 객체
function Calendar(newName, newFormName, newYear, newMonth, newDate) {
  // Calendar 객체에 속한 함수 등록
  this.setDates = setDates; // 해당월의 데이터 배열 생성
  this.runScriptDate = runScriptDate;
  this.runScriptYM = runScriptYM;
  
  // Calendar 객체의 속성 선언
  this.name;				// Calendar 객체의 이름
  this.myYear;				// 조회할 년
  this.myMonth;				// 조회할 월(month:0~11)
  this.myDate;				// 조회할 일
  this.format;				// 년월일 format
        
  // argument 세팅
  if ( newName != null ) this.name = newName                                     // Calendar 객체 이름 세팅
    else this.name = d_name;
  if ( newFormName != null ) this.formName = newFormName;                        // form 이름 세팅
    else this.formName = d_formName;
  if ( newYear != null && newYear.length != 0 )	this.myYear = newYear;           // 조회할 년 세팅
    else this.myYear = currentDate.getFullYear();
  if ( newMonth != null && newMonth.length != 0 )	this.myMonth = newMonth - 1;   // 조회할 월 세팅
    else this.myMonth = currentDate.getMonth();
  if ( newDate != null && newDate.length != 0 )	this.myDate = newDate;           // 조회할 일 세팅
    else this.myDate = currentDate.getDate();
}
/* ------------------------------------------------------------ 
		3. 날짜 관련된 함수 구현
   ------------------------------------------------------------ */
// 조회 년월의 마지막날 일자를 구함
function getDaysOfMonth(year, month) {
  /*
    Check for leap year ..
    1.Years evenly divisible by four are normally leap years, except for...
    2.Years also evenly divisible by 100 are not leap years, except for...
    3.Years also evenly divisible by 400 are leap years.
  */
  if ( (year % 4) == 0 ) {
    if ( (year % 100) == 0 && (year % 400) != 0 ) return DOMonth[month];
    return lDOMonth[month];
  } else 
    return DOMonth[month];
}

// 첫번째 요일 구하기
function getFirstDay(year, month) {
  var tmpDate = new Date(); 
  tmpDate.setDate(1); 
  tmpDate.setMonth(month); 
  tmpDate.setFullYear(year); 
    
  return tmpDate.getDay(); 
}

// 마지막 요일 구하기
function getLastDay(year, month) {
  var tmpDate = new Date(); 
  tmpDate.setDate(getDaysOfMonth(year, month)); 
  tmpDate.setMonth(month); 
  tmpDate.setFullYear(year); 

  return tmpDate.getDay(); 
}


// 현재월의 앞뒤 년월 / 감소(incr:-1), 증가(incr:1)
function calcMonthYear(p_Year, p_Month, incr) { 
  /* 
    Will return an 1-D array with 1st element being the calculated month 
    and second being the calculated year 
    after applying the month increment/decrement as specified by 'incr' parameter. 
    'incr' will normally have 1/-1 to navigate thru the months. 
  */ 
  var ret_arr = new Array(); 

  if ( incr == -1 ) { 
    // 뒤로
    if ( p_Month == 0 ) { 
      ret_arr[1] = 11; 
      ret_arr[0] = parseInt(p_Year)-1; 
    } else { 
      ret_arr[1] = parseInt(p_Month)-1; 
      ret_arr[0] = parseInt(p_Year); 
    }
  } else if ( incr == 1 ) { 
    // 앞으로
    if ( p_Month == 11 ) { 
      ret_arr[1] = 0; 
      ret_arr[0] = parseInt(p_Year)+1; 
    } else {
      ret_arr[1] = parseInt(p_Month)+1;
      ret_arr[0] = parseInt(p_Year);
    }
  } else if (incr == 0) {
    // 현제
    ret_arr[1] = parseInt(p_Month);
    ret_arr[0] = parseInt(p_Year);
  }
  return ret_arr; 
}
/* ------------------------------------------------------------ 
		4. Calendar 객체의 함수 구현
   ------------------------------------------------------------ */
// 해당월의 데이터 배열 생성
function setDates() {
  //변수선언
  var dates = new Array();										                 // dates = { '&npsp;', '', 1, 2, 3, 4, ...27,.. '&npsp;' };
  var firstDay = getFirstDay(this.myYear, this.myMonth);			 // 첫번째 요일의 숫자값
  var lastDay = getLastDay(this.myYear, this.myMonth);			   // 마지막 요일의 숫자값
  var daysOfMonth = getDaysOfMonth(this.myYear, this.myMonth); // 28, 29, 30, 31 중 하나
  var firstDate = 1;

  //preDaysOfMonth 구하기
  var tmp = calcMonthYear(this.myYear, this.myMonth, -1);
  var preYear = tmp[0];
  var preMonth = tmp[1];
  var preDaysOfMonth = getDaysOfMonth(preYear, preMonth);	// 28, 29, 30, 31 중 하나
  var preDaysOfMonth = preDaysOfMonth - (firstDay-1);

  for ( var i = 0 ; i < firstDay ; i++ ) dates[i] = preDaysOfMonth+i;
  for ( var i = firstDay ; i < daysOfMonth + firstDay ; i++ ) {
    dates[i] = firstDate;
    firstDate ++;
  }
  var len = dates.length;		
  for ( var i = 0 ; i < (6-lastDay) ; i++ ) dates[len+i] = (i+1);
  return dates;
}
/* ------------------------------------------------------------ 
		5. 기타 함수 구현
   ------------------------------------------------------------ */
function supportDates(dates) {
  if( dates.length < 2 ) {
    dates = '0' + dates;
  }
  return dates;
}
/* ------------------------------------------------------------ 
    6. HTML 생성 함수 구현
   ------------------------------------------------------------ */
function runScriptYM() {
  var year = this.myYear;
  var month = eng_months[this.myMonth];
  document.writeln("<div class='current-month' id='current-month'>");
  document.writeln("<div class='current-month__dates' id='current-month__dates' >"+month+" "+year+"</div>");
  document.writeln("<div class='current-month__btn' id='current-month__btn' ></div>");
  document.writeln("</div>");
}

function runScriptDate() {
  var dates = this.setDates();
  var flag = 0;
  var i = 0;
  var param = arguments;
  switch ( param.length ) {
    case 0: //-- 매개변수가 없을때
      var toyear = currentDate.getFullYear();
      var tomonth = supportDates( (currentDate.getMonth()+1).toString() );
      var cmpToday = currentDate.getDate();
      while ( i < dates.length ) {
        document.writeln("<div class='calendar__week'>");
        for (var j = 0 ; j < 7 ; j++) {
          if ( dates[i] == 1 ){
            flag++;
          }
          if ( flag == 1 ) {
            var today = supportDates(dates[i].toString());
            var sDMyDates = toyear+ tomonth + today;
            if ( dates[i] == cmpToday ) {
              document.writeln("<div class='calendar__day calendar__day--selected' id='"+sDMyDates+"'>"+ dates[i]+"</div>");	
            } else {
              document.writeln("<div class='calendar__day' id='"+sDMyDates+"' onclick=\"window.location.href='./index.php?date="+sDMyDates+"';\">"+dates[i]+"</div>");
            }
            i++;
          } else {
            document.writeln("<div class='calendar__exday'>"+dates[i]+"</div>");
            i++;
          }
        }
        document.writeln("</div>");
      }
    break;
    case 1: //-- 매개변수가 한개 일때
      var sDMyYear = this.myYear;
      var sDMyMonth = supportDates((this.myMonth+1).toString());
      while( i < dates.length ) {
        document.writeln("<div class='calendar__week'>");
        for (var j = 0 ; j < 7 ; j++ ) {
          if ( dates[i] == 1 ){
            flag++;
          }
          if ( flag == 1 ) {
            var sDMyDate = supportDates(dates[i].toString());
            var sDMyDates = sDMyYear+sDMyMonth+sDMyDate;
            if ( sDMyDates == param[0] ) {
              document.writeln("<div class='calendar__day calendar__day--selected' id='"+dates[i]+"'>"+dates[i]+"</div>");	
            } else {
              document.writeln("<div class='calendar__day' id='"+sDMyDates+"' onclick=\"window.location.href='./index.php?date="+sDMyDates+ "';\">"+dates[i]+"</div>");
            }
            i++;
          } else {
            document.writeln("<div class='calendar__exday'>"+dates[i]+"</div>");
            i++;
          }
        }
        document.writeln("</div>");
      }
    break;
  }
}
