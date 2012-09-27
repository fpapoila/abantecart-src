<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011, 2012 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/
if (!defined('DIR_CORE')) {
	header('Location: static_pages/');
}

class AConfigManager {
	protected $registry;
	public $errors = 0;
	private $temp = array();
	private $level = 0;
	private $groups = array();

	public function __construct() {
		if (!IS_ADMIN) { // forbid for non admin calls
			throw new AException (AC_ERR_LOAD, 'Error: permission denied to access class AConfigManager');
		}
		$this->registry = Registry::getInstance();
		$this->load->model('setting/extension');
		$this->load->model('setting/setting');
		$this->groups = $this->config->groups;
	}

	public function __get($key) {
		return $this->registry->get($key);
	}

	public function __set($key, $value) {
		$this->registry->set($key, $value);
	}

	/*
	*	Build field for provided key setting
	*   Form - form object where filed will be shown
	*   Data - current settig data
	*   Store_id - Seleted store ID for the setting
	*/
	public function getFormField( $setting_key, $form, $data, $store_id, $group='' ) {
		//locate setting group first
        if(empty($group)){
		    $group = $this->model_setting_setting->getSettingGroup($setting_key, $store_id);
            $group = $group[0];
        }
        $data['one_field'] = $setting_key;
		$fields = $this->getFormFields($group, $form, $data);
		return $fields;
	}

	/*
	*	Build fields array for provided setting group (section)
	*   Form - form object where filed will be shown
	*   Data - current settig data
	*/
	
	public function getFormFields( $group, $form, $data ) {
		$method_name = "_build_form_".$group;
		if (!method_exists( $this, $method_name )) {
			return array();
		}

		return $this->$method_name($form, $data);
	}
	
