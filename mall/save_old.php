<?
# ������ ��û�� ���� ����..
session_start();
include $_SERVER["DOCUMENT_ROOT"]."/new/include/connect.php";
//if(!$wdk_sid) include "../../include/create_sid.php";
//


exit;

$referer_domain = $_SERVER['HTTP_REFERER'];


if(!$_POST['policy_ok']){
	echo("<script>alert('����������޹�ħ�� ���� �ϼž� ��û�ϽǼ� �ֽ��ϴ�.');history.go(-1);</script>");
	exit;
}

if(!$name) {
	echo("<script>alert('�̸��� ��ȭ��ȣ�� �ʼ� �Է»����Դϴ�.');history.go(-1);</script>");
	exit;
}

//winxp sp2 ��������� �߰�
if ($debit == "ONLINE"){
    echo"<script>window.open('/new/shop/on_tmp.htm','PG1','scrollbars=no,resizable=no,left=300,top=300,width=300, height=170')</script>";
}
####################  �߿��� ������...



/////// �ڵ� ��ũ��Ʈ�� �̿��� ���ݿ� ����Ͽ� �α��� ���нÿ��� �����ð��� �����Ŀ� �ٽ� �α��� �ϵ��� ��
$g4['server_time'] = time();
$check_time  = time();
if ($_SESSION[ss_shop_check_time])
 {    
	 if ($check_time <= ($_SESSION[ss_shop_check_time]*1) + 10)
		{   
			echo "<script>alert('���θ� ��û�� 10�� �̳� �����ؼ� ��û�Ҽ� �����ϴ�. ��� ��ٷ��ּ���');</script>";    
			exit;
		}else{
			$_SESSION["ss_shop_check_time"]="";
			$_SESSION["ss_shop_check_time"]=$g4['server_time'];
		}
}
if(!$_SESSION[ss_shop_check_time]){
	$_SESSION["ss_shop_check_time"]=$g4['server_time'];
}
////////////////////////// �ڵ� ��ũ��Ʈ ���� �� //


//�ű� �ֹ���ȣ �߱�
$qry = "update uniqnum set num=num+1";
$db->query($qry);

$sid_rst = $db->query("select num from uniqnum");
$orderno = @mysql_result($sid_rst,0,0);

//$orderno = $wdk_sid;		    // �ֹ���ȣ....
if($wdk_id)$id=$wdk_id;		// ȸ�����̵�
else $id="guest";

$day1=time();
$day2=date("Y-m-d",$day1);

//$OrderAge = date("Y",time()) - $BirthYY;

$OrderAge = $_POST["age"];
//$OrderBirth = $_POST["birth"];
$OrderGender = $_POST["gender"];


# ���� �����Ǵ� �ý����� ���� PG�ڵ�.. (shop1)
$PG_Code=$db->GetField("PGcode","PGInfo","shop='lgtelecom'");

$segum_method="B";  	# �����.. ��û�� ����������� �Ѵ�..
//	$FinalToTalPrice="1200000";

# etc_info ==> �ҵ漼+ī�����������(PG���� �ٸ��Ƿ�)+�ΰ���

$card=$debit;

$Tprice = ($price_orderby == 'A')? $changup_price : $changup_price;

if($card=="ONLINE_CARD") {
	$FinalToTalPrice = $cardprice;
	$PG_fee="0.03";
	$card = "CARD";
} else if($card=="CARD"){
	$FinalToTalPrice = $Tprice;
	//$PG_fee="0.033"; 040115 yhpark : 20031202���� �����
	$PG_fee="0.03";
}else{
	//$FinalToTalPrice = 1200000; $PG_fee="0";
	$FinalToTalPrice = $Tprice; $PG_fee="0";
}

$etc_info="0.03:".$PG_fee.":0.1";

# etc1 ==> �Ⱥ���0,���� 1,�ű�����2,������3  ==> ���������ڿ��� ���̰� �Ⱥ��̰� �����ϰ� ���� �˻��� �����ϰ� �Ѵ�.
$etc1=$db->GetField("etc1","AllDomain","domain='$domain'");
$etc1=0;


