<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content">
  <div class="top">
    <div class="left"></div>
    <div class="right"></div>
    <div class="center">
      <h1><?php echo $heading_title; ?></h1>
    </div>
  </div>
  <div class="middle">
<?php
if(isset($_GET['errortext'])){
	echo "  <div class=\"warning\">".htmlentities($_REQUEST['errortext']) ."</div>";
}
?>
<?php
if(isset($_GET['md5error'])){
	echo "  <div class=\"warning\">MD5 error</div>";
}
?>

<?php
if($window == 0){
?>
<div id="overlay">
</div>

<div id="please_wait">
	<div class="please_wait_content"><?php echo $text_pleasewait ?>
	<br /><span class="dotA">.</span><span class="dotB">.</span><span class="dotC">.</span></div>
</div>
<form action="https://ssl.ditonlinebetalingssystem.dk/auth/default.aspx" method="post" autocomplete="off" name="epay" id="epay">
<?php
}else{
?>
<div style="text-align: center;">	<?php
	if($epay_logo_a){
		echo '<img src="catalog/view/epay/logos/epay.gif">&nbsp;&nbsp;&nbsp; ';
	}
	if($epay_logo_b){
		echo '<img src="catalog/view/epay/logos/pbs.gif">&nbsp;&nbsp;&nbsp; ';
	}
	if($epay_logo_c){
		echo '<img src="catalog/view/epay/logos/euroline.gif"> ';
	}
	if($epay_logo_d){
		echo '<img src="catalog/view/epay/logos/pci.gif"> ';
	}
	if($epay_logo_e){
		echo '<img src="catalog/view/epay/logos/visa_secure.gif"> <img src="catalog/view/epay/logos/mastercard_securecode.gif">';
	}
	?></div><br />
<form action="https://ssl.ditonlinebetalingssystem.dk/popup/default.asp" method="post" name="ePay" target="ePay_window" id="ePay">
<?php
}
?>

	<input type="hidden" name="cms" value="opencart" />
    <input type="hidden" name="merchantnumber" value="<?php echo $merchantnumber ?>" />
    <input type="hidden" name="orderid" value="<?php echo $orderid ?>" />
    <input type="hidden" name="amount" value="<?php echo $amount*100 ?>" />
    <input type="hidden" name="currency" value="<?php echo $currency ?>" />
    <input type="hidden" name="accepturl" value="<?php echo $accepturl ?>" />
    <input type="hidden" name="callbackurl" value="<?php echo $callbackurl ?>" />
    <input type="hidden" name="declineurl" value="<?php echo $declineurl ?>" />
	<input type="hidden" name="instantcallback" value="1" />
	<input type="hidden" name="HTTP_COOKIE" value="<?php echo getenv("HTTP_COOKIE") ?>">
	<input type="hidden" name="language" value="<?php echo $language ?>">
	<input type="hidden" name="description" value="<?php echo urlencode(utf8_decode($comment)); ?>">
	
	<?php
		if($md5mode == 2){
			echo '<input type="hidden" name="md5key" value="'.$md5key.'">';
		}
		
		if($customerfee == 1 and $window == 0){
			echo '<input type="hidden" name="transfee" value="0">';
		}elseif($customerfee == 1){
			echo '<input type="hidden" name="addfee" value="1">';
		}
		
		if(strlen($group) > 0){
			echo '<input type="hidden" name="group" value="'.$group.'">';
		}
		
		if(strlen($authsms) > 0){
			echo '<input type="hidden" name="authsms" value="'.$authsms.'">';
		}
		
		if(strlen($authemail) > 0){
			echo '<input type="hidden" name="authmail" value="'.$authemail.'">';
		}
		
		if($splitpayment == 1){
			echo '<input type="hidden" name="splitpayment" value="1">';
		}
		
		if($instantcapture == 1){
			echo '<input type="hidden" name="instantcapture" value="1">';
		}
		

	?>
	
	<?php
	if($window == 1 or $window == 2){
	?>
	
		<?php
		echo '<input type="hidden" name="ownreceipt" value="'.$ownreceipt.'">';
		echo '<input type="hidden" name="windowstate" value="'.$window.'">';
		?>
	
		<script type="text/javascript" src="http://www.epay.dk/js/standardwindow.js"></script>
		
		<script type="text/javascript">
			$(document).ready(function() {
				
				open_ePay_window();
			
			});
		</script>
		<?php
		echo $stdwindow;
		?>
	<?php
	}elseif($window == 0){
	?>
	
		<link rel="stylesheet" type="text/css" href="<?php echo HTTPS_SERVER ?>catalog/view/epay/dd.css" />
		<script language="javascript" type="text/javascript" src="<?php echo HTTPS_SERVER ?>catalog/view/epay/jquery.dd.js"></script>   
		<script type="text/javascript">
			function checkForm()
			{
				
				$("#overlay").show();
				$("#please_wait").show();
				$("#submitknap").attr("disabled", "true");
				
				animatePleaseWait();	
				
				return true;
			}

			var dots = 0;

			function animatePleaseWait(){
					
				
				if(dots == 0){
					$(".dotA").hide();
					$(".dotB").hide();
					$(".dotC").hide();
				}	
				
				
				if(dots > 0){
					$(".dotA").show();	
				}
				
				if(dots > 1){
					$(".dotB").show();
				}
				
				if(dots > 2){
					$(".dotC").show();
				}
				
				if(dots == 3){
					dots = 0;
				}else{
					dots = dots+1;
				}

				setTimeout("animatePleaseWait()",200);

			}
			
			function jumpToCVC()
			{
				document.getElementById('divCVC').style.display='block';
				$('html, body').animate({scrollTop:$('#divCVC').attr('scrollHeight')}, 'slow');
			}

      		function getFee(cardno, acq)
	        {
			<?php if ($customerfee != 1) echo "return;\n";	?>
                document.getElementById("calculatedFee").innerHTML = '<br /><?php echo $text_pleasewait; ?>';
                
                if (cardno.length < 6) {
					$('#submitknap').hide();
                    return false;
                  }  	    
                cardno = cardno.substr(0,6);
	        		
	            var xmlHttpReq = false;
	            var self = this;
	            // Mozilla/Safari
	            if (window.XMLHttpRequest) {
	                self.xmlHttpReq = new XMLHttpRequest();
	            }
	            // IE
	            else if (window.ActiveXObject) {
	                self.xmlHttpReq = new ActiveXObject("Microsoft.XMLHTTP");
	            }
	            self.xmlHttpReq.open('POST', "<?php echo "https://relay.ditonlinebetalingssystem.dk/relay/v2/relay.cgi/" . HTTPS_SERVER ?>catalog/view/epay/webservice_fee.php?merchantnumber=<?php echo $merchantnumber ?>&cardno_prefix=" + cardno + "&acquirer=" + acq + "&amount=<?php echo $totals[$totalsmax]['value']*100 ?>&currency=<?php echo $currency ?>", true);
                       	
						self.xmlHttpReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        self.xmlHttpReq.onreadystatechange = function() {
                        if (self.xmlHttpReq.readyState == 4) {
                        var returnvalues = self.xmlHttpReq.responseText.split(",");
                        if (returnvalues.length == 3) {
                        var fee = returnvalues[0];
                        var cardtype = returnvalues[1];
                        var cardtext = returnvalues[2];
                        document.getElementById("calculatedFee").innerHTML = '<br /><?php echo $this->session->data['currency'] ?> ' + fee / 100 +'&nbsp;&nbsp;&nbsp;( '+ cardtext +' ) ';
                        document.forms['epay'].transfee.value = fee;
						$('#submitknap').show();
          
                        } else {
      
                        var epayresponse = returnvalues[0];
                        document.getElementById("calculatedFee").innerHTML = '<br />Error (' + epayresponse + ')';
						$('#submitknap').hide();
	                		
                        }
                        }
                        }
                        self.xmlHttpReq.send();
            }
			
			
			$(document).ready(function() {
			$('#paymenttype').msDropDown();	
			});
			
			
			function changepaymenttype(){
				document.getElementById('divCVC').style.display='none';
				<?php if ($customerfee == 1){	?>
				$('#submitknap').hide();
				<?php
				}
				?>
				if($('#paymenttype').val() == 1){
					cardno = document.forms['epay'].cardno.value;
					acq = '1';
					$('#standardpayment').show();
				}else{
					$('#standardpayment').hide();
				}
				
				if($('#paymenttype').val() == 20){ cardno = '000012'; acq = '1'; }
				if($('#paymenttype').val() == 17){ cardno = '000017'; acq = '5'; }
				if($('#paymenttype').val() == 21){ cardno = '000021'; acq = '4'; }
				if($('#paymenttype').val() == 22){ cardno = '000022'; acq = '3'; }

				getFee(cardno, acq);
				
				<?php if ($customerfee == 1){	?>
				if($('#paymenttype').val() == 1){
					document.getElementById("calculatedFee").innerHTML = '';
				}
				<?php
				}
				?>
			}
		</script>

		<table cellspacing="0" cellpadding="2" border="0" width="100%">
			<tr>
				<td style="width:150px;"><?php echo $text_payment_method ?></td>
				<td>&nbsp;&nbsp;
					<select id="paymenttype" name="paymenttype" onchange="changepaymenttype()">
						<?php
						if($payments_a == 1 or $payments_b == 1 or $payments_c == 1 or $payments_d == 1 or $payments_e == 1){
					
						echo '<option value="1" title="'. HTTPS_SERVER .'catalog/view/epay/payments/creditcards_small.png">'.$text_payment_card.'</option>';
				
						}
						?>
						
						<?php
						if($payments_a == 1 and $currency == 208){
					
						echo '<option value="20" title="'. HTTPS_SERVER .'catalog/view/epay/payments/edankort_small.gif">eDankort</option>';
			
						}
						?>
						
						<?php
						if($payments_f == 1){
			
						echo '<option value="17" title="'. HTTPS_SERVER .'catalog/view/epay/payments/ewire_small.png">EWIRE</option>';
				
						}
						?>
						
						<?php
						if($payments_g == 1){
		
						echo '<option value="21" title="'. HTTPS_SERVER .'catalog/view/epay/payments/nordea_small.gif">NORDEA</option>';
			
						}
						?>
						
						<?php
						if($payments_h == 1){
		
						echo '<option value="22" title="'. HTTPS_SERVER .'catalog/view/epay/payments/danske_small.png">Danske Bank</option>';
		
						}
						?>
						
					</select>
					<script type="text/javascript">
					$(document).ready(function() {
						changepaymenttype();
					});
					
					</script>
				</td>
			</tr>

			<tr>
				<td><br /><?php echo $text_amount ?></td>
				<td><br />&nbsp;&nbsp;<span class="price"><?php echo $totals[$totalsmax]['text'] ?></span></td>
			</tr>
			<?php if ($customerfee == 1){ ?>
			<tr>
				<td><br /><?php echo $text_transactionfee ?></td>
				<td id="calculatedFee"></td>
			</tr>
			<?php } ?>
			<tr>
				<td><br /><?php echo $text_orderid ?></td>
				<td><br />&nbsp;&nbsp;<?php echo $orderid ?></td>
			</tr>
			
			</table>
			<div id="standardpayment" style="display: none;">
			<table cellspacing="0" cellpadding="2" border="0" width="100%">
			<?php if ($cardholder == 1){ ?>
			<tr>
				<td><br /><?php echo $text_cardholder ?></td>
				<td><br /><input type="text" name="cardholder" style="width: 180px"></td>
			</tr>
			<?php } ?>
			<tr>
				<td style="width:150px;"><br /><strong><?php echo $text_cardnumber ?></strong></td>
				<td><br /><input type="text" onkeyup="getFee(this.value, 1)" id="cardno" name="cardno" maxlength="20" style="width: 180px" /></td>
			</tr>
			<tr>
				<td><br /><strong><?php echo $text_valid ?></strong></td>
				<td>
					<br /><select name="expmonth" style="width: 45px">
						<option value="01">01</option>
						<option value="02">02</option>
						<option value="03">03</option>
						<option value="04">04</option>
						<option value="05">05</option>
						<option value="06">06</option>
						<option value="07">07</option>
						<option value="08">08</option>
						<option value="09">09</option>
						<option value="10">10</option>
						<option value="11">11</option>
						<option value="12">12</option>
					</select>&nbsp;<select name="expyear" style="width: 45px">
						<option value="09">09</option>
						<option value="10">10</option>
						<option value="11">11</option>
						<option value="12">12</option>
						<option value="13">13</option>
						<option value="14">14</option>
						<option value="15" SELECTED>15</option>
						<option value="16">16</option>
						<option value="17">17</option>
						<option value="18">18</option>
						<option value="19">19</option>
						<option value="20">20</option>
					</select>
				</td>
			</tr>
			<tr>
				<td><br /><strong><?php echo $text_controlciffers ?></strong></td>
				<td><br /><input type="text" name="cvc" maxlength="4" style="width: 50px" /> <a href="javascript:void(0);" onclick="javascript:jumpToCVC();">?</a>
				</td>
			</tr>
			</table>
			</div>
			<a name="a_divCVC"></a>
<div id="divCVC" class="content" name="divCVC" style="display: none;">
	<h3><?php echo $text_controlciffers ?></h3>
	<table cellspacing="0" cellpadding="10" border="0" style="width:100%;"> 
		<tbody>
		<tr>
			<td>
				<table width="100%" border="0">
					<tr>
						<td valign="top">
							<img src="catalog/view/epay/cvc_dk.gif" border="0">
							<br>
							<?php echo $text_cvcdankort; ?>
						</td>
						<td valign="top" style="width: 10px;">&nbsp;</td>
						<td valign="top">
							<img src="catalog/view/epay/cvc_master.gif" border="0">
							<br>
							<?php echo $text_cvcmaster; ?>
						</td>
					</tr>
				</table>			
			</td>
		</tr>
		</tbody>
	</table>
</div>
			      <div class="buttons">
        <table>
          <tr>
            <td align="left"><a class="button" style="text-decoration: none;" href="<?php echo $back ?>"><span><?php echo $button_back ?></span></a></td>
            <td align="right"><a onclick="$('#epay').submit();checkForm();" id="submitknap" class="button"><span><?php echo $button_continue; ?></span></a></td>
          </tr>
        </table>
      </div>
					
</form>
					<script type="text/javascript">
					$(document).ready(function() {
						<?php if ($customerfee == 1) echo "$('#submitknap').hide()";	?> 
					});
					
					</script>
<!-- ePay relay url replacement for links and forms -->
<SCRIPT TYPE="TEXT/JAVASCRIPT" SRC="https://relay.ditonlinebetalingssystem.dk/relay/v2/replace_relay_urls.js"></SCRIPT>
<?php
}
?>
<?php
if($window == 1 or $window == 2){
?>
    <br /><br /> <div class="buttons">   <table>
          <tr>
            <td align="left"><a class="button" style="text-decoration: none;" href="<?php echo $back ?>"><span><?php echo $button_back ?></span></a></td>
          </tr>
        </table></div>
<?php
}

?>
  </div>
  <div class="bottom">
    <div class="left"></div>
    <div class="right"></div>
    <div class="center"></div>
  </div>
</div>
<?php echo $footer; ?> 