	private function _build_form_details( $form, $data ) {
		$fields = $props = array();
 		// details section
        $fields['name'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'store_name',
            'value' => $data['store_name'],
            'required' => true,
            'style' => 'large-field',
        ));
        $fields['url'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_url',
            'value' => $data['config_url'],
            'required' => true,
            'style' => 'large-field',
        ));
        $fields['title'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_title',
            'value' => $data['config_title'],
            'required' => true,
            'style' => 'large-field',
        ));
        $fields['meta_description'] = $form->getFieldHtml($props[] = array(
            'type' => 'textarea',
            'name' => 'config_meta_description',
            'value' => $data['config_meta_description'],
            'style' => 'large-field',
        ));
        $fields['description'] = $form->getFieldHtml($props[] = array(
            'type' => 'textarea',
            'name' => 'config_description_' . $this->session->data['content_language_id'],
            'value' => $data['config_description_' . $this->session->data['content_language_id']],
            'style' => 'xl-field',
        ));
        $fields['owner'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_owner',
            'value' => $data['config_owner'],
            'required' => true,
            'style' => 'large-field',
        ));
        $fields['address'] = $form->getFieldHtml($props[] = array(
            'type' => 'textarea',
            'name' => 'config_address',
            'value' => $data['config_address'],
            'required' => true,
            'style' => 'large-field',
        ));
        $fields['email'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'store_main_email',
            'value' => $data['store_main_email'],
            'required' => true,
            'style' => 'large-field',
        ));
        $fields['telephone'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_telephone',
            'value' => $data['config_telephone'],
            'required' => true,
            'style' => 'medium-field',
        ));
        $fields['fax'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_fax',
            'value' => $data['config_fax'],
            'style' => 'medium-field',
        ));

        $this->load->model('localisation/country');
        $countries = array();
        $results =  $this->model_localisation_country->getCountries();
        foreach ($results as $c) {
            $countries[$c['country_id']] = $c['name'];
        }

        $results = $this->language->getAvailableLanguages();
        $languages = array();
        foreach ($results as $v) {
            $languages[$v['code']] = $v['name'];
        }

        $this->load->model('localisation/currency');
        $results = $this->model_localisation_currency->getCurrencies();
        $currencies = array();
        foreach ($results as $v) {
            $currencies[$v['code']] = $v['title'];
        }

        $this->load->model('localisation/length_class');
        $results = $this->model_localisation_length_class->getLengthClasses();
        $length_classes = array();
        foreach ($results as $v) {
            $length_classes[$v['unit']] = $v['title'];
        }

        $this->load->model('localisation/weight_class');
        $results = $this->model_localisation_weight_class->getWeightClasses();
        $weight_classes = array();
        foreach ($results as $v) {
            $weight_classes[$v['unit']] = $v['title'];
        }

        $fields['country'] = $form->getFieldHtml($props[] = array(
            'type' => 'selectbox',
            'name' => 'config_country_id',
            'value' => $data['config_country_id'],
            'options' => $countries,
            'style' => 'large-field',
        ));
        $fields['zone'] = $form->getFieldHtml($props[] = array(
            'type' => 'selectbox',
            'name' => 'config_zone_id',
            'value' => $data['config_zone_id'],
            'options' => array(),
            'style' => 'large-field',
        ));
        $fields['language'] = $form->getFieldHtml($props[] = array(
            'type' => 'selectbox',
            'name' => 'config_storefront_language',
            'value' => $data['config_storefront_language'],
            'options' => $languages,
        ));
        $fields['admin_language'] = $form->getFieldHtml($props[] = array(
            'type' => 'selectbox',
            'name' => 'admin_language',
            'value' => $data['admin_language'],
            'options' => $languages,
        ));
        $fields['currency'] = $form->getFieldHtml($props[] = array(
            'type' => 'selectbox',
            'name' => 'config_currency',
            'value' => $data['config_currency'],
            'options' => $currencies,
        ));
        $fields['currency_auto'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'config_currency_auto',
            'value' => $data['config_currency_auto'],
            'style' => 'btn_switch',
        ));
        $fields['length_class'] = $form->getFieldHtml($props[] = array(
            'type' => 'selectbox',
            'name' => 'config_length_class',
            'value' => $data['config_length_class'],
            'options' => $length_classes,
        ));

        $fields['weight_class'] = $form->getFieldHtml($props[] = array(
            'type' => 'selectbox',
            'name' => 'config_weight_class',
            'value' => $data['config_weight_class'],
            'options' => $weight_classes,
        ));
        if(isset($data['one_field'])){
            $fields = $this->_filterField($fields,$props,$data['one_field']);
        }
		return $fields;
	}

	private function _build_form_general( $form, $data ) {
		$fields = array();
		//general section
        $this->load->model('localisation/stock_status');
        $stock_statuses = array();
        $results = $this->model_localisation_stock_status->getStockStatuses();
        foreach ($results as $item) {
            $stock_statuses[$item['stock_status_id']] = $item['name'];
        }

        $fields['catalog_limit'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_catalog_limit',
            'value' => $data['config_catalog_limit'],
            'required' => true,
            'style' => 'small-field',
        ));
        $fields['admin_limit'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_admin_limit',
            'value' => $data['config_admin_limit'],
            'required' => true,
            'style' => 'small-field',
        ));
        $fields['bestseller_limit'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_bestseller_limit',
            'value' => $data['config_bestseller_limit'],
            'required' => true,
            'style' => 'small-field',
        ));
        $fields['featured_limit'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_featured_limit',
            'value' => $data['config_featured_limit'],
            'required' => true,
            'style' => 'small-field',
        ));
        $fields['latest_limit'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_latest_limit',
            'value' => $data['config_latest_limit'],
            'required' => true,
            'style' => 'small-field',
        ));
        $fields['special_limit'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_special_limit',
            'value' => $data['config_special_limit'],
            'required' => true,
            'style' => 'small-field',
        ));
        $fields['stock_display'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'config_stock_display',
            'value' => $data['config_stock_display'],
            'style' => 'btn_switch',
        ));
        $fields['nostock_autodisable'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'config_nostock_autodisable',
            'value' => $data['config_nostock_autodisable'],
            'style' => 'btn_switch',
        ));
        $fields['stock_status'] = $form->getFieldHtml($props[] = array(
            'type' => 'selectbox',
            'name' => 'config_stock_status_id',
            'value' => $data['config_stock_status_id'],
            'options' => $stock_statuses,
        ));        
        $fields['reviews'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'enable_reviews',
            'value' => $data['enable_reviews'],
            'style' => 'btn_switch',
        ));
        $fields['download'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'config_download',
            'value' => $data['config_download'],
            'style' => 'btn_switch',
        ));
        $this->load->model('localisation/order_status');
        $order_statuses = array();
        $results = $this->model_localisation_order_status->getOrderStatuses();
        foreach ($results as $item) {
            $order_statuses[$item['order_status_id']] = $item['name'];
        }
        $fields['download_status'] = $form->getFieldHtml($props[] = array(
            'type' => 'selectbox',
            'name' => 'config_download_status',
            'value' => $data['config_download_status'],
            'options' => $order_statuses,
        ));
        $fields['help_links'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'config_help_links',
            'value' => $data['config_help_links'],
            'style' => 'btn_switch',
        ));
        $fields['show_tree_data'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'config_show_tree_data',
            'value' => $data['config_show_tree_data'],
            'style' => 'btn_switch',
        ));

        if(isset($data['one_field'])){
            $fields = $this->_filterField($fields,$props,$data['one_field']);
        }
		return $fields;
	}
	
	private function _build_form_checkout( $form, $data ) {
		$fields = array();
		//checkout section
        $this->load->model('sale/customer_group');
        $results = $this->model_sale_customer_group->getCustomerGroups();
        $customer_groups = array();
        foreach ($results as $item) {
            $customer_groups[$item['customer_group_id']] = $item['name'];
        }

        $this->load->model('localisation/order_status');
        $order_statuses = array();
        $results = $this->model_localisation_order_status->getOrderStatuses();
        foreach ($results as $item) {
            $order_statuses[$item['order_status_id']] = $item['name'];
        }

        $cntmnr = new AContentManager();
        $results = $cntmnr->getContents();
        $contents = array('' => $this->language->get('text_none'));
        foreach ($results as $item) {
            if (!$item['status']) continue;
            $contents[$item['content_id']] = $item['title'];
        }

        $fields['tax'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'config_tax',
            'value' => $data['config_tax'],
            'style' => 'btn_switch',
        ));
        $fields['tax_store'] = $form->getFieldHtml($props[] = array(
            'type' => 'selectbox',
            'name' => 'config_tax_store',
            'value' => $data['config_tax_store'],
            'options' => array($this->language->get('entry_tax_store_0'), $this->language->get('entry_tax_store_1')),
        ));
        $fields['tax_customer'] = $form->getFieldHtml($props[] = array(
            'type' => 'selectbox',
            'name' => 'config_tax_customer',
            'value' => $data['config_tax_customer'],
            'options' => array($this->language->get('entry_tax_customer_0'), $this->language->get('entry_tax_customer_1')),
        ));
        $fields['invoice'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'starting_invoice_id',
            'value' => $data['starting_invoice_id'],
            'style' => 'small-field',
        ));
        $fields['invoice_prefix'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'invoice_prefix',
            'value' => $data['invoice_prefix'],
            'style' => 'small-field',
        ));
        $fields['customer_group'] = $form->getFieldHtml($props[] = array(
            'type' => 'selectbox',
            'name' => 'config_customer_group_id',
            'value' => $data['config_customer_group_id'],
            'options' => $customer_groups,
        ));
        $fields['customer_price'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'config_customer_price',
            'value' => $data['config_customer_price'],
            'style' => 'btn_switch',
        ));
        $fields['customer_approval'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'config_customer_approval',
            'value' => $data['config_customer_approval'],
            'style' => 'btn_switch',
        ));
        $fields['guest_checkout'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'config_guest_checkout',
            'value' => $data['config_guest_checkout'],
            'style' => 'btn_switch',
        ));
        $fields['account'] = $form->getFieldHtml($props[] = array(
            'type' => 'selectbox',
            'name' => 'config_account_id',
            'value' => $data['config_account_id'],
            'options' => $contents,
        ));
        $fields['checkout'] = $form->getFieldHtml($props[] = array(
            'type' => 'selectbox',
            'name' => 'config_checkout_id',
            'value' => $data['config_checkout_id'],
            'options' => $contents,
        ));
        $fields['stock_checkout'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'config_stock_checkout',
            'value' => $data['config_stock_checkout'],
            'style' => 'btn_switch',
        ));
        $fields['order_status'] = $form->getFieldHtml($props[] = array(
            'type' => 'selectbox',
            'name' => 'config_order_status_id',
            'value' => $data['config_order_status_id'],
            'options' => $order_statuses,
        ));
        $fields['cart_weight'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'config_cart_weight',
            'value' => $data['config_cart_weight'],
            'style' => 'btn_switch',
        ));
        $fields['shipping_session'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'config_shipping_session',
            'value' => $data['config_shipping_session'],
            'style' => 'btn_switch',
        ));
        $fields['cart_ajax'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'config_cart_ajax',
            'value' => $data['config_cart_ajax'],
            'style' => 'btn_switch',
        ));
        if(isset($data['one_field'])){
            $fields = $this->_filterField($fields,$props,$data['one_field']);
        }
		return $fields;
	}
	
	private function _build_form_appearance( $form, $data ) {
		$fields = array();
		//appearance section 
        $templates = array();
        $directories = glob(DIR_STOREFRONT . 'view/*', GLOB_ONLYDIR);
        foreach ($directories as $directory) {
            $templates[basename($directory)] = basename($directory);
        }
        $extension_templates = $this->extension_manager->getExtensionsList(array('category' => 'template', 'status' => 1));
        if ($extension_templates->total > 0)
            foreach ($extension_templates->rows as $row) {
                $templates[$row['key']] = $row['key'];
            }

        $fields['template'] = $form->getFieldHtml($props[] = array(
            'type' => 'selectbox',
            'name' => 'config_storefront_template',
            'value' => $data['config_storefront_template'],
            'options' => $templates,
            'style' => 'large-field',
        ));


		//appearance section
		$templates = array();
		$directories = glob(DIR_APP_SECTION . 'view/*', GLOB_ONLYDIR);
		foreach ($directories as $directory) {
		   $templates[basename($directory)] = basename($directory);
		}

        $fields['admin_template'] = $form->getFieldHtml($props[] = array(
            'type' => 'selectbox',
            'name' => 'admin_template',
            'value' => $data['admin_template'],
            'options' => $templates,
            'style' => 'large-field',
        ));
 
        $fields['storefront_width'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'storefront_width',
            'value' => $data['storefront_width'],
            'style' => 'small-field',
            'required' => true,
        ));
        $fields['admin_width'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'admin_width',
            'value' => $data['admin_width'],
            'style' => 'small-field',
            'required' => true,
        ));

        $fields['logo'] = $form->getFieldHtml($props[] = array(
            'type' => 'hidden',
            'name' => 'config_logo',
            'value' => htmlspecialchars($data['config_logo'], ENT_COMPAT, 'UTF-8'),
        ));
        $fields['icon'] = $form->getFieldHtml($props[] = array(
            'type' => 'hidden',
            'name' => 'config_icon',
            'value' => htmlspecialchars($data['config_icon'], ENT_COMPAT, 'UTF-8'),
        ));
        $fields['image_thumb_width'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_image_thumb_width',
            'value' => $data['config_image_thumb_width'],
            'style' => 'small-field',
            'required' => true,
        ));
        $fields['image_thumb_height'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_image_thumb_height',
            'value' => $data['config_image_thumb_height'],
            'style' => 'small-field',
            'required' => true,
        ));
        $fields['image_popup_width'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_image_popup_width',
            'value' => $data['config_image_popup_width'],
            'style' => 'small-field',
            'required' => true,
        ));
        $fields['image_popup_height'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_image_popup_height',
            'value' => $data['config_image_popup_height'],
            'style' => 'small-field',
            'required' => true,
        ));
        $fields['image_category_width'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_image_category_width',
            'value' => $data['config_image_category_width'],
            'style' => 'small-field',
            'required' => true,
        ));
        $fields['image_category_height'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_image_category_height',
            'value' => $data['config_image_category_height'],
            'style' => 'small-field',
            'required' => true,
        ));
        $fields['image_product_width'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_image_product_width',
            'value' => $data['config_image_product_width'],
            'style' => 'small-field',
            'required' => true,
        ));
        $fields['image_product_height'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_image_product_height',
            'value' => $data['config_image_product_height'],
            'style' => 'small-field',
            'required' => true,
        ));
        $fields['image_additional_width'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_image_additional_width',
            'value' => $data['config_image_additional_width'],
            'style' => 'small-field',
            'required' => true,
        ));
        $fields['image_additional_height'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_image_additional_height',
            'value' => $data['config_image_additional_height'],
            'style' => 'small-field',
            'required' => true,
        ));
        $fields['image_related_width'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_image_related_width',
            'value' => $data['config_image_related_width'],
            'style' => 'small-field',
            'required' => true,
        ));
        $fields['image_related_height'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_image_related_height',
            'value' => $data['config_image_related_height'],
            'style' => 'small-field',
            'required' => true,
        ));
        $fields['image_cart_width'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_image_cart_width',
            'value' => $data['config_image_cart_width'],
            'style' => 'small-field',
            'required' => true,
        ));
        $fields['image_cart_height'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_image_cart_height',
            'value' => $data['config_image_cart_height'],
            'style' => 'small-field',
            'required' => true,
        ));
        $fields['image_grid_width'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_image_grid_width',
            'value' => $data['config_image_grid_width'],
            'style' => 'small-field',
            'required' => true,
        ));
        $fields['image_grid_height'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_image_grid_height',
            'value' => $data['config_image_grid_height'],
            'style' => 'small-field',
            'required' => true,
        ));
        if(isset($data['one_field'])){
            $fields = $this->_filterField($fields,$props,$data['one_field']);
        }
		return $fields;
	}
	
	private function _build_form_mail( $form, $data ) {
		$fields = array();
		//mail section
		
        $fields['mail_protocol'] = $form->getFieldHtml($props[] = array(
            'type' => 'selectbox',
            'name' => 'config_mail_protocol',
            'value' => $data['config_mail_protocol'],
            'options' => array(
                'mail' => $this->language->get('text_mail'),
                'smtp' => $this->language->get('text_smtp'),
            ),
            'style' => "no-save",
        ));
        $fields['mail_parameter'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_mail_parameter',
            'value' => $data['config_mail_parameter'],
        ));
        $fields['smtp_host'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_smtp_host',
            'value' => $data['config_smtp_host'],
            'required' => true
        ));
        $fields['smtp_username'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_smtp_username',
            'value' => $data['config_smtp_username'],
        ));
        $fields['smtp_password'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_smtp_password',
            'value' => $data['config_smtp_password'],
        ));
        $fields['smtp_port'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_smtp_port',
            'value' => $data['config_smtp_port'],
            'required' => true
        ));
        $fields['smtp_timeout'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_smtp_timeout',
            'value' => $data['config_smtp_timeout'],
            'required' => true
        ));
        $fields['alert_mail'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'config_alert_mail',
            'value' => $data['config_alert_mail'],
            'style' => 'btn_switch',
        ));
        $fields['alert_emails'] = $form->getFieldHtml($props[] = array(
            'type' => 'textarea',
            'name' => 'config_alert_emails',
            'value' => $data['config_alert_emails'],
            'style' => 'large-field',
        ));
		if(isset($data['one_field'])){
			$fields = $this->_filterField($fields,$props,$data['one_field']);
		}
		return $fields;
	}
	
	private function _build_form_api( $form, $data ) {
		$fields = array();
		//api section 
        $fields['storefront_api_status'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'config_storefront_api_status',
            'value' => $data['config_storefront_api_status'],
            'style' => 'btn_switch',
        ));
        $fields['storefront_api_key'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_storefront_api_key',
            'value' => $data['config_storefront_api_key'],
        ));
        $fields['storefront_api_stock_check'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'config_storefront_api_stock_check',
            'value' => $data['config_storefront_api_stock_check'],
            'style' => 'btn_switch',
        ));
        if(isset($data['one_field'])){
            $fields = $this->_filterField($fields,$props,$data['one_field']);
        }
		return $fields;
	}
	
	private function _build_form_system( $form, $data ) {
		$fields = array();
		//system section 
        $fields['ssl'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'config_ssl',
            'value' => $data['config_ssl'],
            'style' => 'btn_switch',
        ));
        $fields['session_ttl'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_session_ttl',
            'value' => $data['config_session_ttl'],
        ));
        $fields['maintenance'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'config_maintenance',
            'value' => $data['config_maintenance'],
            'style' => 'btn_switch',
        ));
        $fields['encryption'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'encryption_key',
            'value' => $data['encryption_key'],
        ));
        $fields['seo_url'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'enable_seo_url',
            'value' => $data['enable_seo_url'],
            'style' => 'btn_switch',
        ));
        $fields['compression'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_compression',
            'value' => $data['config_compression'],
        ));
        $fields['cache_enable'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'config_cache_enable',
            'value' => $data['config_cache_enable'],
            'style' => 'btn_switch',
        ));
        $fields['upload_max_size'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_upload_max_size',
            'value' => number_format($data['config_upload_max_size'], 0, '.', $this->language->get('thousand_point'))
        )).' (<= '.ini_get('post_max_size').')';

        $fields['error_display'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'config_error_display',
            'value' => $data['config_error_display'],
            'style' => 'btn_switch',
        ));
        $fields['error_log'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'config_error_log',
            'value' => $data['config_error_log'],
            'style' => 'btn_switch',
        ));
        $fields['debug'] = $form->getFieldHtml($props[] = array(
            'type' => 'selectbox',
            'name' => 'config_debug',
            'value' => $data['config_debug'],
            'options' => array(
                0 => $this->language->get('entry_debug_0'),
                1 => $this->language->get('entry_debug_1'),
                2 => $this->language->get('entry_debug_2'),
            ),
        ));
        $fields['debug_level'] = $form->getFieldHtml($props[] = array(
            'type' => 'selectbox',
            'name' => 'config_debug_level',
            'value' => $data['config_debug_level'],
            'options' => array(
                0 => $this->language->get('entry_debug_level_0'),
                1 => $this->language->get('entry_debug_level_1'),
                2 => $this->language->get('entry_debug_level_2'),
                3 => $this->language->get('entry_debug_level_3'),
                4 => $this->language->get('entry_debug_level_4'),
                5 => $this->language->get('entry_debug_level_5'),
            ),
        ));
        $fields['template_debug'] = $form->getFieldHtml($props[] = array(
            'type' => 'checkbox',
            'name' => 'storefront_template_debug',
            'value' => $data['storefront_template_debug'],
            'style' => 'btn_switch',
            'attr' => 'reload_on_save="true"'
        ));
        $fields['error_filename'] = $form->getFieldHtml($props[] = array(
            'type' => 'input',
            'name' => 'config_error_filename',
            'value' => $data['config_error_filename'],
            'required' => true,
        ));
        if(isset($data['one_field'])){
            $fields = $this->_filterField($fields,$props,$data['one_field']);
        }
		return $fields;
	}

    private function _filterField($fields, $props, $field_name ){
        $output = array();
        foreach($props as $n=>$properties){
            if($field_name == $properties['name']
		       || ( is_int(strpos($field_name,'config_description')) && is_int(strpos($properties['name'],'config_description')))){
                $names = array_keys($fields);
                $name = $names[$n];
                $output = array($name => $fields[$name]);
                break;
            }
        }
        return $output;
    }


	// validate form fields
	public function validate($group, $fields=array()){
		if(empty($group) || !is_array($fields)){
			return false;
		}
		$this->load->language('setting/setting');

		foreach( $fields as $field_name => $field_value ){
				switch ($group) {
					case 'details':
						if ( $field_name=='store_name' &&  !$field_value) {
							$error['name'] = $this->language->get('error_name');
						}
						if ($field_name == 'config_title' &&  !$field_value) {
							$error['title'] = $this->language->get('error_title');
						}

						if ($field_name == 'config_url' &&  !$field_value) {
							$error['url'] = $this->language->get('error_url');
						}
						if(sizeof($fields)>1){
							if ((strlen(utf8_decode($fields['config_owner'])) < 2) || (strlen(utf8_decode($fields['config_owner'])) > 64)) {
								$error['owner'] = $this->language->get('error_owner');
							}

							if ((strlen(utf8_decode($fields['config_address'])) < 2) || (strlen(utf8_decode($fields['config_address'])) > 256)) {
								$error['address'] = $this->language->get('error_address');
							}

							$pattern = '/^[A-Z0-9._%-]+@[A-Z0-9][A-Z0-9.-]{0,61}[A-Z0-9]\.[A-Z]{2,6}$/i';
							if ((strlen(utf8_decode($fields['store_main_email'])) > 96) || (!preg_match($pattern, $fields['store_main_email']))) {
								$error['email'] = $this->language->get('error_email');
							}

							if ((strlen(utf8_decode($fields['config_telephone'])) < 2) || (strlen(utf8_decode($fields['config_telephone'])) > 32)) {
								$error['telephone'] = $this->language->get('error_telephone');
							}
						}
						break;


					case 'general':
						//if ($field == 'config_admin_limit' &&  !$value) {
			//$error['admin_limit'] = $this->language->get('error_limit');
		//}

						if ($field_name == 'config_catalog_limit' &&  !$field_value) {
							$error['catalog_limit'] = $this->language->get('error_limit');
						}

						if ($field_name == 'config_bestseller_limit' &&  !$field_value) {
							$error['bestseller_limit'] = $this->language->get('error_limit');
						}

						if ($field_name == 'config_featured_limit' &&  !$field_value) {
							$error['featured_limit'] = $this->language->get('error_limit');
						}

						if ($field_name == 'config_latest_limit' &&  !$field_value) {
							$error['latest_limit'] = $this->language->get('error_limit');
						}

						if ($field_name == 'config_special_limit' &&  !$field_value) {
							$error['special_limit'] = $this->language->get('error_limit');
						}
						break;

					case 'appearance':
						if (($field_name == 'config_image_thumb_width' &&  !$field_value) || ($field_name == 'config_image_thumb_height' &&  !$field_value)) {
							$error['image_thumb_width'] = $error['image_thumb_height'] = $this->language->get('error_image_thumb');
						}

						if (($field_name == 'config_image_popup_width' &&  !$field_value) || ($field_name == 'config_image_popup_height' &&  !$field_value)) {
							$error['image_popup_height'] = $error['image_popup_width'] = $this->language->get('error_image_popup');
						}

						if (($field_name == 'config_image_category_width' &&  !$field_value) || ($field_name == 'config_image_category_height' &&  !$field_value)) {
							$error['image_category_height'] = $this->language->get('error_image_category');
						}

						if (($field_name == 'config_image_product_width' &&  !$field_value) || ($field_name == 'config_image_product_height' &&  !$field_value)) {
							$error['image_product_height'] = $this->language->get('error_image_product');
						}

						if (($field_name == 'config_image_additional_width' &&  !$field_value) || ($field_name == 'config_image_additional_height' &&  !$field_value)) {
							$error['image_additional_height'] = $this->language->get('error_image_additional');
						}

						if (($field_name == 'config_image_related_width' &&  !$field_value) || ($field_name == 'config_image_related_height' &&  !$field_value)) {
							$error['image_related_height'] = $this->language->get('error_image_related');
						}

						if (($field_name == 'config_image_cart_width' &&  !$field_value) || ($field_name == 'config_image_cart_height' &&  !$field_value)) {
							$error['image_cart_height'] = $this->language->get('error_image_cart');
						}

						if (($field_name == 'config_image_grid_width' &&  !$field_value) || ($field_name == 'config_image_grid_height' &&  !$field_value)) {
							$error['image_grid_height'] = $this->language->get('error_image_grid');
						}
						break;

					case 'checkout':
						break;

					case 'api':
						break;

					case 'mail':

						if (($fields['config_mail_protocol'] =='smtp')
							&& (($field_name == 'config_smtp_host' &&  !$field_value) || ($field_name == 'config_smtp_port' &&  !$field_value) || ($field_name == 'config_smtp_timeout' &&  !$field_value))
						) {
							$error['mail'] = $this->language->get('error_mail');
						}

						break;

					case 'system':
						if ($field_name == 'config_error_filename' &&  !$field_value) {
							$error['error_filename'] = $this->language->get('error_error_filename');
						}
						if ( $field_name == 'config_upload_max_size' ) {
							$fields[$field_value] = preformatInteger($field_value);
						}

						break;
					default:
				}


			}
	return array('error'=>$error, 'validated'=>$fields);
	}

}
