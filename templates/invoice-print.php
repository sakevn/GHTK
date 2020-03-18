<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>In hóa đơn</title>
        <style>
            *{
                padding: 0;
                margin: 0;
                -webkit-box-sizing: border-box;
                -moz-box-sizing: border-box;
                box-sizing: border-box;
            }
            .invoice_a4 {
                width: 297mm;
                margin: 0 auto;
                overflow: hidden;
            }
            .page {
                float: left;
                width: 50%;
                height: 100%;
                height: 209mm;
                position: relative;
                padding: 10mm 0;
            }
            img{
                max-width: 100%;
                height: auto;
            }
            body, table{
                font-family: Arial;
                font-size: 12px;
            }
            .invoice_wrap {
                display: block;
                /*height: 100%;*/
                overflow: hidden;
                border: 1px solid black;
                margin: 0 7mm;
            }
            .invoice_wrap table {
                border-collapse: collapse;
                border: 1px solid #000;
                width: 100%;
            }
            .invoice_wrap table td, .invoice_wrap table th {
                padding: 5px;
                border: 1px solid #000;
                vertical-align: top;
            }
            .invoice_header:after, .invoice_total:after {
                content: "";
                display: table;
                clear: both;
            }
            .invoice_logo {
                display: table-cell;
                text-align: center;
                vertical-align: middle;
            }
            .invoice_infor {
                display: table-cell;
                padding: 5px 0;
                text-align: center;
                vertical-align: middle;
                width: 69mm;
            }
            div[id^="barcode_mvd"] {
                margin: 0 auto;
            }
            .invoice_row ~ .invoice_row {
                border-top: 1px dashed #000;
                padding-top: 5px;
                padding-bottom: 5px;
            }
            .invoice_body {
                display: table;
                width: 100%;
            }
            .invoice_body_left, .invoice_body_right {
                display: table-cell;
                padding: 0 10px;
            }
            .invoice_body_left {
                border-right: 1px solid #000;
                width: 50px;
            }
            .invoice_body_right div[id^="barcode_mdh"] {
                float: right;
            }
            .invoice_total_left, .invoice_total_right {
                width: 50%;
                float: left;
                text-align: center;
            }
            .invoice_total strong {
                display: block;
                margin: 0 0 5px 0;
            }
            span.total_price {
                font-size: 20px;
                font-weight: 700;
            }
            .invoice_total_right {
                min-height: 80px;
            }
            .invoice_wrap table tr th:nth-child(2),
            .invoice_wrap table tr td:nth-child(2){
                width: 30px;
                text-align: center;
            }
            .invoice_sanpham {
                padding: 0 5px 5px 5px;
            }
            .invoice_logo .no-print {
                padding-top: 10px;
                color: red;
            }
            .invoice_header {
                height: 33mm;
                overflow: hidden;
                display: table;
                width: 100%;
            }
            .invoice_row.shop_address {
                max-height: 24.3mm;
                overflow: hidden;
            }
            .invoice_row.customer_address {
                max-height: 20mm;
                overflow: hidden;
            }
            .invoice_row.product_row {
                height: 66mm;
                overflow: hidden;
            }
            .invoice_row.note_row {
                height: 17.2mm;
                overflow: hidden;
                line-height: 1.3;
            }
            .invoice_total {
                padding-top: 10px;
            }
            .invoice_total_left {
                text-align: left;
                padding-left: 20px;
            }
            .invoice_total_left p {
                margin-top: 10px;
            }
            .invoice_total_left p strong {
                display: inline-block;
            }
            .note_print_before {
                width: 100%;
                max-width: 800px;
                margin: 30px auto 0;
                border: 2px dashed red;
                padding: 10px;
                font-size: 15px;
                text-align: center;
            }
            body:not(.print_1_col) .invoice_a4 .page:nth-of-type(2n+1) {
                border-right: 1px solid #000;
            }
            .note_print_before button {
                padding: 5px 10px;
                border-radius: 5px;
                border: 0;
                background: green;
                color: #fff;
                margin: 10px 10px 0 10px;
                cursor: pointer;
                outline: none;
            }
            .note_print_before button.selected {
                background: red;
            }
            @media print{
                .no-print{
                    display: none;
                }
            }
            /*@page { size: A5 landscape; margin: 0;}*/
            .print_1_col .page{
                width: 100%;
            }
            .print_1_col .invoice_a4 {
                width: 148mm;
            }
            /*@page { size: portrait; margin: 0;}*/
            .free_height{
                display: none;
            }
            .print_1_col .free_height{
                display: inline-block;
            }
            .free_height_body .invoice_row.product_row {
                height: auto;
            }
            .free_height_body .page {
                height: auto;
                padding: 3mm 0;
            }
            .print_a8 .invoice_a4 {
                width: 80mm;
            }
            .print_a8 .invoice_wrap {
                margin: 0;
                border: 0;
            }
            .print_a8 .invoice_logo {
                display: block;
                width: 100%;
            }
            .print_a8 .invoice_header {
                display: block;
                height: auto;
            }
            .print_a8 .invoice_infor {
                display: block;
                width: 100%;
            }
            .print_a8 .invoice_total {
                padding: 0;
            }
            .print_a8 .invoice_total_left {
                padding-left: 5px;
            }
            .free_height_body .invoice_row.note_row {
                height: auto;
            }
            .print_a8 .page {
                border-bottom: 1px solid #000;
            }
            .print_a8 .page:last-of-type{
                border-bottom: 0;
            }
        </style>

        <script type='text/javascript' src='<?php echo home_url();?>/wp-includes/js/jquery/jquery.js'></script>
        <script type="text/javascript" src="<?php echo DEVVN_GHTK_URL;?>assets/js/jquery-barcode.min.js"></script>
        <script>
            (function ($) {
                $(document).ready(function () {
                    function resetBodyClass(){
                        $('body').removeClass('print_a8 print_1_col free_height_body');
                    }
                    $('.in_a5').on('click', function () {
                        resetBodyClass();
                        $('.note_print_before button').removeClass('selected');
                        $(this).addClass('selected');
                        $('.style_js').html('<style>@page { size: A5 landscape; margin: 0;}</style>');
                    });
                    $('.in_a8').on('click', function () {
                        resetBodyClass();
                        $('body').addClass('print_a8');
                        $('body').addClass('print_1_col');
                        $('body').addClass('free_height_body');
                        $('#free_height').prop('checked', true);
                        $('.note_print_before button').removeClass('selected');
                        $(this).addClass('selected');
                        $('.style_js').html('<style>@page { size: portrait; margin: 0;}</style>');
                    });
                    $('.in_a6').on('click', function () {
                        resetBodyClass();
                        $('body').addClass('print_1_col');
                        $('.note_print_before button').removeClass('selected');
                        $(this).addClass('selected');
                        $('.style_js').html('<style>@page { size: portrait; margin: 0;}</style>');
                        if($('#free_height').is(':checked')){
                            $('body').addClass('free_height_body');
                        }else{
                            $('body').removeClass('free_height_body');
                        }
                    });
                    $('#free_height').on('change', function () {
                        if($(this).is(':checked')){
                            $('body').addClass('free_height_body');
                        }else{
                            $('body').removeClass('free_height_body');
                        }
                    });
                });
            })(jQuery);
        </script>
    </head>
    <body>
    <div class="note_print_before no-print">
        Chú ý: In với khổ giấy A5 theo chiều ngang (Hóa đơn sẽ có kích thước khổ giấy A6)
        <p>
            <button class="in_a5 selected">In A5 - Khổ ngang</button>
            <button class="in_a8">Khổ 80mm</button>
            <button class="in_a6">In A6 - Khổ dọc</button>
            <label class="free_height"><input type="checkbox" name="free_height" value="1" id="free_height"/> Không giới hạn chiều cao</label>
        </p>
    </div>
    <div class="style_js"><style>@page { size: A5 landscape; margin: 0;}</style></div>

    <div class="invoice_a4">
        <?php
        function devvn_order_print_formatted_address_replacements($address){
            unset($address['first_name']);
            unset($address['last_name']);
            return $address;
        }
        foreach($order_args as $order):
        $item_count = $order->get_item_count();
        $order_id = $order->get_id();

        $ghtk_status = apply_filters('devvn_invoice_order_ghtk_full', get_post_meta($order_id ,'_order_ghtk_full', true));
        $ghtk_order = apply_filters('devvn_invoice_order_ghtk_fullinfor', get_post_meta($order_id ,'_order_ghtk_fullinfor', true));
        $ghtk_id = isset($ghtk_status['order']['label_id']) ? $ghtk_status['order']['label_id'] : '';
        if(!$ghtk_id) $ghtk_id = isset($ghtk_status['order']['label']) ? $ghtk_status['order']['label'] : '';
        $ghtk_id_num = preg_split("/[.]/", $ghtk_id);
        if($ghtk_id_num && is_array($ghtk_id_num)){
            $ghtk_id_num = end($ghtk_id_num);
        }else{
            $ghtk_id_num = '';
        }
        ?>
        <div class="page">
            <div class="invoice_wrap">
                <div class="invoice_row">
                    <div class="invoice_header">
                        <div class="invoice_logo">
                            <?php if($logo = $this_class->get_options('print_logo')):?>
                            <?php echo wp_get_attachment_image($logo, 'full');?>
                            <?php else:?>
                                <div class="no-print">Thay logo tại WP Admin -> Cài đặt -> Cài đặt GHTK -> Cài đặt in hóa đơn -> Logo. (Thông báo này sẽ không hiển thị khi in)</div>
                            <?php endif;?>
                        </div>
                        <div class="invoice_infor">
                            <div id="barcode_mvd_<?php echo $order_id;?>"></div>
                            Mã vận đơn: <?php echo $ghtk_id;?><br>
                            Đơn vị: Giao hàng tiết kiệm<br>
                            Mã đơn hàng: #<?php echo $order_id;?>
                        </div>
                    </div>
                </div>
                <div class="invoice_row shop_address">
                    <div class="invoice_body">
                        <div class="invoice_body_left">Từ</div>
                        <div class="invoice_body_right">
                            <div id="barcode_mdh_<?php echo $order_id;?>"></div>
                            <?php echo isset($ghtk_order['order']['pick_name']) ? sanitize_text_field($ghtk_order['order']['pick_name']) : '';?><br>
                            <?php if($ghtk_order['order']['pick_tel']):?>
                            ĐT: <?php echo sanitize_text_field($ghtk_order['order']['pick_tel']);?><br>
                            <?php endif;?>
                            ĐC: <?php echo ($ghtk_order['order']['pick_address']) ? sanitize_text_field($ghtk_order['order']['pick_address']) : '';?>
                                <?php echo ($ghtk_order['order']['pick_street']) ? sanitize_text_field($ghtk_order['order']['pick_street']) .',' : '';?>
                                <?php echo ($ghtk_order['order']['pick_ward']) ? sanitize_text_field($ghtk_order['order']['pick_ward']) .',' : '';?>
                                <?php echo ($ghtk_order['order']['pick_district']) ? sanitize_text_field($ghtk_order['order']['pick_district']) .',' : '';?>
                                <?php echo ($ghtk_order['order']['pick_province']) ? sanitize_text_field($ghtk_order['order']['pick_province']) . '<br>' : '';?>
                            Mã đơn hàng: #<?php echo $order_id;?>
                            <?php do_action('devvn_invoice_after_shop_address', $order);?>
                        </div>
                    </div>
                </div>
                <div class="invoice_row customer_address">
                    <div class="invoice_body">
                        <div class="invoice_body_left">Đến</div>
                        <div class="invoice_body_right">
                            Tên: <?php echo $order->get_formatted_shipping_full_name();?><br>
                            <?php $shipping_phone = get_post_meta( $order_id, '_shipping_phone', true );?>
                            ĐT: <?php echo ($shipping_phone) ? $shipping_phone : $order->get_billing_phone();?><br>
                            ĐC:
                            <?php
                            add_filter('woocommerce_order_formatted_shipping_address', 'devvn_order_print_formatted_address_replacements', 10);
                            add_filter('woocommerce_order_formatted_billing_address', 'devvn_order_print_formatted_address_replacements', 10);
                            if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() ) :
                                $address = $order->get_formatted_shipping_address();
                            else:
                                $address = $order->get_formatted_billing_address();
                            endif;
                            if ( $address ) {
                                echo esc_html( preg_replace( '#<br\s*/?>#i', ', ', $address ) ) . '<br>';
                            }
                            remove_filter('woocommerce_order_formatted_billing_address', 'devvn_order_print_formatted_address_replacements', 10);
                            remove_filter('woocommerce_order_formatted_shipping_address', 'devvn_order_print_formatted_address_replacements', 10);
                            ?>
                            <?php do_action('devvn_invoice_after_customer_address', $order);?>
                        </div>
                    </div>
                </div>
                <div class="invoice_row product_row">
                    <div class="invoice_sanpham">
                        <strong style="display: block; margin-bottom: 5px;">Sản phẩm (Tổng SL sản phẩm: <?php echo $item_count;?>)</strong>
                        <table>
                            <thead>
                                <tr>
                                    <th>Tên sp</th>
                                    <th>SL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $all_prods = $this_class->get_product_args($order);
                                if($all_prods && !is_wp_error($all_prods) && !empty($all_prods)):
                                    foreach($all_prods as $product):
                                    ?>
                                    <tr>
                                        <td><?php echo $product['name']?></td>
                                        <td><?php echo $product['quantity']?></td>
                                    </tr>
                                    <?php endforeach;?>
                                <?php endif;?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="invoice_row note_row">
                    <div class="invoice_sanpham">
                        <strong>Ghi chú: </strong> <?php echo isset($ghtk_order['order']['note']) ? sanitize_textarea_field($ghtk_order['order']['note']) : '';?>
                    </div>
                </div>
                <div class="invoice_row">
                    <div class="invoice_total">
                        <div class="invoice_total_left">
                            <strong>Tiền thu người nhận:</strong>
                            <span class="total_price"><?php echo wc_price($order->get_total());?></span>
                            <?php if($note_print = $this_class->get_options('print_note')):?>
                            <p>
                                <?php echo $note_print;?>
                            </p>
                            <?php endif;?>
                        </div>
                        <div class="invoice_total_right">
                            <strong>Nhân viên lấy hàng ký nhận</strong>
                            <span>(Kí và ghi rõ họ tên)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function($){
                $("#barcode_mvd_<?php echo $order_id;?>").barcode(
                    "<?php echo $ghtk_id_num;?>",
                    "code128",
                    {
                        barWidth: 2,
                        barHeight: 50,
                        moduleSize: 5,
                        fontSize: 14,
                    }
                );
                $("#barcode_mdh_<?php echo $order_id;?>").barcode(
                    "<?php echo $order_id;?>",
                    "code128",
                );
            })
        </script>
        <?php endforeach;?>
    </div>
    </body>
</html>