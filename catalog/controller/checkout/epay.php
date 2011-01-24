<?php 
class ControllerCheckoutepay extends Controller {
	private $error = array();


  
	function get_iso_code($code) {
    switch (strtoupper($code)){
    	case 'ADP': return '020'; break;
		case 'AED': return '784'; break;
		case 'AFA': return '004'; break;
		case 'ALL': return '008'; break;
		case 'AMD': return '051'; break;
		case 'ANG': return '532'; break;
		case 'AOA': return '973'; break;
		case 'ARS': return '032'; break;
		case 'AUD': return '036'; break;
		case 'AWG': return '533'; break;
		case 'AZM': return '031'; break;
		case 'BAM': return '977'; break;
		case 'BBD': return '052'; break;
		case 'BDT': return '050'; break;
		case 'BGL': return '100'; break;
		case 'BGN': return '975'; break;
		case 'BHD': return '048'; break;
		case 'BIF': return '108'; break;
		case 'BMD': return '060'; break;
		case 'BND': return '096'; break;
		case 'BOB': return '068'; break;
		case 'BOV': return '984'; break;
		case 'BRL': return '986'; break;
		case 'BSD': return '044'; break;
		case 'BTN': return '064'; break;
		case 'BWP': return '072'; break;
		case 'BYR': return '974'; break;
		case 'BZD': return '084'; break;
		case 'CAD': return '124'; break;
		case 'CDF': return '976'; break;
		case 'CHF': return '756'; break;
		case 'CLF': return '990'; break;
		case 'CLP': return '152'; break;
		case 'CNY': return '156'; break;
		case 'COP': return '170'; break;
		case 'CRC': return '188'; break;
		case 'CUP': return '192'; break;
		case 'CVE': return '132'; break;
		case 'CYP': return '196'; break;
		case 'CZK': return '203'; break;
		case 'DJF': return '262'; break;
		case 'DKK': return '208'; break;
		case 'DOP': return '214'; break;
		case 'DZD': return '012'; break;
		case 'ECS': return '218'; break;
		case 'ECV': return '983'; break;
		case 'EEK': return '233'; break;
		case 'EGP': return '818'; break;
		case 'ERN': return '232'; break;
		case 'ETB': return '230'; break;
		case 'EUR': return '978'; break;
		case 'FJD': return '242'; break;
		case 'FKP': return '238'; break;
		case 'GBP': return '826'; break;
		case 'GEL': return '981'; break;
		case 'GHC': return '288'; break;
		case 'GIP': return '292'; break;
		case 'GMD': return '270'; break;
		case 'GNF': return '324'; break;
		case 'GTQ': return '320'; break;
		case 'GWP': return '624'; break;
		case 'GYD': return '328'; break;
		case 'HKD': return '344'; break;
		case 'HNL': return '340'; break;
		case 'HRK': return '191'; break;
		case 'HTG': return '332'; break;
		case 'HUF': return '348'; break;
		case 'IDR': return '360'; break;
		case 'ILS': return '376'; break;
		case 'INR': return '356'; break;
		case 'IQD': return '368'; break;
		case 'IRR': return '364'; break;
		case 'ISK': return '352'; break;
		case 'JMD': return '388'; break;
		case 'JOD': return '400'; break;
		case 'JPY': return '392'; break;
		case 'KES': return '404'; break;
		case 'KGS': return '417'; break;
		case 'KHR': return '116'; break;
		case 'KMF': return '174'; break;
		case 'KPW': return '408'; break;
		case 'KRW': return '410'; break;
		case 'KWD': return '414'; break;
		case 'KYD': return '136'; break;
		case 'KZT': return '398'; break;
		case 'LAK': return '418'; break;
		case 'LBP': return '422'; break;
		case 'LKR': return '144'; break;
		case 'LRD': return '430'; break;
		case 'LSL': return '426'; break;
		case 'LTL': return '440'; break;
		case 'LVL': return '428'; break;
		case 'LYD': return '434'; break;
		case 'MAD': return '504'; break;
		case 'MDL': return '498'; break;
		case 'MGF': return '450'; break;
		case 'MKD': return '807'; break;
		case 'MMK': return '104'; break;
		case 'MNT': return '496'; break;
		case 'MOP': return '446'; break;
		case 'MRO': return '478'; break;
		case 'MTL': return '470'; break;
		case 'MUR': return '480'; break;
		case 'MVR': return '462'; break;
		case 'MWK': return '454'; break;
		case 'MXN': return '484'; break;
		case 'MXV': return '979'; break;
		case 'MYR': return '458'; break;
		case 'MZM': return '508'; break;
		case 'NAD': return '516'; break;
		case 'NGN': return '566'; break;
		case 'NIO': return '558'; break;
		case 'NOK': return '578'; break;
		case 'NPR': return '524'; break;
		case 'NZD': return '554'; break;
		case 'OMR': return '512'; break;
		case 'PAB': return '590'; break;
		case 'PEN': return '604'; break;
		case 'PGK': return '598'; break;
		case 'PHP': return '608'; break;
		case 'PKR': return '586'; break;
		case 'PLN': return '985'; break;
		case 'PYG': return '600'; break;
		case 'QAR': return '634'; break;
		case 'ROL': return '642'; break;
		case 'RUB': return '643'; break;
		case 'RUR': return '810'; break;
		case 'RWF': return '646'; break;
		case 'SAR': return '682'; break;
		case 'SBD': return '090'; break;
		case 'SCR': return '690'; break;
		case 'SDD': return '736'; break;
		case 'SEK': return '752'; break;
		case 'SGD': return '702'; break;
		case 'SHP': return '654'; break;
		case 'SIT': return '705'; break;
		case 'SKK': return '703'; break;
		case 'SLL': return '694'; break;
		case 'SOS': return '706'; break;
		case 'SRG': return '740'; break;
		case 'STD': return '678'; break;
		case 'SVC': return '222'; break;
		case 'SYP': return '760'; break;
		case 'SZL': return '748'; break;
		case 'THB': return '764'; break;
		case 'TJS': return '972'; break;
		case 'TMM': return '795'; break;
		case 'TND': return '788'; break;
		case 'TOP': return '776'; break;
		case 'TPE': return '626'; break;
		case 'TRL': return '792'; break;
		case 'TRY': return '949'; break;
		case 'TTD': return '780'; break;
		case 'TWD': return '901'; break;
		case 'TZS': return '834'; break;
		case 'UAH': return '980'; break;
		case 'UGX': return '800'; break;
		case 'USD': return '840'; break;
		case 'UYU': return '858'; break;
		case 'UZS': return '860'; break;
		case 'VEB': return '862'; break;
		case 'VND': return '704'; break;
		case 'VUV': return '548'; break;
		case 'XAF': return '950'; break;
		case 'XCD': return '951'; break;
		case 'XOF': return '952'; break;
		case 'XPF': return '953'; break;
		case 'YER': return '886'; break;
		case 'YUM': return '891'; break;
		case 'ZAR': return '710'; break;
		case 'ZMK': return '894'; break;
		case 'ZWD': return '716'; break;
    }
    //
    // As default return 208 for Danish Kroner
    //
    return '208';
  }
  

 	
  	public function index() {
    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$shipping = explode('.', $this->request->post['shipping_method']);
			
			$this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
			
			$this->session->data['comment'] = strip_tags($this->request->post['comment']);

	  		$this->redirect(HTTPS_SERVER . 'index.php?route=checkout/payment');
    	}
		
