<?
include_once($_SERVER["DOCUMENT_ROOT"]."/new/include/connect.php");

include_once($DOCUMENT_ROOT."/include/PGBANK.php");

include("../header.html");

//switch ($_GET['mode']) {
$filename = 'agency_'.'result.php';
$title = '- ���θ���û�Ϸ�';
//}




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





function PayResultView($type,$orderno,$orderday,$name,$hp,$url){
	global	$db;

	// ���Ἲ �˻�
	$PayResultWhere= "where orderno >= '$orderno' and etc2 = '5' and Oname like '$name' and Orderday2 = '$orderday' and Ohp like '$hp' and url like '$url' order by orderno ASC limit 2";
	$PayResultList = selectAll("OrderInfo",'*',$PayResultWhere);

	//CARD INFO
	$q2="select * from JG_Card_Save where pg_orderno='".$PayResultList['orderno'][0]."'";
	$result2=$db->query($q2);
	$row2=mysql_fetch_array($result2);

	if($row2[pg_installment] == "00"){
		$CARD_MONT	= "�Ͻú�";
	}else{
		$CARD_MONT	= $row2[pg_installment]."����";
	}
	$CARD_TYPE	= $row2[pg_cname]."ī�� / ".$CARD_MONT;

	//lg pg
	$pg_mid		= "Joagift";
	$pg_mert	= "58232cededb95e44006de9f6dd3eb8b1";
	//lg kvp
	$pg_mert2	= "433d80cf6cc37ed9a3ef1150e80030f8";

	$authdata	= md5($pg_mid.$row2[pg_tid].$pg_mert);
	$ReceiptPrint= "&nbsp;&nbsp;<a href=\"javascript:showReceiptByTID('$pg_mid', '$row2[pg_tid]', '$authdata')\"><img src=\"{$http_set}joagift.co.kr/new/images/mail/tm_prt.gif\" align='absMiddle' alt='���������'/></a>";
	$ReceiptPrint2= "&nbsp;&nbsp;<a href=\"https://pgweb.dacom.net/pg/wmp/etc/jsp/SettlementSearch.jsp\" target=\"_blank\"><img src=\"{$http_set}joagift.co.kr/new/images/mail/tm_prt.gif\" align='absMiddle' alt='���������'/></a>";

	//���Ʊ���Ʈ(��) ���¹�ȣ
	$bank_id1 = "214-872-5687"; // ���
	$bank_id2 = "368-17-002748";        // ����
	$bank_id3 = "787237-04-002837";     // ����
	$bank_id4 = "1005-701-319412";      // �츮
	$bank_id5 = "140-005-759810";       // ����
	$bank_id6 = "630-008495-965";       // ��ȯ
	$bank_id7 = "368-02-158311";        // ����(������)

	$arrBankInfo = array("���"=>"�������".$bank_id1, "����"=>"����(�߾�)".$bank_id2, "����"=>"��������".$bank_id3, "�츮"=>"�츮����".$bank_id4, "����"=>"��������".$bank_id5, "��ȯ"=>"��ȯ����".$bank_id6);

	if($type == "A"){
		if(count($PayResultList['orderno']) == "2"){
			$PayPrice	= "ī����� : ".number_format($PayResultList['TotalPrice'][0])." �� / �������Ա� : ".number_format($PayResultList['TotalPrice'][1])." �� (�ΰ�������)";
			return $PayPrice;
		}else{
			if($PayResultList['debit'][0] == "CARD"){
				$PayPrice	= number_format($PayResultList['TotalPrice'][0])." �� (�ΰ�������)";
			}else{
				$PayPrice	= number_format($PayResultList['TotalPrice'][0])." �� (�ΰ�������)";
			}
			return $PayPrice;
		}
	}else if($type == "B"){
		for($i=0;$i<count($PayResultList['orderno']);$i++ ){
			if($PayResultList['debit'][$i]=="CARD"){
				$PayInfo	= "<span style='font-weight:bold;'>�� �� ī ��</span> [ ".$CARD_TYPE." ] <span style='font-weight:bold; color:#ff668a;'>���ι�ȣ : ".$PayResultList['settle'][$i]."</span> ".$ReceiptPrint."<br />";
			}else{
				$PayInfo    .= "<span style='font-weight:bold;'>�������Ա�</span> [	".$arrBankInfo[$PayResultList['settle'][$i]]." : ���Ʊ���Ʈ(��) ]";
			}
		}
		return $PayInfo;
	}else{
		for($i=0;$i<count($PayResultList['orderno']);$i++ ){
			if($PayResultList['debit'][$i]=="CARD"){
				$PayInfo	= "<span style='font-weight:bold;'>�� �� ī ��</span> [ ".$CARD_TYPE." ] <span style='font-weight:bold; color:#ff668a;'>���ι�ȣ : ".$PayResultList['settle'][$i]."</span> ".$ReceiptPrint2."<br />";
			}else{
				$PayInfo    .= "<span style='font-weight:bold;'>�������Ա�</span> [	".$arrBankInfo[$PayResultList['settle'][$i]]." : ���Ʊ���Ʈ(��) ]";
			}
		}
		return $PayInfo;
	}
}

