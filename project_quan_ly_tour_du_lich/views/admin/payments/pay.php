<div class="aventura-content">
    <h2>Chọn phương thức thanh toán</h2>
    <form method="get" action="index.php">
        <input type="hidden" name="act" value="payment/redirect">
        <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking_id) ?>">
        <div style="margin: 30px 0; display: flex; gap: 30px;">
            <label><input type="radio" name="method" value="VNPay" checked> <img src="public/images/vnpay.png" alt="VNPay" style="height:32px;vertical-align:middle;"> VNPay</label>
            <label><input type="radio" name="method" value="Momo"> <img src="public/images/momo.png" alt="Momo" style="height:32px;vertical-align:middle;"> Momo</label>
            <label><input type="radio" name="method" value="Paypal"> <img src="public/images/paypal.png" alt="Paypal" style="height:32px;vertical-align:middle;"> Paypal</label>
        </div>
        <button type="submit" class="aventura-btn aventura-btn-gold"><i class="bi bi-credit-card"></i> Thanh toán ngay</button>
    </form>
</div>
