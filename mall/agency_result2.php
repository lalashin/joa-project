<?
include_once($_SERVER["DOCUMENT_ROOT"]."/new/include/connect.php");

include_once($DOCUMENT_ROOT."/include/PGBANK.php");

include("../header.html");

//switch ($_GET['mode']) {
$filename = 'agency_'.'result.php';
$title = '- 쇼핑몰신청완료';
//}




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





function PayResultView($type,$orderno,$orderday,$name,$hp,$url){
	global	$db;

	// 무결성 검사
	$PayResultWhere= "where orderno >= '$orderno' and etc2 = '5' and Oname like '$name' and Orderday2 = '$orderday' and Ohp like '$hp' and url like '$url' order by orderno ASC limit 2";
	$PayResultList = selectAll("OrderInfo",'*',$PayResultWhere);

	//CARD INFO
	$q2="select * from JG_Card_Save where pg_orderno='".$PayResultList['orderno'][0]."'";
	$result2=$db->query($q2);
	$row2=mysql_fetch_array($result2);

	if($row2[pg_installment] == "00"){
		$CARD_MONT	= "일시불";
	}else{
		$CARD_MONT	= $row2[pg_installment]."개월";
	}
	$CARD_TYPE	= $row2[pg_cname]."카드 / ".$CARD_MONT;

	//lg pg
	$pg_mid		= "Joagift";
	$pg_mert	= "58232cededb95e44006de9f6dd3eb8b1";
	//lg kvp
	$pg_mert2	= "433d80cf6cc37ed9a3ef1150e80030f8";

	$authdata	= md5($pg_mid.$row2[pg_tid].$pg_mert);
	$ReceiptPrint= "&nbsp;&nbsp;<a href=\"javascript:showReceiptByTID('$pg_mid', '$row2[pg_tid]', '$authdata')\"><img src=\"{$http_set}joagift.co.kr/new/images/mail/tm_prt.gif\" align='absMiddle' alt='영수증출력'/></a>";
	$ReceiptPrint2= "&nbsp;&nbsp;<a href=\"https://pgweb.dacom.net/pg/wmp/etc/jsp/SettlementSearch.jsp\" target=\"_blank\"><img src=\"{$http_set}joagift.co.kr/new/images/mail/tm_prt.gif\" align='absMiddle' alt='영수증출력'/></a>";

	//조아기프트(주) 계좌번호
	$bank_id1 = "214-872-5687"; // 기업
	$bank_id2 = "368-17-002748";        // 농협
	$bank_id3 = "787237-04-002837";     // 국민
	$bank_id4 = "1005-701-319412";      // 우리
	$bank_id5 = "140-005-759810";       // 신한
	$bank_id6 = "630-008495-965";       // 외환
	$bank_id7 = "368-02-158311";        // 개인(김재점)

	$arrBankInfo = array("기업"=>"기업은행".$bank_id1, "농협"=>"농협(중앙)".$bank_id2, "국민"=>"국민은행".$bank_id3, "우리"=>"우리은행".$bank_id4, "신한"=>"신한은행".$bank_id5, "외환"=>"외환은행".$bank_id6);

	if($type == "A"){
		if(count($PayResultList['orderno']) == "2"){
			$PayPrice	= "카드결제 : ".number_format($PayResultList['TotalPrice'][0])." 원 / 무통장입금 : ".number_format($PayResultList['TotalPrice'][1])." 원 (부가세포함)";
			return $PayPrice;
		}else{
			if($PayResultList['debit'][0] == "CARD"){
				$PayPrice	= number_format($PayResultList['TotalPrice'][0])." 원 (부가세포함)";
			}else{
				$PayPrice	= number_format($PayResultList['TotalPrice'][0])." 원 (부가세포함)";
			}
			return $PayPrice;
		}
	}else if($type == "B"){
		for($i=0;$i<count($PayResultList['orderno']);$i++ ){
			if($PayResultList['debit'][$i]=="CARD"){
				$PayInfo	= "<span style='font-weight:bold;'>신 용 카 드</span> [ ".$CARD_TYPE." ] <span style='font-weight:bold; color:#ff668a;'>승인번호 : ".$PayResultList['settle'][$i]."</span> ".$ReceiptPrint."<br />";
			}else{
				$PayInfo    .= "<span style='font-weight:bold;'>무통장입금</span> [	".$arrBankInfo[$PayResultList['settle'][$i]]." : 조아기프트(주) ]";
			}
		}
		return $PayInfo;
	}else{
		for($i=0;$i<count($PayResultList['orderno']);$i++ ){
			if($PayResultList['debit'][$i]=="CARD"){
				$PayInfo	= "<span style='font-weight:bold;'>신 용 카 드</span> [ ".$CARD_TYPE." ] <span style='font-weight:bold; color:#ff668a;'>승인번호 : ".$PayResultList['settle'][$i]."</span> ".$ReceiptPrint2."<br />";
			}else{
				$PayInfo    .= "<span style='font-weight:bold;'>무통장입금</span> [	".$arrBankInfo[$PayResultList['settle'][$i]]." : 조아기프트(주) ]";
			}
		}
		return $PayInfo;
	}
}

