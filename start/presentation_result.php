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


$Cdate=time();  // ���� ��¥..
$BM=$BM;
$BY=$BY;
$busi_date=$BM.$BY;
$regedate=$Cdate;

$ReqAge = $BirthYY;
$BirthSex= $ReqAge;

//$count=$db->getCount("$table","where ip='$sec_ip'","ip");
$count = false;

if($count){
	//$db->historyBack("�̹� [$sec_ip]�� IP�� ��û�� �Ϸ� �Ǿ����ϴ�.");
	//exit;
}else{

	if(!$name || !$BirthYY || !$area || !$tel){
		$db->historyBack("��û ������ �ùٸ��� �ʽ��ϴ�.\\nȮ���� �ٽ� �ۼ��� �ֽñ� �ٶ��ϴ�.");
		exit;
	}else{
		$c_manager = selectOne("BusiExplan","*"," where m_name<>'' order by num desc  limit 1");//���� �ֹ��� Ȯ�� ���� ��Ȯ���� orderday2����ؾ���
	
		$c_manager = '�ڿ���';
		$query = "insert into $table (regedate,name,m_name,age, area,cell_numb,mail_addr,requ_dayx, cdebit,domain,busi_date,ip,browser,birthsex, regday, rpage, refer) VALUES ('$regedate','$name','$c_manager','$ReqAge' ,'$area' , '$tel', '$email', '$requ_dayx', '".$cdebit."','".$_SERVER[HTTP_HOST]."', '$busi_date', '$sec_ip','$httpReferer','$BirthSex', now(), '$rpage', '".$refer."')";

		//echo $query;
		//exit;
		
		$ret = $db->query($query);
		//==================����� ���� �ڵ� �߼�     START      ====================================
		
		//$manager_name_array = array("�ڼ�ȣ", "�ڿ���");//��� �����//�Ⱥ���,����ȣ
		//$manager_hp_array = array("010-4704-6742", "010-6444-4998");//��� ����� ��ȭ��ȣ //"010-6209-3534", "010-5307-1871", 20160206 ����Կ�û���� ����
		//$key = array_search($c_manager, $manager_name_array);
		//$message_sms = "�������ȸ ��û ".$name."��, ����ó :".$cell_numb." ";
		//smsSend('sms', $c_manager, $manager_hp_array[$key], $message_sms, '1544-6233');

	   if($ret){
		   echo "success";
		   exit;
	   }

	}
}
?>

