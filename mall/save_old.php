<?
# 무점포 신청시 값을 저장..
session_start();
include $_SERVER["DOCUMENT_ROOT"]."/new/include/connect.php";
//if(!$wdk_sid) include "../../include/create_sid.php";
//


exit;

$referer_domain = $_SERVER['HTTP_REFERER'];


if(!$_POST['policy_ok']){
	echo("<script>alert('개인정보취급방침에 동의 하셔야 신청하실수 있습니다.');history.go(-1);</script>");
	exit;
}

if(!$name) {
	echo("<script>alert('이름과 전화번호는 필수 입력사항입니다.');history.go(-1);</script>");
	exit;
}

//winxp sp2 설정관계로 추가
if ($debit == "ONLINE"){
    echo"<script>window.open('/new/shop/on_tmp.htm','PG1','scrollbars=no,resizable=no,left=300,top=300,width=300, height=170')</script>";
}
####################  중요한 변수들...



/////// 자동 스크립트를 이용한 공격에 대비하여 로그인 실패시에는 일정시간이 지난후에 다시 로그인 하도록 함
$g4['server_time'] = time();
$check_time  = time();
if ($_SESSION[ss_shop_check_time])
 {    
	 if ($check_time <= ($_SESSION[ss_shop_check_time]*1) + 10)
		{   
			echo "<script>alert('쇼핑몰 신청은 10초 이내 연속해서 신청할수 없습니다. 잠시 기다려주세요');</script>";    
			exit;
		}else{
			$_SESSION["ss_shop_check_time"]="";
			$_SESSION["ss_shop_check_time"]=$g4['server_time'];
		}
}
if(!$_SESSION[ss_shop_check_time]){
	$_SESSION["ss_shop_check_time"]=$g4['server_time'];
}
////////////////////////// 자동 스크립트 방지 끝 //


//신규 주문번호 발급
$qry = "update uniqnum set num=num+1";
$db->query($qry);

$sid_rst = $db->query("select num from uniqnum");
$orderno = @mysql_result($sid_rst,0,0);

//$orderno = $wdk_sid;		    // 주문번호....
if($wdk_id)$id=$wdk_id;		// 회원아이디
else $id="guest";

$day1=time();
$day2=date("Y-m-d",$day1);

//$OrderAge = date("Y",time()) - $BirthYY;

$OrderAge = $_POST["age"];
//$OrderBirth = $_POST["birth"];
$OrderGender = $_POST["gender"];


# 현재 결제되는 시스템이 갖는 PG코드.. (shop1)
$PG_Code=$db->GetField("PGcode","PGInfo","shop='lgtelecom'");

$segum_method="B";  	# 사업자.. 신청시 본사발행으로 한다..
//	$FinalToTalPrice="1200000";

# etc_info ==> 소득세+카드결제수수료(PG마다 다르므로)+부과세

$card=$debit;

$Tprice = ($price_orderby == 'A')? $changup_price : $changup_price;

if($card=="ONLINE_CARD") {
	$FinalToTalPrice = $cardprice;
	$PG_fee="0.03";
	$card = "CARD";
} else if($card=="CARD"){
	$FinalToTalPrice = $Tprice;
	//$PG_fee="0.033"; 040115 yhpark : 20031202부터 변경됨
	$PG_fee="0.03";
}else{
	//$FinalToTalPrice = 1200000; $PG_fee="0";
	$FinalToTalPrice = $Tprice; $PG_fee="0";
}

$etc_info="0.03:".$PG_fee.":0.1";

# etc1 ==> 안보임0,지사 1,신규지사2,무점포3  ==> 공개관리자에서 보이고 안보이게 설정하고 또한 검색을 용이하게 한다.
$etc1=$db->GetField("etc1","AllDomain","domain='$domain'");
$etc1=0;


//$gname="쇼핑몰구축 신청 : $name";    040614 yhpark : 김차장요청으로 변경
//$gname="판촉물주문";
$gname	= "쇼핑몰제작비"; // 20060601 LKS : 사장님 요청으로 "판촉물주문"=>"쇼핑몰신청" 으로 명칭 변경
//if($_POST['sex'] == 'M'){$sex = "남성";}else{$sex = "여성";}
//$BirthSex	= $OrderAge."세 ".",생년월일:".$OrderBirth." , 성별:".$OrderGender;
$BirthSex	= $OrderAge."세 "." , 성별:".$OrderGender." , 접속경로:".$referer_domain;

//print_r($_POST);


$tel		= $tel1."-".$tel2."-".$tel3;
// $hp		= $hp1."-".$hp2."-".$hp3;
$hp		= $hp;
$zipcode	= $code1."-".$code2;
$zipaddr	= $addr1."&nbsp;".$addr2;
$myemail	= $email1."@".$email2;