$q="select * from OrderInfo where orderno='$orderno'";
$result = $db->query($q);
$row = mysql_fetch_array($result);

//$EtcMess	= str_replace("↓메세지","",$row[EtcMess]);

$EtcMess	= nl2br(str_replace("↓메세지","",$row[EtcMess]));
?>

<script language="JavaScript" src="//pgweb.dacom.net/WEB_SERVER/js/receipt_link.js"></script>
 
<!--========================================================
                            CONTENT 
=========================================================-->
    
<!-- 경로 /-->
<div class="block">		
	<div class="contents">	
		<a href="<?=$http_set?>joagift.co.kr/agency2/"><p class="home">홈</p></a>
		<p class="cate">쇼핑몰신청</p> <p class="cate">신청완료</p>
	</div>
</div>	
<!--/ 경로 -->	
<!-- 쇼핑몰신청 /-->        
<div class="block bgcloud" style="padding:30px 0;">		
<div class="contents">  
	<center>
          <h3>쇼핑몰 신청이 완료되었습니다.</h3>
          <p class="lh18">담당자가 바로 연락드리겠습니다.<br>
			 감사합니다.</p>
          
          <table cellpadding="0" cellspacing="15" class="tbl_ok">
              <colgroup>
                <col width="100">
                <col width="*">
              </colgroup>
              <tbody>
                <tr>
                  <th>성명</th>
                  <td><?=$row[Oname]?>
                  </td>
                </tr>
                <tr>
                  <th>이메일</th>
                  <td><?=$row[Oemail]?>
                  </td>
                </tr>
                <tr>
                  <th>연락처</th>
                  <td><?=$row[Ohp]?>
                  </td>
                </tr>
               <!-- <tr>
                  <th>연령</th>
                  <td><?=$row[age]?>
                  </td>
                </tr>-->				  
                <tr>
                  <th>납부방법</th>
                  <td><?=PayResultView('B',$orderno,$row[Orderday2],$row[Oname],$row[Ohp],$row[url])?>
                  </td>
                </tr>
                <tr id="cardpay">
                  <th>납부금액</th>
                  <td><?=PayResultView('A',$orderno,$row[Orderday2],$row[Oname],$row[Ohp],$row[url])?>
                  </td>
                </tr>
              </tbody>
            </table>
       </center>
</div>  
</div>           

<?

###############################################################  메일 컨텐트
$SMail	= "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=euc-kr\" />
<title>쇼핑몰 신청 안내메일</title>
<script language=\"JavaScript\" src=\"//pgweb.dacom.net/WEB_SERVER/js/receipt_link.js\"></script>
</head>

<body>

