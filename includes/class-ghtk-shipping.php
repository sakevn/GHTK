<?php
/*
 * Author Name: Le Van Toan
 * Author URI: https://levantoan.com
 */
function ghtk_shipping_method_init() {
    if ( ! class_exists( 'WC_GHTK_Shipping_Method' ) ) {
        class WC_GHTK_Shipping_Method extends WC_Shipping_Method {
            public $ghtk_mess = '';
            /**
             * Constructor for your shipping class
             *
             * @access public
             * @return void
             */
            public function __construct() {

                $this->id                 = 'ghtk_shipping_method';
                $this->method_title       = __( 'Giao hàng tiết kiệm (GHTK)' );
                $this->method_description = __( 'Tính phí vận chuyển và đồng bộ đơn hàng với giao hàng tiết kiệm (GHTK)' );

                $this->init();

                $this->enabled            = $this->settings['enabled'];
                $this->title              = $this->settings['title'];

            }

            /**
             * Init your settings
             *
             * @access public
             * @return void
             */
            function init() {
                // Load the settings API
                $this->init_form_fields();
                $this->init_settings();

                // Save settings in admin if you have any defined
                add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
            }

            function init_form_fields() {
                $this->form_fields = array(
                    'enabled' => array(
                        'title'     => __( 'Kích hoạt', 'devvn-ghtk' ),
                        'type'      => 'checkbox',
                        'label'     => __( 'Kích hoạt tính phí vận chuyển bằng GHTK', 'devvn-ghtk' ),
                        'default'   => 'yes',
                    ),
                    'title' => array(
                        'title' => __( 'Tiêu đề', 'devvn-ghtk' ),
                        'type' => 'text',
                        'description' => __( 'Mô tả cho phương thức vận chuyển', 'devvn-ghtk' ),
                        'default' => __( 'Vận chuyển qua GHTK', 'devvn-ghtk' )
                    ),
                );
            } // End init_form_fields()

            /**
             * calculate_shipping function.
             *
             * @access public
             * @param mixed $package
             * @return void
             */
            public function calculate_shipping( $package = array() ) {

                $mainsetting = devvn_ghtk();

                $transport = apply_filters('devvn_ghtk_shipping_methob', array(
                    'fly' => apply_filters('text_fly', 'GHTK đường bay'),
                    'road' => apply_filters('text_road', 'GHTK đường bộ'),
                ));

                $state_customer = isset($package['destination']['state']) ? $package['destination']['state'] : '';

                $hub_near = $mainsetting->get_near_hubs($state_customer);
                $state_store = $mainsetting->get_store_address('pick_province', $hub_near);

                if($state_store == $state_customer && isset($transport['fly'])){
                    unset($transport['fly']);
                }

                $shipping_okie = false;
                foreach($transport as $key=>$val){
                    $cost = ghtk_api()->get_shipping_fee($package, $key);
                    if(isset($cost['success']) && $cost['success']){
                        $shipping_fee =  isset($cost['fee']['fee']) ? $cost['fee']['fee'] : '';
                        if($shipping_fee) {
                            $rate = array(
                                'id' => $this->id . $key,
                                'label' => $val,
                                'cost' => $shipping_fee,
                                'calc_tax' => 'per_item',
                                'meta_data' => array(
                                    'hubsid' => isset($cost['hubsid']) ? $cost['hubsid'] : 0,
                                    'transport' => $key,
                                )
                            );
                            $this->add_rate($rate);
                            $shipping_okie = true;
                        }
                    }
                }
                if(!$shipping_okie){
                    $this->ghtk_mess = __('Nhập địa chỉ đầy đủ của bạn để xem phí giao hàng.', 'devvn-ghtk');//$cost['message'];
                    add_filter("woocommerce_cart_no_shipping_available_html", array($this, "devvn_no_shipping_cart"));
                    add_filter("woocommerce_no_shipping_available_html", array($this, "devvn_no_shipping_cart"));
                }
            }

            function devvn_no_shipping_cart(){
                return $this->ghtk_mess;
            }
        }
    }
}

add_action( 'woocommerce_shipping_init', 'ghtk_shipping_method_init' );

function add_your_shipping_method( $methods ) {
    $methods['ghtk_shipping_method'] = 'WC_GHTK_Shipping_Method';
    return $methods;
}

add_filter( 'woocommerce_shipping_methods', 'add_your_shipping_method' );

class DevVN_GHTK_API{

