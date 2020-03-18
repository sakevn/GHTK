<div class="wrap">
	<h1>Giao hàng tiết kiệm</h1>
	<p>Plugin được viết và phát triển bởi <a href="https://Doibu.com" target="_blank" title="Đến web của Nhà Phát Triên">Doibu.com</a></p>

    <div class="devvn_setting_note">
        <p><strong style="color: red">Chú ý:</strong> Để plugin hoạt động chính xác. Vui lòng xem cấu hình <a href="https://doibu.com/product/plugin-ket-noi-giao-hang-tiet-kiem-voi-woocommerce/" target="_blank">tại đây</a></p>
        <p>- Sử dụng shortcode [ghtk_tracking_form] để hiển thị form kiểm tra trạng thái đơn hàng (Tracking).</p>
        <p>- Sau khi đăng ký webhook và hệ thống báo thành công nhưng vẫn hiện thông báo bắt đăng ký webhook và không hiện link webhook thì đó là do cache api của GHTK thôi. Cứ để 1 thời gian sẽ được. </p>
    </div>


	<form method="post" action="options.php" novalidate="novalidate">
	<?php
	settings_fields( $this->_optionGroup );
	$flra_options = wp_parse_args(get_option($this->_optionName),$this->_defaultOptions);
	?>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="activeplugin"><?php _e('Ẩn mục phường/xã','devvn-ghtk')?></label></th>
					<td>
						<label><input type="checkbox" name="<?php echo $this->_optionName?>[active_village]" <?php checked('1',$flra_options['active_village'])?> value="1" /> <?php _e('Ẩn mục phường/xã','devvn-ghtk')?></label>	                   
					</td>
				</tr>
                <tr>
                    <th scope="row"><label for="required_village"><?php _e('KHÔNG bắt buộc nhập phường/xã','devvn-ghtk')?></label></th>
                    <td>
                        <label><input type="checkbox" name="<?php echo $this->_optionName?>[required_village]" <?php checked('1',$flra_options['required_village'])?> value="1" /> <?php _e('Không bắt buộc','devvn-ghtk')?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="to_vnd"><?php _e('Chuyển ₫ sang VNĐ','devvn-ghtk')?></label></th>
                    <td>
                        <label><input type="checkbox" name="<?php echo $this->_optionName?>[to_vnd]" <?php checked('1',$flra_options['to_vnd'])?> value="1" id="to_vnd"/> <?php _e('Cho phép chuyển sang VNĐ','devvn-ghtk')?></label><br>
                        <small>Xem thêm <a href="http://doibu.com/thay-doi-ky-hieu-tien-te-dong-viet-nam-trong-woocommerce-d-sang-vnd/" target="_blank"> cách thiết lập đơn vị tiền tệ ₫ (Việt Nam đồng)</a></small>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="remove_methob_title"><?php _e('Loại bỏ tiêu đề vận chuyển','devvn-ghtk')?></label></th>
                    <td>
                        <label><input type="checkbox" name="<?php echo $this->_optionName?>[remove_methob_title]" <?php checked('1',$flra_options['remove_methob_title'])?> value="1" id="remove_methob_title"/> <?php _e('Loại bỏ hoàn toàn tiêu đề của phương thức vận chuyển','devvn-ghtk')?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="freeship_remove_other_methob"><?php _e('Ẩn phương thức khi có free-shipping','devvn-ghtk')?></label></th>
                    <td>
                        <label><input type="checkbox" name="<?php echo $this->_optionName?>[freeship_remove_other_methob]" <?php checked('1',$flra_options['freeship_remove_other_methob'])?> value="1" id="freeship_remove_other_methob"/> <?php _e('Ẩn tất cả những phương thức vận chuyển khác khi có miễn phí vận chuyển','devvn-ghtk')?></label>
                    </td>
                </tr>
                <tr class="devvn_pro">
                    <th scope="row"><label for="khoiluong_quydoi"><?php _e('Số quy đổi','devvn-ghtk')?></label></th>
                    <td>
                        <input type="number" min="0" name="<?php echo $this->_optionName?>[khoiluong_quydoi]" value="<?php echo $flra_options['khoiluong_quydoi'];?>" id="khoiluong_quydoi"/> <br>
                        <small><?php _e('Thương số quy đổi. Mặc định theo Viettel Post là 6000','devvn-ghtk')?></small>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="active_vnd2usd"><?php _e('Kích hoạt chuyển đổi VNĐ sang USD','devvn-ghtk')?></label></th>
                    <td>
                        <label><input type="checkbox" name="<?php echo $this->_optionName?>[active_vnd2usd]" <?php checked('1',$flra_options['active_vnd2usd'])?> value="1" /> <?php _e('Kích hoạt chuyển đổi VNĐ sang USD để có thể sử dụng paypal','devvn-ghtk')?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="vnd_usd_rate"><?php _e('VNĐ quy đổi sang tiền','devvn-ghtk')?></label></th>
                    <td>
                        <select name="<?php echo $this->_optionName?>[vnd2usd_currency]" id="vnd2usd_currency">
                            <?php
                            $paypal_supported_currencies = array(
                                'AUD',
                                'BRL',
                                'CAD',
                                'MXN',
                                'NZD',
                                'HKD',
                                'SGD',
                                'USD',
                                'EUR',
                                'JPY',
                                'TRY',
                                'NOK',
                                'CZK',
                                'DKK',
                                'HUF',
                                'ILS',
                                'MYR',
                                'PHP',
                                'PLN',
                                'SEK',
                                'CHF',
                                'TWD',
                                'THB',
                                'GBP',
                                'RMB',
                                'RUB'
                            );
                            foreach ( $paypal_supported_currencies as $currency ) {
                                if ( strtoupper( $currency ) == $flra_options['vnd2usd_currency'] ) {
                                    printf( '<option selected="selected" value="%1$s">%1$s</option>', $currency );
                                } else {
                                    printf( '<option value="%1$s">%1$s</option>', $currency );
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="vnd_usd_rate"><?php _e('Số quy đổi','devvn-ghtk')?></label></th>
                    <td>
                        <input type="number" min="0" name="<?php echo $this->_optionName?>[vnd_usd_rate]" value="<?php echo $flra_options['vnd_usd_rate'];?>" id="vnd_usd_rate"/> <br>
                        <small><?php _e('Tỷ giá quy đổi từ VNĐ','devvn-ghtk')?></small>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="active_orderstyle"><?php _e('Thay đổi giao diện trang đơn hàng','devvn-ghtk')?></label></th>
                    <td>
                        <label><input type="checkbox" name="<?php echo $this->_optionName?>[active_orderstyle]" <?php checked('1',$flra_options['active_orderstyle'])?> value="1" /> <?php _e('Thay đổi giao diện trang danh sách đơn hàng','devvn-ghtk')?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="alepay_support"><?php _e('Alepay','devvn-ghtk')?></label></th>
                    <td>
                        <label><input type="checkbox" name="<?php echo $this->_optionName?>[alepay_support]" <?php checked('1',$flra_options['alepay_support'])?> value="1" /> <?php _e('Hỗ trợ thanh toán qua Alepay','devvn-ghtk')?></label>
                        <br><small>Để thanh toán qua Alepay bắt buộc phải có first_name và country. Để tải plugin Alepay hãy đăng ký với Alepay và họ sẽ cung cấp Plugin</small>
                    </td>
                </tr>
                <tr>
                    <th scope="row"></th>
                    <td>
                        <label><input type="checkbox" name="<?php echo $this->_optionName?>[enable_postcode]" <?php checked('1',$flra_options['enable_postcode'])?> value="1" /> <?php _e('Hiện trường Postcode','devvn-ghtk')?></label>
                        <br><small>Nếu sử dụng kiểu thanh toán "Tokenization" của Alepay thì bắt buộc cần Postcode.</small>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="active_orderstyle"><?php _e('Xưng hô','devvn-ghtk')?></label></th>
                    <td>
                        <label><input type="checkbox" name="<?php echo $this->_optionName?>[enable_gender]" <?php checked('1',$flra_options['enable_gender'])?> value="1" /> <?php _e('Hiển thị mục chọn cách xưng hô','devvn-ghtk')?></label>
                    </td>
                </tr>
                <?php do_settings_fields($this->_optionGroup, 'default'); ?>
			</tbody>
		</table>
        <h2>Cài đặt thông tin cửa hàng</h2>
        <table class="form-table infor-shop">
            <tbody>

                <tr>
                    <th scope="row"><label for="token_key"><?php _e('Token Key','devvn-ghtk')?><span class="devvn_require">*</span></label></th>
                    <td>
                        <input type="text" name="<?php echo $this->_optionName?>[token_key]" value="<?php echo $flra_options['token_key'];?>" id="token_key"/> <br>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="mashop"><?php _e('Mã SHOP','devvn-ghtk')?></label></th>
                    <td>
                        <input type="text" name="<?php echo $this->_optionName?>[mashop]" value="<?php echo $flra_options['mashop'];?>" id="mashop"/> <br>
                        <small>Hãy đăng nhập vào tài khoản GHTK của bạn sẽ thấy mã shop của bạn ngay chỗ Xin chào! XXXXX .Ví dụ mã shop: S611269</small>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="is_freeship"><?php _e('Miễn phí vận chuyển','devvn-ghtk')?></label></th>
                    <td>
                        <label><input type="checkbox" name="<?php echo $this->_optionName?>[is_freeship]" <?php checked('1',$flra_options['is_freeship'])?> value="1" /> <?php _e('Freeship cho người nhận hàng.','devvn-ghtk')?></label><br>
                        <small>Nếu tích chọn COD sẽ chỉ thu người nhận hàng bằng giá trị hàng, nếu không chọn COD sẽ thu tiền người nhận số tiền bằng giá trị đơn hàng + phí ship của đơn hàng</small>
                    </td>
                </tr>
                <tr style="display: none">
                    <th scope="row"><label for="transport"><?php _e('Hình thức vận chuyển','devvn-ghtk')?></label></th>
                    <td>
                        <label><input type="radio" name="<?php echo $this->_optionName?>[transport]" <?php checked('road',$flra_options['transport'])?> value="road" /> <?php _e('Đường bộ.','devvn-ghtk')?></label><br>
                        <label><input type="radio" name="<?php echo $this->_optionName?>[transport]" <?php checked('fly',$flra_options['transport'])?> value="fly" /> <?php _e('Đường bay.','devvn-ghtk')?></label><br>
                        <small>Nếu tích chọn thì phí ship sẽ được tính theo đường bay. Còn không thì hàng sẽ được vận chuyển bằng đường bộ.<br> Để đường bộ thì phí ship sẽ rẻ hơn.</small>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="is_sandbox"><?php _e('Sandbox','devvn-ghtk')?></label></th>
                    <td>
                        <label><input type="checkbox" name="<?php echo $this->_optionName?>[is_sandbox]" <?php checked('1',$flra_options['is_sandbox'])?> value="1" /> <?php _e('Chế độ TEST','devvn-ghtk')?></label><br>
                        <small>Chế độ thử nghiệm. Đơn hàng sẽ không được xử lý. Đăng ký tại https://dev.ghtk.vn</small>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style=" padding: 0; ">
                        <h2>Địa chỉ lấy hàng</h2>
                        <table class="list_shop_address">
                            <tfoot>
                                <tr>
                                    <td colspan="4">
                                        <button class="button ghtk_add_hubs" type="button">Thêm cửa hàng/kho</button>
                                    </td>
                                </tr>
                            </tfoot>
                            <tbody class="tbody_hubs">
                                <?php
                                $shop_store = isset($flra_options['shop_store']) ? $flra_options['shop_store'] : array();
                                if($shop_store && !empty($shop_store)):
                                    foreach ($shop_store as $stt=>$shop_address):
                                    $pick_ismain = isset($shop_address['pick_ismain']) ? $shop_address['pick_ismain'] : 0;
                                    $pick_cities = isset($shop_address['pick_cities']) ? (array) $shop_address['pick_cities'] : array();
                                    ?>
                                    <tr class="ghtk_hubs_tr">
                                        <td>
                                            <fieldset>
                                                <legend><?php _e( 'Kho hàng', 'devvn-ghtk' );?> <span class="hubs_stt"><?php echo $stt + 1;?></span> <a href="javascript:void(0);" class="ghtk_delete_hubs">Xóa</a></legend>
                                                <table>
                                                    <tbody>
                                                    <tr>
                                                        <td>
                                                            <label for="shop_store[<?php echo $stt;?>]pick_address_id"><?php _e('Mã kho trên GHTK','devvn-ghtk')?></label><br>
                                                            <input type="text" name="<?php echo $this->_optionName?>[shop_store][<?php echo $stt;?>][pick_address_id]" value="<?php echo $shop_address['pick_address_id'];?>" id="shop_store[<?php echo $stt;?>]pick_address_id"/> <br>
                                                            <small><?php _e('Mã kho trên hệ thống của GHTK - sẽ ưu tiên chọn nếu điền mã kho tại đây.','devvn-ghtk')?></small>
                                                        </td>
                                                        <td>
                                                            <label for="shop_store[<?php echo $stt;?>]pick_name"><?php _e('Tên chủ SHOP ','devvn-ghtk')?><span class="devvn_require">*</span></label><br>
                                                            <input type="text" name="<?php echo $this->_optionName?>[shop_store][<?php echo $stt;?>][pick_name]" value="<?php echo isset($shop_address['pick_name']) ? $shop_address['pick_name'] : '';?>" id="shop_store[0]pick_name"/> <br>
                                                            <small><?php _e('String - Tên người liên hệ lấy hàng hóa','devvn-ghtk')?></small>
                                                        </td>

                                                        <td>
                                                            <label for="shop_store[<?php echo $stt;?>]pick_email"><?php _e('Email shop','devvn-ghtk')?></label><br>
                                                            <input type="text" name="<?php echo $this->_optionName?>[shop_store][<?php echo $stt;?>][pick_email]" value="<?php echo isset($shop_address['pick_email']) ? $shop_address['pick_email'] : '';?>" id="shop_store[0]pick_email"/> <br>
                                                            <small><?php _e('String - Email liên hệ nơi lấy hàng hóa','devvn-ghtk')?></small>
                                                        </td>
                                                        <td>
                                                            <label for="shop_store[<?php echo $stt;?>]pick_tel"><?php _e('Số điện thoại SHOP','devvn-ghtk')?><span class="devvn_require">*</span></label><br>
                                                            <input type="text" name="<?php echo $this->_optionName?>[shop_store][<?php echo $stt;?>][pick_tel]" value="<?php echo isset($shop_address['pick_tel']) ? $shop_address['pick_tel'] : '';?>" id="shop_store[0]pick_tel"/> <br>
                                                            <small><?php _e('String - Số điện thoại liên hệ nơi lấy hàng hóa','devvn-ghtk')?></small>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <label for="shop_store[<?php echo $stt;?>]pick_province"><?php _e('Tỉnh thành','devvn-ghtk')?><span class="devvn_require">*</span></label><br>
                                                            <select name="<?php echo $this->_optionName?>[shop_store][<?php echo $stt;?>][pick_province]" id="shop_store[<?php echo $stt;?>]pick_province" class="devvn_pick_province">
                                                                <option value=""><?php _e('Chọn tỉnh/thành phố','devvn-ghtk')?></option>
                                                                <?php if($this->tinh_thanhpho && is_array($this->tinh_thanhpho)):?>
                                                                    <?php foreach($this->tinh_thanhpho as $k=>$v):?>
                                                                        <option value="<?php echo $k;?>" <?php echo selected($k,$shop_address['pick_province'])?>><?php echo $v;?></option>
                                                                    <?php endforeach;?>
                                                                <?php endif;?>
                                                            </select><br>
                                                            <small><?php _e('Tỉnh/thành phố nơi lấy hàng hóa','devvn-ghtk')?></small>
                                                        </td>
                                                        <td>
                                                            <label for="shop_store[<?php echo $stt;?>]pick_district"><?php _e('Quận/huyện','devvn-ghtk')?><span class="devvn_require">*</span></label><br>
                                                            <?php
                                                            $district = $this->get_list_district_select($shop_address['pick_province']);
                                                            ?>
                                                            <select name="<?php echo $this->_optionName?>[shop_store][<?php echo $stt;?>][pick_district]" id="shop_store[<?php echo $stt;?>]pick_district" class="devvn_pick_district">
                                                                <option value=""><?php _e('Chọn quận/huyện','devvn-ghtk')?></option>
                                                                <?php if($district && is_array($district)):?>
                                                                    <?php foreach($district as $k=>$v):?>
                                                                        <option value="<?php echo $k;?>" <?php echo selected($k,$shop_address['pick_district'])?>><?php echo $v;?></option>
                                                                    <?php endforeach;?>
                                                                <?php endif;?>
                                                            </select><br>
                                                            <small><?php _e('Quận/huyện nơi lấy hàng hóa','devvn-ghtk')?></small>
                                                        </td>
                                                        <td>
                                                            <label for="shop_store[<?php echo $stt;?>]pick_ward"><?php _e('Xã/Phường/thị trấn','devvn-ghtk')?></label><br>
                                                            <?php
                                                            $villages = $this->get_list_village_select($shop_address['pick_district']);
                                                            ?>
                                                            <select name="<?php echo $this->_optionName?>[shop_store][<?php echo $stt;?>][pick_ward]" id="shop_store[<?php echo $stt;?>]pick_ward" class="devvn_pick_ward">
                                                                <option value=""><?php _e('Chọn phường/xã','devvn-ghtk')?></option>
                                                                <?php if($villages && is_array($villages)):?>
                                                                    <?php foreach($villages as $k=>$v):?>
                                                                        <option value="<?php echo $k;?>" <?php echo selected($k,$shop_address['pick_ward'])?>><?php echo $v;?></option>
                                                                    <?php endforeach;?>
                                                                <?php endif;?>
                                                            </select><br>
                                                            <small><?php _e('Phường/xã nơi lấy hàng hóa','devvn-ghtk')?></small>
                                                        </td>
                                                        <td>
                                                            <label for="shop_store[<?php echo $stt;?>]pick_street"><?php _e('Tên đường/phố','devvn-ghtk')?></label><br>
                                                            <input type="text" name="<?php echo $this->_optionName?>[shop_store][<?php echo $stt;?>][pick_street]" value="<?php echo isset($shop_address['pick_street']) ? $shop_address['pick_street'] : '';?>" id="shop_store[<?php echo $stt;?>]pick_street"/> <br>
                                                            <small><?php _e('String - Tên đường/phố nơi lấy hàng hóa','devvn-ghtk')?></small>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td colspan="4">
                                                            <label for="shop_store[<?php echo $stt;?>]pick_address"><?php _e('Địa chỉ','devvn-ghtk')?><span class="devvn_require">*</span></label><br>
                                                            <input type="text" name="<?php echo $this->_optionName?>[shop_store][<?php echo $stt;?>][pick_address]" value="<?php echo isset($shop_address['pick_address']) ? $shop_address['pick_address'] : '';?>" id="shop_store[<?php echo $stt;?>]pick_address"/> <br>
                                                            <small><?php _e('String - Địa chỉ ngắn gọn để lấy nhận hàng hóa. Ví dụ: nhà số 5, tổ 3, ngách 11, ngõ 45','devvn-ghtk')?></small>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="4">
                                                            <label><input type="checkbox" class="pick_ismain_onlyone" onclick="selectOnlyThis(this)" name="<?php echo $this->_optionName?>[shop_store][<?php echo $stt;?>][pick_ismain]" value="1" <?php checked(1, $pick_ismain, true);?>/> <?php _e('Cửa hàng/kho chính','devvn-ghtk');?></label>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="4">
                                                            <label for="shop_store[<?php echo $stt;?>]pick_cities"><?php _e('Khu vực bán hàng','devvn-ghtk')?></label><br>

                                                            <?php if($this->tinh_thanhpho && is_array($this->tinh_thanhpho)):?>
                                                            <div class="khuvuc_banhang">
                                                                <?php foreach($this->tinh_thanhpho as $k=>$v):?>
                                                                    <label>
                                                                    <input type="checkbox" value="<?php echo $k;?>" name="<?php echo $this->_optionName?>[shop_store][<?php echo $stt;?>][pick_cities][]"  <?php echo (in_array($k,$pick_cities)) ? 'checked="checked"' : '';?>/> <?php echo $v;?>
                                                                    </label>
                                                                <?php endforeach;?>
                                                            </div>
                                                            <?php endif;?>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </fieldset>
                                        </td>
                                    </tr>
                                    <?php endforeach;?>
                                <?php endif;?>
                            </tbody>
                        </table>
                    </td>
                </tr>

            </tbody>
        </table>
        <script type="text/template" id="tmpl-ghtk-hubs-template">
            <tr class="ghtk_hubs_tr" id="ghtk_hubs_{{{data.id}}}">
                <td>
                    <fieldset>
                        <legend><?php _e( 'Kho hàng', 'devvn-ghtk' ); ?> <span class="hubs_stt">{{{data.id + 1}}}</span> <a href="javascript:void(0);" class="ghtk_delete_hubs">Xóa</a> </legend>
                        <table>
                            <tbody>
                            <tr>
                                <td>
                                    <label for="shop_store[{{{data.id}}}]pick_address_id"><?php _e('Mã kho trên GHTK','devvn-ghtk')?></label><br>
                                    <input type="text" name="<?php echo $this->_optionName?>[shop_store][{{{data.id}}}][pick_address_id]" value="" id="shop_store[{{{data.id}}}]pick_address_id"/> <br>
                                    <small><?php _e('Mã kho trên hệ thống của GHTK - sẽ ưu tiên chọn nếu điền mã kho tại đây.','devvn-ghtk')?></small>
                                </td>
                                <td>
                                    <label for="shop_store[{{{data.id}}}]pick_name"><?php _e('Tên chủ SHOP ','devvn-ghtk')?><span class="devvn_require">*</span></label><br>
                                    <input type="text" name="<?php echo $this->_optionName?>[shop_store][{{{data.id}}}][pick_name]" value="" id="shop_store[{{{data.id}}}]pick_name"/> <br>
                                    <small><?php _e('String - Tên người liên hệ lấy hàng hóa','devvn-ghtk')?></small>
                                </td>

                                <td>
                                    <label for="shop_store[{{{data.id}}}]pick_email"><?php _e('Email shop','devvn-ghtk')?></label><br>
                                    <input type="text" name="<?php echo $this->_optionName?>[shop_store][{{{data.id}}}][pick_email]" value="" id="shop_store[{{{data.id}}}]pick_email"/> <br>
                                    <small><?php _e('String - Email liên hệ nơi lấy hàng hóa','devvn-ghtk')?></small>
                                </td>
                                <td>
                                    <label for="shop_store[{{{data.id}}}]pick_tel"><?php _e('Số điện thoại SHOP','devvn-ghtk')?><span class="devvn_require">*</span></label><br>
                                    <input type="text" name="<?php echo $this->_optionName?>[shop_store][{{{data.id}}}][pick_tel]" value="" id="shop_store[{{{data.id}}}]pick_tel"/> <br>
                                    <small><?php _e('String - Số điện thoại liên hệ nơi lấy hàng hóa','devvn-ghtk')?></small>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <label for="shop_store[{{{data.id}}}]pick_province"><?php _e('Tỉnh thành','devvn-ghtk')?><span class="devvn_require">*</span></label><br>
                                    <select name="<?php echo $this->_optionName?>[shop_store][{{{data.id}}}][pick_province]" id="shop_store[{{{data.id}}}]pick_province" class="devvn_pick_province">
                                        <option value=""><?php _e('Chọn tỉnh/thành phố','devvn-ghtk')?></option>
                                        <?php if($this->tinh_thanhpho && is_array($this->tinh_thanhpho)):?>
                                            <?php foreach($this->tinh_thanhpho as $k=>$v):?>
                                                <option value="<?php echo $k;?>"><?php echo $v;?></option>
                                            <?php endforeach;?>
                                        <?php endif;?>
                                    </select><br>
                                    <small><?php _e('Tỉnh/thành phố nơi lấy hàng hóa','devvn-ghtk')?></small>
                                </td>
                                <td>
                                    <label for="shop_store[{{{data.id}}}]pick_district"><?php _e('Quận/huyện','devvn-ghtk')?><span class="devvn_require">*</span></label><br>
                                    <select name="<?php echo $this->_optionName?>[shop_store][{{{data.id}}}][pick_district]" id="shop_store[{{{data.id}}}]pick_district" class="devvn_pick_district">
                                        <option value=""><?php _e('Chọn quận/huyện','devvn-ghtk')?></option>
                                    </select><br>
                                    <small><?php _e('Quận/huyện nơi lấy hàng hóa','devvn-ghtk')?></small>
                                </td>
                                <td>
                                    <label for="shop_store[{{{data.id}}}]pick_ward"><?php _e('Xã/Phường/thị trấn','devvn-ghtk')?></label><br>
                                    <select name="<?php echo $this->_optionName?>[shop_store][{{{data.id}}}][pick_ward]" id="shop_store[{{{data.id}}}]pick_ward" class="devvn_pick_ward">
                                        <option value=""><?php _e('Chọn phường/xã','devvn-ghtk')?></option>
                                    </select><br>
                                    <small><?php _e('Phường/xã nơi lấy hàng hóa','devvn-ghtk')?></small>
                                </td>
                                <td>
                                    <label for="shop_store[{{{data.id}}}]pick_street"><?php _e('Tên đường/phố','devvn-ghtk')?></label><br>
                                    <input type="text" name="<?php echo $this->_optionName?>[shop_store][{{{data.id}}}][pick_street]" value="" id="shop_store[{{{data.id}}}]pick_street"/> <br>
                                    <small><?php _e('String - Tên đường/phố nơi lấy hàng hóa','devvn-ghtk')?></small>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="4">
                                    <label for="shop_store[{{{data.id}}}]pick_address"><?php _e('Địa chỉ','devvn-ghtk')?><span class="devvn_require">*</span></label><br>
                                    <input type="text" name="<?php echo $this->_optionName?>[shop_store][{{{data.id}}}][pick_address]" value="" id="shop_store[{{{data.id}}}]pick_address"/> <br>
                                    <small><?php _e('String - Địa chỉ ngắn gọn để lấy nhận hàng hóa. Ví dụ: nhà số 5, tổ 3, ngách 11, ngõ 45','devvn-ghtk')?></small>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    <label><input type="checkbox" class="pick_ismain_onlyone" onclick="selectOnlyThis(this)" name="<?php echo $this->_optionName?>[shop_store][{{{data.id}}}][pick_ismain]" value="1" /> <?php _e('Cửa hàng/kho chính','devvn-ghtk');?></label>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    <label for="shop_store[{{{data.id}}}]pick_cities"><?php _e('Khu vực bán hàng','devvn-ghtk')?></label><br>

                                    <?php if($this->tinh_thanhpho && is_array($this->tinh_thanhpho)):?>
                                        <div class="khuvuc_banhang">
                                            <?php foreach($this->tinh_thanhpho as $k=>$v):?>
                                                <label>
                                                    <input type="checkbox" value="<?php echo $k;?>" name="<?php echo $this->_optionName?>[shop_store][{{{data.id}}}][pick_cities][]"/> <?php echo $v;?>
                                                </label>
                                            <?php endforeach;?>
                                        </div>
                                    <?php endif;?>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </fieldset>
                </td>
            </tr>
        </script>
        <?php
        $listURL = ghtk_api()->get_list_url();
        if($listURL && is_array($listURL) && $listURL['success']):
        ?>
        <h2 id="webhook">Webhook</h2>
        <small>Có thể đã thêm hoặc xóa URL thành công mà vẫn hiện trong list lý do vì có cache bên API.</small>
        <table class="form-table devvn_table devvn_table_border devvn_table_listurl">
            <thead>
                <tr>
                    <td class="_listurl_stt">STT</td>
                    <td class="_listurl_link">Đường dẫn</td>
                    <td class="_listurl_action"></td>
                </tr>
            </thead>
            <?php
            if($listURL['data']):
            $listURLA = explode(',',$listURL['data']);
            ?>
            <tbody>
                <?php $stt = 1; foreach ($listURLA as $url):?>
                <tr>
                    <td><?php echo $stt;?></td>
                    <td><?php echo $url;?></td>
                    <td><button type="button" class="ghtk_delete_url button" data-url="<?php echo esc_attr($url);?>">Xóa URL</button></td>
                </tr>
                <?php $stt++; endforeach;?>
            </tbody>
            <?php endif;?>
            <tfoot>
                <tr>
                    <td colspan="3">
                        <button class="button ghtk_add_url" type="button">Thêm URL</button>
                        <?php
                        $ghtk_hash = $flra_options['ghtk_hash'];
                        if($ghtk_hash):
                        ?>
                        <input type="hidden" value="<?php echo sanitize_text_field($ghtk_hash);?>" name="<?php echo $this->_optionName?>[ghtk_hash]" id="ghtk_hash"/>
                        <?php else:?>
                        <input type="hidden" value="<?php echo wp_generate_password(24, false)?>" name="<?php echo $this->_optionName?>[ghtk_hash]" id="ghtk_hash"/>
                        <?php endif;?>
                    </td>
                </tr>
            </tfoot>
        </table>
        <?php endif;?>
        <h2>Cài đặt in hóa đơn</h2>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="print_logo"><?php _e('Logo','devvn-ghtk')?></label></th>
                    <td>
                        <?php $image_ID = $flra_options['print_logo'];?>
                        <div class="svl-upload-image <?php if($image_ID):?>has-image<?php endif;?>">
                            <div class="view-has-value">
                                <input type="hidden" name="<?php echo $this->_optionName?>[print_logo]" class="regular-text" id="image" value="<?php echo $image_ID?>"/>
                                <img src="<?php echo ($image_ID)?wp_get_attachment_image_url($image_ID,'full'):'';?>" class="image_view pins_img"/>
                                <a href="#" class="svl-delete-image">x</a>
                            </div>
                            <div class="hidden-has-value"><input type="button" class="ghtk-upload button" value="<?php _e( 'Select images', 'devvn' )?>" /></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="print_note"><?php _e('Ghi chú','devvn-ghtk')?></label></th>
                    <td>
                        <textarea rows="5" name="<?php echo $this->_optionName?>[print_note]" id="print_note"><?php echo $flra_options['print_note']?></textarea><br>
                        <small>Ghi chú sau phần giá thu người nhận.</small>
                    </td>
                </tr>
            </tbody>
        </table>
        <h2>Cài đặt thông báo cho khách hàng khi đăng đơn thành công.</h2>
        <p><small>
                Sử dụng {site_title} để hiển thị tiêu đề website<br>
                {ship_id} để hiển thị mã vận đơn bên ghtk<br>
                {order_id} để hiển thị mã đơn hàng trên web của bạn<br>
                {estimated_deliver} để hiển thị ngày dự kiến giao hàng<br>
            </small></p>
        <table class="form-table infor-shop">
            <tbody>
                <tr>
                    <th scope="row"><label for="send_shipid_active"><?php _e('Kích hoạt','devvn-ghtk')?></label></th>
                    <td>
                        <label><input type="checkbox" name="<?php echo $this->_optionName?>[send_shipid_active]" <?php checked('1',$flra_options['send_shipid_active'])?> value="1" /> <?php _e('Kích hoạt','devvn-ghtk')?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="send_shipid_title"><?php _e('Tiêu đề mail','devvn-ghtk')?></label></th>
                    <td>
                        <input type="text" name="<?php echo $this->_optionName?>[send_shipid_title]" value="<?php echo $flra_options['send_shipid_title'];?>" id="send_shipid_title"/> <br>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="send_shipid_content">Nội dung email</label></th>
                    <td>
                        <?php
                        $settings = array(
                            'textarea_name' => $this->_optionName.'[send_shipid_content]',
                        );
                        wp_editor( $flra_options['send_shipid_content'], 'send_shipid_content', $settings );?>
                    </td>
                </tr>
            </tbody>
        </table>
        <h2>Auto update</h2>
        <p><small>Điền key để tự động update.</small></p>
        <table class="form-table infor-shop">
            <tbody>
                <tr>
                    <th scope="row"><label for="license_key"><?php _e('License Key','devvn-ghtk')?></label></th>
                    <td>
                        <input type="text" name="<?php echo $this->_optionName?>[license_key]" value="<?php echo $flra_options['license_key'];?>" id="license_key"/> <br>
                    </td>
                </tr>
            </tbody>
        </table>
		<?php do_settings_sections($this->_optionGroup, 'default'); ?>
		<?php submit_button();?>
	</form>	
</div>