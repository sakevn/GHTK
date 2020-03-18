<?php
/*
 * Plugin Name: DevVN - Woocommerce - Giao Hàng Tiết Kiệm (GHTK)
 * Plugin URI: https://doibu.com/san-pham/plugin-ket-noi-giao-hang-tiet-kiem-voi-woocommerce-ghtk-vs-woocommerce/
 * Version: 1.3.3
 * Description: Add province/city, district, commune/ward/town to checkout form and simplify checkout form. Sync order and calc shipping code form GHTK
 * Author: Le Van Toan
 * Author URI: http://doibu.com
 * Text Domain: devvn-ghtk
 * Domain Path: /languages
 * WC requires at least: 3.0.0
 * WC tested up to: 3.8.1
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

register_activation_hook(   __FILE__, array( 'DevVN_Woo_GHTK_Class', 'on_activation' ) );
register_deactivation_hook( __FILE__, array( 'DevVN_Woo_GHTK_Class', 'on_deactivation' ) );
register_uninstall_hook(    __FILE__, array( 'DevVN_Woo_GHTK_Class', 'on_uninstall' ) );

//add_action( 'plugins_loaded', array( 'DevVN_Woo_GHTK_Class', 'init' ) );
load_textdomain('devvn-ghtk', dirname(__FILE__) . '/languages/devvn-ghtk-' . get_locale() . '.mo');

class DevVN_Woo_GHTK_Class
{
    protected static $instance;

	protected $_version = '1.3.3';
	public $_optionName = 'devvn_woo_district';
	public $_optionGroup = 'devvn-district-options-group';
	public $_defaultOptions = array(
	    'active_village'	            =>	'',
        'required_village'	            =>	'',
        'to_vnd'	                    =>	'',
        'remove_methob_title'	        =>	'',
        'freeship_remove_other_methob'  =>  '',
        'khoiluong_quydoi'              =>  '6000',
        'active_vnd2usd'                =>  0,
        'vnd_usd_rate'                  =>  '22745',
        'vnd2usd_currency'              =>  'USD',

        'shop_store'                    =>  array(),

        'token_key'                     => '',
        'is_freeship'                   =>  0,
        'is_sandbox'                    =>  0,
        'transport'                     =>  'road', //road hoặc fly
        'active_orderstyle'             =>  0,
        'ghtk_hash'                     =>  '',

        'alepay_support'                =>  0,
        'enable_postcode'               =>  0,

        'enable_gender'                 =>  0,

        'print_logo'                   =>  '',
        'print_note'                   =>  '<strong>Chú ý:</strong> Kiểm tra hàng khi có mặt shiper.',
        'mashop'                       =>  '',

        'license_key'                  =>  '',

        'send_shipid_active'           =>  0,
        'send_shipid_title'            =>  'Mã vận đơn tại {site_title}',
        'send_shipid_content'          =>  'Đơn hàng #{order_id} của bạn đang được vận chuyển. <br>Mã vận đơn là: {ship_id}<br> Ngày dự kiến giao hàng: {estimated_deliver}',

	);
	public $_default_status = array(
        'success'   =>  false,
        'message'   =>  '',
        'order' =>  array(
            'label_id'  =>  '',
            'partner_id'  =>  '',
            'status'  =>  '',
            'created'  =>  '',
            'updated'  =>  '',
            'pick_date'  =>  '',
            'pick_period'  =>  '',
            'deliver_date'  =>  '',
            'deliver_period'  =>  '',

            'status_id' =>  '',
            'action_time' =>  '',
            'reason_code' =>  '',
            'reason' =>  '',
            'weight' =>  '',
            'fee' =>  '',
        )
    );

	public $_weight_option = 'kilogram';
	public $tinh_thanhpho = array();

    public static function init(){
        is_null( self::$instance ) AND self::$instance = new self;
        return self::$instance;
    }

	public function __construct(){

        $this->define_constants();

        $this->set_weight_option();

        include 'cities/tinh_thanhpho.php';
        $this->tinh_thanhpho = $tinh_thanhpho;

    	add_filter( 'woocommerce_checkout_fields' , array($this, 'custom_override_checkout_fields'), 99999 );
    	add_filter( 'woocommerce_states', array($this, 'vietnam_cities_woocommerce'), 9999 );

        add_action('woocommerce_checkout_process', array($this, 'devvn_gender_field_process'));

    	add_action( 'wp_enqueue_scripts', array($this, 'devvn_enqueue_UseAjaxInWp') );
    	add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

    	add_action( 'wp_ajax_load_diagioihanhchinh', array($this, 'load_diagioihanhchinh_func') );
		add_action( 'wp_ajax_nopriv_load_diagioihanhchinh', array($this, 'load_diagioihanhchinh_func') );

		add_filter('woocommerce_localisation_address_formats', array($this, 'devvn_woocommerce_localisation_address_formats') );
		add_filter('woocommerce_order_formatted_billing_address', array($this, 'devvn_woocommerce_order_formatted_billing_address'), 10, 2);

		add_action( 'woocommerce_admin_order_data_after_shipping_address', array($this, 'devvn_after_shipping_address'), 10, 1 );
		add_filter('woocommerce_order_formatted_shipping_address', array($this, 'devvn_woocommerce_order_formatted_shipping_address'), 10, 2);

		add_filter('woocommerce_order_details_after_customer_details', array($this, 'devvn_woocommerce_order_details_after_customer_details'), 10);

		//my account
		add_filter('woocommerce_my_account_my_address_formatted_address',array($this, 'devvn_woocommerce_my_account_my_address_formatted_address'),10,3);

		//More action
        add_filter( 'default_checkout_billing_country', array($this, 'devvn_change_default_checkout_country'), 999 );
        add_filter( 'default_checkout_shipping_country', array($this, 'devvn_change_default_checkout_country'), 999 );

		//Options
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_mysettings') );
        add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'plugin_action_links' ) );

		add_option( $this->_optionName, $this->_defaultOptions );

        include_once( 'includes/apps.php' );
        include_once( 'includes/class-ghtk-shipping.php' );

        add_filter( 'woocommerce_default_address_fields' , array( $this, 'devvn_custom_override_default_address_fields'), 99999 );
        add_filter('woocommerce_get_country_locale', array($this, 'devvn_woocommerce_get_country_locale'), 99);

        //admin order address, form billing
        add_filter('woocommerce_admin_billing_fields', array($this, 'devvn_woocommerce_admin_billing_fields'), 99);
        add_filter('woocommerce_admin_shipping_fields', array($this, 'devvn_woocommerce_admin_shipping_fields'), 99);

        add_filter('woocommerce_form_field_select', array($this, 'devvn_woocommerce_form_field_select'), 10, 4);

        //ghtk to edit order
        add_action('woocommerce_admin_order_data_after_order_details', array($this, 'devvn_woocommerce_admin_order_data_after_order_details') );
        add_action( 'wp_ajax_dang_order_ghtk', array($this, 'func_dang_order_ghtk') );
        add_action( 'wp_ajax_print_order_ghtk', array($this, 'print_order_ghtk_func') );
        add_action( 'wp_ajax_check_status_ghtk', array($this, 'check_status_ghtk_func') );
        add_action( 'wp_ajax_ghtk_add_url', array($this, 'ghtk_add_url_func') );
        add_action( 'wp_ajax_ghtk_delete_url', array($this, 'ghtk_delete_url_func') );
        add_action( 'wp_ajax_nopriv_update_shipping_status', array($this, 'update_shipping_status_func') );

        add_filter('woocommerce_shipping_calculator_enable_postcode','__return_false');

        add_filter('woocommerce_get_order_address', array($this, 'devvn_woocommerce_get_order_address'), 99, 2);  //API V1
        add_filter('woocommerce_rest_prepare_shop_order_object', array($this, 'devvn_woocommerce_rest_prepare_shop_order_object'), 99, 3);//API V2

        include_once ('includes/updates.php');

        add_action( 'wp_ajax_inhoadon_ghtk', array($this, 'devvn_print_order') );

        //Support xc-woo-google-cloud-print
        add_action('xc_woo_cloud_print_after_order_details', array($this, 'xc_woo_cloud_print_after_order_details'), 10, 2);
        //Support point of sale
        add_action('admin_print_footer_scripts', array($this, 'devvn_admin_print_footer_scripts'), 999999);

        //Tracking
        add_action( 'wp_ajax_ghtk_tracking', array($this, 'ghtk_tracking_func') );
        add_action( 'wp_ajax_nopriv_ghtk_tracking', array($this, 'ghtk_tracking_func') );
        add_shortcode('ghtk_tracking_form', array($this, 'ghtk_tracking_form_func'));

        add_filter('woocommerce_formatted_address_replacements', array($this, 'devvn_woocommerce_formatted_address_replacements'), 9);
        add_action( 'admin_notices',  array($this, 'webhook_admin_notice__error') );

        //since 1.2.8
        //add_action( 'woocommerce_order_status_completed', array($this, 'auto_order_to_ghtk'), 10, 1 );

        //Since 1.3.1
        add_action('devvn_ghtk_action', array($this, 'devvn_ghtk_action_func'));
        add_action( 'wp_ajax_ghtk_creat_order_ajax', array($this, 'ghtk_creat_order_ajax_func') );
        add_filter( 'bulk_actions-edit-shop_order', array( $this, 'define_bulk_actions' ) );
        add_filter( 'handle_bulk_actions-edit-shop_order', array( $this, 'ghtk_bulk_action_handler'), 10, 3 );

        //1.3.3
        add_filter('woocommerce_customer_meta_fields', array($this, 'woocommerce_customer_meta_fields') );
        add_filter('woocommerce_general_settings', array($this, 'woocommerce_general_settings') );
        //1.3.3 - fix bug select load by ajax
        add_action('woocommerce_admin_field_selectajax', array($this, 'woocommerce_admin_field_selectajax'));

    }

    public function define_constants(){
        if (!defined('DEVVN_GHTK_VERSION_NUM'))
            define('DEVVN_GHTK_VERSION_NUM', $this->_version);
        if (!defined('DEVVN_GHTK_URL'))
            define('DEVVN_GHTK_URL', plugin_dir_url(__FILE__));
        if (!defined('DEVVN_GHTK_BASENAME'))
            define('DEVVN_GHTK_BASENAME', plugin_basename(__FILE__));
        if (!defined('DEVVN_GHTK_PLUGIN_DIR'))
            define('DEVVN_GHTK_PLUGIN_DIR', plugin_dir_path(__FILE__));
    }

    function set_weight_option(){
	    $wc_weight = get_option( 'woocommerce_weight_unit' );
	    if($wc_weight == 'g')
	        $this->_weight_option = 'gram';
    }

    public static function on_activation(){
        if ( ! current_user_can( 'activate_plugins' ) )
            return false;
        $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
        check_admin_referer( "activate-plugin_{$plugin}" );

    }

    public static function on_deactivation(){
        if ( ! current_user_can( 'activate_plugins' ) )
            return false;
        $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
        check_admin_referer( "deactivate-plugin_{$plugin}" );

    }

    public static function on_uninstall(){
        if ( ! current_user_can( 'activate_plugins' ) )
            return false;
    }

	function admin_menu() {
		add_options_page(
			__('Cài đặt GHTK','devvn-ghtk'),
			__('Cài đặt GHTK','devvn-ghtk'),
			'manage_options',
			'devvn-woo-ghtk',
			array(
				$this,
				'devvn_district_setting'
			)
		);
	}

	function register_mysettings() {
		register_setting( $this->_optionGroup, $this->_optionName );
	}

	function  devvn_district_setting() {
        wp_enqueue_media();
        include 'includes/options-page.php';
	}

	function vietnam_cities_woocommerce( $states ) {
        if(!is_array($this->tinh_thanhpho) || empty($this->tinh_thanhpho)){
            include 'cities/tinh_thanhpho.php';
            $this->tinh_thanhpho = $tinh_thanhpho;
        }
	  	$states['VN'] = $this->tinh_thanhpho;
	  	return $states;
	}

    function custom_override_checkout_fields( $fields ) {

        if($this->get_options('enable_gender')) {
            $fields['billing']['billing_gender'] = array(
                'label' => __('Gender', 'devvn-ghtk'),
                //'class' => array('form-row-first'),
                'priority' => 5,
                'default'  => 'female',
                'required' => true,
                'type'            => 'radio',
                'options'         => array(
                    'male'     => __( 'Mr', 'devvn-ghtk' ),
                    'female'      => __( 'Mrs', 'devvn-ghtk' ),
                ),
            );
        }
        if(!$this->get_options('alepay_support')) {
            //Billing
            $fields['billing']['billing_first_name'] = array(
                'label' => __('Full name', 'devvn-ghtk'),
                'placeholder' => _x('Type Full name', 'placeholder', 'devvn-ghtk'),
                'required' => true,
                'class' => array('form-row-wide'),
                'clear' => true,
                'priority' => 10
            );
        }
        if(isset($fields['billing']['billing_phone'])) {
            $fields['billing']['billing_phone']['class'] = array('form-row-first');
            $fields['billing']['billing_phone']['placeholder'] = __('Type your phone', 'devvn-ghtk');
        }
        if(isset($fields['billing']['billing_email'])) {
            $fields['billing']['billing_email']['class'] = array('form-row-last');
            $fields['billing']['billing_email']['placeholder'] = __('Type your email', 'devvn-ghtk');
        }
        $fields['billing']['billing_state'] = array(
            'label'			=> __('Province/City', 'devvn-ghtk'),
            'required' 		=> true,
            'type'			=> 'select',
            'class'    		=> array( 'form-row-wide', 'devvn-address-field' ),
            'placeholder'	=> _x('Select Province/City', 'placeholder', 'devvn-ghtk'),
            'options'   	=> array( '' => __( 'Select Province/City', 'devvn-ghtk' ) ) + $this->tinh_thanhpho,
            'priority'  =>  30
        );
        $fields['billing']['billing_city'] = array(
            'label'		=> __('District', 'devvn-ghtk'),
            'required' 	=> true,
            'type'		=>	'select',
            'class'    	=> array( 'form-row-wide', 'address-field', 'update_totals_on_change' ),
            'placeholder'	=>	_x('Select District', 'placeholder', 'devvn-ghtk'),
            'options'   => array(
                ''	=> ''
            ),
            'priority'  =>  40
        );
        if(!$this->get_options()) {
            $fields['billing']['billing_address_2'] = array(
                'label' => __('Commune/Ward/Town', 'devvn-ghtk'),
                'required' => true,
                'type' => 'select',
                'class' => array('form-row-wide', 'devvn-address-field'),
                'placeholder' => _x('Select Commune/Ward/Town', 'placeholder', 'devvn-ghtk'),
                'options' => array(
                    '' => ''
                ),
                'priority'  =>  50
            );
            if ($this->get_options('required_village')) {
                $fields['billing']['billing_address_2']['required'] = false;
            }
        }
        $fields['billing']['billing_address_1']['placeholder'] = _x('Ex: No. 20, 90 Alley', 'placeholder', 'devvn-ghtk');
        $fields['billing']['billing_address_1']['class'] = array('form-row-wide');

        $fields['billing']['billing_address_1']['priority']  = 60;
        if(isset($fields['billing']['billing_phone'])) {
            $fields['billing']['billing_phone']['priority'] = 20;
        }
        if(isset($fields['billing']['billing_email'])) {
            $fields['billing']['billing_email']['priority'] = 21;
        }
        if(!$this->get_options('alepay_support')) {
            unset($fields['billing']['billing_country']);
            unset($fields['billing']['billing_last_name']);
        }else{
            $fields['billing']['billing_country']['priority'] = 22;
        }
        unset($fields['billing']['billing_company']);

        //Shipping
        if(!$this->get_options('alepay_support')) {
            $fields['shipping']['shipping_first_name'] = array(
                'label' => __('Full name', 'devvn-ghtk'),
                'placeholder' => _x('Type Full name', 'placeholder', 'devvn-ghtk'),
                'required' => true,
                'class' => array('form-row-first'),
                'clear' => true,
                'priority' => 10
            );
        }
        $fields['shipping']['shipping_phone'] = array(
            'label' => __('Phone', 'devvn-ghtk'),
            'placeholder' => _x('Phone', 'placeholder', 'devvn-ghtk'),
            'required' => false,
            'class' => array('form-row-last'),
            'clear' => true,
            'priority'  =>  20
        );
        if($this->get_options('alepay_support')) {
            $fields['shipping']['shipping_phone']['class'] = array('form-row-wide');
        }
        $fields['shipping']['shipping_state'] = array(
            'label'		=> __('Province/City', 'devvn-ghtk'),
            'required' 	=> true,
            'type'		=>	'select',
            'class'    	=> array( 'form-row-wide', 'devvn-address-field' ),
            'placeholder'	=>	_x('Select Province/City', 'placeholder', 'devvn-ghtk'),
            'options'   => array( '' => __( 'Select Province/City', 'devvn-ghtk' ) ) + $this->tinh_thanhpho,
            'priority'  =>  30
        );
        $fields['shipping']['shipping_city'] = array(
            'label'		=> __('District', 'devvn-ghtk'),
            'required' 	=> true,
            'type'		=>	'select',
            'class'    	=> array( 'form-row-wide', 'address-field', 'update_totals_on_change' ),
            'placeholder'	=>	_x('Select District', 'placeholder', 'devvn-ghtk'),
            'options'   => array(
                ''	=> '',
            ),
            'priority'  =>  40
        );
        if(!$this->get_options()) {
            $fields['shipping']['shipping_address_2'] = array(
                'label' => __('Commune/Ward/Town', 'devvn-ghtk'),
                'required' => true,
                'type' => 'select',
                'class' => array('form-row-wide', 'devvn-address-field'),
                'placeholder' => _x('Select Commune/Ward/Town', 'placeholder', 'devvn-ghtk'),
                'options' => array(
                    '' => '',
                ),
                'priority'  =>  50
            );
            if ($this->get_options('required_village')) {
                $fields['shipping']['shipping_address_2']['required'] = false;
            }
        }
        $fields['shipping']['shipping_address_1']['placeholder'] = _x('Ex: No. 20, 90 Alley', 'placeholder', 'devvn-ghtk');
        $fields['shipping']['shipping_address_1']['class'] = array('form-row-wide');
        $fields['shipping']['shipping_address_1']['priority'] = 60;
        if(!$this->get_options('alepay_support')) {
            unset($fields['shipping']['shipping_country']);
            unset($fields['shipping']['shipping_last_name']);
        }else{
            $fields['shipping']['shipping_country']['priority'] = 22;
        }
        unset($fields['shipping']['shipping_company']);

        uasort( $fields['billing'], array( $this, 'sort_fields_by_order' ) );
        uasort( $fields['shipping'], array( $this, 'sort_fields_by_order' ) );

        return $fields;
    }

    function sort_fields_by_order($a, $b){
        if(!isset($b['priority']) || !isset($a['priority']) || $a['priority'] == $b['priority']){
            return 0;
        }
        return ($a['priority'] < $b['priority']) ? -1 : 1;
    }

	function search_in_array($array, $key, $value)
	{
	    $results = array();

	    if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value) {
                $results[] = $array;
            }elseif(isset($array[$key]) && is_serialized($array[$key]) && in_array($value,maybe_unserialize($array[$key]))){
                $results[] = $array;
            }
	        foreach ($array as $subarray) {
	            $results = array_merge($results, $this->search_in_array($subarray, $key, $value));
	        }
	    }

	    return $results;
	}

	function check_file_open_status($file_url = ''){
        if(!$file_url) return false;
        try {
            $response = @file_get_contents($file_url);
            $status_line = $http_response_header[0];
            preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
            $status = $match[1];
            return $status;
        }
        catch (Exception $e) {
            return false;
        }
        return false;
    }

	function devvn_enqueue_UseAjaxInWp() {

		if(is_checkout() || is_page(get_option( 'woocommerce_edit_address_page_id' ))){
            wp_enqueue_style( 'ghtk_styles', plugins_url( '/assets/css/devvn_dwas_style.css', __FILE__ ), array(), $this->_version, 'all' );
			wp_enqueue_script( 'devvn_tinhthanhpho', plugins_url('assets/js/devvn_tinhthanh.js', __FILE__), array('jquery','select2'), $this->_version, true);

			$get_address = DEVVN_GHTK_URL . 'get-address.php';
			if($this->check_file_open_status($get_address) != 200){
                $get_address = admin_url( 'admin-ajax.php');
            }

			$php_array = array(
				'admin_ajax'		=>	admin_url( 'admin-ajax.php'),
				'get_address'		=>	$get_address,
				'home_url'			=>	home_url(),
                'formatNoMatches'   =>  __('No value', 'devvn-ghtk')
			);
			wp_localize_script( 'devvn_tinhthanhpho', 'ghtk_array', $php_array );
		}
	}

	function load_diagioihanhchinh_func() {
		$matp = isset($_POST['matp']) ? sanitize_text_field($_POST['matp']) : '';
		$maqh = isset($_POST['maqh']) ? intval($_POST['maqh']) : '';
		if($matp){
			$result = $this->get_list_district($matp);
			wp_send_json_success($result);
		}
		if($maqh){
			$result = $this->get_list_village($maqh);
			wp_send_json_success($result);
		}
		wp_send_json_error();
		die();
	}
	function devvn_get_name_location($arg = array(), $id = '', $key = ''){
		if(is_array($arg) && !empty($arg)){
			$nameQuan = $this->search_in_array($arg,$key,$id);
			$nameQuan = isset($nameQuan[0]['name'])?$nameQuan[0]['name']:'';
			return $nameQuan;
		}
		return false;
	}

	function get_name_city($id = ''){
		if(!is_array($this->tinh_thanhpho) || empty($this->tinh_thanhpho)){
			include 'cities/tinh_thanhpho.php';
            $this->tinh_thanhpho = $tinh_thanhpho;
		}
		$id_tinh = sanitize_text_field($id);
		$tinh_thanhpho = (isset($this->tinh_thanhpho[$id_tinh]))?$this->tinh_thanhpho[$id_tinh]:'';
		return $tinh_thanhpho;
	}

	function get_name_district($id = ''){
		include 'cities/quan_huyen.php';
		$id_quan = sprintf("%03d", intval($id));
		if(is_array($quan_huyen) && !empty($quan_huyen)){
			$nameQuan = $this->search_in_array($quan_huyen,'maqh',$id_quan);
			$nameQuan = isset($nameQuan[0]['name'])?$nameQuan[0]['name']:'';
			return $nameQuan;
		}
		return false;
	}

	function get_name_village($id = ''){
		include 'cities/xa_phuong_thitran.php';
		$id_xa = sprintf("%05d", intval($id));
		if(is_array($xa_phuong_thitran) && !empty($xa_phuong_thitran)){
			$name = $this->search_in_array($xa_phuong_thitran,'xaid',$id_xa);
			$name = isset($name[0]['name'])?$name[0]['name']:'';
			return $name;
		}
		return false;
	}

	function devvn_woocommerce_localisation_address_formats($arg){
        if(isset($arg['default'])) unset($arg['default']);
        if(isset($arg['VN'])) unset($arg['VN']);
		$arg['default'] = "{gender} {name}\n{company}\n{address_1}\n{address_2}\n{city}\n{state}\n{country}";
		$arg['VN'] = "{gender} {name}\n{company}\n{address_1}\n{address_2}\n{city}\n{state}\n{country}";
		return $arg;
	}

	function devvn_woocommerce_order_formatted_billing_address($eArg,$eThis){

        if(!$eArg) return array();

        if($this->devvn_check_woo_version()){
            $orderID = $eThis->get_id();
        }else {
            $orderID = $eThis->id;
        }

		$nameTinh = $this->get_name_city(get_post_meta( $orderID, '_billing_state', true ));
		$nameQuan = $this->get_name_district(get_post_meta( $orderID, '_billing_city', true ));
		$nameXa = $this->get_name_village(get_post_meta( $orderID, '_billing_address_2', true ));

		unset($eArg['state']);
		unset($eArg['city']);
		unset($eArg['address_2']);

		$eArg['state'] = $nameTinh;
		$eArg['city'] = $nameQuan;
		$eArg['address_2'] = $nameXa;

		return $eArg;
	}

	function devvn_woocommerce_order_formatted_shipping_address($eArg,$eThis){


        if(!$eArg) return array();

        if($this->devvn_check_woo_version()){
            $orderID = $eThis->get_id();
        }else {
            $orderID = $eThis->id;
        }

		$nameTinh = $this->get_name_city(get_post_meta( $orderID, '_shipping_state', true ));
		$nameQuan = $this->get_name_district(get_post_meta( $orderID, '_shipping_city', true ));
		$nameXa = $this->get_name_village(get_post_meta( $orderID, '_shipping_address_2', true ));

		unset($eArg['state']);
		unset($eArg['city']);
		unset($eArg['address_2']);

		$eArg['state'] = $nameTinh;
		$eArg['city'] = $nameQuan;
		$eArg['address_2'] = $nameXa;

		return $eArg;
	}

	function devvn_woocommerce_my_account_my_address_formatted_address($args, $customer_id, $name){

        if(!$args) return array();

		$nameTinh = $this->get_name_city(get_user_meta( $customer_id, $name.'_state', true ));
		$nameQuan = $this->get_name_district(get_user_meta( $customer_id, $name.'_city', true ));
		$nameXa = $this->get_name_village(get_user_meta( $customer_id, $name.'_address_2', true ));

		unset($args['address_2']);
		unset($args['city']);
		unset($args['state']);

		$args['state'] = $nameTinh;
		$args['city'] = $nameQuan;
		$args['address_2'] = $nameXa;

		return $args;
	}

    function natorder($a,$b) {
        return strnatcasecmp ( $a['name'], $b['name'] );
    }

	function get_list_district($matp = ''){
		if(!$matp) return false;
		include 'cities/quan_huyen.php';
		$matp = sanitize_text_field($matp);
		$result = $this->search_in_array($quan_huyen,'matp',$matp);
        usort($result, array($this, 'natorder') );
		return $result;
	}

    function get_list_district_select($matp = ''){
        $district_select  = array();
        $district_select_array = $this->get_list_district($matp);
        if($district_select_array && is_array($district_select_array)){
            foreach ($district_select_array as $district){
                $district_select[$district['maqh']] = $district['name'];
            }
        }
        return $district_select;
    }

	function get_list_village($maqh = ''){
		if(!$maqh) return false;
		include 'cities/xa_phuong_thitran.php';
		$id_xa = sprintf("%05d", intval($maqh));
		$result = $this->search_in_array($xa_phuong_thitran,'maqh',$id_xa);
        usort($result, array($this, 'natorder') );
		return $result;
	}

    function get_list_village_select($maqh = ''){
        $village_select  = array();
        $village_select_array = $this->get_list_village($maqh);
        if($village_select_array && is_array($village_select_array)){
            foreach ($village_select_array as $village){
                $village_select[$village['xaid']] = $village['name'];
            }
        }
        return $village_select;
    }

	function devvn_after_shipping_address($order){
	    if($this->devvn_check_woo_version()){
            $orderID = $order->get_id();
        }else {
            $orderID = $order->id;
        }
	    echo '<p><strong>'.__('Phone number of the recipient', 'devvn-ghtk').':</strong> <br>' . get_post_meta( $orderID, '_shipping_phone', true ) . '</p>';
	}

	function devvn_woocommerce_order_details_after_customer_details($order){
		ob_start();
        if($this->devvn_check_woo_version()){
            $orderID = $order->get_id();
        }else {
            $orderID = $order->id;
        }
        $sdtnguoinhan = get_post_meta( $orderID, '_shipping_phone', true );
		if ( $sdtnguoinhan ) : ?>
			<tr>
				<th><?php _e( 'Shipping Phone:', 'devvn-ghtk' ); ?></th>
				<td><?php echo esc_html( $sdtnguoinhan ); ?></td>
			</tr>
		<?php endif;
		echo ob_get_clean();
	}

	public function get_options($option = 'active_village'){
		$flra_options = wp_parse_args(get_option($this->_optionName),$this->_defaultOptions);
		return isset($flra_options[$option])?$flra_options[$option]:false;
	}

	public function admin_enqueue_scripts() {
        $get_address = DEVVN_GHTK_URL . 'get-address.php';
        if($this->check_file_open_status($get_address) != 200){
            $get_address = admin_url( 'admin-ajax.php');
        }
        wp_enqueue_style( 'style.magnific-popup', plugins_url( '/assets/css/magnific-popup.css', __FILE__ ), array(), $this->_version, 'all' );
        wp_enqueue_style( 'woocommerce_district_shipping_styles', plugins_url( '/assets/css/admin.css', __FILE__ ), array(), $this->_version, 'all' );
        wp_enqueue_script( 'jquery.magnific-popup', plugins_url( '/assets/js/jquery.magnific-popup.min.js', __FILE__ ), array( 'jquery' ), $this->_version, true );
        wp_enqueue_script( 'woocommerce_district_admin_order', plugins_url( '/assets/js/admin-district-admin-order.js', __FILE__ ), array( 'jquery', 'select2', 'wp-util'), $this->_version, true );
        wp_localize_script( 'woocommerce_district_admin_order', 'admin_ghtk_array', array(
            'ajaxurl'   =>  admin_url('admin-ajax.php'),
            'get_address'		=>	$get_address,
            'formatNoMatches'   =>  __('No value', 'devvn-ghtk')
        ) );
	}

    public function devvn_check_woo_version($version = '3.0.0'){
        if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, $version, '>=' ) ) {
            return true;
        }
        return false;
    }
    function devvn_change_default_checkout_country() {
        return 'VN';
    }

    function devvn_woocommerce_get_country_locale($args){
        $args['VN'] = array(
            'state' => array(
                'label'        => __('Province/City', 'devvn-ghtk'),
                'priority'     => 41,
            ),
            'city' => array(
                'priority'     => 42,
            ),
            'address_1' => array(
                'priority'     => 44,
            ),
        );
        if(!$this->get_options()) {
            $args['VN']['address_2'] = array(
                'hidden'   => false,
                'priority'     => 43,
            );
        }
        return $args;
    }

    function devvn_custom_override_default_address_fields( $address_fields ) {
        if(!$this->get_options('alepay_support')) {
            unset($address_fields['last_name']);
            $address_fields['first_name'] = array(
                'label' => __('Full name', 'devvn-ghtk'),
                'placeholder' => _x('Type Full name', 'placeholder', 'devvn-ghtk'),
                'required' => true,
                'class' => array('form-row-wide'),
                'clear' => true
            );
        }
        if(!$this->get_options('enable_postcode')) {
            unset($address_fields['postcode']);
        }
        $address_fields['city'] = array(
            'label'        => __('District', 'devvn-ghtk'),
            'type'		=>	'select',
            'required' => true,
            'class' => array('form-row-wide'),
            'placeholder'	=>	_x('Select District', 'placeholder', 'devvn-ghtk'),
            'options'   => array(
                ''	=> ''
            ),
        );
        if(!$this->get_options()) {
            $address_fields['address_2'] = array(
                'label' => __('Commune/Ward/Town', 'devvn-ghtk'),
                'type' => 'select',
                'class' => array('form-row-wide'),
                'placeholder' => _x('Select Commune/Ward/Town', 'placeholder', 'devvn-ghtk'),
                'options' => array(
                    '' => ''
                ),
            );
        }else{
            unset($address_fields['address_2']);
        }
        $address_fields['address_1']['class'] = array('form-row-wide');
        return $address_fields;
    }
    function devvn_woocommerce_admin_billing_fields($billing_fields){
        global $thepostid, $post;
        $thepostid = empty( $thepostid ) ? $post->ID : $thepostid;
        $city = get_post_meta( $thepostid, '_billing_state', true );
        $district = get_post_meta( $thepostid, '_billing_city', true );
        $billing_fields = array(
            'first_name' => array(
                'label' => __( 'First name', 'woocommerce' ),
                'show'  => false,
            ),
            'last_name' => array(
                'label' => __( 'Last name', 'woocommerce' ),
                'show'  => false,
            ),
            'company' => array(
                'label' => __( 'Company', 'woocommerce' ),
                'show'  => false,
            ),
            'country' => array(
                'label'   => __( 'Country', 'woocommerce' ),
                'show'    => false,
                'class'   => 'js_field-country select short',
                'type'    => 'select',
                'options' => array( '' => __( 'Select a country&hellip;', 'woocommerce' ) ) + WC()->countries->get_allowed_countries(),
            ),
            'state' => array(
                'label' => __( 'Tỉnh/thành phố', 'woocommerce' ),
                'class'   => 'js_field-state select short',
                'show'  => false,
            ),
            'city' => array(
                'label' => __( 'Quận/huyện', 'woocommerce' ),
                'class'   => 'js_field-city select short',
                'type'      =>  'select',
                'show'  => false,
                'options' => array( '' => __( 'Chọn quận/huyện&hellip;', 'woocommerce' ) ) + $this->get_list_district_select($city),
            ),
            'address_2' => array(
                'label' => __( 'Xã/phường/thị trấn', 'woocommerce' ),
                'show'  => false,
                'class'   => 'js_field-address_2 select short',
                'type'      =>  'select',
                'options' => array( '' => __( 'Chọn xã/phường/thị trấn&hellip;', 'woocommerce' ) ) + $this->get_list_village_select($district),
            ),
            'address_1' => array(
                'label' => __( 'Address line 1', 'woocommerce' ),
                'show'  => false,
            ),
            'email' => array(
                'label' => __( 'Email address', 'woocommerce' ),
            ),
            'phone' => array(
                'label' => __( 'Phone', 'woocommerce' ),
            )
        );
        if($this->get_options()) {
            unset($billing_fields['address_2']);
        }
        return $billing_fields;
    }
    function devvn_woocommerce_admin_shipping_fields($shipping_fields){
        global $thepostid, $post;
        $thepostid = empty( $thepostid ) ? $post->ID : $thepostid;
        $city = get_post_meta( $thepostid, '_shipping_state', true );
        $district = get_post_meta( $thepostid, '_shipping_city', true );
        $billing_fields = array(
            'first_name' => array(
                'label' => __( 'First name', 'woocommerce' ),
                'show'  => false,
            ),
            'last_name' => array(
                'label' => __( 'Last name', 'woocommerce' ),
                'show'  => false,
            ),
            'company' => array(
                'label' => __( 'Company', 'woocommerce' ),
                'show'  => false,
            ),
            'country' => array(
                'label'   => __( 'Country', 'woocommerce' ),
                'show'    => false,
                'type'    => 'select',
                'class'   => 'js_field-country select short',
                'options' => array( '' => __( 'Select a country&hellip;', 'woocommerce' ) ) + WC()->countries->get_shipping_countries(),
            ),
            'state' => array(
                'label' => __( 'Tỉnh/thành phố', 'woocommerce' ),
                'class'   => 'js_field-state select short',
                'show'  => false,
            ),
            'city' => array(
                'label' => __( 'Quận/huyện', 'woocommerce' ),
                'class'   => 'js_field-city select short',
                'type'      =>  'select',
                'show'  => false,
                'options' => array( '' => __( 'Chọn quận/huyện&hellip;', 'woocommerce' ) ) + $this->get_list_district_select($city),
            ),
            'address_2' => array(
                'label' => __( 'Xã/phường/thị trấn', 'woocommerce' ),
                'show'  => false,
                'class'   => 'js_field-address_2 select short',
                'type'      =>  'select',
                'options' => array( '' => __( 'Chọn xã/phường/thị trấn&hellip;', 'woocommerce' ) ) + $this->get_list_village_select($district),
            ),
            'address_1' => array(
                'label' => __( 'Address line 1', 'woocommerce' ),
                'show'  => false,
            ),
        );
        if($this->get_options()) {
            unset($billing_fields['address_2']);
        }
        return $billing_fields;
    }

    function get_cart_contents_weight( $package = array() ) {
        $weight = 0;
        if(isset($package['contents']) && !empty($package['contents'])) {
            foreach ($package['contents'] as $cart_item_key => $values) {
                $weight += (float)$values['data']->get_weight() * $values['quantity'];
            }
            switch(get_option( 'woocommerce_weight_unit' )){
                case 'kg':
                    $weight = $weight * 1000;
                    break;
                case 'lbs':
                    $weight = $weight * 453.59237;
                    break;
                case 'oz':
                    $weight = $weight * 28.34952;
                    break;
            }
        }
        return apply_filters( 'woocommerce_cart_contents_weight', $weight );
    }

    function convert_weight_to_kg( $weight ) {
        switch(get_option( 'woocommerce_weight_unit' )){
            case 'g':
                $weight = $weight * 0.001;
                break;
            case 'lbs':
                $weight = $weight * 0.45359237;
                break;
            case 'oz':
                $weight = $weight * 0.0283495231;
                break;
        }
        return $weight; //return kilogram
    }

    function get_cart_contents_weight_kg( $package = array() ) {
        $weight = 0;
        if(isset($package['contents']) && !empty($package['contents'])) {
            foreach ($package['contents'] as $cart_item_key => $values) {
                $weight += (float)$values['data']->get_weight() * $values['quantity'];
            }
            $weight = $this->convert_weight_to_kg($weight);
        }
        return apply_filters( 'woocommerce_cart_contents_weight_kg', $weight );
    }

    public static function plugin_action_links( $links ) {
        $action_links = array(
            'settings' => '<a href="' . admin_url( 'options-general.php?page=devvn-woo-ghtk' ) . '" title="' . esc_attr( __( 'Settings', 'devvn-ghtk' ) ) . '">' . __( 'Settings', 'devvn-ghtk' ) . '</a>',
        );

        return array_merge( $action_links, $links );
    }

    function order_action($label = '', $order = ''){
        if(!$label) return false;
        $orderid = ($order) ? $order->get_id() : '';
        $printed = get_post_meta($orderid,'devvn_printed', true);
        $printed_class = '';
        if($printed) $printed_class = '<i class="dashicons dashicons-yes-alt"></i>';
        $action = '<p class="form-field form-field-wide">
                        <a href="javascript:void(0)" class="button button-primary check_status_ghtk" data-label="'.esc_attr($label).'" data-nonce="'.wp_create_nonce('check_status_ghtk').'" data-orderid="'.$orderid.'">' . __('Check đơn hàng', 'devvn-ghtk') . '</a>
                        <a href="' . wp_nonce_url(admin_url('admin-ajax.php?action=print_order_ghtk&order=' . esc_attr($label)), 'print_order_action', 'nonce') . '" target="_blank" class="button button-primary">' . __('In hóa đơn GHTK', 'devvn-ghtk') . '</a>
                        <a href="' . admin_url('admin-ajax.php?action=inhoadon_ghtk&order_id=' . esc_attr($orderid)) . '" target="_blank" class="button button-primary">' . __('In hóa đơn theo mẫu riêng ', 'devvn-ghtk') . $printed_class .'</a>
                    </p>';
        return $action;

    }

    function get_status($order = ''){
        $result = array(
            'status'    => false,
            'content'   => ''
        );
        if(!$order) return $result;

        $order_ghtk_full = get_post_meta($order->get_id() ,'_order_ghtk_full', true);
        if(!$order_ghtk_full) $order_ghtk_full = get_post_meta($order->get_id() ,'_order_ghtk', true);
        if(($order_ghtk_full && is_array($order_ghtk_full) && !empty($order_ghtk_full))){
            $success = isset($order_ghtk_full['success']) ? $order_ghtk_full['success'] : '';
            $order_ghtk = isset($order_ghtk_full['order']) ? $order_ghtk_full['order'] : array();
            if ($success && $order_ghtk) {
                ob_start();

                $label = isset($order_ghtk['label_id']) ? $order_ghtk['label_id'] : '';
                if(!$label) $label = isset($order_ghtk['label']) ? $order_ghtk['label'] : '';

                $status_text = isset($order_ghtk['status_text']) ? $order_ghtk['status_text'] : '';

                $estimated_pick_time = isset($order_ghtk['estimated_pick_time']) ? $order_ghtk['estimated_pick_time'] : '';
                if(!$estimated_pick_time) $estimated_pick_time = isset($order_ghtk['pick_date']) ? $order_ghtk['pick_date'] : '';

                $estimated_deliver_time = isset($order_ghtk['estimated_deliver_time']) ? $order_ghtk['estimated_deliver_time'] : '';
                if(!$estimated_deliver_time) $estimated_deliver_time = isset($order_ghtk['deliver_date']) ? $order_ghtk['deliver_date'] : '';

                $action_time = isset($order_ghtk['action_time']) ? $order_ghtk['action_time'] : '';
                $reason_code = isset($order_ghtk['reason_code']) ? $order_ghtk['reason_code'] : '';
                $reason = isset($order_ghtk['reason']) ? $order_ghtk['reason'] : '';
                $status_id = isset($order_ghtk['status_id']) ? $order_ghtk['status_id'] : '';
                if($status_id) $status_text = ghtk_api()->get_status_text($status_id);
                if(!$status_text) $status_text = 'Đã đăng đơn. Chưa có thông tin';
                ?>
                <p class="form-field form-field-wide">
                    <strong>Tình trạng:</strong> <?php echo $status_text;?><br>
                    <?php if($reason_code):
                    $reason_text = ghtk_api()->get_reason_text($reason_code);
                    ?>
                    <strong>Lý do:</strong> <?php echo ($reason_text) ? $reason_text : $reason;?><br>
                    <?php endif;?>
                    <strong>ID trên GHTK:</strong> <?php echo $label; ?><br>
                    <strong>Ngày lấy hàng:</strong> <?php echo $estimated_pick_time; ?><br>
                    <strong>Ngày giao hàng:</strong> <?php echo $estimated_deliver_time; ?><br>
                    <?php if($action_time):?><strong>Cập nhật lúc:</strong> <?php echo date("h:i d/m/Y", strtotime($action_time));; ?><br><?php endif;?>
                </p>
                <?php
                if ($label) {
                    echo $this->order_action($label, $order);
                }
                $result['content'] = ob_get_clean();
            }else{
                $result['content'] = isset($order_ghtk_full['message']) ? $order_ghtk_full['message'] : '';
            }
            $result['status'] = true;

        }
        return $result;
    }

    function order_get_shipping_total($order, $view = false){
        $refunded = $order->get_total_shipping_refunded();
        $shipping_total = $order->get_shipping_total();
        $shipping_total_html = wc_price( $shipping_total, array( 'currency' => $order->get_currency() ) ); // WPCS: XSS ok.;
        if ( $refunded > 0 ) {
            $shipping_total = $order->get_shipping_total() - $refunded;
            $shipping_total_html = '<del>' . wp_strip_all_tags( wc_price( $shipping_total, array( 'currency' => $order->get_currency() ) ) ) . '</del> <ins>' . wc_price( $shipping_total - $refunded, array( 'currency' => $order->get_currency() ) ) . '</ins>'; // WPCS: XSS ok.
        }
        if($view) return $shipping_total_html;
        return $shipping_total;
    }

    function order_get_total($order){
        $order_total = $order->get_total();
        $order_shipping_total = $this->order_get_shipping_total($order);

        return $order_total - $order_shipping_total;
    }

    function devvn_woocommerce_admin_order_data_after_order_details($order){
        ?>
        <div class="devvn_ghtk_action_box" id="devvn_ghtk_action_box_<?php echo $order->get_id();?>"><?php $this->devvn_ghtk_action_box($order);?></div>
        <?php
    }
    function devvn_ghtk_action_box($order, $view = true){
        ob_start();
        $order_ghtk = $this->get_status($order);
        if($order_ghtk['status'] && $order_ghtk['content']) {
            echo '<div class="order_status_ghtk">';
            echo $order_ghtk['content'];
            echo '</div>';
        }else {
            echo '<p class="form-field form-field-wide"><a href="javascript:void(0)" class="button button-primary devvn_ghtk_popup_button" data-orderid="'.$order->get_id().'">' . __('Đăng đơn hàng lên GHTK', 'devvn-ghtk') . '</a></p>';
        }
        $html = ob_get_clean();
        if($view) echo $html;
        return $html;
    }

    function get_customer_address_shipping($order){
        if(!$order) return false;
        $customer_address = array();
        if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() ) :
            $customer_address['name'] = $order->get_formatted_shipping_full_name();
            $customer_address['address'] = $order->get_shipping_address_1();
            $customer_address['province'] = $this->get_name_city($order->get_shipping_state());
            $customer_address['district'] = $this->get_name_district($order->get_shipping_city());
            $customer_address['ward'] = $this->get_name_village($order->get_shipping_address_2());
        else:
            $customer_address['name'] = $order->get_formatted_billing_full_name();
            $customer_address['address'] = $order->get_billing_address_1();
            $customer_address['province'] = $this->get_name_city($order->get_billing_state());
            $customer_address['district'] = $this->get_name_district($order->get_billing_city());
            $customer_address['ward'] = $this->get_name_village($order->get_billing_address_2());
        endif;
        return $customer_address;
    }

    function get_product_args($orderThis){
        $products = array();
        $order_items = $orderThis->get_items();
        if($order_items && !empty($order_items)) {
            $key = 0;
            foreach ($order_items as $item) {
                $product = $item->get_product();
                if($product && !is_wp_error($product)) {
                    $subtitle = array();
                    $meta_data = $item->get_formatted_meta_data('');
                    if (is_array($item->get_meta_data())) {
                        $variations = array();
                        $hidden_order_itemmeta = apply_filters(
                            'woocommerce_hidden_order_itemmeta', array(
                                '_qty',
                                '_tax_class',
                                '_product_id',
                                '_variation_id',
                                '_line_subtotal',
                                '_line_subtotal_tax',
                                '_line_total',
                                '_line_tax',
                                'method_id',
                                'cost',
                                '_reduced_stock',
                                '_price',
                                '_wc_cog_item_cost',
                                '_wc_cog_item_total_cost',
                            )
                        );
                        foreach ($meta_data as $meta_id => $meta) :
                            if (in_array($meta->key, $hidden_order_itemmeta, true)) {
                                continue;
                            }
                            $variations[$meta->display_key] = wp_strip_all_tags($meta->display_value);
                        endforeach;

                        if ($variations && is_array($variations)) {
                            foreach ($variations as $k => $v) {
                                $subtitle[] = $k . ' - ' . $v;
                            }
                        }
                    }

                    if ($subtitle) {
                        $name_prod = sanitize_text_field($item['name']) . ' | ' . implode(" | ", $subtitle);
                    } else {
                        $name_prod = sanitize_text_field($item['name']);
                    }

                    /*
                     * Since 1.2.3 - 12.01.2019
                     * Author: https://doibu.com
                    */

                    $thisW = (float)$this->convert_weight_to_kg((float)$product->get_weight());
                    $thisPrice = (float)$orderThis->get_item_subtotal($item, false, true);
                    $thisQty = (float)$item->get_quantity();

                    $products[$key]['name'] = $name_prod;
                    $products[$key]['weight'] = round($thisW * $thisQty, 1);
                    $products[$key]['price'] = $thisPrice;
                    $products[$key]['quantity'] = $thisQty;

                    $key++;
                }
            }
        }
        return $products;
    }

    function func_dang_order_ghtk(){
        $order_id = isset($_POST['orderid']) ? intval($_POST['orderid']) : '';
        if (!$order_id || !wp_verify_nonce( $_REQUEST['nonce'], "order_ghtk_nonce")) {
            wp_send_json_error("No naughty business please");
        }
        $orderThis = wc_get_order( $order_id );
        if(!$orderThis || is_wp_error($orderThis)) return false;

        $thuho = isset($_POST['thuho']) ? intval($_POST['thuho']) : '';
        $isfreeship = isset($_POST['isfreeship']) ? intval($_POST['isfreeship']) : '0';
        $note = isset($_POST['note']) ? sanitize_textarea_field($_POST['note']) : '';
        $shop_store_id = isset($_POST['shop_store_id']) ? sanitize_textarea_field($_POST['shop_store_id']) : 0;
        $pick_option = isset($_POST['pick_option']) ? sanitize_textarea_field($_POST['pick_option']) : 'cod';
        $transport = isset($_POST['transport']) ? sanitize_textarea_field($_POST['transport']) : 'fly';
        $devvn_ghtk_giatridon = isset($_POST['devvn_ghtk_giatridon']) ? intval($_POST['devvn_ghtk_giatridon']) : $this->order_get_total($orderThis);

        $shipping_phone = get_post_meta( $order_id, '_shipping_phone', true );
        if ( ! wc_ship_to_billing_address_only() && $orderThis->needs_shipping_address() && $shipping_phone) {
            $shipping_phone = $shipping_phone;
        }elseif ( $orderThis->get_billing_phone() ) {
            $shipping_phone = $orderThis->get_billing_phone();
        }

        extract($this->get_customer_address_shipping($orderThis));

        $products = $this->get_product_args($orderThis);

        //wp_send_json_error($products);

        $pick_address_id = sanitize_text_field($this->get_store_address('pick_address_id', $shop_store_id));

        $order = array(
            'products'  => $products,
            'order' => array(
                'id'    =>  $orderThis->get_id(),

                'pick_money' =>  $thuho, //$orderThis->get_subtotal(), //Integer - Số tiền cần thu hộ. Nếu bằng 0 thì không thu hộ tiền. Tính theo VNĐ

                'pick_address_id' =>  $pick_address_id,

                'pick_name' =>  sanitize_text_field($this->get_store_address('pick_name', $shop_store_id)), //String - Tên người liên hệ lấy hàng hóa
                'pick_address' =>  sanitize_text_field($this->get_store_address('pick_address', $shop_store_id)), //String - Địa chỉ ngắn gọn để lấy nhận hàng hóa. Ví dụ: nhà số 5, tổ 3, ngách 11, ngõ 45
                'pick_province' =>  $this->get_name_city($this->get_store_address('pick_province', $shop_store_id)), //String - Tên tỉnh/thành phố nơi lấy hàng hóa
                'pick_district' =>  $this->get_name_district($this->get_store_address('pick_district', $shop_store_id)), //String - Tên quận/huyện nơi lấy hàng hóa
                'pick_tel' =>  sanitize_text_field($this->get_store_address('pick_tel', $shop_store_id)), //String - Số điện thoại liên hệ nơi lấy hàng hóa

                'pick_email' =>  sanitize_text_field($this->get_store_address('pick_email', $shop_store_id)), // no - String - Email liên hệ nơi lấy hàng hóa
                'pick_ward' =>  $this->get_name_village($this->get_store_address('pick_ward', $shop_store_id)), //no - String - Tên phường/xã nơi lấy hàng hóa
                'pick_street' =>  sanitize_text_field($this->get_store_address('pick_street', $shop_store_id)), //no - String - Tên đường/phố nơi lấy hàng hóa

                'name' =>  $name, // String - tên người nhận hàng
                'address' =>  $address, // String - Địa chỉ chi tiết của người nhận hàng, ví dụ: Chung cư CT1, ngõ 58, đường Trần Bình
                'province' =>  $province, // String - Tên tỉnh/thành phố của người nhận hàng hóa
                'district' =>  $district, // String - Tên quận/huyện của người nhận hàng hóa
                'ward' =>  $ward, // no	- String - Tên phường/xã của người nhận hàng hóa
                'street' =>  '', // no - String - Tên đường/phố của người nhận hàng hóa
                'tel' =>  $shipping_phone, // String - Số điện thoại người nhận hàng hóa
                'email' =>  $orderThis->get_billing_email(), // yes	String - Email người nhận hàng hóa

                'is_freeship' =>  $isfreeship, // Integer - Freeship cho người nhận hàng.
                'weight_option' =>  'kilogram', //$this->_weight_option, //no	- String - nhận một trong hai giá trị gram và kilogram

                'value' =>  $devvn_ghtk_giatridon, //Interger (VNĐ) - Giá trị thực đơn hàng, áp dụng tính phí bảo hiểm

                'note'  =>  $note,

                //'pick_date' => '2018/06/30', //String YYYY/MM/DD - Hẹn ngày lấy hàng - mặc định không sử dụng được field này, cấu hình riêng cho từng gói dịch vụ

                'pick_option' =>  $pick_option,
                'transport' =>  $transport,
            )
        );
        /*$outs = array(
            'html' => $this->devvn_ghtk_action_box($orderThis, false)
        );
        wp_send_json_success($outs);*/

        if($order_ghtk = ghtk_api()->ghtk_creat_order(wp_json_encode($order))){
            if(isset($order_ghtk['success']) && $order_ghtk['success']){

                $order_ghtk = wp_parse_args($order_ghtk, $this->_default_status);

                update_post_meta( $orderThis->get_id(), '_order_ghtk_full', $order_ghtk );
                update_post_meta( $orderThis->get_id(), '_order_ghtk_fullinfor', $order );

                //Send order number to customer
                if($orderThis->get_billing_email() && $this->get_options('send_shipid_active')) {
                    $send_shipid_title = $this->get_options('send_shipid_title');
                    $send_shipid_content = apply_filters('the_content', $this->get_options('send_shipid_content'));
                    $to = $orderThis->get_billing_email();
                    $ma_van_don = isset($order_ghtk['order']['label_id']) ? $order_ghtk['order']['label_id'] : '';
                    if(!$ma_van_don) $ma_van_don = isset($order_ghtk['order']['label']) ? $order_ghtk['order']['label'] : '';
                    if($ma_van_don) {
                        $subject = $this->mail_string_filter($send_shipid_title, $orderThis, $order_ghtk);
                        $body = $this->mail_string_filter($send_shipid_content, $orderThis, $order_ghtk);
                        $headers = array('Content-Type: text/html; charset=UTF-8');
                        $sendmail = wp_mail($to, $subject, $body, $headers);
                    }
                }
                $outs = array(
                    'html' => $this->devvn_ghtk_action_box($orderThis, false),
                    'text_status'   => $this->ghtk_get_text_status($orderThis)
                );
                wp_send_json_success($outs);
            }else{
                wp_send_json_error(isset($order_ghtk['message']) ? $order_ghtk['message'] : '');
            }

            wp_send_json_error($order_ghtk);
        }
        wp_send_json_error($order_ghtk);
        die();
    }

    function mail_string_filter($string, $orderThis, $order_ghtk){
        $ma_van_don = isset($order_ghtk['order']['label_id']) ? $order_ghtk['order']['label_id'] : '';
        if(!$ma_van_don) $ma_van_don = isset($order_ghtk['order']['label']) ? $order_ghtk['order']['label'] : '';
        $estimated_deliver = isset($order_ghtk['order']['estimated_deliver_time']) ? $order_ghtk['order']['estimated_deliver_time'] : '';
        $string = str_replace( '{site_title}', get_bloginfo('name'), $string );
        $string = str_replace( '{ship_id}', $ma_van_don, $string );
        $string = str_replace( '{order_id}', $orderThis->get_id(), $string );
        $string = str_replace( '{estimated_deliver}', $estimated_deliver, $string );
        return $string;
    }

    function get_store_address($field = 'address_id', $stt = 0, $args = null){
        $shop_store = $this->get_options('shop_store');
        if($shop_store && is_array($shop_store) && !empty($shop_store)){
            return isset($shop_store[$stt][$field]) ? $shop_store[$stt][$field] : '';
        }
        return false;
    }

    function get_main_hubs(){
        $shop_store = $this->get_options('shop_store');
        if($shop_store && is_array($shop_store) && !empty($shop_store)){
            foreach($shop_store as $k => $v){
                if(isset($v['pick_ismain']) && $v['pick_ismain']){
                    return $k;
                }
            }
        }
        return 0;
    }

    function get_near_hubs($state){
        $shop_store = $this->get_options('shop_store');
        $main_hubs = $this->get_main_hubs();
        if($shop_store && is_array($shop_store) && !empty($shop_store)){
            if(isset($shop_store[$main_hubs]['pick_cities']) && $shop_store[$main_hubs]['pick_cities'] && in_array($state, $shop_store[$main_hubs]['pick_cities'])){
                return $main_hubs;
            }
            foreach($shop_store as $k => $v){
                if(isset($v['pick_cities']) && $v['pick_cities'] && in_array($state, $v['pick_cities'])){
                    return $k;
                }
            }
            if($main_hubs) return $main_hubs;
        }
        return 0;
    }

    function print_order_ghtk_func(){
        if ( !wp_verify_nonce( $_REQUEST['nonce'], "print_order_action")) {
            exit("No naughty business please");
        }
        $order_id = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : '';
        if($order_id){
            ghtk_api()->inhoadon_ghtk($order_id);
        }
        die('Hãy nhập đầy đủ thông tin');
    }
    function check_status_ghtk_func(){
        $label = isset($_POST['label']) ? sanitize_text_field($_POST['label']) : '';
        $orderid = isset($_POST['orderid']) ? intval($_POST['orderid']) : '';
        if (!$orderid || !$label || !wp_verify_nonce( $_REQUEST['nonce'], "check_status_ghtk")) {
            exit("No naughty business please");
        }
        $result = ghtk_api()->get_status($label);
        $order = wc_get_order($orderid);
        if(isset($result['success']) && $result['success']){
            $status = isset($result['order']['status']) ? $result['order']['status'] : '';

            $old_status = get_post_meta($order->get_id() ,'_order_ghtk_full', true);
            $result = wp_parse_args($result, $old_status);
            $result = wp_parse_args($result, $this->_default_status);

            update_post_meta( $order->get_id(), '_order_ghtk_full', $result );
            switch ($status){
                case '-1':
                    $order->update_status('cancelled');
                    break;
                case '13':
                case '20':
                case '21':
                    $order->update_status('failed');
                    break;
                case '45':
                case '6':
                case '5':
                    $order->update_status('completed');
                    break;
                default:
                    $order->update_status('processing');
                    break;
            }
            wp_send_json_success($this->get_status($order));
        }else{
            wp_send_json_error(isset($result['message']) ? $result['message'] : '');
        }
        die('Hãy nhập đầy đủ thông tin');
    }

    function devvn_woocommerce_form_field_select($field, $key, $args, $value){
        if(in_array($key, array('billing_city','shipping_city','billing_address_2','shipping_address_2'))) {
            if(in_array($key, array('billing_city','shipping_city'))) {
                if(!is_checkout() && is_user_logged_in()){
                    if('billing_city' === $key) {
                        $state = wc_get_post_data_by_key('billing_state', get_user_meta(get_current_user_id(), 'billing_state', true));
                    }else{
                        $state = wc_get_post_data_by_key('shipping_state', get_user_meta(get_current_user_id(), 'shipping_state', true));
                    }
                }else {
                    $state = WC()->checkout->get_value('billing_city' === $key ? 'billing_state' : 'shipping_state');
                }
                $city = array('' => ($args['placeholder']) ? $args['placeholder'] : __('Choose an option', 'woocommerce')) + $this->get_list_district_select($state);
                $args['options'] = $city;
            }elseif(in_array($key, array('billing_address_2','shipping_address_2'))) {
                if(!is_checkout() && is_user_logged_in()){
                    if('billing_address_2' === $key) {
                        $city = wc_get_post_data_by_key('billing_city', get_user_meta(get_current_user_id(), 'billing_city', true));
                    }else{
                        $city = wc_get_post_data_by_key('shipping_city', get_user_meta(get_current_user_id(), 'shipping_city', true));
                    }
                }else {
                    $city = WC()->checkout->get_value('billing_address_2' === $key ? 'billing_city' : 'shipping_city');
                }
                $village = array('' => ($args['placeholder']) ? $args['placeholder'] : __('Choose an option', 'woocommerce')) + $this->get_list_village_select($city);
                $args['options'] = $village;
            }

            if ($args['required']) {
                $args['class'][] = 'validate-required';
                $required = ' <abbr class="required" title="' . esc_attr__('required', 'woocommerce') . '">*</abbr>';
            } else {
                $required = '';
            }

            if (is_string($args['label_class'])) {
                $args['label_class'] = array($args['label_class']);
            }

            // Custom attribute handling.
            $custom_attributes = array();
            $args['custom_attributes'] = array_filter((array)$args['custom_attributes'], 'strlen');

            if ($args['maxlength']) {
                $args['custom_attributes']['maxlength'] = absint($args['maxlength']);
            }

            if (!empty($args['autocomplete'])) {
                $args['custom_attributes']['autocomplete'] = $args['autocomplete'];
            }

            if (true === $args['autofocus']) {
                $args['custom_attributes']['autofocus'] = 'autofocus';
            }

            if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
                foreach ($args['custom_attributes'] as $attribute => $attribute_value) {
                    $custom_attributes[] = esc_attr($attribute) . '="' . esc_attr($attribute_value) . '"';
                }
            }

            if (!empty($args['validate'])) {
                foreach ($args['validate'] as $validate) {
                    $args['class'][] = 'validate-' . $validate;
                }
            }

            $label_id = $args['id'];
            $sort = $args['priority'] ? $args['priority'] : '';
            $field_container = '<p class="form-row %1$s" id="%2$s" data-priority="' . esc_attr($sort) . '">%3$s</p>';

            $options = $field = '';

            if (!empty($args['options'])) {
                foreach ($args['options'] as $option_key => $option_text) {
                    if ('' === $option_key) {
                        // If we have a blank option, select2 needs a placeholder.
                        if (empty($args['placeholder'])) {
                            $args['placeholder'] = $option_text ? $option_text : __('Choose an option', 'woocommerce');
                        }
                        $custom_attributes[] = 'data-allow_clear="true"';
                    }
                    $options .= '<option value="' . esc_attr($option_key) . '" ' . selected($value, $option_key, false) . '>' . esc_attr($option_text) . '</option>';
                }

                $field .= '<select name="' . esc_attr($key) . '" id="' . esc_attr($args['id']) . '" class="select ' . esc_attr(implode(' ', $args['input_class'])) . '" ' . implode(' ', $custom_attributes) . ' data-placeholder="' . esc_attr($args['placeholder']) . '">
                    ' . $options . '
                </select>';
            }

            if (!empty($field)) {
                $field_html = '';

                if ($args['label'] && 'checkbox' != $args['type']) {
                    $field_html .= '<label for="' . esc_attr($label_id) . '" class="' . esc_attr(implode(' ', $args['label_class'])) . '">' . $args['label'] . $required . '</label>';
                }

                $field_html .= $field;

                if ($args['description']) {
                    $field_html .= '<span class="description">' . esc_html($args['description']) . '</span>';
                }

                $container_class = esc_attr(implode(' ', $args['class']));
                $container_id = esc_attr($args['id']) . '_field';
                $field = sprintf($field_container, $container_class, $container_id, $field_html);
            }
            return $field;
        }
        return $field;
    }

    function ghtk_add_url_func(){
        $hash = isset($_POST['hash']) ? $_POST['hash'] : '';
        if(!$hash) wp_send_json_error();
        $url = admin_url('admin-ajax.php?action=update_shipping_status&hash='.$hash);
        if($result = ghtk_api()->add_url($url)){
            if(isset($result['success']) && $result['success']) {
                wp_send_json_success('Thêm URL thành công');
            }else{
                wp_send_json_error($result['message']);
            }
        }else{
            wp_send_json_error();
        }
    }
    function ghtk_delete_url_func(){
        $url = isset($_POST['url']) ? esc_url($_POST['url']) : '';
        if(!$url) wp_send_json_error();
        if($result = ghtk_api()->delete_url($url)){
            if(isset($result['success']) && $result['success']) {
                wp_send_json_success('Xóa URL thành công');
            }else{
                wp_send_json_error($result['message']);
            }
        }else{
            wp_send_json_error();
        }
    }
    function update_shipping_status_func(){

        $POST = json_decode(file_get_contents('php://input'), true);

        if(isset($_POST) && empty($_POST)){
            $_POST = $POST;
        }

        $ghtk_hash = $this->get_options('ghtk_hash');
        $hash = isset($_GET['hash']) ? sanitize_text_field($_GET['hash']) : '';
        if(!$hash || !$ghtk_hash) die();

        $partner_id = isset($_POST['partner_id']) ? sanitize_text_field($_POST['partner_id']) : '';
        $label_id = isset($_POST['label_id']) ? sanitize_text_field($_POST['label_id']) : '';
        $status_id = isset($_POST['status_id']) ? sanitize_text_field($_POST['status_id']) : '';
        $action_time = isset($_POST['action_time']) ? sanitize_text_field($_POST['action_time']) : '';
        $reason_code = isset($_POST['reason_code']) ? sanitize_text_field($_POST['reason_code']) : '';
        $reason = isset($_POST['reason']) ? sanitize_text_field($_POST['reason']) : '';
        $weight = isset($_POST['weight']) ? sanitize_text_field($_POST['weight']) : '';
        $fee = isset($_POST['fee']) ? sanitize_text_field($_POST['fee']) : '';

        if($ghtk_hash == $hash && $partner_id){
            $order = wc_get_order($partner_id);
            if($order && !is_wp_error($order)){
                $old_status = get_post_meta($order->get_id() ,'_order_ghtk_full', true);

                $result = array();
                $result = wp_parse_args($result, $old_status);

                $result['success'] = true;
                $result['message'] = ghtk_api()->get_reason_text($reason);
                $result['order']['label_id'] = $label_id;
                $result['order']['partner_id'] = $partner_id;
                $result['order']['status'] = $status_id;
                $result['order']['status_id'] = $status_id;
                $result['order']['action_time'] = $action_time;
                $result['order']['reason_code'] = $reason_code;
                $result['order']['reason'] = $reason;
                $result['order']['weight'] = $weight;
                $result['order']['fee'] = $fee;

                update_post_meta( $order->get_id(), '_order_ghtk_full', $result );

                switch ($status_id){
                    case '-1':
                        $order->update_status('cancelled');
                        break;
                    case '13':
                    case '20':
                    case '21':
                        $order->update_status('failed');
                        break;
                    case '45':
                    case '6':
                    case '5':
                        $order->update_status('completed');
                        break;
                    default:
                        $order->update_status('processing');
                        break;
                }
            }
        }
        die();
    }
    function devvn_woocommerce_get_order_address($value, $type){
        if($type == 'billing' || $type == 'shipping'){
            if(isset($value['state']) && $value['state']){
                $state = $value['state'];
                $value['state'] = $this->get_name_city($state);
            }
            if(isset($value['city']) && $value['city']){
                $city = $value['city'];
                $value['city'] = $this->get_name_district($city);
            }
            if(isset($value['address_2']) && $value['address_2']){
                $address_2 = $value['address_2'];
                $value['address_2'] = $this->get_name_village($address_2);
            }
        }
        return $value;
    }
    function devvn_woocommerce_rest_prepare_shop_order_object($response, $order, $request){
        if( empty( $response->data ) ) {
            return $response;
        }

        $fields = array(
            'billing',
            'shipping'
        );

        foreach($fields as $field){
            if(isset($response->data[$field]['state']) && $response->data[$field]['state']){
                $state = $response->data[$field]['state'];
                $response->data[$field]['state'] = $this->get_name_city($state);
            }

            if(isset($response->data[$field]['city']) && $response->data[$field]['city']){
                $city = $response->data[$field]['city'];
                $response->data[$field]['city'] = $this->get_name_district($city);
            }

            if(isset($response->data[$field]['address_2']) && $response->data[$field]['address_2']){
                $address_2 = $response->data[$field]['address_2'];
                $response->data[$field]['address_2'] = $this->get_name_village($address_2);
            }
        }

        return $response;
    }
    function devvn_print_order(){
        $order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';
        if(!$order_id) die();
        $order_args = array();
        $order_id_args = explode(',', $order_id);
        foreach ($order_id_args as $order_id){
            $order = wc_get_order((int)$order_id);
            if($order && !is_wp_error($order)){
                update_post_meta($order_id,'devvn_printed', 1);
                $order_args[] = $order;
            }
        }
        if($order_args && !is_wp_error($order_args)) {
            ghtk_get_template('invoice-print.php', array('order_args' => $order_args, 'this_class' => $this));
        }
        die();
    }
    function xc_woo_cloud_print_after_order_details($type, $order){
        $order_id = ( version_compare( WC_VERSION, '2.7', '<' ) ) ? $order->id : $order->get_id();
        $ghtk_status = get_post_meta($order_id ,'_order_ghtk_full', true);
        $ghtk_id = isset($ghtk_status['order']['label_id']) ? $ghtk_status['order']['label_id'] : '';

        if(!$ghtk_id) $ghtk_id = isset($ghtk_status['order']['label']) ? $ghtk_status['order']['label'] : '';

        if($ghtk_id){
            echo 'Mã vận đơn GHTK:<br>';
            echo $ghtk_id;
        }
    }
    function devvn_admin_print_footer_scripts(){
        $get_address = DEVVN_GHTK_URL . 'get-address.php';
        if($this->check_file_open_status($get_address) != 200){
            $get_address = admin_url( 'admin-ajax.php');
        }
        ?>
        <link rel='stylesheet' href='<?php echo plugins_url( '/assets/css/pos_devvn.css', __FILE__ )?>' type='text/css' media='all' />

        <script type='text/javascript'>
            var ghtk_array = <?php echo json_encode(array(
                'admin_ajax'		=>	admin_url( 'admin-ajax.php'),
                'get_address'		=>	$get_address,
                'home_url'			=>	home_url(),
                'formatNoMatches'   =>  __('No value', 'devvn-ghtk')
            ));?>
        </script>
        <script type='text/javascript' src='<?php echo plugins_url( '/assets/js/pos_devvn_tinhthanh.js', __FILE__ )?>'></script>
        <?php
    }

    function check_your_site(){
        $your_origin = isset($_SERVER['HTTP_ORIGIN']) ? parse_url(esc_url($_SERVER['HTTP_ORIGIN']), PHP_URL_HOST) : '';
        if(!$your_origin) {
            $your_origin = isset($_SERVER['HTTP_REFERER']) ? parse_url(esc_url($_SERVER['HTTP_REFERER']), PHP_URL_HOST) : '';
        }
        $home_url = parse_url(home_url(), PHP_URL_HOST);
        if ( $your_origin != $home_url ) {
            return false;
        }
        return true;
    }

    function ghtk_tracking_func(){

        $result = array(
            'fragments' => apply_filters('ghtk_fragments_default', array(
                    '.ghtk_tracking_result' => __('Không tìm thấy dữ liệu. Vui lòng thử lại sau!','devvn-ghtk')
                )
            )
        );

        if($this->check_your_site()) {

            parse_str($_POST['data'], $params);

            $shipping_id = isset($params['orderid']) ? wc_clean($params['orderid']) : '';

            if (!$shipping_id) wp_send_json_error($result);

            $response = ghtk_api()->tracking_order($shipping_id);

            if ($response) {
                if ($response['code'] == "SUCCESS") {
                    ob_start();
                    ghtk_get_template('tracking-result.php', array(
                        'package' =>  isset($response['package']) ? $response['package'] : array(),
                        'logs' =>  isset($response['logs']) ? $response['logs'] : array(),
                    ));
                    $html = ob_get_clean();
                    $result['fragments'] = array(
                        '.ghtk_tracking_result' => $html
                    );
                    wp_send_json_success($result);
                }
            }
        }

        wp_send_json_error($result);
        die();
    }

    function ghtk_tracking_form_func(){
        ob_start();

        $mashop = $this->get_options('mashop');
        $token = $this->get_options('token_key');
        $shipID = isset($_GET['orderid']) ? wc_clean(esc_attr($_GET['orderid'])) : '';
        $result = '';
        if($shipID) {
            $h = hash_hmac('md5', $shipID, strrev($token));

            $url = 'https://khachhang.giaohangtietkiem.vn/khach-hang/tra-cuu?s=' . urlencode($mashop) . '&o=' . urlencode($shipID) . '&h=' . urlencode($h) . '&json=1';

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_SSL_VERIFYPEER => is_ssl(),
                /*CURLOPT_HTTPHEADER => array(
                    "content-type: content-type: application/json",
                    "Token: $this->token",
                ),*/
            ));

            $response = curl_exec($curl);
            curl_close($curl);

            $result = json_decode($response, true);
        }
        ?>
        <div class="devvn_ghtk_tracking_form">
            <?php do_action('before_ghtk_tracking_form');?>
            <div class="ghtk_tracking_form">
                <form method="GET" action="" id="devvn_ghtk_tracking">
                    <input id="orderid" name="orderid" value="<?php echo $shipID;?>" required placeholder="<?php echo apply_filters('ghtk_tracking_input_text', __('Nhập mã vận đơn của bạn','devvn-ghtk'));?>">
                    <button type="submit"><?php echo apply_filters('ghtk_tracking_submit_text', __('Kiểm tra','devvn-ghtk'));?></button>
                </form>
            </div>
            <?php do_action('after_ghtk_tracking_form');?>
            <div class="ghtk_tracking_result">
                <?php
                if ($result) {
                    if ($result['code'] == "SUCCESS") {
                        ghtk_get_template('tracking-result.php', array(
                            'package' =>  isset($result['package']) ? $result['package'] : array(),
                            'logs' =>  isset($result['logs']) ? $result['logs'] : array(),
                        ));
                    }
                }
                ?>
            </div>
            <?php do_action('after_ghtk_tracking_result');?>
        </div>
        <?php
        return ob_get_clean();
    }

    function devvn_woocommerce_formatted_address_replacements($replace){

        if(isset($replace['{city}']) && is_numeric($replace['{city}'])) {
            $oldCity = isset($replace['{city}']) ? $replace['{city}'] : '';
            $replace['{city}'] = $this->get_name_district($oldCity);
        }

        if(isset($replace['{city_upper}'])&& is_numeric($replace['{city_upper}'])) {
            $oldCityUpper = isset($replace['{city_upper}']) ? $replace['{city_upper}'] : '';
            $replace['{city_upper}'] = strtoupper($this->get_name_district($oldCityUpper));
        }

        if(isset($replace['{address_2}']) && is_numeric($replace['{address_2}'])) {
            $oldCity = isset($replace['{address_2}']) ? $replace['{address_2}'] : '';
            $replace['{address_2}'] = $this->get_name_village($oldCity);
        }

        if(isset($replace['{address_2_upper}']) && is_numeric($replace['{address_2_upper}'])) {
            $oldCityUpper = isset($replace['{address_2_upper}']) ? $replace['{address_2_upper}'] : '';
            $replace['{address_2_upper}'] = strtoupper($this->get_name_village($oldCityUpper));
        }

        if(is_cart()) {
            $replace['{address_1}'] = '';
            $replace['{address_1_upper}'] = '';
            $replace['{address_2}'] = '';
            $replace['{address_2_upper}'] = '';
        }

        return $replace;
    }

    function webhook_admin_notice__error(){

        $current_screen = get_current_screen();

        if($current_screen->id === 'settings_page_devvn-woo-ghtk') {

            $listURL = ghtk_api()->get_list_url();
            if($listURL && is_array($listURL) && $listURL['success']) {

                $ghtk_hash = $this->get_options('ghtk_hash');

                if (!$ghtk_hash || empty($listURL['data'])) {
                    $class = 'notice notice-error is-dismissibl';
                    $message = __('<strong>Bạn chưa đăng ký Webhook</strong> - Webhook để tự động cập nhật trạng thái đơn hàng từ GHTK về web. Thêm <a href="#webhook" title="">tại đây</a>', 'devvn-ghtk');
                    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
                }
            }
        }
    }

    function auto_order_to_ghtk( $order_id ) {
        error_log( "Order complete for order $order_id", 0 );
    }

    function devvn_ghtk_action_func($order){
        $this->devvn_woocommerce_admin_order_data_after_order_details($order);
    }

    function ghtk_creat_order_ajax_func(){
        $orderid = isset($_POST['orderid']) ? intval($_POST['orderid']) : '';
        if(!$orderid) wp_send_json_error();

        $order = wc_get_order($orderid);

        if(!$order || is_wp_error($order)) wp_send_json_error();
        $output = array();

        ob_start();
        $this->ghtk_creat_order_form($order);
        $output['html'] = ob_get_clean();

        wp_send_json_success($output);

        die();

    }

    function ghtk_creat_order_form($order){
        $shipping_methods = $order->get_shipping_methods();
        extract($this->get_customer_address_shipping($order));
        ?>
        <div class="devvn_ghtk_popup" id="devvn_ghtk_popup">
            <div class="devvn_ghtk_popup_box">
                <div class="devvn_ghtk_popup_title"><?php _e('Đăng đơn lên GHTK') ?></div>
                <div class="devvn_ghtk_popup_content">
                    <div class="devvn_ghtk_popup_content_left">
                        <h3><?php _e('Cửa hàng/kho lấy hàng', 'devvn-ghtk'); ?></h3>

                        <p><?php
                            $shop_store = $this->get_options('shop_store');
                            if($shop_store && is_array($shop_store) && !empty($shop_store)){
                                $HubID_Order = "";
                                foreach ( $shipping_methods as $shipping_method ) {
                                    foreach($shipping_method->get_formatted_meta_data() as $meta_data){
                                        if($meta_data->key && $meta_data->key == 'hubsid' && $HubID_Order == ""){
                                            $HubID_Order = $meta_data->value;
                                        }
                                    }
                                }
                                ?>
                                <select name="shop_store_id" id="shop_store_id">
                                    <?php foreach($shop_store as $k=>$v):
                                        $viewName = '';
                                        $pick_address_id = isset($v['pick_address_id']) ? $v['pick_address_id'] : '';
                                        $pick_address = isset($v['pick_address']) ? $v['pick_address'] : '';
                                        $pick_province = isset($v['pick_province']) ? $this->get_name_city($v['pick_province']) : '';
                                        $pick_district = isset($v['pick_district']) ? $this->get_name_district($v['pick_district']) : '';
                                        if($pick_address_id) $viewName .= '#'.$pick_address_id.' - ';
                                        if($pick_address) $viewName .= $pick_address;
                                        if($pick_province) $viewName .= ', ' . $pick_province;
                                        if($pick_district) $viewName .= ', ' . $pick_district;
                                        ?>
                                        <option value="<?php echo $k;?>" <?php selected($k, $HubID_Order, true);?>><?php echo $viewName;?></option>
                                    <?php endforeach;?>
                                </select>
                                <?php
                            }
                            ?></p>
                        <h3><?php _e('Thông tin đơn hàng', 'devvn-ghtk'); ?></h3>
                        <table class="devvn_table_style" style=" margin-bottom: 15px; ">
                            <tr>
                                <td style=" width: 115px; ">Giá trị đơn hàng: <br><small>(Tính cả phụ phí)</small></td>
                                <td><strong><?php echo wc_price($this->order_get_total($order)); ?></strong></td>
                            </tr>
                            <tr>
                                <td style=" width: 115px; ">Phí ship:</td>
                                <td>
                                    <strong><?php echo $this->order_get_shipping_total($order, true);?></strong>
                                    <br><small>(Phí ship này là có thể không phải của GHTK. Khi đăng đơn lên GHTK phí ship sẽ được tính lại)</small>
                                </td>
                            </tr>
                        </table>

                        <h3><?php _e('Thông tin người nhận hàng', 'devvn-ghtk'); ?></h3>
                        <?php
                        $shipping_phone = get_post_meta($order->get_id(), '_shipping_phone', true);
                        if (!wc_ship_to_billing_address_only() && $order->needs_shipping_address() && $shipping_phone) {
                            $shipping_phone = esc_html($shipping_phone);
                        } elseif ($order->get_billing_phone()) {
                            $shipping_phone = esc_html($order->get_billing_phone());
                        }
                        ?>
                        <table class="devvn_table_style">
                            <tbody>
                                <tr>
                                    <td><strong>SĐT:</strong></td>
                                    <td><?php echo $shipping_phone?></td>
                                </tr>
                                <tr>
                                    <td><strong>Họ tên:</strong></td>
                                    <td><?php echo $name;?></td>
                                </tr>
                                <tr>
                                    <td><strong>Địa chỉ:</strong></td>
                                    <td><?php echo $address;?></td>
                                </tr>
                                <tr>
                                    <td><strong>Xã/phường:</strong></td>
                                    <td><?php echo $ward;?></td>
                                </tr>
                                <tr>
                                    <td><strong>Quận/huyện:</strong></td>
                                    <td><?php echo $district;?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tỉnh/thành phố:</strong></td>
                                    <td><?php echo $province;?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="devvn_ghtk_popup_content_right">
                        <h3><?php _e('Thông tin đăng đơn lên GHTK', 'devvn-ghtk'); ?></h3>
                        <p style="color: red">(Chỉnh sửa thông số sẽ ảnh hưởng tới phí ship hiện tại)</p>
                        <table class="devvn_table_style">
                            <tbody>
                                <tr>
                                    <td>Phí ship</td>
                                    <td>
                                        <label style="margin-right: 15px;"><input name="devvn_ghtk_isfreeship" type="radio" value="0" <?php echo (!$this->get_options('is_freeship') && $order->get_shipping_total()) ? 'checked="checked"' : '';?>> Khách trả</label>
                                        <label><input name="devvn_ghtk_isfreeship" type="radio" value="1" <?php echo ($this->get_options('is_freeship') || $order->get_shipping_total() == 0) ? 'checked="checked"' : '';?>> Shop trả</label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Tiền thu hộ</td>
                                    <td>
                                        <input id="devvn_ghtk_thuho" type="number" min="0" value="<?php echo ($this->get_options('is_freeship') || $order->get_shipping_total() == 0) ? $order->get_total() : $this->order_get_total($order); ?>" data-subtotal="<?php echo $this->order_get_total($order);?>" data-total="<?php echo $order->get_total();?>"><br>
                                        <label><input type="checkbox" name="khach_da_bank" id="khach_da_bank"> <small style="font-size: 95%">Khách đã chuyển khoản thì thu hộ = 0</small></label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Gửi hàng tại bưu cục?</td>
                                    <td>
                                        <label><input name="devvn_ghtk_pick_option" type="radio" value="cod" checked> Shipper đến lấy hàng</label><br>
                                        <label><input name="devvn_ghtk_pick_option" type="radio" value="post"> Shop gửi hàng tại bưu cục</label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Hình thức vận chuyển</td>
                                    <td>
                                        <?php
                                        $ship_transport = "";
                                        foreach ( $shipping_methods as $shipping_method ) {
                                            foreach($shipping_method->get_formatted_meta_data() as $meta_data){
                                                if($meta_data->key && $meta_data->key == 'transport' && $ship_transport == ""){
                                                    $ship_transport = $meta_data->value;
                                                }
                                            }
                                        }
                                        ?>
                                        <label><input name="devvn_ghtk_transport" type="radio" value="road" <?php echo ($ship_transport && $ship_transport == 'road' && $ship_transport != 'fly') ? 'checked' : 'checked';?>> Đường bộ</label>
                                        <label><input name="devvn_ghtk_transport" type="radio" value="fly" <?php echo ($ship_transport && $ship_transport == 'fly') ? 'checked' : '';?>> Đường bay</label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Giá trị hàng hóa</td>
                                    <td>
                                        <input name="devvn_ghtk_giatridon" id="devvn_ghtk_giatridon" type="text" value="<?php echo $this->order_get_total($order);?>"><br>
                                        <small>Áp dụng để tính bảo hiểm đơn hàng. Có thể thay đổi để tránh phí bảo hiểm.</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Ghi chú cho GHTK khi giao hàng</td>
                                    <td><textarea id="devvn_ghtk_note"><?php echo wp_kses_post( $order->get_customer_note() ); ?></textarea></td>
                                </tr>
                            </tbody>
                        </table>
                        <?php wp_nonce_field('order_ghtk_nonce', 'ghtk_nonce'); ?>
                    </div>
                </div>
                <div class="devvn_ghtk_popup_footer">
                    <button class="button button-cancel" type="button" id="devvn_ghtk_popup_close">Hủy đăng đơn</button>
                    <button class="button button-primary dang_order_ghtk">Đăng ngay</button>
                    <input type="hidden" name="post_ID" class="post_ID" value="<?php echo $order->get_id();?>">
                </div>
            </div>
        </div>
        <?php
    }

    function ghtk_get_text_status($the_order){
        ob_start();
        echo '<div class="ghtk_get_text_status" id="ghtk_get_text_status_'.$the_order->get_id().'">';
        $order_ghtk_full = get_post_meta($the_order->get_id() ,'_order_ghtk_full', true);
        if(!$order_ghtk_full) $order_ghtk_full = get_post_meta($the_order->get_id() ,'_order_ghtk', true);
        if(isset($order_ghtk_full['order'])){
            $order_ghtk = $order_ghtk_full['order'];
            $label = isset($order_ghtk['label_id']) ? $order_ghtk['label_id'] : '';
            if(!$label) $label = isset($order_ghtk['label']) ? $order_ghtk['label'] : '';

            $status_text = isset($order_ghtk['status_text']) ? $order_ghtk['status_text'] : '';
            $status_id = isset($order_ghtk['status_id']) ? $order_ghtk['status_id'] : '';
            if($status_id) $status_text = ghtk_api()->get_status_text($status_id);
            if(!$status_text) $status_text = 'Đã đăng đơn. Chưa có thông tin';

            if(!$label){
                echo '<span style="color: red;">Chưa đăng đơn lên GHTK</span>';
            }else{
                echo '<span style="color: blue;">'.$label.'</span><br>';
                echo '<span style="color: blue;">'.$status_text.'</span>';
            }
        }else{
            $status = $the_order->get_status();
            if( $status != "completed") {
                echo '<span style="color: red;">Chưa đăng đơn lên GHTK</span>';
            }
        }
        echo '</div>';
        return ob_get_clean();
    }

    public function define_bulk_actions( $actions ) {
        if ( isset( $actions['edit'] ) ) {
            unset( $actions['edit'] );
        }

        $actions['devvn_bulk_print_order'] = __( 'In đơn hàng', 'devvn' );

        return $actions;
    }

    function ghtk_bulk_action_handler($redirect_to, $action_name, $post_ids){
        if ( 'devvn_bulk_print_order' === $action_name ) {
            $post_ids_new = array();
            foreach ($post_ids as $post_id){
                $order = wc_get_order($post_id);
                $order_ghtk = $this->get_status($order);
                if($order_ghtk['status'] && $order_ghtk['content']) {
                    $post_ids_new[] = $post_id;
                }
            }
            if($post_ids_new) {
                $post_id_list = implode(',', $post_ids_new);
                $redirect_to = admin_url('/admin-ajax.php?action=inhoadon_ghtk&order_id=' . $post_id_list);
                wp_safe_redirect($redirect_to);
                exit;
            }else{
                return $redirect_to;
            }
        }

        else
            return $redirect_to;
    }

    function devvn_gender_field_process(){
        if ( !isset($_POST['billing_gender']) && $this->get_options('enable_gender') && !$_POST['billing_gender'] )
            wc_add_notice( __( 'Please choose gender','devvn-ghtk' ), 'error' );
    }

    function woocommerce_customer_meta_fields($fields){

        global $user_id;

        //billing
        $city = get_user_meta( $user_id, 'billing_state', true );
        $district = get_user_meta( $user_id, 'billing_city', true );

        $billing_fields_old = $fields['billing']['fields'];
        $billing_fields = $fields['billing']['fields'];
        foreach ($billing_fields_old as $key=>$value){
            if(!isset($value['priority'])){
                if($key == 'billing_country') {
                    $value['priority'] = '20';
                }elseif($key == 'billing_state') {
                    $value['priority'] = '30';
                    $value['label'] = __('Tỉnh/Thành phố','devvn-ghtk');
                }elseif($key == 'billing_city') {
                    $value['priority'] = '40';
                    $value['type'] = 'select';
                    $value['class'] = 'js_field-city';
                    $value['label'] = __('Quận/Huyện','devvn-ghtk');
                    $value['options'] = array( '' => __( 'Chọn quận/huyện&hellip;', 'woocommerce' ) ) + $this->get_list_district_select($city);
                }elseif($key == 'billing_address_2') {
                    $value['priority'] = '50';
                    $value['type'] = 'select';
                    $value['class'] = 'js_field-address_2';
                    $value['label'] = __('Xã/Phường/Thị trấn','devvn-ghtk');
                    $value['options'] = array( '' => __( 'Chọn xã/phường/thị trấn&hellip;', 'woocommerce' ) ) + $this->get_list_village_select($district);
                }elseif($key == 'billing_address_1') {
                    $value['priority'] = '60';
                }else{
                    $value['priority'] = '10';
                }
            }
            $billing_fields[$key] = $value;
        }
        uasort( $billing_fields, array( $this, 'sort_fields_by_order' ) );
        $fields['billing']['fields'] = $billing_fields;

        //shipping
        $city = get_user_meta( $user_id, 'shipping_state', true );
        $district = get_user_meta( $user_id, 'shipping_city', true );

        $shipping_fields_old = $fields['shipping']['fields'];
        $shipping_fields = $fields['shipping']['fields'];
        foreach ($shipping_fields_old as $key=>$value){
            if(!isset($value['priority'])){
                if($key == 'shipping_country') {
                    $value['priority'] = '20';
                }elseif($key == 'shipping_state') {
                    $value['priority'] = '30';
                    $value['label'] = __('Tỉnh/Thành phố','devvn-ghtk');
                }elseif($key == 'shipping_city') {
                    $value['priority'] = '40';
                    $value['type'] = 'select';
                    $value['class'] = 'js_field-city';
                    $value['label'] = __('Quận/Huyện','devvn-ghtk');
                    $value['options'] = array( '' => __( 'Chọn quận/huyện&hellip;', 'woocommerce' ) ) + $this->get_list_district_select($city);
                }elseif($key == 'shipping_address_2') {
                    $value['priority'] = '50';
                    $value['type'] = 'select';
                    $value['class'] = 'js_field-address_2';
                    $value['label'] = __('Xã/Phường/Thị trấn','devvn-ghtk');
                    $value['options'] = array( '' => __( 'Chọn xã/phường/thị trấn&hellip;', 'woocommerce' ) ) + $this->get_list_village_select($district);
                }elseif($key == 'shipping_address_1') {
                    $value['priority'] = '60';
                }else{
                    $value['priority'] = '10';
                }
            }
            $shipping_fields[$key] = $value;
        }
        uasort( $shipping_fields, array( $this, 'sort_fields_by_order' ) );
        $fields['shipping']['fields'] = $shipping_fields;

        return $fields;
    }

    function woocommerce_general_settings($fields){

        $fields_new = array();

        $city     = WC()->countries->get_base_state();
        $district = WC()->countries->get_base_city();

        $i = 1;
        foreach ($fields as $field){
            if($field['id'] == 'store_address' && isset($field['type']) && $field['type'] != 'sectionend'){
                $field['priority'] = 1;
            }elseif($field['id'] == 'woocommerce_default_country'){
                $field['priority'] = 2;
                $field['default'] = 'VN:HANOI';
            }elseif($field['id'] == 'woocommerce_store_city'){
                $field['priority'] = 3;
                $field['title'] = __('Quận/Huyện','devvn-ghtk');
                $field['type'] = 'selectajax';
                $field['options'] = array( '' => __( 'Chọn quận/huyện&hellip;', 'woocommerce' ) ) + $this->get_list_district_select($city);
            }elseif($field['id'] == 'woocommerce_store_address_2'){
                $field['priority'] = 4;
                $field['title'] = __('Xã/Phường/Thị trấn','devvn-ghtk');
                $field['type'] = 'selectajax';
                $field['options'] = array( '' => __( 'Chọn xã/phường/thị trấn&hellip;', 'woocommerce' ) ) + $this->get_list_village_select($district);
            }elseif($field['id'] == 'woocommerce_store_address'){
                $field['priority'] = 5;
            }else {
                $field['priority'] = 10 + $i;
            }
            $fields_new[] = $field;
            $i++;
        }

        uasort( $fields_new, array( $this, 'sort_fields_by_order' ) );

        return $fields_new;
    }

    function woocommerce_admin_field_selectajax($value){

        // Custom attribute handling.
        $custom_attributes = array();

        if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
            foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
                $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
            }
        }

        $field_description = WC_Admin_Settings::get_field_description( $value );
        $description       = $field_description['description'];
        $tooltip_html      = $field_description['tooltip_html'];

        $option_value = $value['value'];

        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
            </th>
            <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
                <select
                        name="<?php echo esc_attr( $value['id'] ); ?><?php echo ( 'multiselect' === $value['type'] ) ? '[]' : ''; ?>"
                        id="<?php echo esc_attr( $value['id'] ); ?>"
                        style="<?php echo esc_attr( $value['css'] ); ?>"
                        class="<?php echo esc_attr( $value['class'] ); ?>"
                    <?php echo implode( ' ', $custom_attributes ); // WPCS: XSS ok. ?>
                    <?php echo 'multiselect' === $value['type'] ? 'multiple="multiple"' : ''; ?>
                >
                    <?php
                    foreach ( $value['options'] as $key => $val ) {
                        ?>
                        <option value="<?php echo esc_attr( $key ); ?>"
                            <?php

                            if ( is_array( $option_value ) ) {
                                selected( in_array( (string) $key, $option_value, true ), true );
                            } else {
                                selected( $option_value, (string) $key );
                            }

                            ?>
                        ><?php echo esc_html( $val ); ?></option>
                        <?php
                    }
                    ?>
                </select> <?php echo $description; // WPCS: XSS ok. ?>
            </td>
        </tr>
        <?php
    }

}

