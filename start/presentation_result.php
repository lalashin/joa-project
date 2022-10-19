<?
include_once($_SERVER['DOCUMENT_ROOT']."/include/connect.php");

$table		= "BusiExplan";

$sec_ip		= $_SERVER["REMOTE_ADDR"];


$user_bro	= $_SERVER["HTTP_USER_AGENT"];
$httpReferer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;

foreach($_POST as $uk=>$uv) $_POST[$uk]=iconv("UTF-8","EUC-KR",$uv);

//print_r($_POST);

$name		= ltrim($_POST['name1']);
$BirthYY	= ltrim($_POST['BirthYY1']);
$tel		= ltrim($_POST['tel']);
$area		= ltrim($_POST['area']);
$cdebit	 =	trim($_POST["cdebit"]);
$BM			= ltrim($_POST['BM']);
$BY			= ltrim($_POST['BY']);


$refer = $_POST["refer"];


$Cdate=time();  // 현재 날짜..
$BM=$BM;
$BY=$BY;
$busi_date=$BM.$BY;
$regedate=$Cdate;

$ReqAge = $BirthYY;
$BirthSex= $ReqAge;

//$count=$db->getCount("$table","where ip='$sec_ip'","ip");
$count = false;

if($count){
	//$db->historyBack("이미 [$sec_ip]의 IP는 신청이 완료 되었습니다.");
	//exit;
}else{

	if(!$name || !$BirthYY || !$area || !$tel){
		$db->historyBack("신청 내역이 올바르지 않습니다.\\n확인후 다시 작성해 주시기 바랍니다.");
		exit;
	}else{
		$c_manager = selectOne("BusiExplan","*"," where m_name<>'' order by num desc  limit 1");//기존 주문서 확인 정렬 정확히는 orderday2사용해야함
	
		$c_manager = '박영규';
		$query = "insert into $table (regedate,name,m_name,age, area,cell_numb,mail_addr,requ_dayx, cdebit,domain,busi_date,ip,browser,birthsex, regday, rpage, refer) VALUES ('$regedate','$name','$c_manager','$ReqAge' ,'$area' , '$tel', '$email', '$requ_dayx', '".$cdebit."','".$_SERVER[HTTP_HOST]."', '$busi_date', '$sec_ip','$httpReferer','$BirthSex', now(), '$rpage', '".$refer."')";

		//echo $query;
		//exit;
		
		$ret = $db->query($query);
		//==================담당자 문자 자동 발송     START      ====================================
		
		//$manager_name_array = array("박성호", "박영규");//배분 담당자//안병수,유동호
		//$manager_hp_array = array("010-4704-6742", "010-6444-4998");//배분 담당자 전화번호 //"010-6209-3534", "010-5307-1871", 20160206 사장님요청으로 변경
		//$key = array_search($c_manager, $manager_name_array);
		//$message_sms = "사업설명회 신청 ".$name."고객, 연락처 :".$cell_numb." ";
		//smsSend('sms', $c_manager, $manager_hp_array[$key], $message_sms, '1544-6233');

	   if($ret){
		   echo "success";
		   exit;
	   }

	}
}
?>