#$message="주민번호 : $jumin \n\n↓메세지\n".$message;
$message=mysql_real_escape_string($message);

// 유저 환경 저장.
$secu_ip	= $_SERVER[REMOTE_ADDR];
$secu_time	= DATE("Ymd H:i:s");
$secu_agent	= $_SERVER['HTTP_USER_AGENT'];

# etc2 ==> 무점포 결제인 경우.. : 5

# OrderInfo테이블에 자료 삽입..
$cnt=$db->getCount("OrderInfo","where orderno=$orderno","Oname");

if($cnt){$db->dbDelete("OrderInfo","where orderno=$orderno");}	// 기존 자료는 삭제한다..

if($card=="ONLINE"){
	$settle = $bank;
} else {
	$settle = "결제전";
}

// if($_SERVER['REMOTE_ADDR'] == '61.37.199.228'){
// 		print_r2($_REQUEST);
// 		echo $cardprice;
// 		exit;
// }
$query="Insert into  OrderInfo (
			orderno, id, Oname, Orderday1, TotalPrice, debit, settle, segum, files, url, Orderday2,
			Oemail, Ozip, Oaddr, Ophone, Ohp, Rname, Remail, Rzip, Raddr, Rphone, Rhp, PrintMess, EtcMess,
			hopeday, deliver,segum_method,PG,etc_info,memo,etc1,etc2, secu_ip, secu_time, secu_agent,manager
		) values(
			'$orderno','$id','$name','$day2','$FinalToTalPrice','$card','".$settle."','N','','$domain','$day1',
			'$email','$zipcode','$zipaddr','$tel','$hp','$name','$myemail','$zipcode','$zipaddr','$tel','$hp','$printmessage','$message',
			'$HopeDay',0,'$segum_method','$PG_Code','$etc_info','$BirthSex',$etc1,5,'$secu_ip','$secu_time','$secu_agent','$manager'
		)
		";
$rst = mysql_query($query);

if($rst){
	$refer_sql = "insert into OrderInfo_refer(orderno, refer, regday)values('".$orderno."', '".$refer."', now())";
	$rrt = mysql_query($refer_sql);
}



$cnt=$db->getCount("MyBag","where orderno=$orderno","code");
if($cnt){$db->dbDelete("MyBag","where orderno=$orderno");}	// 기존 자료는 삭제한다..

$saleprice	= round($FinalToTalPrice/1.1);
$addprice	= $FinalToTalPrice - $saleprice;

$query="Insert into MyBag set
	orderno='".$orderno."',
	id='".$id."',
	code='가a0-0',
	Gname='".$gname."',
	size='-',
	color='-',
	matierial='-',
	amount=1,
	saleprice='".$saleprice."',
	deliverprice=0,
	addprice='".$addprice."',
	printprice=0,
	littlebuy='N',
	buyprice=0,
	deliverprice2=0,
	printprice2=0,
	cardfee=0,
	addprice2=0,
	soduk=0,
	Jisaprice=0,
	soxx_amtx=0,
	Imgway='offline.gif'";

//echo $query;
//echo "<br />";
$db->query($query);

//041013 yhpark : 무점포 신청도 결제 결과를 받기 위해 추가, 중복되는 데이타와 쿠키가 지워진 데이타 정리
if ($srch_sumx){
    $srch_qury="select orderno from srch_data where orderno='$orderno'";
	$srch_rslt=$db->query($srch_qury);
	$chck_ordr=@mysql_result($srch_rslt,0,0);

    $srch_sum1=@explode("|",$srch_sumx);
    $srch_engi=$srch_sum1[0];
    $srch_keyx=$srch_sum1[1];

    if ($srch_engi == "deleted"){
        $srch_engi="";
        $srch_keyx="";
	}else{
	    if ($chck_ordr){
	        $srch_upxx="update srch_data set srch_engi='$srch_engi',srch_keyx='$srch_keyx' where orderno='$orderno'";
			$upxx_rlst=$db->query($srch_upxx);
	    }else{
		    $query="Insert into srch_data(orderno,srch_engi,srch_keyx) values('$orderno','$srch_engi','$srch_keyx')";
		    $db->query($query);
	    }
    }
}