$q="select * from OrderInfo where orderno='$orderno'";
$result = $db->query($q);
$row = mysql_fetch_array($result);

//$EtcMess	= str_replace("��޼���","",$row[EtcMess]);

$EtcMess	= nl2br(str_replace("��޼���","",$row[EtcMess]));
?>

<script language="JavaScript" src="//pgweb.dacom.net/WEB_SERVER/js/receipt_link.js"></script>
 
<!--========================================================
                            CONTENT 
=========================================================-->
    
<!-- ��� /-->
<div class="block">		
	<div class="contents">	
		<a href="<?=$http_set?>joagift.co.kr/agency2/"><p class="home">Ȩ</p></a>
		<p class="cate">���θ���û</p> <p class="cate">��û�Ϸ�</p>
	</div>
</div>	
<!--/ ��� -->	
<!-- ���θ���û /-->        
<div class="block bgcloud" style="padding:30px 0;">		
<div class="contents">  
	<center>
          <h3>���θ� ��û�� �Ϸ�Ǿ����ϴ�.</h3>
          <p class="lh18">����ڰ� �ٷ� �����帮�ڽ��ϴ�.<br>
			 �����մϴ�.</p>
          
          <table cellpadding="0" cellspacing="15" class="tbl_ok">
              <colgroup>
                <col width="100">
                <col width="*">
              </colgroup>
              <tbody>
                <tr>
                  <th>����</th>
                  <td><?=$row[Oname]?>
                  </td>
                </tr>
                <tr>
                  <th>�̸���</th>
                  <td><?=$row[Oemail]?>
                  </td>
                </tr>
                <tr>
                  <th>����ó</th>
                  <td><?=$row[Ohp]?>
                  </td>
                </tr>
               <!-- <tr>
                  <th>����</th>
                  <td><?=$row[age]?>
                  </td>
                </tr>-->				  
                <tr>
                  <th>���ι��</th>
                  <td><?=PayResultView('B',$orderno,$row[Orderday2],$row[Oname],$row[Ohp],$row[url])?>
                  </td>
                </tr>
                <tr id="cardpay">
                  <th>���αݾ�</th>
                  <td><?=PayResultView('A',$orderno,$row[Orderday2],$row[Oname],$row[Ohp],$row[url])?>
                  </td>
                </tr>
              </tbody>
            </table>
       </center>
</div>  
</div>           

<?

###############################################################  ���� ����Ʈ
$SMail	= "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=euc-kr\" />
<title>���θ� ��û �ȳ�����</title>
<script language=\"JavaScript\" src=\"//pgweb.dacom.net/WEB_SERVER/js/receipt_link.js\"></script>
</head>

<body>