		if (!$this->cart->hasProducts() || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
	  		$this->redirect(HTTPS_SERVER . 'index.php?route=checkout/cart');
    	}
		
		
		$this->language->load('checkout/epay');
				
		$this->load->model('account/address');
				

		
		$this->load->model('checkout/extension');
		
		
		$this->document->breadcrumbs = array();

      	$this->document->breadcrumbs[] = array(
        	'href'      => HTTP_SERVER . 'index.php?route=common/home',
        	'text'      => $this->language->get('text_home'),
        	'separator' => FALSE
      	); 

      	$this->document->breadcrumbs[] = array(
        	'href'      => HTTP_SERVER . 'index.php?route=checkout/cart',
        	'text'      => $this->language->get('text_basket'),
        	'separator' => $this->language->get('text_separator')
      	);

      	$this->document->breadcrumbs[] = array(
        	'href'      => HTTP_SERVER . 'index.php?route=checkout/shipping',
        	'text'      => $this->language->get('text_shipping'),
        	'separator' => $this->language->get('text_separator')
      	);
		
		//Language
		$this->document->title = $this->language->get('text_title');
		
    	$this->data['heading_title'] = $this->language->get('text_title');
 		$this->data['text_payment_method'] = $this->language->get('payment_method');
		$this->data['text_payment_card'] = $this->language->get('payment_card');
		
