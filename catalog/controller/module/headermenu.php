<?php  
class ControllerModuleHeaderMenu extends Controller {
	protected $category_id = 0;
	protected $path = array();
	
	protected function index() {
		$this->language->load('module/headermenu');
		
    	$this->data['heading_title'] = $this->language->get('heading_title');
		
		$this->load->model('catalog/category');
		$this->load->model('tool/seo_url');
		
		if (isset($this->request->get['path'])) {
			$this->path = explode('_', $this->request->get['path']);
			
			$this->category_id = end($this->path);
		}
		
		$this->data['category'] = $this->getCategories(0);
												
		$this->id = 'headermenu';

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/headermenu.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/headermenu.tpl';
		} else {
			$this->template = 'default/template/module/headermenu.tpl';
		}
		
		$this->render();
  	}
	
	protected function getCategories($parent_id, $current_path = '') {
		$category_id = array_shift($this->path);
		
		$output = '';
		
		$results = $this->model_catalog_category->getCategories($parent_id);
		
		if ($results) { 
			$output .= '<ul>';
    	}
		
		foreach ($results as $result) {	
			if (!$current_path) {
				$new_path = $result['category_id'];
			} else {
				$new_path = $current_path . '_' . $result['category_id'];
			}
			
			$output .= '<li>';
			
			$children = '';

			$children = $this->getChildCategories($result['category_id'], $new_path);

			$output .= '<a class="hide" href="' . $this->model_tool_seo_url->rewrite(HTTP_SERVER . 'index.php?route=product/category&path=' . $new_path)  . '">' . $result['name'] . '</a>';
		
        	$output .= $children;
        
        	$output .= '</li>'; 
		}
 
		if ($results) {
			$output .= '</ul>';
		}
		
		return $output;
	}
	
	protected function getChildCategories($parent_id, $current_path = '') {
		$category_id = array_shift($this->path);
		
		$output = '';
		
		$results = $this->model_catalog_category->getCategories($parent_id);
		
		if ($results) { 
			$output .= '<ul>';
    	}
		
		foreach ($results as $result) {	
			if (!$current_path) {
				$new_path = $result['category_id'];
			} else {
				$new_path = $current_path . '_' . $result['category_id'];
			}
			
			$output .= '<li>';
			
			$children = '';
			
			if ($category_id == $result['category_id']) {
				$children = $this->getChildCategories($result['category_id'], $new_path);
			}
			
			$output .= '<a href="' . $this->model_tool_seo_url->rewrite(HTTP_SERVER . 'index.php?route=product/category&path=' . $new_path)  . '">' . $result['name'] . '</a>';
						
        	$output .= $children;
        
        	$output .= '</li>'; 
		}
 
		if ($results) {
			$output .= '</ul>';
		}
		
		return $output;
	}
}
?>