<table width='100%' border='0' cellpadding='0' cellspacing='0'>
	<tr>
    	<td width='650px' align='center'>
			<div style='margin:0 auto; width:644px; border:3px solid #dcdcdc;'>
				<div style='width:644px; margin-bottom:20px;'><img src='{$http_set}joagift.co.kr/new/images/mail/admin_oktit.png' alt='���θ� ��û�� ȯ���մϴ�.' /></div>

                <div style='width:600px; margin:0 auto;'>
                	<div style='margin-left:10px; font-size:12px; line-height:18px; letter-spacing:0px; color:#888; text-align:left;'>
                    	<p>���Ʊ���Ʈ(��)�� ���θ� ��û�� ȯ���մϴ�.<br/>
                    </div>

                    <!--��û�� ����-->
                    <div style='margin-top:20px; padding:0; line-height:16px;'>
                    <table width='100%' cellpadding='0' cellspacing='0' >
                    <tr>
                    	<td colspan='2' align='left' style='border-bottom:2px solid #dcdcdc; '><img src='{$http_set}joagift.co.kr/new/images/mail/tm_01.gif' alt='��û������' /></td>
                    </tr>
                    <tr>
                    	<td style='border-bottom:1px dotted #dcdcdc;' height='28' width='85'><img src='{$http_set}joagift.co.kr/new/images/mail/tm01.gif' alt='����' ></td>
                        <td style='border-bottom:1px dotted #dcdcdc; padding-left:10px; font-size:11px; line-height:18px; letter-spacing:0px; color:#4e4e4e; text-align:left;' height='28'>".$row[Oname]."</td>
                    </tr>
                    <tr>
                    	<td style='border-bottom:1px dotted #dcdcdc;' height='28' width='85'><img src='{$http_set}joagift.co.kr/new/images/mail/tm02.gif' alt='����' ></td>
                        <td style='border-bottom:1px dotted #dcdcdc; padding-left:10px; font-size:11px; line-height:18px; letter-spacing:0px; color:#4e4e4e; text-align:left;' height='28'>".$row[age]."</td>
                    </tr>
                    <tr>
                    	<td style='border-bottom:1px dotted #dcdcdc;' height='28' width='85'><img src='{$http_set}joagift.co.kr/new/images/mail/tm03.gif' alt='����ó' ></td>
                        <td style='border-bottom:1px dotted #dcdcdc; padding-left:10px; font-size:11px; line-height:18px; letter-spacing:0px; color:#4e4e4e; text-align:left;' height='28'>".$row[Ohp]." / ".$row[Ophone]."</td>
                    </tr>
                    <tr>
                    	<td style='border-bottom:1px dotted #dcdcdc;' height='28' width='85'><img src='{$http_set}joagift.co.kr/new/images/mail/tm04.gif' alt='�ּ�' ></td>
                        <td style='border-bottom:1px dotted #dcdcdc; padding-left:10px; font-size:11px; line-height:18px; letter-spacing:0px; color:#4e4e4e; text-align:left; word-break:break-all;' height='28'>".$row[Oaddr]."</td>
                    </tr>
                    </table>
                    </div>

                    <!--��������-->
                    <div style='margin-top:20px; padding:0; line-height:16px;'>
                    <table width='100%' cellpadding='0' cellspacing='0'>
                    <tr>
                    	<td colspan='2' align='left' style='border-bottom:2px solid #dcdcdc;'><img src='{$http_set}joagift.co.kr/new/images/mail/tm_02.gif' alt='��������' /></td>
                    </tr>
					<tr>
                    	<td style='border-bottom:1px dotted #dcdcdc;' height='28' width='85'><img src='{$http_set}joagift.co.kr/new/images/mail/tm06.gif' alt='�����ð�' ></td>
                        <td style='border-bottom:1px dotted #dcdcdc; padding-left:10px; font-size:11px; line-height:18px; letter-spacing:0px; color:#4e4e4e; text-align:left;' height='28'>".date('Y-m-d H:i:s',$row[Orderday2])."</td>
                    </tr>";

$SMail	.=	"		<tr>
                    	<td style='border-bottom:1px dotted #dcdcdc;' height='28' width='85'><img src='{$http_set}joagift.co.kr/new/images/mail/tm07.gif' alt='���αݾ�' ></td>
                        <td style='border-bottom:1px dotted #dcdcdc; padding-left:10px; font-size:11px; line-height:18px; letter-spacing:0px; color:#4e4e4e; text-align:left;' height='28'><span style='font-weight:bold;'>".PayResultView('A',$orderno,$row[Orderday2],$row[Oname],$row[Ohp],$row[url])."</span></td>
					</tr>
					<tr>
                    	<td style='border-bottom:2px solid #dcdcdc;' height='28' width='85'><img src='{$http_set}joagift.co.kr/new/images/mail/tm05.gif' alt='���ι��' ></td>
                        <td style='border-bottom:2px solid #dcdcdc; padding-left:10px; font-size:11px; line-height:18px; letter-spacing:0px; color:#4e4e4e; text-align:left;' height='28'><span style='font-weight:bold;'>".PayResultView('C',$orderno,$row[Orderday2],$row[Oname],$row[Ohp],$row[url])."</span></td>
                    </tr>";