function devvn_ghtk(){
    return DevVN_Woo_GHTK_Class::init();
}
devvn_ghtk();

include_once('includes/class-order-style.php');

function ghtk_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
    if ( ! empty( $args ) && is_array( $args ) ) {
        extract( $args ); // @codingStandardsIgnoreLine
    }

    $located = ghtk_locate_template( $template_name, $template_path, $default_path );

    if ( ! file_exists( $located ) ) {
        /* translators: %s template */
        ghtk_doing_it_wrong( __FUNCTION__, sprintf( __( '%s không tồn tại.', 'devvn-ghtk' ), '<code>' . $located . '</code>' ), '2.1' );
        return;
    }

    // Allow 3rd party plugin filter template file from their plugin.
    $located = apply_filters( 'ghtk_get_template', $located, $template_name, $args, $template_path, $default_path );

    do_action( 'ghtk_before_template_part', $template_name, $template_path, $located, $args );

    include $located;

    do_action( 'ghtk_after_template_part', $template_name, $template_path, $located, $args );
}

function ghtk_locate_template( $template_name, $template_path = '', $default_path = '' ) {
    if ( ! $template_path ) {
        $template_path = apply_filters( 'ghtk_template_path', 'devvn-ghtk/' );
    }

    if ( ! $default_path ) {
        $default_path =  untrailingslashit( plugin_dir_path( __FILE__ )) . '/templates/';
    }

    // Look within passed path within the theme - this is priority.
    $template = locate_template(
        array(
            trailingslashit( $template_path ) . $template_name,
            $template_name,
        )
    );

    if ( ! $template ) {
        $template = $default_path . $template_name;
    }

    // Return what we found.
    return apply_filters( 'ghtk_locate_template', $template, $template_name, $template_path );
}