//현금 + 카드 주문인 경우 현금 주문서 추가로 입력
if($debit=="ONLINE_CARD") {
	//신규 주문번호 발급
	$query = "update uniqnum set num=num+1";
	$db->query($query);
	$uniqnum = selectOne("uniqnum","*","");

	$settle = $bank;
	$OL_CD_TOT_PRICE = $Tprice - $cardprice;
	$oc_saleprice = round($OL_CD_TOT_PRICE/1.1);
	$oc_addprice =  $OL_CD_TOT_PRICE - $oc_saleprice;

	$query="Insert into  OrderInfo (
				orderno, id, Oname, Orderday1, TotalPrice, debit, settle, segum, files, url, Orderday2,
				Oemail, Ozip, Oaddr, Ophone, Ohp, Rname, Remail, Rzip, Raddr, Rphone, Rhp, PrintMess, EtcMess,
				hopeday, deliver,segum_method,PG,etc_info,memo,etc1,etc2, secu_ip, secu_time, secu_agent,manager
			) values(
				'".$uniqnum['num']."','$id','$name','$day2','$OL_CD_TOT_PRICE','ONLINE','".$settle."','N','','$domain','$day1',
				'$email','$zipcode','$zipaddr','$tel','$hp','$name','$myemail','$zipcode','$zipaddr','$tel','$hp','$printmessage','$message',
				'$HopeDay',0,'$segum_method','$PG_Code','$etc_info','$BirthSex',$etc1,5,'$secu_ip','$secu_time','$secu_agent','$manager'
			)
			";
	//echo $query;
	//echo "<br />";
	//EXIT;
	$rst1 = mysql_query($query);
	if($rst1){
		$refer_sql = "insert into OrderInfo_refer(orderno, refer, regday)values('".$orderno."', '".$refer."', now())";
		$rrt = mysql_query($refer_sql);
	}




	$query="Insert into MyBag set
		orderno='".$uniqnum['num']."',
		id='".$id."',
		code='가a0-0',
		Gname='".$gname."',
		size='-',
		color='-',
		matierial='-',
		amount=1,
		saleprice=".$oc_saleprice.",
		deliverprice=0,
		addprice=".$oc_addprice.",
		printprice=0,
		littlebuy='N',
		buyprice=0,
		deliverprice2=0,
		printprice2=0,
		cardfee=0,
		addprice2=0,
		soduk=0,
		Jisaprice=0,
		soxx_amtx=0,
		Imgway='offline.gif'";
	//echo $query;
	//echo "<br />";
	$db->query($query);
}


//================================담당자 문자 자동 발송     START      ====================================
//161025 영규과장님 요청으로 강제로 배분
$menager_up="update OrderInfo set manager='".$chan_store_manager."' where orderno='$orderno'";
$db->query($menager_up);



if($card == "ONLINE"){
	$meta_url = "./agency_result.php?orderno=${orderno}";
	echo "<meta http-equiv='Cache-Control' content='no-cache'/>";
	echo "<meta http-equiv='Expires' content='0'/>";
	echo "<meta http-equiv='Pragma' content='no-cache'/>";
	echo "<meta http-equiv='refresh' content='2;url=$meta_url'>";
	exit;

}else{

	// PG Setting
    $shop_url   = "joagift.com";                        // 샵 URL
	$mid        = "Joagift";                            // Dacom 제공 상점아이디
    $mertkey    = "58232cededb95e44006de9f6dd3eb8b1";   // 데이콤에서 발급받은 키값
    $shop_mode  = "service";                            // 테스트 또는 실결제 설정
    $pg_path	= "/new/shop/LG_UPLUS/";				// pg 경로 설정
	$pg_page	= "JGBD_PG_AGENCY_1.php";				// pg 연결 페이지명
?>

	<form name="send_page" method="post" id="LGD_PAYINFO" action="<?=$pg_path.$pg_page?>">
    <input type="hidden" name="pay_method" value="<?=$card?>">    <!-- 결제종류-->
    <input type="hidden" name="CST_MID" value="<?=$mid?>">
    <input type="hidden" name="CST_PLATFORM" value="<?=$shop_mode?>">
    <input type="hidden" name="LGD_MERTKEY" value="<?=$mertkey?>">
    <input type="hidden" name="LGD_OID" value="<?=$orderno?>">
    <input type="hidden" name="LGD_BUYER" value="<?=$_POST['name']?>">
	<input type="hidden" name="LGD_BUYERID" value="<?=$id?>">
	<input type="hidden" name="LGD_BUYERIP" value="<?=$secu_ip?>">
    <input type="hidden" name="LGD_BUYEREMAIL" value="<?=$_POST['email']?>">
    <input type="hidden" name="LGD_BUYERADDRESS" value="<?=$zipaddr?>">
    <input type="hidden" name="LGD_BUYERPHONE" value="<?=$hp?>">
    <input type="hidden" name="LGD_TIMESTAMP" value="<?=date("YmdHms");?>">
    <input type="hidden" name="LGD_PRODUCTINFO" value="<?=$gname?>">
	<input type="hidden" name="LGD_PRODUCTCODE" value="가a0-0">
	<input type="hidden" name="LGD_AMOUNT" value="<?=$FinalToTalPrice?>">
	<input type="hidden" name="ShopMD" value="5">
	<input type="hidden" name="chorderno" value="<?=$orderno?>">
	</form>
	<script>
		//alert("이동");
		document.send_page.submit();
	</script>
<?
}
exit;
?>