$SMail	.=	"		</tr>
                    </table>
                    </div>

                    <div style='margin:30px 0; text-align:center;'>
                    <img src='{$http_set}joagift.co.kr/new/images/mail/tm_info.gif' alt='��û�������'/>
                    </div>

                </div>

                <div style='width: 644px; border-top: solid 3px #dcdcdc; margin-top:0;'>

                    <div style='width:570px; margin:10px 30px; border-bottom:solid 1px #dcdcdc; font-size:11px; letter-spacing:0px; line-height:16px; color:#7d7d7d; text-align:left;'>
                        <!--p style='padding-bottom:7px; margin:0; padding:0;'>�� ������ ������Ÿ� �̿����� �� ������ȣ� ���� ������ �ǰ� 2018�� 12�� 1�� �������� ȸ������ �̸��� ���� ���ǿ��θ� Ȯ���� ��� ȸ���Բ��� ���ſ� �����ϼ̱⿡ ȸ������ �Ǵ� �������� ������ ������ �ֽ� �̸��� �ּҷ� [����]�� ǥ������ �ʰ� �߼۵Ǵ� �����Դϴ�.</p-->
                        <p style='padding-bottom:7px; '>�� ������ �߽������̹Ƿ� ȸ�ŵ��� �ʽ��ϴ�. ���ǻ����� �����͸� �̿��� �ֽʽÿ�.<br />
                                <b>������ :<font style='color:#CC0000; font-size:12px;'> 1544-6233</font></b> (��~�� : ���� 9��~���� 9�� / �� : ���� 9��~���� 6��)</p>
                    </div>
                    <div style='width:570px; margin:15px 30px; font-size:11px; letter-spacing:0px; color:#7d7d7d; line-height:16px; border:0; text-align:left;'>
                    	���Ʊ���Ʈ(��) ����Ư���� ��õ�� ���������1�� 165 ��������Ͻ����� 9�� [��ǥ�̻� : ������]<br />
                        ����ڵ�Ϲ�ȣ : 214-87-25687 | ����Ǹž��Ű� : �� �����õ-0276 ȣ | �ΰ�����Ǹž��Ű� : �� 4644 ȣ<br />
                        ������ : <b>1544-6233(��)</b> | FAX : 070-5092-5630 | eMail : <a href='mailto:webmaster@joagift.com'>webmaster@joagift.com</a><br />
                        Copyright <b>�����Ʊ���Ʈ(��)</b> All Rights Reserved.
                  </div>

                </div>

            </div>

        </td>
	</tr>
</table>
</body>
</html>
";

//echo $SMail;

###############################################################  ���� ���Ϻ����� ����
$MESSAGE = $SMail;
$MESSAGE = stripslashes($MESSAGE);

// ���email
$TO = $row[Oemail];

$TITLE = "���Ʊ���Ʈ(��)�� ���θ� ��û�� �Ϸ� �Ǿ����ϴ�.";
$SUBJECT = $TITLE;
$FROM = "webmaster@joagift.com";

function SendMail($TO,$FROM,$SUBJECT,$MESSAGE){

	$headers  = "From: ���Ʊ���Ʈ(��)<".$FROM.">\r\n";
    $headers .= "Reply-To: ".$FROM."\r\n";
	$headers .= 'MIME-Version: 1.0' . "\n";
	$headers .= 'Content-type: text/html; charset=EUC-KR' . "\r\n";

	mail($TO,$SUBJECT,$MESSAGE,$headers, '-f'.$FROM);
}   // �Լ�...

SendMail($TO,$FROM,$SUBJECT,$MESSAGE);





			$msg_user = $row[Oname]."�� ���θ� ��û�� �Ϸ�Ǿ����ϴ�. \n�ֹ���ȣ : ".$orderno." \n����ڰ� �����帮�ڽ��ϴ�.\n�����մϴ�";
			
			$msg = "���θ� ��û \n".$row[Oname]."�� ,".$row[Ohp]."\n�ֹ���ȣ:".$orderno;
			$subject = "���θ� ��û";
			$oname=$row[Oname];

			$hp = get_shoporder_tel();
			$joa_tel = $joa_tel_080618;
			
			//smsSend("lms", $oname, $row[Ohp], $msg_user, $joa_tel_080618,$subject); // ������ �߼�
			kakaotalk_msg($row[Ohp], $joa_tel_080618, "B_JA_06_02_02485", $msg_user, '');

			//smsSend("lms", $oname, $hp, $msg, $joa_tel_080618,$subject); // �ڰ���Կ��� �߼�
			kakaotalk_msg($hp, $joa_tel_080618, "B_JA_06_02_02483", $msg, '');




//echo "TO : ".$TO." || FROM : ".$FROM." || TITLE : ".$SUBJECT ."|| SUBJECT : ".$MESSAGE;
//alert();
############################################################## ���Ϻ����� ��

?>

<? include("../footer.html") ?>