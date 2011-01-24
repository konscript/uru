<?php
class ControllerPaymentepay extends Controller {
	protected function index() {
		$this->language->load('payment/epay');
		
		$this->data['text_instruction'] = $this->language->get('text_instruction');
		$this->data['text_payment'] = $this->language->get('text_payment');
		
		$this->data['button_confirm'] = $this->language->get('button_confirm');
		$this->data['button_back'] = $this->language->get('button_back');
		
		$this->data['epay_availablepayments_a'] = $this->config->get('epay_availablepayments_a');
		$this->data['epay_availablepayments_b'] = $this->config->get('epay_availablepayments_b');
		$this->data['epay_availablepayments_c'] = $this->config->get('epay_availablepayments_c');
		$this->data['epay_availablepayments_d'] = $this->config->get('epay_availablepayments_d');
		$this->data['epay_availablepayments_e'] = $this->config->get('epay_availablepayments_e');
		$this->data['epay_availablepayments_f'] = $this->config->get('epay_availablepayments_f');
		$this->data['epay_availablepayments_g'] = $this->config->get('epay_availablepayments_g');
		$this->data['epay_availablepayments_h'] = $this->config->get('epay_availablepayments_h');

		$this->data['epay_logo_a'] = $this->config->get('epay_logo_a');
		$this->data['epay_logo_b'] = $this->config->get('epay_logo_b');
		$this->data['epay_logo_c'] = $this->config->get('epay_logo_c');
		$this->data['epay_logo_d'] = $this->config->get('epay_logo_d');
		$this->data['epay_logo_e'] = $this->config->get('epay_logo_e');

		if(intval($this->config->get('epay_payment_window')) == 0){
		$this->data['continue'] = 'https://relay.ditonlinebetalingssystem.dk/relay/v2/relay.cgi/'. HTTPS_SERVER . 'index.php?route=checkout/epay&forcerelay=1&HTTP_COOKIE='.getenv("HTTP_COOKIE");
		}else{
		$this->data['continue'] = HTTPS_SERVER . 'index.php?route=checkout/epay&HTTP_COOKIE='.getenv("HTTP_COOKIE");	
		}
		
		if($this->request->get['route'] == 'checkout/confirm'){
			$this->data['back'] = HTTPS_SERVER . 'index.php?route=checkout/payment';
		}elseif ($this->request->get['route'] != 'checkout/guest_step_3') {
			$this->data['back'] = HTTPS_SERVER . 'index.php?route=checkout/confirm';
		} else {
			$this->data['back'] = HTTPS_SERVER . 'index.php?route=checkout/guest_step_2';
		}
		
		$this->id = 'payment';

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/epay.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/epay.tpl';
		} else {
			$this->template = 'default/template/payment/epay.tpl';
		}	
		
		$this->render(); 
	}
	
	static function getCardnameById($cardid) {
		switch ($cardid) {
			case 1: return 'DANKORT';
			case 2: return 'VISA DANKORT';
			case 3: return 'VISA ELECTRON FOREIGN';
			case 4: return 'MASTERCARD';
			case 5: return 'MASTERCARD FOREIGN';
			case 6: return 'VISA ELECTRON';
			case 7: return 'JCB';
			case 8: return 'DINERS';
			case 9: return 'MAESTRO';
			case 10: return 'AMERICAN EXPRESS';
			case 12: return 'EDK';
			case 13: return 'DINERS FOREIGN';
			case 14: return 'AMERICAN EXPRESS FOREIGN';
			case 15: return 'MAESTRO FOREIGN';
			case 16: return 'FORBRUGSFORENINGEN';
			case 17: return 'EWIRE';
			case 18: return 'VISA';
			case 19: return 'IKANO';
			case 20: return 'OTHERS';
			case 21: return 'Nordea e-betaling';
			case 22: return 'Danske Netbetaling';
			case 24: return 'LIC MASTERCARD';
			case 25: return 'LIC MASTERCARD FOREIGN';
		}
		return 'unknown';
	}
	
	public function confirm() {
		$this->language->load('payment/epay');
		
		$this->load->model('checkout/order');
		
		$amount = $this->currency->format($_GET['amount']/100, $this->session->data['currency'], FALSE, TRUE);
		
		$comment = $this->language->get('payment_process') . $amount;
		$comment .= $this->language->get('payment_with_transactionid') . $_GET['tid'];
		$comment .= $this->language->get('payment_card') . $this->getCardnameById($_GET['cardid']) . ' - XXXX XXXX XXXX ' . $_GET['cardnopostfix'];
			
		
			if($this->config->get('epay_md5mode') == 1 or $this->config->get('epay_md5mode') == 2){
				$md5 = 0;
				
				if(isset($_GET['eKey'])){
					$thiskey = $_GET['amount'] . $_GET['orderid'] . $_GET['tid'] . $this->config->get('epay_md5key');
					$epaykey = $_GET['eKey'];
					
						if(md5($thiskey) == $epaykey){
							$md5 = 1;
						}
					
				}else{
					$md5 = 0;
				}
		
			}else{
				$md5 = 1;
			}
		
			if($md5 == 1){
				$this->model_checkout_order->confirm($_GET['orderid'], $this->config->get('epay_order_status_id'), $comment);
				echo "OK";
			}else{
				header('HTTP/1.1 500 Internal Server Error');
				header("Status: 500 Internal Server Error");
			}
				

		
	}
	
	public function accept() {
			if($this->config->get('epay_md5mode') == 1 or $this->config->get('epay_md5mode') == 2){
				$md5 = 0;
				
				if(isset($_GET['eKey'])){
					$thiskey = $_GET['amount'] . $_GET['orderid'] . $_GET['tid'] . $this->config->get('epay_md5key');
					$epaykey = $_GET['eKey'];
					
						if(md5($thiskey) == $epaykey){
							$md5 = 1;
						}
					
				}else{
					$md5 = 0;
				}
		
			}else{
				$md5 = 1;
			}
			
			if($md5 == 1){
				$this->redirect(HTTPS_SERVER . 'index.php?route=checkout/success');
			}else{
				if(intval($this->config->get('epay_payment_window')) == 0){
					$this->redirect('https://relay.ditonlinebetalingssystem.dk/relay/v2/relay.cgi/'. HTTPS_SERVER . 'index.php?route=checkout/epay&forcerelay=1&md5error=1&HTTP_COOKIE='.getenv("HTTP_COOKIE"));		
				}else{
					$this->redirect(HTTPS_SERVER . 'index.php?route=checkout/epay&md5error=1&HTTP_COOKIE='.getenv("HTTP_COOKIE"));			
				}
			}

	}	
}
?>