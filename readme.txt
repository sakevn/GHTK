== Changelog ==

Thông tin thêm [về plugin này](https://doibu.com/san-pham/plugin-ket-noi-giao-hang-tiet-kiem-voi-woocommerce-ghtk-vs-woocommerce/).

= 1.3.4 - xx.xx.2019 =

* Thêm action devvn_invoice_after_shop_address, devvn_invoice_after_customer_address
* Thêm filter devvn_invoice_order_ghtk_full, devvn_invoice_order_ghtk_fullinfor
* Không tự động bật bàn phím khi ấn vào chọn tỉnh thành trên mobile

+++++ Mở rộng: Thêm người tạo đơn vào hóa đơn ++++++
add_action('woocommerce_order_status_changed', 'devvn_set_author_order', 10, 3);
function devvn_set_author_order($order_id, $from, $to){
    if($to == 'completed'){
        $current_user = wp_get_current_user();
        update_post_meta($order_id, 'devvn_author_order', $current_user->ID);
    }
}

add_action('devvn_invoice_after_shop_address', 'devvn_invoice_after_shop_address_author_order');
function devvn_invoice_after_shop_address_author_order($order){
    $devvn_author_order = get_post_meta($order->get_id(), 'devvn_author_order', true);
    if($devvn_author_order) {
        $user = get_user_by('id', $devvn_author_order);
        if($user && !is_wp_error($user)) {
            printf(__('<br>Người tạo đơn: %s<br>', 'devvn'), $user->display_name);
        }
    }
}

= 1.3.3 - 05.12.2019 =

* Thêm mục chọn cách xưng hô (Anh, Chị) trong trang checkout - Optional
* Fix: lỗi khi chọn freeship trong cài đặt thì giá trị thu hộ lúc đăng đơn bị sai lúc ban đầu load
* Thêm mục checkbox "Khách đã chuyển khoản thu hộ = 0". Nếu được chọn thì tiền thu hộ tự đồng = 0
* Loại bỏ hình thức vận chuyển theo đường bay nếu tỉnh thành của khách mua hàng trùng với kho của shop
* Thêm lựa chọn tỉnh thành > quận huyện > phường xã khi xem profile của thành viên trong admin
* Thêm lựa chọn tỉnh thành > quận huyện > phường xã vào địa chỉ cửa hàng tại Woocomerce > setting > general > Store Address
* Thêm khổ in cho máy in nhiệt khổ giấy 80mm
* Fix lỗi khi chọn địa chỉ tự động điền sẽ bị lỗi không load quận/huyện theo tỉnh/thành phố
* Thêm dấu tích màu vàng vào button "In hóa đơn theo mẫu riêng" nếu đơn đó đã được in
* Thêm {estimated_deliver} để hiển thị ngày dự kiến giao hàng trong email gửi cho khách hàng khi tạo vận đơn

= 1.3.2 - 20.11.2019 =

* Sửa lỗi nhân đôi thông tin đăng đơn tại danh sách sản phẩm
* Thêm kiểu in đơn hàng: In theo chiều dọc khổ A6 - Phù hợp với máy in nhiệt theo cuộn
* Sửa lỗi load địa chỉ với 1 số web cài bảo mật cao không cho thực thi trực tiếp file .php từ bên ngoài
* Tinh chỉnh lại style

= 1.3.1 - 16.11.2019 =

* Thêm chức năng đăng đơn hàng lên GHTK bên ngoài list đơn hàng trong admin
* In cùng lúc nhiều đơn hàng theo mẫu riêng
* Tối ưu lại danh sách đơn hàng trong admin. Thêm list sản phẩm bên ngoài danh sách đơn hàng.
* Thay đổi bố cục lúc tạo đơn hàng trực quan và dễ nhìn hơn.
* Đồng bộ trạng thái từ GHTK "Đã giao hàng/Chưa đối soát" sang "Đã hoàn thành" ở Woo

= 1.3.0 - 14.11.2019 =

* Chọn xong quận huyện mới bắt đầu tính phí ship, Chọn tỉnh thành và xã phường sẽ không tính phí ship nữa. Tránh mất thời gian khi checkout
* Đổi First name thành Họ và tên, trước đây là Last name. (Đồng bộ với 1 số phần mềm khác)
* Cải thiện tốc độ tải địa chỉ tỉnh thành, quận huyện và xã phường lên 100 lần so với bản cũ
* Thêm filter devvn_ghtk_shipping_methob để tùy chỉnh lại thứ tự và hình thức giao hàng

= 1.2.9 - 14.08.2019 =

* Update: Sửa lỗi với phiên bản woocommerce 3.7.0

= 1.2.8 - 07.08.2019 =

* Update: Sửa lỗi không nhận mã vận đơn khi in bằng mẫu riêng

= 1.2.7 - 12.06.2019 =

* Update: Thêm các khu ở huyện Côn Đảo
* Update: Sắp xếp các tên tỉnh thành/quận huyện/ xã phường theo chữ cái A-Z

= 1.2.6 - 26.05.2019 =

* Fix: Lỗi hiển thị thuộc tính sản phẩm khi đăng lên GHTK ở tên SP
* Add: Thêm ô nhập giá trị hàng hóa khi đăng đơn. Cái này để thay đổi giá trị khi không muốn đóng bảo hiểm vận đơn.
* Update: Đặt mặc định chú ý đơn hàng của khách vào chỗ ghi chú cho GHTK khi đăng đơn. Thấy nhiều người cần nên để mặc định luôn.
* Update: Đổi lại thư viện popup sang Magnific-Popup [https://github.com/dimsemenov/Magnific-Popup] do nội dung đăng đơn dài cái cũ không còn phù hợp nữa

= 1.2.5 - 03.04.2019 =

* Fix: Sửa lỗi nhận với 1 số trường hơp bị sai mã vạch khi in với mẫu riêng

= 1.2.4.1 - 08.03.2019 =

* Fix: Sửa nhanh 1 lỗi ko nhận tên SHOP khi đăng đơn trong bản 1.2.4

= 1.2.4 - 08.03.2019 =

* Add: Thêm lựa chọn hình thức vận chuyển khi đăng đơn
* Add: Thêm lựa chọn gửi hàng tại điểm khi đăng đơn
* Add: Thêm lựa chọn hình thức vận chuyển: đường bộ - đường bay ở trang checkout
* Update: Trạng thái hoàn trả hàng của GHTK -> Tạm giữ trên Woocommerce

= 1.2.3 - 16.01.2019 =

* Fix: Chỉnh lại phần khối lượng của 1 sp trước khi đăng đơn lên GHTK khi số lượng sản phẩm > 1 sp

= 1.2.2 - 17.11.2018 =

* Update: Update tương thích với thay đổi của API mới bên GHTK
* Fix: Chỉnh lại định dạng địa chỉ kho hàng khi có mã kho hàng

= 1.2.1 - 13.11.2018 =

* Add: Chế độ sandbox - Hoạt động ở môi trường test. Đơn hàng sẽ không được thực thi ở chế độ này
* Fix: Fix nhanh lỗi hiển thị địa chỉ ở bản update 1.2.0
* Fix: Định dạng mặc định khối lượng về KG khi đăng đơn hàng

= 1.2.0 - 06.11.2018 =

* Add: Lựa chọn hình thức vận chuyển đường bộ (road) hoặc đường bay (fly)
* Update: Tương thích với Woocommerce 3.5.1

= 1.1.9 - 10.10.2018 =

* Add: Thêm chức năng gửi mã vận đơn cho khách hàng khi đã đăng đơn thành công lên GHTK

= 1.1.8 - 13.09.2018 =

* Fix: Sửa định dạng của webhook URL
* Fix: Thay đổi kho giao hàng khi đăng đơn lên GHTK

= 1.1.6 - 07.07.2018 =

* Add: Có thể thêm nhiều kho hàng, lựa chọn cửa hàng/kho giao hàng khi đăng đơn
* Add: Có thể lựa chọn khu vực bán hàng cho cửa hàng/kho để giảm chi phí khi tính phí ship và đăng đơn lên GHTK
* Update: Sắp xếp tỉnh thành theo thứ tự A-Z và đưa Hà Nội và Hồ Chí Minh lên đầu

= 1.1.5 - 14.06.2018 =

* Tracking đơn hàng ngay trên website. Sử dụng shortcode [ghtk_tracking_form] để hiển thị form tracking
  - Để có thể tracking cần có mã shop và token.
  - Mã shop thêm tại mục sau Setting/Cài đặt GHTK -> Cài đặt thông tin cửa hàng -> Mã SHOP

= 1.1.4 - 02.05.2018 =

* Add: Hỗ trợ plugin Point Of Sale
* Update: Lưu trạng thái đơn hàng bằng ajax

= 1.1.3 - 15.04.2018 =

* FIX: Sửa lỗi khi check vào Shop trả tiền ship nhưng vẫn tính cho khách trả
* ADD: Thêm mã ghtk ra ngoài trang toàn bộ đơn hàng

= 1.1.2 - 03.04.2018 =

* FIX: Sửa lỗi không hiển thị trường first_name khi kích hoạt hỗ trợ thanh toán qua Alepay

= 1.1.1 - 30.03.2018 =

* Update: Sửa lỗi ko hiển thị attribute ở tên sản phẩm khi in hóa đơn với sản phẩm được thêm vào sau khi khách đã đặt hàng.

= 1.1.0 - 22.03.2018 =

* Fix: Thêm thuộc tính vào tên sản phẩm khi in hóa đơn theo mẫu riêng

= 1.0.9 - 20.03.2018 =

* Add: In hóa đơn theo mẫu riêng của shop

= 1.0.8 - 13.03.2018 =

* Update: Thay đổi thông báo khi chưa điền đầy đủ thông tin như: tỉnh thành phố, quận huyện.
* Update: Hiển thị tên của tỉnh/thành phố, quận huyện và xã phường thị trấn trong APP IOS của Woocommerce

= 1.0.7 - 12.03.2018 =

* Update: Thêm js ở phần tính phí vận chuyển tại trang giỏ hàng để phù hợp với 1 số theme

= 1.0.6 - 10.03.2018 =

* Update: Thay đổi trạng thái đơn hàng khi đã đối soát -> đơn hàng về đã hoàn thành

= 1.0.5 - 08.03.2018 =

* Update: Có thể lựa chọn quận huyện và tính phí vận chuyển ngay trên trang giỏ hàng.
* Update: 1 số css

= 1.0.4 - 06.03.2018 =

* ADD: Tự động update plugin thông qua license
* Fix: Sửa lỗi khi ẩn field xã phường ở bản 1.0.3

= 1.0.3 - 27.02.2018 =

* Fix: Sửa tổng giá trị đơn hàng gửi lên GHTK khi có mã giảm giá
* Fix: Cập nhật tình trạng đơn hàng bằng Webhook

= 1.0.2 - 09.02.2018 =

* Add: Thêm bộ lọc đơn hàng theo tỉnh thành
* Update: Support cổng thanh toán Alepay (Setting -> Cài đặt GHTK -> Kích hoạt Alepay)
* Update: 99% Tương thích với plugin "WooCommerce Checkout Field Editor (Manager) Pro"
* Update: Tương thích với Woocommerce 3.3.x
* Update: Tương thích với Flatsome
* Update: Tương thích với PHP 7.x.x

= 1.0.1 - 07.02.2018 =

* Tiêu đề sản phẩm kèm theo variation của sản phẩm. Ví dụ Iphone Màu-trắng | Dung lượng – 8G
* Sử dụng webhook để tự động cập nhật tình trạng đơn hàng từ hệ thống của ghtk. Các thiết lập webhook [xem thêm tại đây](https://doibu.com/san-pham/plugin-ket-noi-giao-hang-tiet-kiem-voi-woocommerce-ghtk-vs-woocommerce/#setting-webhook)

= 1.0 - 02.02.2018=

* Ra mắt plugin