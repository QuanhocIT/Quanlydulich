<?php if (!isset($dv)) { return; } ?>

<!-- Modal xác nhận -->
<div class="modal fade" id="xacNhanModal<?php echo $dv['id']; ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận dịch vụ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?act=nhaCungCap/xacNhanBooking">
                <div class="modal-body">
                    <input type="hidden" name="dich_vu_id" value="<?php echo $dv['id']; ?>">
                    <input type="hidden" name="action" value="xac_nhan">
                    <p><strong>Tour:</strong> <?php echo htmlspecialchars($dv['ten_tour'] ?? 'N/A'); ?></p>
                    <p><strong>Dịch vụ:</strong> <?php echo htmlspecialchars($dv['ten_dich_vu']); ?></p>
                    <div class="mb-3">
                        <label class="form-label">Giá tiền (VND)</label>
                        <input type="number" class="form-control" name="gia_tien"
                               value="<?php echo $dv['gia_tien'] ?? ''; ?>" min="0" step="1000" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Xác nhận</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal từ chối -->
<div class="modal fade" id="tuChoiModal<?php echo $dv['id']; ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Từ chối dịch vụ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?act=nhaCungCap/xacNhanBooking">
                <div class="modal-body">
                    <input type="hidden" name="dich_vu_id" value="<?php echo $dv['id']; ?>">
                    <input type="hidden" name="action" value="tu_choi">
                    <p><strong>Tour:</strong> <?php echo htmlspecialchars($dv['ten_tour'] ?? 'N/A'); ?></p>
                    <p><strong>Dịch vụ:</strong> <?php echo htmlspecialchars($dv['ten_dich_vu']); ?></p>
                    <div class="mb-3">
                        <label class="form-label">Lý do từ chối</label>
                        <textarea class="form-control" name="ghi_chu" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Từ chối</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal cập nhật giá -->
<div class="modal fade" id="capNhatGiaModal<?php echo $dv['id']; ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cập nhật giá dịch vụ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?act=nhaCungCap/capNhatGia">
                <div class="modal-body">
                    <input type="hidden" name="dich_vu_id" value="<?php echo $dv['id']; ?>">
                    <p><strong>Tour:</strong> <?php echo htmlspecialchars($dv['ten_tour'] ?? 'N/A'); ?></p>
                    <p><strong>Dịch vụ:</strong> <?php echo htmlspecialchars($dv['ten_dich_vu']); ?></p>
                    <div class="mb-3">
                        <label class="form-label">Giá tiền (VND)</label>
                        <input type="number" class="form-control" name="gia_tien"
                               value="<?php echo $dv['gia_tien'] ?? ''; ?>" min="0" step="1000" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal ghi chú -->
<div class="modal fade" id="ghiChuModal<?php echo $dv['id']; ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ghi chú</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><?php echo nl2br(htmlspecialchars($dv['ghi_chu'] ?? '')); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
