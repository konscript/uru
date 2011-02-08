<?php  
class ControllerCommonHome extends Controller {
	public function index() {	
		$this->language->load('common/home');		
		$this->document->title = $this->config->get('config_title');
		$this->document->description = $this->config->get('config_meta_description');
		
		$this->data['heading_title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));
		
		$this->load->model('setting/store');
		
		if (!$this->config->get('config_store_id')) {
			$this->data['welcome'] = html_entity_decode($this->config->get('config_description_' . $this->config->get('config_language_id')), ENT_QUOTES, 'UTF-8');
		} else {
			$store_info = $this->model_setting_store->getStore($this->config->get('config_store_id'));
			
			if ($store_info) {
				$this->data['welcome'] = html_entity_decode($store_info['description'], ENT_QUOTES, 'UTF-8');
			} else {
				$this->data['welcome'] = '';
			}
		}
		
		

      /* START: Added for homepage category hacks */
      $this->load->model('catalog/category');
      $this->load->model('tool/seo_url');      
      $this->load->model('tool/image');      
      //Set Category for display
           $category_id = '0';
      //Get total categories in your category
      $category_total = $this->model_catalog_category->getTotalCategoriesByCategoryId($category_id);
      $this->data['products'] = array();
      if (isset($category_total)) {
         $this->data['categories'] = array();
         $results = $this->model_catalog_category->getCategories($category_id);
         

         foreach ($results as $result) {
            if ($result['image']) {
               $image = $result['image'];
            } else {
               $image = 'no_image.jpg';
            }

            $this->data['categories'][] = array(
            'name'  => $result['name'],
            //'href'  => $this->model_tool_seo_url->rewrite($this->url->http('product/category&path=' . $result['category_id'])),
            'href'  => $this->model_tool_seo_url->rewrite(HTTP_SERVER . 'index.php?route=product/category&path=' . $result['category_id']),
            //'thumb' => image_resize($image, $this->config->get('config_image_category_width'), $this->config->get('config_image_category_height'))
            'thumb'   => $this->model_tool_image->resize($image, $this->config->get('config_image_category_width'), $this->config->get('config_image_category_height'))
            );
            }

         $this->id       = 'content';
         $this->template = $this->template = 'default/template/common/home.tpl';
         $this->layout   = 'common/layout';
         $this->render();
        }
        /* END: Added for homepage category hacks */
      
		
						
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/home.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/common/home.tpl';
		} else {
			$this->template = 'default/template/common/home.tpl';
		}
		
		$this->children = array(
			'common/column_right',
			'common/column_left',
			'common/footer',
			'common/header'
		);
		
		$this->load->model('checkout/extension');
		
		$module_data = $this->model_checkout_extension->getExtensionsByPosition('module', 'home');
		
		$this->data['modules'] = $module_data;
		
		foreach ($module_data as $result) {
			$this->children[] = 'module/' . $result['code'];
		}
		
		$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));
	}
}
?>