<table width='100%' border='0' cellpadding='0' cellspacing='0'>
	<tr>
    	<td width='650px' align='center'>
			<div style='margin:0 auto; width:644px; border:3px solid #dcdcdc;'>
				<div style='width:644px; margin-bottom:20px;'><img src='{$http_set}joagift.co.kr/new/images/mail/admin_oktit.png' alt='쇼핑몰 신청을 환영합니다.' /></div>

                <div style='width:600px; margin:0 auto;'>
                	<div style='margin-left:10px; font-size:12px; line-height:18px; letter-spacing:0px; color:#888; text-align:left;'>
                    	<p>조아기프트(주)의 쇼핑몰 신청을 환영합니다.<br/>
                    </div>

                    <!--신청자 정보-->
                    <div style='margin-top:20px; padding:0; line-height:16px;'>
                    <table width='100%' cellpadding='0' cellspacing='0' >
                    <tr>
                    	<td colspan='2' align='left' style='border-bottom:2px solid #dcdcdc; '><img src='{$http_set}joagift.co.kr/new/images/mail/tm_01.gif' alt='신청자정보' /></td>
                    </tr>
                    <tr>
                    	<td style='border-bottom:1px dotted #dcdcdc;' height='28' width='85'><img src='{$http_set}joagift.co.kr/new/images/mail/tm01.gif' alt='성명' ></td>
                        <td style='border-bottom:1px dotted #dcdcdc; padding-left:10px; font-size:11px; line-height:18px; letter-spacing:0px; color:#4e4e4e; text-align:left;' height='28'>".$row[Oname]."</td>
                    </tr>
                    <tr>
                    	<td style='border-bottom:1px dotted #dcdcdc;' height='28' width='85'><img src='{$http_set}joagift.co.kr/new/images/mail/tm02.gif' alt='나이' ></td>
                        <td style='border-bottom:1px dotted #dcdcdc; padding-left:10px; font-size:11px; line-height:18px; letter-spacing:0px; color:#4e4e4e; text-align:left;' height='28'>".$row[age]."</td>
                    </tr>
                    <tr>
                    	<td style='border-bottom:1px dotted #dcdcdc;' height='28' width='85'><img src='{$http_set}joagift.co.kr/new/images/mail/tm03.gif' alt='연락처' ></td>
                        <td style='border-bottom:1px dotted #dcdcdc; padding-left:10px; font-size:11px; line-height:18px; letter-spacing:0px; color:#4e4e4e; text-align:left;' height='28'>".$row[Ohp]." / ".$row[Ophone]."</td>
                    </tr>
                    <tr>
                    	<td style='border-bottom:1px dotted #dcdcdc;' height='28' width='85'><img src='{$http_set}joagift.co.kr/new/images/mail/tm04.gif' alt='주소' ></td>
                        <td style='border-bottom:1px dotted #dcdcdc; padding-left:10px; font-size:11px; line-height:18px; letter-spacing:0px; color:#4e4e4e; text-align:left; word-break:break-all;' height='28'>".$row[Oaddr]."</td>
                    </tr>
                    </table>
                    </div>

                    <!--결제정보-->
                    <div style='margin-top:20px; padding:0; line-height:16px;'>
                    <table width='100%' cellpadding='0' cellspacing='0'>
                    <tr>
                    	<td colspan='2' align='left' style='border-bottom:2px solid #dcdcdc;'><img src='{$http_set}joagift.co.kr/new/images/mail/tm_02.gif' alt='결제정보' /></td>
                    </tr>
					<tr>
                    	<td style='border-bottom:1px dotted #dcdcdc;' height='28' width='85'><img src='{$http_set}joagift.co.kr/new/images/mail/tm06.gif' alt='결제시간' ></td>
                        <td style='border-bottom:1px dotted #dcdcdc; padding-left:10px; font-size:11px; line-height:18px; letter-spacing:0px; color:#4e4e4e; text-align:left;' height='28'>".date('Y-m-d H:i:s',$row[Orderday2])."</td>
                    </tr>";

$SMail	.=	"		<tr>
                    	<td style='border-bottom:1px dotted #dcdcdc;' height='28' width='85'><img src='{$http_set}joagift.co.kr/new/images/mail/tm07.gif' alt='납부금액' ></td>
                        <td style='border-bottom:1px dotted #dcdcdc; padding-left:10px; font-size:11px; line-height:18px; letter-spacing:0px; color:#4e4e4e; text-align:left;' height='28'><span style='font-weight:bold;'>".PayResultView('A',$orderno,$row[Orderday2],$row[Oname],$row[Ohp],$row[url])."</span></td>
					</tr>
					<tr>
                    	<td style='border-bottom:2px solid #dcdcdc;' height='28' width='85'><img src='{$http_set}joagift.co.kr/new/images/mail/tm05.gif' alt='납부방법' ></td>
                        <td style='border-bottom:2px solid #dcdcdc; padding-left:10px; font-size:11px; line-height:18px; letter-spacing:0px; color:#4e4e4e; text-align:left;' height='28'><span style='font-weight:bold;'>".PayResultView('C',$orderno,$row[Orderday2],$row[Oname],$row[Ohp],$row[url])."</span></td>
                    </tr>";