function ghtk_doing_it_wrong( $function, $message, $version ) {
    // @codingStandardsIgnoreStart
    $message .= ' Backtrace: ' . wp_debug_backtrace_summary();

    if ( is_ajax() ) {
        do_action( 'doing_it_wrong_run', $function, $message, $version );
        error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
    } else {
        _doing_it_wrong( $function, $message, $version );
    }
    // @codingStandardsIgnoreEnd
}

}//End if active woo



if(!function_exists('devvn_woocommerce_localisation_address_formats_vn')) {
    add_filter('woocommerce_localisation_address_formats', 'devvn_woocommerce_localisation_address_formats_vn', 999999);
    function devvn_woocommerce_localisation_address_formats_vn($arg)
    {
        if (isset($arg['default'])) unset($arg['default']);
        if (isset($arg['VN'])) unset($arg['VN']);
        $arg['default'] = "{gender} {name}\n{company}\n{address_1}\n{address_2}\n{city}\n{state}\n{country}";
        $arg['VN'] = "{gender} {name}\n{company}\n{address_1}\n{address_2}\n{city}\n{state}\n{country}";
        return $arg;
    }
}

if(!function_exists('devvn_woocommerce_order_formatted_billing_address_gender')) {
    add_filter('woocommerce_order_formatted_billing_address', 'devvn_woocommerce_order_formatted_billing_address_gender', 10, 2);
    function devvn_woocommerce_order_formatted_billing_address_gender($address_arg, $thisParent)
    {
        $gender = get_post_meta($thisParent->get_id(), '_billing_gender', true);
        if (!isset($address_arg['gender']) && $gender) {
            $gender = ($gender == 'male') ? 'Anh' : 'Chị';
            $address_arg['gender'] = $gender;
        }
        return $address_arg;
    }
}

if(!function_exists('devvn_woocommerce_formatted_address_replacements_gender')) {
    add_filter('woocommerce_formatted_address_replacements', 'devvn_woocommerce_formatted_address_replacements_gender', 10, 2);
    function devvn_woocommerce_formatted_address_replacements_gender($replace, $args)
    {
        if (!isset($replace['{gender}']) && isset($args['gender'])) {
            $replace['{gender}'] = $args['gender'];
        } else {
            $replace['{gender}'] = '';
        }
        return $replace;
    }
}

if(!function_exists('devvn_custom_checkout_field_display_admin_order_meta_gender')) {
    add_action('woocommerce_admin_order_data_after_billing_address', 'devvn_custom_checkout_field_display_admin_order_meta_gender', 10, 1);
    function devvn_custom_checkout_field_display_admin_order_meta_gender($order)
    {
        $gender = get_post_meta($order->get_id(), '_billing_gender', true);
        if($gender) {
            $gender = ($gender == 'male') ? 'Anh' : 'Chị';

            echo '<p><strong>' . __('Xưng hô', 'devvn-ghtk') . ':</strong> ' . $gender . '</p>';
        }
    }
}