    protected static $_instance = null;
    private $token = '';
    private $mashop = '';
    private $transport = 'road';
    private $linkhook = 'https://services.giaohangtietkiem.vn';

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        $mainsetting = devvn_ghtk();
        if($mainsetting->get_options('is_sandbox') == 1) {
            $this->linkhook = 'https://dev.ghtk.vn';
        }
        $this->token = $mainsetting->get_options('token_key');
        $this->mashop = $mainsetting->get_options('mashop');
        $this->transport = $mainsetting->get_options('transport');
    }

    function get_shipping_fee( $package = array(), $transports ){

        $mainsetting = devvn_ghtk();

        $state = isset($package['destination']['state']) ? $package['destination']['state'] : '';
        $city = isset($package['destination']['city']) ? $package['destination']['city'] : '';

        $hub_near = $mainsetting->get_near_hubs($state);

        $data = array(
            'pick_address_id'               =>  sanitize_text_field($mainsetting->get_store_address('pick_address_id', $hub_near)),
            'pick_address'                  =>  sanitize_text_field($mainsetting->get_store_address('pick_address', $hub_near)),
            'pick_province'                 =>  $mainsetting->get_name_city($mainsetting->get_store_address('pick_province', $hub_near)),
            'pick_district'                 =>  $mainsetting->get_name_district($mainsetting->get_store_address('pick_district', $hub_near)),
            'pick_ward'                     =>  $mainsetting->get_name_village($mainsetting->get_store_address('pick_ward', $hub_near)),
            'pick_street'                   =>  sanitize_text_field($mainsetting->get_store_address('pick_street', $hub_near)),

            "province" => $state ? $mainsetting->get_name_city($state) : '',
            "district" => $city ? $mainsetting->get_name_district($city) : '',

            "weight" => $mainsetting->get_cart_contents_weight($package),
            "value" => isset($package['cart_subtotal']) ? $package['cart_subtotal'] : '',
            "transport" => $transports
        );

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->linkhook . "/services/shipment/fee?" . http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYPEER => is_ssl(),
            CURLOPT_HTTPHEADER => array(
                "content-type: content-type: application/json",
                "Token: $this->token",
            ),
        ));

        $response = curl_exec($curl);
        //error_log(json_encode($data));
        curl_close($curl);

        $result = json_decode($response,true);
        if($result && is_array($result) && !is_wp_error($result)){
            $result['hubsid'] = $hub_near;
            return $result;
        }

        return false;
    }
    function ghtk_creat_order( $order = '' ){

        if(!$order) return false;

        $order = <<<HTTP_BODY
$order
HTTP_BODY;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->linkhook."/services/shipment/order",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $order,
            CURLOPT_SSL_VERIFYPEER => is_ssl(),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Token: $this->token",
                "Content-Length: " . strlen($order),
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($response,true);

        if($result && is_array($result) && !is_wp_error($result)){
            return $result;
        }

        return false;
    }
    function inhoadon_ghtk($label = ''){
        if(!$label) return;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->linkhook."/services/label/$label",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYPEER => is_ssl(),
            CURLOPT_HTTPHEADER => array(
                "Token: $this->token",
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($response,true);


        if(isset($result['success']) && !$result['success']){
            echo $result['message'];
            die();
        }

        $name = 'order-'.$label.'.pdf';
        header('Content-Type: application/pdf');
        header('Content-Length: '.strlen( $response ));
        header('Content-disposition: inline; filename="' . $name . '"');
        header('Cache-Control: public, must-revalidate, max-age=0');
        echo $response;
        die();
    }
    function get_status($label_id = ""){

        if(!$label_id) return;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->linkhook."/services/shipment/v2/$label_id",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYPEER => is_ssl(),
            CURLOPT_HTTPHEADER => array(
                "Token: $this->token",
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response,true);
        return $result;
    }
    function get_list_url(){

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->linkhook."/services/webhook",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYPEER => is_ssl(),
            CURLOPT_HTTPHEADER => array(
                "Token: $this->token",
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($response,true);
        return $result;
    }
    function add_url($url = ''){

        if(!$url) return false;

        $data = array(
            'url' => esc_url_raw($url)
        );

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->linkhook."/services/webhook/add",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYPEER => is_ssl(),
            CURLOPT_HTTPHEADER => array(
                "Token: $this->token",
            ),
            CURLOPT_POSTFIELDS => $data
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($response,true);
        return $result;
    }
    function delete_url($url = ''){

        if(!$url) return false;

        $data = array(
            'url' => esc_url($url)
        );

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->linkhook."/services/webhook/del",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYPEER => is_ssl(),
            CURLOPT_HTTPHEADER => array(
                "Token: $this->token",
            ),
            CURLOPT_POSTFIELDS => $data
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($response,true);
        return $result;
    }
    function tracking_order($shipID = ''){
        if(!$shipID || !$this->mashop || !$this->token) return false;

        $shipID = wc_clean(esc_attr($shipID));
        $h = hash_hmac('md5', $shipID, strrev($this->token));

        $url = 'https://khachhang.giaohangtietkiem.vn/khach-hang/tra-cuu?s='.urlencode($this->mashop).'&o='.urlencode($shipID).'&h='.urlencode($h).'&json=1';

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

        $result = json_decode($response,true);

        if($result && is_array($result) && !is_wp_error($result)){
            return $result;
        }

        return false;

    }
    function get_status_text($status_id = ''){
        if(!$status_id) return false;
        $status_text = '';
        switch($status_id){
            case '-1':
                $status_text = 'Hủy đơn hàng';
                break;
            case '1':
                $status_text = 'Chưa tiếp nhận';
                break;
            case '2':
                $status_text = 'Đã tiếp nhận';
                break;
            case '3':
                $status_text = 'Đã lấy hàng/Đã nhập kho';
                break;
            case '4':
                $status_text = 'Đã điều phối giao hàng/Đang giao hàng';
                break;
            case '5':
                $status_text = 'Đã giao hàng/Chưa đối soát';
                break;
            case '6':
                $status_text = 'Đã đối soát';
                break;
            case '7':
                $status_text = 'Không lấy được hàng';
                break;
            case '8':
                $status_text = 'Hoãn lấy hàng';
                break;
            case '10':
                $status_text = 'Delay giao hàng';
                break;
            case '11':
                $status_text = 'Đã đối soát công nợ trả hàng';
                break;
            case '12':
                $status_text = 'Đã điều phối lấy hàng/Đang lấy hàng';
                break;
            case '13':
                $status_text = 'Đơn hàng bồi hoàn';
                break;
            case '20':
                $status_text = 'Đang trả hàng (COD cầm hàng đi trả)';
                break;
            case '21':
                $status_text = 'Đã trả hàng (COD đã trả xong hàng)';
                break;
            case '123':
                $status_text = 'Shipper báo đã lấy hàng';
                break;
            case '127':
                $status_text = 'Shipper (nhân viên lấy/giao hàng) báo không lấy được hàng';
                break;
            case '128':
                $status_text = 'Shipper báo delay lấy hàng';
                break;
            case '45':
                $status_text = 'Shipper báo đã giao hàng';
                break;
            case '49':
                $status_text = 'Shipper báo không giao được giao hàng';
                break;
            case '410':
                $status_text = 'Shipper báo delay giao hàng';
                break;
        }
        return $status_text;
    }
    function get_reason_text($reason_code = ''){
        if(!$reason_code) return false;
        $status_text = '';
        switch($reason_code){
            case '100':
                $status_text = 'Nhà cung cấp (NCC) hẹn lấy vào ca tiếp theo';
                break;
            case '101':
                $status_text = 'GHTK không liên lạc được với NCC';
                break;
            case '102':
                $status_text = 'NCC chưa có hàng';
                break;
            case '103':
                $status_text = 'NCC đổi địa chỉ';
                break;
            case '104':
                $status_text = 'NCC hẹn ngày lấy hàng';
                break;
            case '105':
                $status_text = 'GHTK quá tải, không lấy kịp';
                break;
            case '106':
                $status_text = 'Do điều kiện thời tiết, khách quan';
                break;
            case '107':
                $status_text = 'Lý do khác';
                break;
            case '110':
                $status_text = 'Địa chỉ ngoài vùng phục vụ';
                break;
            case '111':
                $status_text = 'Hàng không nhận vận chuyển';
                break;
            case '112':
                $status_text = 'NCC báo hủy';
                break;
            case '113':
                $status_text = 'NCC hoãn/không liên lạc được 3 lần';
                break;
            case '114':
                $status_text = 'Lý do khác';
                break;
            case '115':
                $status_text = 'Đối tác hủy đơn qua API';
                break;
            case '120':
                $status_text = 'GHTK quá tải, giao không kịp';
                break;
            case '121':
                $status_text = 'Người nhận hàng hẹn giao ca tiếp theo';
                break;
            case '122':
                $status_text = 'Không gọi được cho người nhận hàng';
                break;
            case '123':
                $status_text = 'Người nhận hàng hẹn ngày giao';
                break;
            case '124':
                $status_text = 'Người nhận hàng chuyển địa chỉ nhận mới';
                break;
            case '125':
                $status_text = 'Địa chỉ người nhận sai, cần NCC check lại';
                break;
            case '126':
                $status_text = 'Do điều kiện thời tiết, khách quan';
                break;
            case '127':
                $status_text = 'Lý do khác';
                break;
            case '128':
                $status_text = 'Đối tác hẹn thời gian giao hàng';
                break;
            case '129':
                $status_text = 'Không tìm thấy hàng';
                break;
            case '1200':
                $status_text = 'SĐT người nhận sai, cần NCC check lại';
                break;
            case '130':
                $status_text = 'Người nhận không đồng ý nhận sản phẩm';
                break;
            case '131':
                $status_text = 'Không liên lạc được với KH 3 lần';
                break;
            case '132':
                $status_text = 'KH hẹn giao lại quá 3 lần';
                break;
            case '133':
                $status_text = 'Shop báo hủy đơn hàng';
                break;
            case '134':
                $status_text = 'Lý do khác';
                break;
            case '135':
                $status_text = 'Đối tác hủy đơn qua API';
                break;
            case '140':
                $status_text = 'NCC hẹn trả ca sau';
                break;
            case '141':
                $status_text = 'Không liên lạc được với NCC';
                break;
            case '142':
                $status_text = 'NCC không có nhà';
                break;
            case '143':
                $status_text = 'NCC hẹn ngày trả';
                break;
            case '144':
                $status_text = 'Lý do khác';
                break;
        }
        return $status_text;
    }
}

function ghtk_api(){
    return DevVN_GHTK_API::instance();
}