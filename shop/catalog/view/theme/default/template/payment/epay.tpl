<div class="content" style="text-align: center;">
<?php
	if($epay_availablepayments_a){
		echo '<img src="catalog/view/epay/logos/1.gif"> <img src="catalog/view/epay/logos/2.gif"> ';
	}
	if($epay_availablepayments_b){
		echo '<img src="catalog/view/epay/logos/7.gif"> <img src="catalog/view/epay/logos/9.gif"> <img src="catalog/view/epay/logos/10.gif"> <img src="catalog/view/epay/logos/11.gif">';
	}
	if($epay_availablepayments_c){
		echo '<img src="catalog/view/epay/logos/12.gif"> ';
	}
	if($epay_availablepayments_d){
		echo '<img src="catalog/view/epay/logos/13.gif"> ';
	}
	if($epay_availablepayments_e){
		echo '<br /><img src="catalog/view/epay/logos/6.gif"> ';
	}
	if($epay_availablepayments_f){
		echo '<img src="catalog/view/epay/logos/5.gif"> ';
	}
	if($epay_availablepayments_g){
		echo '<img src="catalog/view/epay/logos/4.gif"> ';
	}
	if($epay_availablepayments_h){
		echo '<img src="catalog/view/epay/logos/3.gif"> ';
	}
	
	echo "<br /><br />";
	
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
?>
</div>
<div class="buttons">
  <table>
    <tr>
      <td align="left"><a onclick="location = '<?php echo str_replace('&', '&amp;', $back); ?>'" class="button"><span><?php echo $button_back; ?></span></a></td>
      <td align="right"><a id="checkout" class="button"><span><?php echo $button_confirm; ?></span></a></td>
    </tr>
  </table>
</div>
<script type="text/javascript"><!--
$('#checkout').click(function() {
			location = '<?php echo $continue; ?>';	

});
//--></script>