//$gname="���θ����� ��û : $name";    040614 yhpark : �������û���� ����
//$gname="���˹��ֹ�";
$gname	= "���θ����ۺ�"; // 20060601 LKS : ����� ��û���� "���˹��ֹ�"=>"���θ���û" ���� ��Ī ����
//if($_POST['sex'] == 'M'){$sex = "����";}else{$sex = "����";}
//$BirthSex	= $OrderAge."�� ".",�������:".$OrderBirth." , ����:".$OrderGender;
$BirthSex	= $OrderAge."�� "." , ����:".$OrderGender." , ���Ӱ��:".$referer_domain;

//print_r($_POST);


$tel		= $tel1."-".$tel2."-".$tel3;
// $hp		= $hp1."-".$hp2."-".$hp3;
$hp		= $hp;
$zipcode	= $code1."-".$code2;
$zipaddr	= $addr1."&nbsp;".$addr2;
$myemail	= $email1."@".$email2;



#$message="�ֹι�ȣ : $jumin \n\n��޼���\n".$message;
$message=mysql_real_escape_string($message);

// ���� ȯ�� ����.
$secu_ip	= $_SERVER[REMOTE_ADDR];
$secu_time	= DATE("Ymd H:i:s");
$secu_agent	= $_SERVER['HTTP_USER_AGENT'];

# etc2 ==> ������ ������ ���.. : 5

# OrderInfo���̺� �ڷ� ����..
$cnt=$db->getCount("OrderInfo","where orderno=$orderno","Oname");

if($cnt){$db->dbDelete("OrderInfo","where orderno=$orderno");}	// ���� �ڷ�� �����Ѵ�..

if($card=="ONLINE"){
	$settle = $bank;
} else {
	$settle = "������";
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
if($cnt){$db->dbDelete("MyBag","where orderno=$orderno");}	// ���� �ڷ�� �����Ѵ�..

$saleprice	= round($FinalToTalPrice/1.1);
$addprice	= $FinalToTalPrice - $saleprice;

$query="Insert into MyBag set
	orderno='".$orderno."',
	id='".$id."',
	code='��a0-0',
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

//041013 yhpark : ������ ��û�� ���� ����� �ޱ� ���� �߰�, �ߺ��Ǵ� ����Ÿ�� ��Ű�� ������ ����Ÿ ����
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

//���� + ī�� �ֹ��� ��� ���� �ֹ��� �߰��� �Է�
if($debit=="ONLINE_CARD") {
	//�ű� �ֹ���ȣ �߱�
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
		code='��a0-0',
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


//================================����� ���� �ڵ� �߼�     START      ====================================
//161025 ���԰���� ��û���� ������ ���
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
    $shop_url   = "joagift.com";                        // �� URL
	$mid        = "Joagift";                            // Dacom ���� �������̵�
    $mertkey    = "58232cededb95e44006de9f6dd3eb8b1";   // �����޿��� �߱޹��� Ű��
    $shop_mode  = "service";                            // �׽�Ʈ �Ǵ� �ǰ��� ����
    $pg_path	= "/new/shop/LG_UPLUS/";				// pg ��� ����
	$pg_page	= "JGBD_PG_AGENCY_1.php";				// pg ���� ��������
?>

	<form name="send_page" method="post" id="LGD_PAYINFO" action="<?=$pg_path.$pg_page?>">
    <input type="hidden" name="pay_method" value="<?=$card?>">    <!-- ��������-->
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
	<input type="hidden" name="LGD_PRODUCTCODE" value="��a0-0">
	<input type="hidden" name="LGD_AMOUNT" value="<?=$FinalToTalPrice?>">
	<input type="hidden" name="ShopMD" value="5">
	<input type="hidden" name="chorderno" value="<?=$orderno?>">
	</form>
	<script>
		//alert("�̵�");
		document.send_page.submit();
	</script>
<?
}
exit;
?>
