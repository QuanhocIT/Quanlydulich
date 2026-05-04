# -*- coding: utf-8 -*-
import sys

path = r'c:\laragon\www\Quanlydulich-main\project_quan_ly_tour_du_lich\views\khach_hang\hoa_don.php'
with open(path, encoding='utf-8') as f:
    c = f.read()

# ── 1. Replace the old head block (DOCTYPE → end of hero/flash) ──────────────
OLD_HEAD_START = '<?php\n\n?><!DOCTYPE html>'
# The content section starts right after the flash messages block
CONTENT_MARKER = '        <?php if (isset($booking) && $booking): ?>'

start = c.find(OLD_HEAD_START)
content_pos = c.find(CONTENT_MARKER)

if start < 0 or content_pos < 0:
    print('ERROR: could not find head/content markers')
    sys.exit(1)

NEW_HEAD = r"""<?php
/** @var array|null $booking */
$booking = $booking ?? null;

$pageTitle  = 'Hóa đơn và thanh toán';
$activePage = 'hoaDon';
$pageHero   = [
    'icon'     => 'bi-receipt',
    'title'    => 'Hóa đơn và thanh toán',
    'subtitle' => 'Theo dõi trạng thái giao dịch, xem chi tiết hóa đơn và hoàn tất thanh toán trực tuyến trong cùng một màn hình.',
    'actions'  => [
        ['label' => 'Lịch sử thanh toán', 'href' => 'index.php?act=khachHang/lichSuThanhToan', 'icon' => 'bi-clock-history'],
        ['label' => 'Quay lại tour đã đặt', 'href' => 'index.php?act=khachHang/yeuCauTour', 'icon' => 'bi-arrow-left', 'ghost' => true],
    ],
];
ob_start(); ?>
.invoice-card {
    background: rgba(255,255,255,.92);
    border: 1px solid rgba(15,23,42,.1);
    border-radius: 24px;
    padding: 2rem;
    box-shadow: 0 14px 38px rgba(2,6,23,.08);
}
.invoice-header {
    border-bottom: 1px solid rgba(15,23,42,.12);
    padding-bottom: 1rem;
    margin-bottom: 2rem;
}
<?php $extraCss = ob_get_clean(); include __DIR__ . '/_layout/header.php'; ?>

    <div class="container py-4 py-lg-5">
"""

c = NEW_HEAD + c[content_pos:]
print('OK: head block replaced')

# ── 2. Replace closing </div></main><script bootstrap> with footer ────────────
OLD_TAIL = '    </div>\n    </main>\n\n    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>'

# The pending-payment JS block that follows should become $extraJs
PENDING_JS_START = '    <?php if (!empty($latestPayment) && (($latestPayment[\'status\'] ?? \'\') === \'DangXuLy\')): ?>'
PENDING_JS_END   = '    <?php endif; ?>'
CLOSING          = '\n</body>\n</html>'

tail_pos = c.find(OLD_TAIL)
if tail_pos < 0:
    print('ERROR: could not find tail bootstrap script')
    sys.exit(1)

after_tail = c[tail_pos + len(OLD_TAIL):]

# Find the pending JS block
pjs_start = after_tail.find(PENDING_JS_START)
if pjs_start >= 0:
    pjs_end = after_tail.find(PENDING_JS_END, pjs_start) + len(PENDING_JS_END)
    pending_js_block = after_tail[pjs_start:pjs_end]
    # Extract inner <script> content for $extraJs
    script_open  = pending_js_block.find('<script>')
    script_close = pending_js_block.rfind('</script>') + len('</script>')
    inner_script = pending_js_block[script_open:script_close] if script_open >= 0 else ''

    NEW_TAIL = "\n    </div>\n\n<?php\nif (!empty($latestPayment) && (($latestPayment['status'] ?? '') === 'DangXuLy')) {\n    $extraJs = '" + inner_script.replace("'", "\\'").replace("\n", "\\n") + "';\n}\ninclude __DIR__ . '/_layout/footer.php';\n?>\n"
else:
    NEW_TAIL = '\n    </div>\n\n<?php include __DIR__ . \'/_layout/footer.php\'; ?>\n'

c = c[:tail_pos] + NEW_TAIL

print('OK: footer block replaced')

with open(path, 'w', encoding='utf-8') as f:
    f.write(c)
print('Written:', path)