		$this->data['text_amount'] = $this->language->get('amount');
		$this->data['text_transactionfee'] = $this->language->get('transactionfee');
		$this->data['text_orderid'] = $this->language->get('orderid');
		
		$this->data['epay_logo_a'] = $this->config->get('epay_logo_a');
		$this->data['epay_logo_b'] = $this->config->get('epay_logo_b');
		$this->data['epay_logo_c'] = $this->config->get('epay_logo_c');
		$this->data['epay_logo_d'] = $this->config->get('epay_logo_d');
		$this->data['epay_logo_e'] = $this->config->get('epay_logo_e');
		
		$this->data['text_cardnumber'] = $this->language->get('cardnumber');
		$this->data['text_valid'] = $this->language->get('valid');
		$this->data['text_controlciffers'] = $this->language->get('controlciffers');
		
		$this->data['text_pleasewait'] = $this->language->get('pleasewait');
		
		$this->data['stdwindow'] = $this->language->get('stdwindow');
		
		$this->data['text_cvcdankort'] = $this->language->get('cvcdankort');
		$this->data['text_cvcmaster'] = $this->language->get('cvcmaster');
		
		$this->data['text_cardholder'] = $this->language->get('cardholder');
  
    	$this->data['button_back'] = $this->language->get('button_back');
    	$this->data['button_continue'] = $this->language->get('button_continue');
   
   		if (isset($this->error['warning'])) {
    		$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
	
		//Setting up data
		$this->data['accepturl'] = HTTPS_SERVER . 'index.php?route=payment/epay/accept';
		$this->data['callbackurl'] = HTTPS_SERVER . 'index.php?route=payment/epay/confirm&HTTP_COOKIE='.getenv("HTTP_COOKIE");
		
		if(intval($this->config->get('epay_payment_window')) == 0){
		$this->data['declineurl'] = 'https://relay.ditonlinebetalingssystem.dk/relay/v2/relay.cgi/'. HTTPS_SERVER . 'index.php?route=checkout/epay&forcerelay=1&HTTP_COOKIE='.getenv("HTTP_COOKIE");		
		}elseif(intval($this->config->get('epay_payment_window')) == 2){
		$this->data['declineurl'] = HTTPS_SERVER . 'index.php?route=checkout/confirm&HTTP_COOKIE='.getenv("HTTP_COOKIE");			
		}else{
		$this->data['declineurl'] = HTTPS_SERVER . 'index.php?route=checkout/epay&HTTP_COOKIE='.getenv("HTTP_COOKIE");			
		}
	
		
		$this->data['merchantnumber'] = $this->config->get('epay_merchant_number');
		
		$this->data['md5mode'] = intval($this->config->get('epay_md5mode'));
		
		
		
		
		$this->data['customerfee'] = intval($this->config->get('epay_customerfee'));
		$this->data['group'] = $this->config->get('epay_group');
		
		$this->data['authsms'] = $this->config->get('epay_authsms');
		$this->data['authemail'] = $this->config->get('epay_authemail');
		
		$this->data['splitpayment'] = intval($this->config->get('epay_splitpayment'));
		$this->data['instantcapture'] = intval($this->config->get('epay_instantcapture'));
		$this->data['cardholder'] = intval($this->config->get('epay_cardholder'));
		
		$this->data['window'] = intval($this->config->get('epay_payment_window'));
		$this->data['ownreceipt'] = intval($this->config->get('epay_ownreceipt'));
		
		$this->data['payments_a'] = intval($this->config->get('epay_availablepayments_a'));
		$this->data['payments_b'] = intval($this->config->get('epay_availablepayments_b'));
		$this->data['payments_c'] = intval($this->config->get('epay_availablepayments_c'));
		$this->data['payments_d'] = intval($this->config->get('epay_availablepayments_d'));
		$this->data['payments_e'] = intval($this->config->get('epay_availablepayments_e'));
		$this->data['payments_f'] = intval($this->config->get('epay_availablepayments_f'));
		$this->data['payments_g'] = intval($this->config->get('epay_availablepayments_g'));
		$this->data['payments_h'] = intval($this->config->get('epay_availablepayments_h'));
		
		if($this->session->data['language'] == 'da' or $this->session->data['language'] == 'dk'){
			$this->data['language'] = 1;			
		}else{
			$this->data['language'] = 2;
		}

		
		$total_data = array();
		$total = 0;
		$taxes = $this->cart->getTaxes();
		 
		

		
		$this->load->model('checkout/order');
		
		$this->data['orderid'] = $this->session->data['order_id'];
		$this->data['comment'] = $this->session->data['comment'];
		
		$this->data['currency'] = $this->get_iso_code($this->session->data['currency']);
		
		
		$this->load->model('checkout/extension');
		
		$sort_order = array(); 
		
		$results = $this->model_checkout_extension->getExtensions('total');
		
		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get($value['key'] . '_sort_order');
		}
		
