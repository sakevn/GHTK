<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>
<div id="orderInfo_table">
    <table>
        <thead>
        <tr>
            <th colspan="2">Thông tin vận đơn</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>
                Người nhận
            </td>
            <td>
                <span>Họ tên: <?php echo $package['customer_fullname'];?></span><br>
                <span>Số điện thoại: <?php echo $package['customer_tel'];?></span><br>
                <span>Địa chỉ: <?php echo $package['customer_address'];?></span><br>
            </td>
        </tr>
        <tr>
            <td>
                Người gửi
            </td>
            <td>
                <span>Họ tên: <?php echo $package['pick_fullname'];?></span><br>
                <span>Số điện thoại: <?php echo $package['pick_tel'];?></span><br>
                <span>Địa chỉ: <?php echo $package['pick_address'];?></span><br>
            </td>
        </tr>
            <td>
                Trạng thái đơn hàng
            </td>
            <td>
                <strong><?php echo $package['status'];?></strong>
            </td>
        </tr>
        </tbody>
    </table>

    <?php if(!empty($logs) && is_array($logs)):?>
    <table>
        <thead>
            <tr>
                <th colspan="2">Thông tin chi tiết</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($logs as $log):?>
            <tr>
                <td><?php echo $log;?></td>
            </tr>
            <?php endforeach;?>
        </tbody>
    </table>
    <?php endif;?>

</div>
