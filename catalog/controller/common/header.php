<?php   
class ControllerCommonHeader extends Controller {
	protected function index() {
	
		/***************** 
		 * START OF HEADER MODULE MOD 
		 *****************/		 		 
		$module_data = array();
				
		$this->load->model('checkout/extension');

		$results = $this->model_checkout_extension->getExtensions('module');
		
		foreach ($results as $result) {
			if ($this->config->get($result['key'] . '_status') && $result['key']=="category") {
				$module_data[] = array(
					'code'       => $result['key'],
					'sort_order' => $this->config->get($result['key'] . '_sort_order')
				);
				
				$this->children[] = 'module/' . $result['key'];		
			}
		}

		$sort_order = array(); 
	  
		foreach ($module_data as $key => $value) {
      		$sort_order[$key] = $value['sort_order'];
    	}

    	array_multisort($sort_order, SORT_ASC, $module_data);			
		
		$this->data['modules'] = $module_data;
		
		/*************** 
		 * END OF HEADER MODULE MOD 
		 ****************/
	
	
    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($this->request->post['language_code'])) {
			$this->session->data['language'] = $this->request->post['language_code'];
		
			if (isset($this->request->post['redirect'])) {
				$this->redirect($this->request->post['redirect']);
			} else {
				$this->redirect(HTTP_SERVER . 'index.php?route=common/home');
			}
    	}		
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($this->request->post['currency_code'])) {
      		$this->currency->set($this->request->post['currency_code']);
			
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['shipping_method']);
				
			if (isset($this->request->post['redirect'])) {
				$this->redirect($this->request->post['redirect']);
			} else {
				$this->redirect(HTTP_SERVER . 'index.php?route=common/home');
			}
   		}
		
		$this->language->load('common/header');
		
		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$server = HTTPS_IMAGE;
		} else {
			$server = HTTP_IMAGE;
		}
			
		$this->data['title'] = $this->document->title;
		$this->data['keywords'] = $this->document->keywords;
		$this->data['description'] = $this->document->description;
		$this->data['template'] = $this->config->get('config_template');
		
		
		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$this->data['base'] = HTTPS_SERVER;
		} else {
			$this->data['base'] = HTTP_SERVER;
		}
		
		if ($this->config->get('config_icon') && file_exists(DIR_IMAGE . $this->config->get('config_icon'))) {
			$this->data['icon'] = $server . $this->config->get('config_icon');
		} else {
			$this->data['icon'] = '';
		}
		
		$this->data['charset'] = $this->language->get('charset');
		$this->data['lang'] = $this->language->get('code');
		$this->data['direction'] = $this->language->get('direction');
		$this->data['links'] = $this->document->links;	
		$this->data['styles'] = $this->document->styles;
		$this->data['scripts'] = $this->document->scripts;		
		$this->data['breadcrumbs'] = $this->document->breadcrumbs;
		
		$this->data['store'] = $this->config->get('config_name');
		
		if ($this->config->get('config_logo') && file_exists(DIR_IMAGE . $this->config->get('config_logo'))) {
			$this->data['logo'] = $server . $this->config->get('config_logo');
		} else {
			$this->data['logo'] = '';
		}
		
		$this->data['text_home'] = $this->language->get('text_home');
		$this->data['text_special'] = $this->language->get('text_special');
		$this->data['text_contact'] = $this->language->get('text_contact');
		$this->data['text_sitemap'] = $this->language->get('text_sitemap');
		$this->data['text_bookmark'] = $this->language->get('text_bookmark');
    	$this->data['text_account'] = $this->language->get('text_account');
    	$this->data['text_login'] = $this->language->get('text_login');
    	$this->data['text_logout'] = $this->language->get('text_logout');
    	$this->data['text_cart'] = $this->language->get('text_cart'); 
    	$this->data['text_checkout'] = $this->language->get('text_checkout');
		$this->data['text_keyword'] = $this->language->get('text_keyword');
		$this->data['text_category'] = $this->language->get('text_category');
		$this->data['text_advanced'] = $this->language->get('text_advanced');
		
 /* CART IN HEADER for OC1.4.9.1 */	
    	$this->data['text_items_count'] = $this->language->get('text_items_count'); 
	$this->data['text_subtotal'] = $this->language->get('text_subtotal');
 /* END */		

		$this->data['entry_search'] = $this->language->get('entry_search');
		
		$this->data['button_go'] = $this->language->get('button_go');

		$this->data['home'] = HTTP_SERVER . 'index.php?route=common/home';
		$this->data['special'] = HTTP_SERVER . 'index.php?route=product/special';
		$this->data['contact'] = HTTP_SERVER . 'index.php?route=information/contact';
    	$this->data['sitemap'] = HTTP_SERVER . 'index.php?route=information/sitemap';
    	$this->data['account'] = HTTPS_SERVER . 'index.php?route=account/account';
		$this->data['logged'] = $this->customer->isLogged();
		$this->data['login'] = HTTPS_SERVER . 'index.php?route=account/login';
		$this->data['logout'] = HTTP_SERVER . 'index.php?route=account/logout';
    	$this->data['cart'] = HTTP_SERVER . 'index.php?route=checkout/cart';
		$this->data['checkout'] = HTTPS_SERVER . 'index.php?route=checkout/shipping';
		
 /*CART IN HEADER for OC1.4.9.1 */		        
		$this->data['items_count'] = HTTP_SERVER . 'index.php?route=checkout/cart';
       		$this->data['subtotal'] = $this->currency->format($this->cart->getTotal());
		$this->data['ajax'] = $this->config->get('cart_ajax');
 /* END */			
		
		if (isset($this->request->get['keyword'])) {
			$this->data['keyword'] = $this->request->get['keyword'];
		} else {
			$this->data['keyword'] = '';
		}
		
		if (isset($this->request->get['category_id'])) {
			$this->data['category_id'] = $this->request->get['category_id'];
		} elseif (isset($this->request->get['path'])) {
			$path = explode('_', $this->request->get['path']);
		
			$this->data['category_id'] = end($path);
		} else {
			$this->data['category_id'] = '';
		}
		
		$this->data['advanced'] = HTTP_SERVER . 'index.php?route=product/search';
		
		$this->load->model('catalog/category');
		
		$this->data['categories'] = $this->getCategories(0);
		
		$this->data['action'] = HTTP_SERVER . 'index.php?route=common/home';

		if (!isset($this->request->get['route'])) {
			$this->data['redirect'] = HTTP_SERVER . 'index.php?route=common/home';
		} else {
			$this->load->model('tool/seo_url');
			
			$data = $this->request->get;
			
			unset($data['_route_']);
			
			$route = $data['route'];
			
			unset($data['route']);
			
			$url = '';
			
			if ($data) {
				$url = '&' . urldecode(http_build_query($data));
			}			
			
			$this->data['redirect'] = $this->model_tool_seo_url->rewrite(HTTP_SERVER . 'index.php?route=' . $route . $url);
		}
		
		$this->data['language_code'] = $this->session->data['language'];
		
		$this->load->model('localisation/language');
		
		$this->data['languages'] = array();
		
		$results = $this->model_localisation_language->getLanguages();
		
		foreach ($results as $result) {
			if ($result['status']) {
				$this->data['languages'][] = array(
					'name'  => $result['name'],
					'code'  => $result['code'],
					'image' => $result['image']
				);	
			}
		}
		
		$this->data['currency_code'] = $this->currency->getCode(); 
		
		$this->load->model('localisation/currency');
		 
		 $this->data['currencies'] = array();
		 
		$results = $this->model_localisation_currency->getCurrencies();	
		
		foreach ($results as $result) {
			if ($result['status']) {
   				$this->data['currencies'][] = array(
					'title' => $result['title'],
					'code'  => $result['code']
				);
			}
		}
		
		$this->id = 'header';
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/header.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/common/header.tpl';
		} else {
			$this->template = 'default/template/common/header.tpl';
		}
		
    	$this->render();
	}	
	
	private function getCategories($parent_id, $level = 0) {
		$level++;
		
		$data = array();
		
		$results = $this->model_catalog_category->getCategories($parent_id);
		
		foreach ($results as $result) {
			$data[] = array(
				'category_id' => $result['category_id'],
				'name'        => str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level) . $result['name']
			);
			
			$children = $this->getCategories($result['category_id'], $level);
			
			if ($children) {
			  $data = array_merge($data, $children);
			}
		}
		
		return $data;
	}
}
?>