		array_multisort($sort_order, SORT_ASC, $results);
		
		foreach ($results as $result) {
			$this->load->model('total/' . $result['key']);

			$this->{'model_total_' . $result['key']}->getTotal($total_data, $total, $taxes);
		}
		
		$sort_order = array(); 
	  
		foreach ($total_data as $key => $value) {
      		$sort_order[$key] = $value['sort_order'];
    	}

    	array_multisort($sort_order, SORT_ASC, $total_data);
		
		foreach ($this->cart->getProducts() as $product) {
      		$option_data = array();

      		foreach ($product['option'] as $option) {
        		$option_data[] = array(
          			'name'  => $option['name'],
          			'value' => $option['value']
        		);
      		} 
 
      		$this->data['products'][] = array(
				'product_id' => $product['product_id'],
        		'name'       => $product['name'],
        		'model'      => $product['model'],
        		'option'     => $option_data,
        		'quantity'   => $product['quantity'],
				'tax'        => $this->tax->getRate($product['tax_class_id']),
        		'price'      => $this->currency->format($product['price']),
        		'total'      => $this->currency->format($product['total']),
				'href'       => HTTP_SERVER . 'index.php?route=product/product&product_id=' . $product['product_id']
      		); 
    	} 
		

		$this->data['totals'] = $total_data;	
		
		$totalsmax = count($total_data)-1;
		$this->data['totalsmax'] = $totalsmax;
	
		$this->data['amount'] = $this->currency->format($total_data[$totalsmax]['value'], $this->session->data['currency'], FALSE, FALSE);
		
		
		$amount = $this->currency->format($total_data[$totalsmax]['value'], $this->session->data['currency'], FALSE, FALSE)*100;
		 
		$md5key = $this->get_iso_code($this->session->data['currency']) . $amount . $this->session->data['order_id'] . $this->config->get('epay_md5key'); 
		 
		$this->data['md5key'] = md5($md5key);
		 
    	$this->data['back'] = HTTPS_SERVER . 'index.php?route=checkout/confirm';
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/checkout/epay.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/checkout/epay.tpl';
		} else {
			$this->template = 'default/template/checkout/epay.tpl';
		}
		
		$this->children = array(
			'common/column_right',
			'common/footer',
			'common/column_left',
			'common/header'
		);
		
		$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));		
  	}
  
  	public function validate() {
    	if (!isset($this->request->post['shipping_method'])) {
	  		$this->error['warning'] = $this->language->get('error_shipping');
		} else {
			$shipping = explode('.', $this->request->post['shipping_method']);
			
			if (!isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {			
				$this->error['warning'] = $this->language->get('error_shipping');
			}
		}
	
		if (!$this->error) {
	  		return TRUE;
		} else {
	  		return FALSE;
		}
  	}
}
?>