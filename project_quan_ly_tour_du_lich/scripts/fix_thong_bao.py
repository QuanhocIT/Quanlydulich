#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Refactor thong_bao.php to use shared layout partials."""
import os

BASE = os.path.join(os.path.dirname(__file__), '..', 'views', 'khach_hang')
file = os.path.join(BASE, 'thong_bao.php')

with open(file, 'r', encoding='utf-8') as f:
    c = f.read()

print('File length:', len(c))
print('Contains DOCTYPE:', '<!DOCTYPE html>' in c)

#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Refactor thong_bao.php (HEAD version) to use shared layout partials."""
import os

BASE = os.path.join(os.path.dirname(__file__), '..', 'views', 'khach_hang')
file = os.path.join(BASE, 'thong_bao.php')

with open(file, 'r', encoding='utf-8') as f:
        c = f.read()

# Step 1: Replace head block
old1 = ('<!DOCTYPE html>\n'
                '<html lang="vi">\n'
                '<head>\n'
                '    <meta charset="UTF-8">\n'
                '    <meta name="viewport" content="width=device-width, initial-scale=1.0">\n'
                '    <title>Th\u00f4ng b\u00e1o - Kh\u00e1ch h\u00e0ng</title>\n'
                '    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">\n'
                '    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">\n'
                '    <style>')

new1 = ('<?php\n'
                '$pageTitle  = \'' + 'Th\u00f4ng b\u00e1o c\u1ee7a b\u1ea1n' + '\';\n'
                '$activePage = \'thongBao\';\n'
                '$pageHero   = [\n'
                '    \'eyebrow\'  => \'TRUNG T\u00c2M TH\u00d4NG B\u00c1O\',\n'
                '    \'icon\'     => \'bi-bell\',\n'
                '    \'title\'    => \'Th\u00f4ng b\u00e1o c\u1ee7a b\u1ea1n\',\n'
                '    \'subtitle\' => \'C\u1eadp nh\u1eadt m\u1ecdi tin t\u1ee9c, \u0111\u1eb7t tour v\u00e0 \u01b0u \u0111\u00e3i t\u1eeb DuLichPro.\',\n'
                '];\n'
                'ob_start(); ?>')

if old1 not in c:
        print('ERROR: head block not found')
else:
        c = c.replace(old1, new1, 1)
        print('OK: head block replaced')

# Step 2: Replace </style></head><body>...PHP init block with layout include
idx_style = c.find('    </style>\n</head>\n<body>')
if idx_style < 0:
        print('ERROR: </style></head><body> not found')
else:
        idx_php_end = c.find("        : '';\n    ?>", idx_style)
        if idx_php_end < 0:
                print('ERROR: PHP end marker not found')
        else:
                end_pos = idx_php_end + len("        : '';\n    ?>")
                old_block = c[idx_style:end_pos]
                new_block = ('<?php $extraCss = ob_get_clean();\n'
                                         '$thongBaoChuaDoc = (int)($thongBaoChuaDoc ?? 0);\n'
                                         'if (!isset($thongBaoList) || !is_array($thongBaoList)) { $thongBaoList = []; }\n'
                                         '$khRealtimeWsEnabled = realtimeWebSocketEnabled() && isLoggedIn() && hasRole(\'KhachHang\');\n'
                                         '$khRealtimeWsUrl   = $khRealtimeWsEnabled ? realtimeWebSocketPublicUrl() : \'\';\n'
                                         '$khRealtimeWsToken = $khRealtimeWsEnabled\n'
                                         '    ? buildRealtimeAuthToken((int)($_SESSION[\'user_id\'] ?? 0), \'KhachHang\', \'notifications\')\n'
                                         '    : \'\';\n'
                                         '$unreadCount = $thongBaoChuaDoc;\n'
                                         'include __DIR__ . \'/_layout/header.php\'; ?>')
                c = c.replace(old_block, new_block, 1)
                print('OK: PHP init block replaced')

# Step 3: Remove bootstrap script
old_bs = '    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>\n'
if old_bs not in c:
        print('WARNING: bootstrap script not found')
else:
        c = c.replace(old_bs, '', 1)
        print('OK: bootstrap script removed')

# Step 4: Replace </body></html> with footer include
old_tail = '</body>\n</html>'
new_tail = "<?php include __DIR__ . '/_layout/footer.php'; ?>"
if old_tail not in c:
        print('WARNING: </body></html> not found')
else:
        c = c.replace(old_tail, new_tail, 1)
        print('OK: footer include added')

with open(file, 'w', encoding='utf-8') as f:
        f.write(c)
print('Written:', file)