$SMail	.=	"		</tr>
                    </table>
                    </div>

                    <div style='margin:30px 0; text-align:center;'>
                    <img src='{$http_set}joagift.co.kr/new/images/mail/tm_info.gif' alt='신청진행사항'/>
                    </div>

                </div>

                <div style='width: 644px; border-top: solid 3px #dcdcdc; margin-top:0;'>

                    <div style='width:570px; margin:10px 30px; border-bottom:solid 1px #dcdcdc; font-size:11px; letter-spacing:0px; line-height:16px; color:#7d7d7d; text-align:left;'>
                        <!--p style='padding-bottom:7px; margin:0; padding:0;'>본 메일은 정보통신망 이용촉진 및 정보보호등에 관한 법률에 의거 2018년 12월 1일 기준으로 회원님의 이메일 수신 동의여부를 확인한 결과 회원님께서 수신에 동의하셨기에 회원가입 또는 개인정보 수정시 기재해 주신 이메일 주소로 [광고]를 표시하지 않고 발송되는 메일입니다.</p-->
                        <p style='padding-bottom:7px; '>본 메일은 발신전용이므로 회신되지 않습니다. 문의사항은 고객센터를 이용해 주십시오.<br />
                                <b>고객센터 :<font style='color:#CC0000; font-size:12px;'> 1544-6233</font></b> (월~금 : 오전 9시~오후 9시 / 토 : 오전 9시~오후 6시)</p>
                    </div>
                    <div style='width:570px; margin:15px 30px; font-size:11px; letter-spacing:0px; color:#7d7d7d; line-height:16px; border:0; text-align:left;'>
                    	조아기프트(주) 서울특별시 금천구 가산디지털1로 165 가산비지니스센터 9층 [대표이사 : 김재점]<br />
                        사업자등록번호 : 214-87-25687 | 통신판매업신고 : 제 서울금천-0276 호 | 부가통신판매업신고 : 제 4644 호<br />
                        고객센터 : <b>1544-6233(代)</b> | FAX : 070-5092-5630 | eMail : <a href='mailto:webmaster@joagift.com'>webmaster@joagift.com</a><br />
                        Copyright <b>ⓒ조아기프트(주)</b> All Rights Reserved.
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

###############################################################  실제 메일보내기 시작
$MESSAGE = $SMail;
$MESSAGE = stripslashes($MESSAGE);

// 멤버email
$TO = $row[Oemail];

$TITLE = "조아기프트(주)의 쇼핑몰 신청이 완료 되었습니다.";
$SUBJECT = $TITLE;
$FROM = "webmaster@joagift.com";

function SendMail($TO,$FROM,$SUBJECT,$MESSAGE){

	$headers  = "From: 조아기프트(주)<".$FROM.">\r\n";
    $headers .= "Reply-To: ".$FROM."\r\n";
	$headers .= 'MIME-Version: 1.0' . "\n";
	$headers .= 'Content-type: text/html; charset=EUC-KR' . "\r\n";

	mail($TO,$SUBJECT,$MESSAGE,$headers, '-f'.$FROM);
}   // 함수...

SendMail($TO,$FROM,$SUBJECT,$MESSAGE);





			$msg_user = $row[Oname]."님 쇼핑몰 신청이 완료되었습니다. \n주문번호 : ".$orderno." \n담당자가 연락드리겠습니다.\n감사합니다";
			
			$msg = "쇼핑몰 신청 \n".$row[Oname]."님 ,".$row[Ohp]."\n주문번호:".$orderno;
			$subject = "쇼핑몰 신청";
			$oname=$row[Oname];

			$hp = get_shoporder_tel();
			$joa_tel = $joa_tel_080618;
			
			//smsSend("lms", $oname, $row[Ohp], $msg_user, $joa_tel_080618,$subject); // 고객에게 발송
			kakaotalk_msg($row[Ohp], $joa_tel_080618, "B_JA_06_02_02485", $msg_user, '');

			//smsSend("lms", $oname, $hp, $msg, $joa_tel_080618,$subject); // 박과장님에게 발송
			kakaotalk_msg($hp, $joa_tel_080618, "B_JA_06_02_02483", $msg, '');




//echo "TO : ".$TO." || FROM : ".$FROM." || TITLE : ".$SUBJECT ."|| SUBJECT : ".$MESSAGE;
//alert();
############################################################## 메일보내기 끝

?>

<? include("../footer.html") ?>