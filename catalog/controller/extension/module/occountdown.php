<?php  
class ControllerExtensionModuleOccountdown extends Controller {
	public function index($setting) {

		$this->load->language('extension/module/occountdown');

		$this->load->model('extension/module/occountdown');
		$this->load->model('catalog/ocproductrotator');
		$this->load->model('tool/image');

		$data = array();

		$data['heading_title'] 	= $this->language->get('heading_title');

		$data['text_empty'] = $this->language->get('text_empty');
		$data['text_sale'] = $this->language->get('text_sale');
		$data['text_new'] = $this->language->get('text_new');
		$data['text_reviews'] 	= $this->language->get('text_reviews');
		$data['text_years'] 	= $this->language->get('text_years');
		$data['text_months'] 	= $this->language->get('text_months');
		$data['text_weeks'] 	= $this->language->get('text_weeks');
		$data['text_days'] 		= $this->language->get('text_days');
		$data['text_hours'] 	= $this->language->get('text_hours');
		$data['text_minutes'] 	= $this->language->get('text_minutes');
		$data['text_seconds'] 	= $this->language->get('text_seconds');

		$data['button_cart'] 	= $this->language->get('button_cart');
		$data['button_wishlist'] = $this->language->get('button_wishlist');
		$data['button_compare'] = $this->language->get('button_compare');

		if (empty($setting['limit'])) {
			$setting['limit'] = 4;
		}

		$lang_code = $this->session->data['language'];

		if(isset($setting['title']) && $setting['title']) {
			$data['title'] = $setting['title'][$lang_code]['title'];
		} else {
			$data['title'] = $this->language->get('heading_title');
		}
		
		if(isset($setting['rotator']) && $setting['rotator']) {
			$product_rotator_status = (int) $this->config->get('ocproductrotator_status');
		} else {
			$product_rotator_status = 0;
		}

		$data['products'] = array();

		$results = $this->model_extension_module_occountdown->getTimeCountdown($setting['limit']);
		if ($results) {
			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
				}
				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['orgprice'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}
				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['orgprice'], $this->session->data['currency']);
				} else {
					$tax = false;
				}
				if ($result['date_start'] != null) {
					$date_start = $result['date_start'] ;
				} else {
					$date_start = false;
				}
				if($result['date_end'] != null) {
					$date_end = $result['date_end'] ;
				} else {
					$date_end = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = $result['rating'];
				} else {
					$rating = false;
				}

				$reviews = sprintf($this->language->get('text_reviews'), (int)$result['reviews']);

				if($product_rotator_status == 1) {
					$product_id = $result['product_id'];
					$product_rotator_image = $this->model_catalog_ocproductrotator->getProductRotatorImage($product_id);

					if($product_rotator_image) {
						$rotator_image = $this->model_tool_image->resize($product_rotator_image, $setting['width'], $setting['height']);
					} else {
						$rotator_image = false;
					}
				} else {
					$rotator_image = false;
				}

				if(strtotime($date_start) && strtotime($date_end)) {
					$data['products'][] = array(
						'product_id'  	=> $result['product_id'],
						'thumb'       	=> $image,
						'rotator_image' => $rotator_image,
						'name'        	=> $result['name'],
						'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get($this->config->get('config_theme') . '_product_description_length')) . '..',
						'orgprice'      => $price,
						'special'     	=> $special,
						'date_start'  	=> $date_start,
						'date_end'    	=> $date_end,
						'tax'         	=> $tax,
						'rating'      	=> $rating,
						'href'        	=> $this->url->link('product/product', 'product_id=' . $result['product_id']),
						'reviews' 		=> $reviews
					);
				}
			}
		}


		if(isset($setting['rows'])) {
			$rows = (int) $setting['rows'];
		} else {
			$rows = 1;
		}

		$data['config_slide'] = array(
			'items' => $setting['item'],
			'autoplay' => $setting['autoplay'],
			'f_show_nextback' => $setting['shownextback'],
			'f_show_label' => $setting['showlabel'],
			'f_show_ctr' => $setting['shownav'],
			'f_speed' => $setting['speed'],
			'f_rows' => $rows
		);

		$this->document->addScript('catalog/view/javascript/jquery.plugin.js');
		$this->document->addStyle('catalog/view/javascript/jquery.countdown.css');

		return $this->load->view('extension/module/occountdown',$data);
	}
}

?>