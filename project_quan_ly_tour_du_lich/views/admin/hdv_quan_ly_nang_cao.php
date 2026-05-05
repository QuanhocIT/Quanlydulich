<?php
$pageTitle = 'Quản lý HDV Nâng cao';
$currentPage = 'nhanSu';
ob_start();
?>
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.css' rel='stylesheet' />
<style>
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-left: -8px;
            margin-right: -8px;
        }
        .row > [class*="col-"] {
            padding-left: 8px;
            padding-right: 8px;
            box-sizing: border-box;
        }
        .col-12 { width: 100%; }
        .col-md-3 { width: 25%; }
        .col-md-4 { width: 33.333333%; }
        .col-md-6 { width: 50%; }
        .col-lg-4 { width: 33.333333%; }

        .d-flex { display: flex; }
        .justify-content-between { justify-content: space-between; }
        .align-items-center { align-items: center; }
        .align-items-start { align-items: flex-start; }
        .flex-wrap { flex-wrap: wrap; }
        .flex-grow-1 { flex-grow: 1; }
        .h-100 { height: 100%; }
        .w-100 { width: 100%; }
        .mb-0 { margin-bottom: 0; }
        .mb-1 { margin-bottom: 0.25rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-3 { margin-bottom: 1rem; }
        .mb-4 { margin-bottom: 1.5rem; }
        .ms-1 { margin-left: 0.25rem; }
        .text-center { text-align: center; }

        .hdv-advanced-wrap {
            padding: 20px;
            max-width: 1220px;
            margin: 0 auto;
        }

        .section-space {
            margin-bottom: 16px;
        }
        .page-header-section {
            position: relative;
            background: linear-gradient(90deg, #2d2d2d 0%, #3a2e13 100%);
            border-radius: 8px;
            padding: 24px 32px;
            margin-bottom: 0;
            box-shadow: 0 2px 12px rgba(212,175,55,0.10);
            display: flex;
            align-items: center;
            gap: 22px;
            overflow: hidden;
        }
        .page-header-glow {
            position: absolute;
            top: 0; left: -60%;
            width: 60%; height: 100%;
            background: linear-gradient(120deg, rgba(255,236,140,0.18) 0%, rgba(255,236,140,0.45) 50%, rgba(255,236,140,0.18) 100%);
            filter: blur(2px);
            animation: phglow 2.8s linear infinite;
            z-index: 1;
            pointer-events: none;
        }
        @keyframes phglow {
            0% { left: -60%; }
            100% { left: 100%; }
        }
        .page-header-avatar {
            width: 64px; height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, #d4af37 60%, #fffde7 100%);
            display: flex; align-items: center; justify-content: center;
            font-size: 2.2rem;
            box-shadow: 0 0 0 4px rgba(212,175,55,0.12);
            z-index: 2;
            flex-shrink: 0;
        }
        .page-header-layout {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 18px;
            z-index: 2;
        }
        .page-header-meta {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .page-header-title {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 700;
            color: #ffe082;
            text-shadow: 0 2px 8px #2d2d2d;
            line-height: 1.2;
        }
        .page-header-desc {
            margin: 0;
            color: #fffde7;
            font-size: 1rem;
            text-shadow: 0 1px 4px #2d2d2d;
        }
        .filter-toolbar {
            display: grid;
            grid-template-columns: 220px 220px minmax(240px, 1fr);
            gap: 12px;
            margin-bottom: 14px;
            align-items: center;
        }
        .filter-field {
            position: relative;
        }
        .filter-field .form-control,
        .filter-field .form-select {
            min-height: 44px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: linear-gradient(165deg, rgba(60, 60, 60, 0.48), rgba(35, 35, 35, 0.62));
            color: var(--text-light);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
        }
        .filter-field .form-control {
            padding-left: 38px;
        }
        .filter-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.55);
            pointer-events: none;
            font-size: 0.92rem;
        }
        .filter-field .form-control::placeholder {
            color: rgba(255, 255, 255, 0.46);
        }
        .quick-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .quick-actions .btn {
            min-width: 180px;
            justify-content: center;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.18);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }

        .hdv-card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-left: 4px solid #007bff;
            border-radius: 8px;
            backdrop-filter: blur(10px);
            transition: all 0.3s;
        }
        .hdv-card:hover {
            transform: translateY(-2px);
            background: rgba(45, 45, 45, 0.6);
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
        }
        .status-sansang { background: rgba(40, 167, 69, 0.3); color: #5cb85c; }
        .status-dangban { background: rgba(255, 193, 7, 0.3); color: #ffc107; }
        .status-nghiphep { background: rgba(220, 53, 69, 0.3); color: #dc3545; }
        .status-tamnhi { background: rgba(0, 123, 255, 0.3); color: #4da3ff; }
        
        .rating-stars {
            color: #ffc107;
        }
        
        .stat-card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-left: 4px solid var(--accent-gold);
            border-radius: 8px;
            padding: 20px 24px;
            backdrop-filter: blur(10px);
            min-height: 96px;
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(212,175,55,0.15);
        }
        .stat-card.bg-success {
            background: rgba(45, 45, 45, 0.5) !important;
            border-left-color: #10b981 !important;
        }
        .stat-card.bg-warning {
            background: rgba(45, 45, 45, 0.5) !important;
            border-left-color: var(--accent-gold) !important;
        }
        .stat-card.bg-info {
            background: rgba(45, 45, 45, 0.5) !important;
            border-left-color: var(--accent-gold) !important;
        }
        .stat-card.bg-primary {
            background: rgba(45, 45, 45, 0.5) !important;
            border-left-color: var(--accent-gold) !important;
        }
        .stat-icon {
            width: 56px; height: 56px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem;
            background: rgba(212,175,55,0.13);
            color: var(--accent-gold);
            transition: all 0.3s;
            flex-shrink: 0;
        }
        .stat-card.bg-success .stat-icon {
            background: rgba(16,185,129,0.13);
            color: #10b981;
        }
        .stat-card:hover .stat-icon {
            background: var(--accent-gold);
            color: var(--primary-dark);
            transform: scale(1.1) rotate(5deg);
        }
        .stat-card.bg-success:hover .stat-icon {
            background: #10b981;
        }
        .stat-card h2 {
            font-size: 2.2rem;
            font-weight: 700;
            line-height: 1;
            margin-top: 8px;
            margin-bottom: 0;
            color: #ffd700;
        }
        .stat-card.bg-success h2 {
            color: #10b981;
        }
        .stat-card h6 {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text-muted);
            opacity: 1;
        }
        
        #calendar {
            max-width: 100%;
            margin: 0 auto;
        }
        
        .fc-event {
            cursor: pointer;
        }
        
        .loai-hdv-badge {
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
        }
        
        .nav-tabs {
            border-bottom: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }
        .nav-tabs .nav-link {
            color: var(--text-muted);
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.04);
            border-radius: 8px;
            padding: 9px 12px;
            cursor: pointer;
        }
        .nav-tabs .nav-link.active {
            background: rgba(212, 175, 55, 0.2);
            border-color: var(--accent-gold);
            color: var(--accent-gold);
            font-weight: 600;
        }
        .tab-content {
            margin-top: 6px;
        }
        .tab-pane {
            display: none;
        }
        .tab-pane.active {
            display: block;
        }
        .card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }
        .card-header {
            background: rgba(45, 45, 45, 0.7);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }
        .card-body {
            color: var(--text-light);
        }
        .table {
            color: var(--text-light);
        }
        .table th {
            background: rgba(45, 45, 45, 0.7);
            color: var(--text-light);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .table td {
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .table tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        .table-light {
            background: rgba(45, 45, 45, 0.7) !important;
        }
        .form-control, .form-select {
            background: rgba(45, 45, 45, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }
        .form-control:focus, .form-select:focus {
            background: rgba(45, 45, 45, 0.8);
            border-color: var(--accent-gold);
            color: var(--text-light);
        }
        .form-label {
            color: var(--text-light);
        }
        .badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 500;
        }
        .bg-primary {
            background: rgba(13, 110, 253, 0.3) !important;
            color: #4da3ff !important;
        }
        .bg-info {
            background: rgba(0, 123, 255, 0.3) !important;
            color: #4da3ff !important;
        }
        .bg-success {
            background: rgba(40, 167, 69, 0.3) !important;
            color: #5cb85c !important;
        }
        .bg-secondary {
            background: rgba(108, 117, 125, 0.3) !important;
            color: #adb5bd !important;
        }
        .bg-danger {
            background: rgba(220, 53, 69, 0.3) !important;
            color: #dc3545 !important;
        }
        .text-white {
            color: var(--text-light) !important;
        }
        .text-muted {
            color: var(--text-muted) !important;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            border: none;
            cursor: pointer;
        }
        .btn-primary {
            background: rgba(13, 110, 253, 0.3);
            color: #4da3ff;
            border: 1px solid rgba(13, 110, 253, 0.5);
        }
        .btn-primary:hover {
            background: rgba(13, 110, 253, 0.5);
        }
        .btn-success {
            background: rgba(40, 167, 69, 0.3);
            color: #5cb85c;
            border: 1px solid rgba(40, 167, 69, 0.5);
        }
        .btn-success:hover {
            background: rgba(40, 167, 69, 0.5);
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }
        .btn-group {
            display: flex;
            gap: 6px;
        }
        .btn-group .btn {
            flex: 1;
            justify-content: center;
            white-space: nowrap;
        }
        .btn-outline-primary {
            background: transparent;
            color: #4da3ff;
            border: 1px solid rgba(13, 110, 253, 0.5);
        }
        .btn-outline-primary:hover {
            background: rgba(13, 110, 253, 0.3);
        }
        .btn-outline-success {
            background: transparent;
            color: #5cb85c;
            border: 1px solid rgba(40, 167, 69, 0.5);
        }
        .btn-outline-success:hover {
            background: rgba(40, 167, 69, 0.3);
        }
        .btn-outline-info {
            background: transparent;
            color: #4da3ff;
            border: 1px solid rgba(0, 123, 255, 0.5);
        }
        .btn-outline-info:hover {
            background: rgba(0, 123, 255, 0.3);
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .list-group-item {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }
        .notification-panel {
            border-radius: 12px;
            overflow: hidden;
        }
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
        }
        .notification-header h5 {
            margin: 0;
            font-size: 1.9rem;
            font-weight: 700;
            color: var(--text-light);
        }
        .notification-counter {
            color: var(--text-muted);
            font-size: 0.92rem;
            white-space: nowrap;
        }
        .notification-list {
            display: grid;
            gap: 10px;
        }
        .notification-item {
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            background: linear-gradient(145deg, rgba(52, 52, 52, 0.56), rgba(38, 38, 38, 0.42));
            padding: 14px 14px 12px;
        }
        .notification-item.is-unread {
            border-left: 4px solid #8f98a3;
        }
        .notification-item.is-read {
            border-left: 4px solid #2f9d57;
        }
        .notification-row-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 8px;
        }
        .notification-title {
            margin: 0;
            font-size: 1.04rem;
            font-weight: 700;
            color: var(--text-light);
        }
        .notification-body {
            margin: 0 0 10px;
            font-size: 1.05rem;
            line-height: 1.55;
            color: rgba(255, 255, 255, 0.92);
        }
        .notification-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            color: var(--text-muted);
            font-size: 0.95rem;
        }
        .notification-meta .meta-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .priority-chip {
            display: inline-flex;
            align-items: center;
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            border: 1px solid transparent;
        }
        .priority-chip.is-critical {
            color: #ff8f8f;
            background: rgba(220, 53, 69, 0.24);
            border-color: rgba(220, 53, 69, 0.45);
        }
        .seen-chip {
            min-width: 98px;
            text-align: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.95rem;
            border-radius: 8px;
            padding: 9px 10px;
        }
        .notification-empty {
            text-align: center;
            color: var(--text-muted);
            border: 1px dashed rgba(255, 255, 255, 0.16);
            border-radius: 10px;
            padding: 28px 16px;
        }
        .modal-content {
            background: rgba(45, 45, 45, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 12px;
        }
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1055;
            display: none;
            width: 100%;
            height: 100%;
            overflow-x: hidden;
            overflow-y: auto;
            outline: 0;
            background: rgba(0, 0, 0, 0.55);
        }
        .modal.show {
            display: block;
        }
        .modal.fade {
            transition: opacity 0.15s linear;
        }
        .modal.fade:not(.show) {
            opacity: 0;
            pointer-events: none;
        }
        .modal.show .modal-dialog {
            transform: translateY(0);
        }
        .modal-dialog {
            max-width: 680px;
            width: calc(100% - 24px);
            margin: 24px auto;
            transform: translateY(10px);
            transition: transform 0.2s ease;
        }
        .modal-body {
            display: grid;
            gap: 12px;
            padding: 18px;
        }
        .modal-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .modal-form .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.92);
        }
        .modal-form .form-control,
        .modal-form .form-select {
            width: 100%;
            min-height: 42px;
            border-radius: 8px;
            padding: 9px 12px;
            line-height: 1.35;
        }
        .modal-form textarea.form-control {
            min-height: 110px;
            resize: vertical;
        }
        .modal-section-title {
            margin: 2px 0 6px;
            padding-bottom: 7px;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.15);
            color: var(--text-light);
            font-size: 0.92rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }
        .field-hint {
            display: block;
            margin-top: 5px;
            color: rgba(255, 255, 255, 0.55);
            font-size: 0.82rem;
        }
        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 14px 18px;
        }
        .modal-title {
            color: var(--text-light);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
        }
        .modal-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px 18px 16px;
        }
        .btn-close {
            filter: invert(1);
        }
        body.modal-open {
            overflow: hidden;
        }

        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .col-lg-4 { width: 50%; }
        }

        @media (max-width: 900px) {
            .col-md-3,
            .col-md-4,
            .col-md-6,
            .col-lg-4 {
                width: 100%;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .btn-group {
                flex-wrap: wrap;
            }
            .modal-grid-2 {
                grid-template-columns: 1fr;
            }
            .hdv-advanced-wrap {
                padding: 10px;
            }
            .page-header-section {
                padding: 20px 18px;
                min-height: 120px;
            }
            .page-header-title {
                font-size: 2.15rem;
            }
            .page-header-desc {
                font-size: 0.98rem;
            }
            .filter-toolbar {
                grid-template-columns: 1fr;
            }
            .quick-actions {
                width: 100%;
            }
            .quick-actions .btn {
                min-width: 0;
                flex: 1 1 100%;
            }
            .notification-header h5 {
                font-size: 1.45rem;
            }
            .notification-row-top {
                flex-direction: column;
                align-items: flex-start;
            }
            .seen-chip {
                min-width: 0;
            }
        }
        body.page-nhanSu .content-area {
            padding: 34px 48px 56px;
            background:
                radial-gradient(circle at 12% 8%, rgba(32, 178, 170, 0.08), transparent 28%),
                radial-gradient(circle at 84% 16%, rgba(212, 175, 55, 0.10), transparent 30%),
                linear-gradient(135deg, #131616 0%, #181b1c 48%, #111313 100%);
        }
        body.page-nhanSu .hdv-advanced-wrap {
            max-width: 100%;
            padding: 0;
        }
        body.page-nhanSu .hdv-advanced-wrap .page-header-section {
            min-height: 190px;
            padding: 34px 44px;
            border: 1px solid rgba(212,175,55,.22);
            background:
                linear-gradient(90deg, rgba(16,22,22,.92) 0%, rgba(24,30,29,.86) 54%, rgba(212,175,55,.45) 100%),
                url('<?php echo BASE_URL; ?>public/images/logos/hinh-nen-viet-nam-4k10.jpg') center/cover;
            box-shadow: 0 24px 52px rgba(0,0,0,.28);
        }
        body.page-nhanSu .hdv-advanced-wrap .page-header-glow {
            display: none;
        }
        body.page-nhanSu .hdv-advanced-wrap .page-header-avatar {
            width: 92px;
            height: 92px;
            border-radius: 8px;
            background: rgba(212,175,55,.18);
            border: 1px solid rgba(212,175,55,.34);
            box-shadow: 0 18px 34px rgba(0,0,0,.22);
        }
        body.page-nhanSu .hdv-advanced-wrap .page-header-title {
            font-size: 2.15rem;
            color: #ffe082;
            letter-spacing: 0;
        }
        body.page-nhanSu .hdv-advanced-wrap .page-header-desc {
            color: #f5f1df;
            line-height: 1.6;
        }
        body.page-nhanSu .hdv-advanced-wrap .quick-actions .btn {
            min-height: 58px;
            border-radius: 8px;
            padding: 14px 24px;
            font-weight: 800;
        }
        body.page-nhanSu .hdv-advanced-wrap .stats-grid {
            gap: 28px;
            margin: 28px 0 34px;
        }
        body.page-nhanSu .hdv-advanced-wrap .stat-card,
        body.page-nhanSu .hdv-advanced-wrap .hdv-card,
        body.page-nhanSu .hdv-advanced-wrap .tab-pane,
        body.page-nhanSu .hdv-advanced-wrap .notification-card,
        body.page-nhanSu .hdv-advanced-wrap .calendar-wrap {
            background: rgba(28, 30, 31, .80);
            border: 1px solid rgba(212,175,55,.20);
            border-radius: 8px;
            box-shadow: 0 18px 38px rgba(0,0,0,.20);
        }
        body.page-nhanSu .hdv-advanced-wrap .stat-card {
            min-height: 168px;
            padding: 28px 30px;
        }
        body.page-nhanSu .hdv-advanced-wrap .stat-icon {
            width: 72px;
            height: 72px;
            border-radius: 8px;
        }
        body.page-nhanSu .hdv-advanced-wrap .stat-card h2 {
            font-size: 2.65rem;
        }
        body.page-nhanSu .hdv-advanced-wrap .nav-tabs {
            gap: 10px;
            margin-bottom: 16px;
        }
        body.page-nhanSu .hdv-advanced-wrap .nav-tabs .nav-link {
            min-height: 46px;
            border-radius: 8px;
            font-weight: 700;
            padding: 11px 16px;
        }
        body.page-nhanSu .hdv-advanced-wrap .filter-toolbar {
            grid-template-columns: 180px 180px minmax(260px, 1fr);
            gap: 16px;
            margin-bottom: 18px;
        }
        body.page-nhanSu .hdv-advanced-wrap .filter-field .form-control,
        body.page-nhanSu .hdv-advanced-wrap .filter-field .form-select {
            min-height: 54px;
            border-radius: 8px;
            background: rgba(255,255,255,.055);
            border-color: rgba(255,255,255,.18);
        }
        body.page-nhanSu .hdv-advanced-wrap .filter-field .form-control:focus,
        body.page-nhanSu .hdv-advanced-wrap .filter-field .form-select:focus {
            border-color: rgba(32,178,170,.72);
            box-shadow: 0 0 0 3px rgba(32,178,170,.12);
        }
        body.page-nhanSu .hdv-advanced-wrap .hdv-card {
            padding: 26px;
            border-left-color: rgba(212,175,55,.46);
        }
        body.page-nhanSu .hdv-advanced-wrap .status-badge,
        body.page-nhanSu .hdv-advanced-wrap .loai-hdv-badge {
            border-radius: 8px;
            font-weight: 700;
            padding: 7px 12px;
        }
        @media (max-width: 1200px) {
            body.page-nhanSu .hdv-advanced-wrap .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            body.page-nhanSu .hdv-advanced-wrap .filter-toolbar { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            body.page-nhanSu .content-area { padding: 24px 18px 44px; }
            body.page-nhanSu .hdv-advanced-wrap .page-header-section { padding: 24px; }
            body.page-nhanSu .hdv-advanced-wrap .page-header-title { font-size: 1.6rem; }
            body.page-nhanSu .hdv-advanced-wrap .stats-grid { grid-template-columns: 1fr; }
        }
    </style>

<div class="hdv-advanced-wrap">
    <?php if (!empty($_SESSION['flash'])): $f = $_SESSION['flash']; ?>
        <div class="alert alert-<?php echo htmlspecialchars($f['type']); ?>" style="display: flex; justify-content: space-between; align-items: center;">
            <span><?php echo htmlspecialchars($f['message']); ?></span>
            <button type="button" onclick="this.parentElement.style.display='none'" style="background: none; border: none; color: inherit; cursor: pointer; font-size: 1.2rem;">&times;</button>
        </div>
        <?php unset($_SESSION['flash']); endif; ?>

    <div class="page-header-section section-space" style="margin-bottom: 18px;">
        <div class="page-header-glow"></div>
        <div class="page-header-avatar">🧭</div>
        <div class="page-header-layout">
            <div class="page-header-meta">
                <h1 class="page-header-title">Quản lý HDV Nâng cao</h1>
                <p class="page-header-desc">Theo dõi lịch, hiệu suất và thông báo cho hướng dẫn viên trong một màn hình.</p>
            </div>
            <div class="quick-actions">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                    <i class="bi bi-calendar-check"></i> Phân công HDV
                </button>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addNotificationModal">
                    <i class="bi bi-bell"></i> Gửi thông báo
                </button>
            </div>
        </div>
    </div>

        <!-- Thống kê tổng quan -->
        <div class="stats-grid">
            <div class="stat-card bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">HDV Sẵn sàng</h6>
                        <h2 class="mb-0"><?php echo $stats['san_sang'] ?? 0; ?></h2>
                    </div>
                    <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                </div>
            </div>
            <div class="stat-card bg-warning text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Đang làm việc</h6>
                        <h2 class="mb-0"><?php echo $stats['dang_ban'] ?? 0; ?></h2>
                    </div>
                    <div class="stat-icon"><i class="bi bi-briefcase"></i></div>
                </div>
            </div>
            <div class="stat-card bg-info text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Nghỉ phép</h6>
                        <h2 class="mb-0"><?php echo $stats['nghi_phep'] ?? 0; ?></h2>
                    </div>
                    <div class="stat-icon"><i class="bi bi-calendar-x"></i></div>
                </div>
            </div>
            <div class="stat-card bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Tour tháng này</h6>
                        <h2 class="mb-0"><?php echo $stats['tour_thang'] ?? 0; ?></h2>
                    </div>
                    <div class="stat-icon"><i class="bi bi-graph-up"></i></div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <button type="button" class="nav-link active" data-tab-target="#tab-danh-sach" aria-selected="true">
                    <i class="bi bi-list-ul"></i> Danh sách HDV
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" data-tab-target="#tab-lich" aria-selected="false">
                    <i class="bi bi-calendar3"></i> Lịch làm việc
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" data-tab-target="#tab-hieu-suat" aria-selected="false">
                    <i class="bi bi-bar-chart"></i> Hiệu suất
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" data-tab-target="#tab-thong-bao" aria-selected="false">
                    <i class="bi bi-bell"></i> Thông báo
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Tab 1: Danh sách HDV -->
            <div class="tab-pane active" id="tab-danh-sach">
                <div class="filter-toolbar">
                    <div class="filter-field">
                        <select class="form-select" id="filterLoaiHDV">
                            <option value="">Tất cả loại HDV</option>
                            <option value="NoiDia">Nội địa</option>
                            <option value="QuocTe">Quốc tế</option>
                            <option value="ChuyenTuyen">Chuyên tuyến</option>
                            <option value="ChuyenDoan">Chuyên đoàn</option>
                            <option value="TongHop">Tổng hợp</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <select class="form-select" id="filterTrangThai">
                            <option value="">Tất cả trạng thái</option>
                            <option value="SanSang">Sẵn sàng</option>
                            <option value="DangBan">Đang bận</option>
                            <option value="NghiPhep">Nghỉ phép</option>
                            <option value="TamNghi">Tạm nghỉ</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <i class="bi bi-search filter-icon"></i>
                        <input type="text" class="form-control" id="searchHDV" placeholder="Tìm theo tên, tuyến, ngôn ngữ...">
                    </div>
                </div>

                <div class="row" id="hdvList">
                    <?php if (!empty($hdv_list)): foreach($hdv_list as $hdv): ?>
                    <div class="col-md-6 col-lg-4 mb-3 hdv-item" 
                         data-loai="<?php echo htmlspecialchars($hdv['loai_hdv'] ?? ''); ?>"
                         data-trangthai="<?php echo htmlspecialchars($hdv['trang_thai_lam_viec'] ?? ''); ?>">
                        <div class="card hdv-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0">
                                        <?php echo htmlspecialchars($hdv['ho_ten'] ?? 'N/A'); ?>
                                    </h5>
                                    <span class="status-badge status-<?php echo strtolower($hdv['trang_thai_lam_viec'] ?? 'sansang'); ?>">
                                        <?php 
                                        $status_map = [
                                            'SanSang' => 'Sẵn sàng',
                                            'DangBan' => 'Đang bận',
                                            'NghiPhep' => 'Nghỉ phép',
                                            'TamNghi' => 'Tạm nghỉ'
                                        ];
                                        echo $status_map[$hdv['trang_thai_lam_viec'] ?? 'SanSang'];
                                        ?>
                                    </span>
                                </div>
                                
                                <span class="badge bg-primary loai-hdv-badge mb-2">
                                    <?php 
                                    $loai_map = [
                                        'NoiDia' => 'Nội địa',
                                        'QuocTe' => 'Quốc tế',
                                        'ChuyenTuyen' => 'Chuyên tuyến',
                                        'ChuyenDoan' => 'Chuyên đoàn',
                                        'TongHop' => 'Tổng hợp'
                                    ];
                                    echo $loai_map[$hdv['loai_hdv'] ?? 'TongHop'];
                                    ?>
                                </span>
                                
                                <?php if (!empty($hdv['chuyen_tuyen'])): ?>
                                <p class="mb-1"><i class="bi bi-geo-alt"></i> <small><?php echo htmlspecialchars($hdv['chuyen_tuyen']); ?></small></p>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="rating-stars">
                                        <?php 
                                        $rating = floatval($hdv['danh_gia_tb'] ?? 0);
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo $i <= $rating ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
                                        }
                                        ?>
                                        <small class="text-muted ms-1"><?php echo number_format($rating, 1); ?></small>
                                    </span>
                                    <span class="badge bg-secondary"><?php echo $hdv['so_tour_da_dan'] ?? 0; ?> tour</span>
                                </div>
                                
                                <p class="mb-2"><i class="bi bi-translate"></i> <?php echo htmlspecialchars($hdv['ngon_ngu'] ?? 'N/A'); ?></p>
                                
                                <div class="btn-group w-100" role="group">
                                    <a href="index.php?act=admin/hdv_detail&id=<?php echo $hdv['nhan_su_id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> Chi tiết
                                    </a>
                                    <button class="btn btn-sm btn-outline-success" onclick="openScheduleModal(<?php echo $hdv['nhan_su_id']; ?>)">
                                        <i class="bi bi-calendar-plus"></i> Lịch
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="viewPerformance(<?php echo $hdv['nhan_su_id']; ?>)">
                                        <i class="bi bi-graph-up"></i> Hiệu suất
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">Chưa có HDV nào trong hệ thống.</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab 2: Lịch làm việc -->
            <div class="tab-pane" id="tab-lich">
                <div class="card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-calendar3"></i> Lịch làm việc HDV</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                            <i class="bi bi-plus-circle"></i> Thêm lịch
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Bộ lọc -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <select class="form-select" id="filterHDV" onchange="filterScheduleTable()">
                                    <option value="">Tất cả HDV</option>
                                    <?php if (!empty($hdv_list)): foreach($hdv_list as $hdv): ?>
                                    <option value="<?php echo $hdv['nhan_su_id']; ?>">
                                        <?php echo htmlspecialchars($hdv['ho_ten']); ?>
                                    </option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" id="filterScheduleStatus" onchange="filterScheduleTable()">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="SapKhoiHanh">Sắp khởi hành</option>
                                    <option value="DangChay">Đang chạy</option>
                                    <option value="HoanThanh">Hoàn thành</option>
                                </select>
                            </div>
                        </div>

                        <!-- Bảng lịch làm việc -->
                        <div class="table-responsive">
                            <table class="table table-hover" id="scheduleTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>HDV</th>
                                        <th>Tour</th>
                                        <th>Ngày khởi hành</th>
                                        <th>Ngày kết thúc</th>
                                        <th>Điểm tập trung</th>
                                        <th>Trạng thái</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($lich_lam_viec)): foreach($lich_lam_viec as $lich): ?>
                                    <tr data-hdv="<?php echo $lich['nhan_su_id']; ?>" data-status="<?php echo $lich['trang_thai']; ?>">
                                        <td><?php echo htmlspecialchars($lich['ho_ten'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($lich['ten_tour'] ?? 'N/A'); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($lich['ngay_khoi_hanh'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($lich['ngay_ket_thuc'])); ?></td>
                                        <td><?php echo htmlspecialchars($lich['diem_tap_trung'] ?? '-'); ?></td>
                                        <td>
                                            <?php
                                            $statusClass = [
                                                'SapKhoiHanh' => 'bg-info',
                                                'DangChay' => 'bg-warning',
                                                'HoanThanh' => 'bg-success'
                                            ];
                                            $statusLabel = [
                                                'SapKhoiHanh' => 'Sắp khởi hành',
                                                'DangChay' => 'Đang chạy',
                                                'HoanThanh' => 'Hoàn thành'
                                            ];
                                            ?>
                                            <span class="badge <?php echo $statusClass[$lich['trang_thai']] ?? 'bg-secondary'; ?>">
                                                <?php echo $statusLabel[$lich['trang_thai']] ?? $lich['trang_thai']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($lich['ghi_chu'] ?? '-'); ?></td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Chưa có lịch làm việc nào</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Hiệu suất -->
            <div class="tab-pane" id="tab-hieu-suat">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Bảng xếp hạng HDV</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Họ tên</th>
                                        <th>Loại HDV</th>
                                        <th>Số tour</th>
                                        <th>Đánh giá TB</th>
                                        <th>Tour hoàn thành</th>
                                        <th>Tour gần nhất</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($hieu_suat_list)): $rank = 1; foreach($hieu_suat_list as $hs): ?>
                                    <tr>
                                        <td><?php echo $rank++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($hs['ho_ten'] ?? 'N/A'); ?></strong></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php 
                                                $loai_map = [
                                                    'NoiDia' => 'Nội địa',
                                                    'QuocTe' => 'Quốc tế',
                                                    'ChuyenTuyen' => 'Chuyên tuyến',
                                                    'ChuyenDoan' => 'Chuyên đoàn',
                                                    'TongHop' => 'Tổng hợp'
                                                ];
                                                echo $loai_map[$hs['loai_hdv'] ?? 'TongHop'];
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo $hs['tong_tour'] ?? 0; ?></td>
                                        <td>
                                            <span class="rating-stars">
                                                <?php 
                                                $rating = floatval($hs['diem_tb'] ?? 0);
                                                echo number_format($rating, 1);
                                                ?> <i class="bi bi-star-fill"></i>
                                            </span>
                                        </td>
                                        <td><?php echo $hs['tour_hoan_thanh'] ?? 0; ?></td>
                                        <td>
                                            <?php 
                                            if (!empty($hs['tour_gan_nhat'])) {
                                                $date = new DateTime($hs['tour_gan_nhat']);
                                                echo $date->format('d/m/Y');
                                            } else {
                                                echo '<em class="text-muted">Chưa có</em>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <a href="index.php?act=admin/hdv_detail&id=<?php echo $hs['nhan_su_id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye"></i> Chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Chưa có dữ liệu hiệu suất</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 4: Thông báo -->
            <div class="tab-pane" id="tab-thong-bao">
                <div class="card notification-panel">
                    <div class="card-body">
                        <div class="notification-header">
                            <h5>Lịch sử thông báo</h5>
                            <span class="notification-counter"><?php echo count($thong_bao_list ?? []); ?> thông báo</span>
                        </div>
                        <div class="notification-list">
                            <?php if (!empty($thong_bao_list)): foreach($thong_bao_list as $tb): ?>
                            <div class="notification-item <?php echo !empty($tb['da_xem']) ? 'is-read' : 'is-unread'; ?>">
                                <div class="notification-row-top">
                                    <div class="flex-grow-1">
                                        <h6 class="notification-title"><?php echo htmlspecialchars($tb['tieu_de']); ?></h6>
                                        <?php if ($tb['uu_tien'] === 'Cao' || $tb['uu_tien'] === 'KhanCap'): ?>
                                            <span class="priority-chip is-critical">Quan trọng</span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge seen-chip <?php echo $tb['da_xem'] ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $tb['da_xem'] ? 'Đã xem' : 'Chưa xem'; ?>
                                    </span>
                                </div>
                                <p class="notification-body"><?php echo htmlspecialchars($tb['noi_dung']); ?></p>
                                <div class="notification-meta">
                                    <span class="meta-item">
                                        <i class="bi bi-clock"></i>
                                        <?php 
                                        $date = new DateTime($tb['ngay_gui']);
                                        echo $date->format('d/m/Y H:i');
                                        ?>
                                    </span>
                                    <span class="meta-item">
                                        <i class="bi <?php echo !empty($tb['ho_ten']) ? 'bi-person' : 'bi-people'; ?>"></i>
                                        <?php echo !empty($tb['ho_ten']) ? htmlspecialchars($tb['ho_ten']) : 'Tất cả HDV'; ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; else: ?>
                            <div class="notification-empty">
                                <i class="bi bi-bell-slash" style="font-size: 1.25rem;"></i>
                                <div style="margin-top: 8px;">Chưa có thông báo nào</div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Phân công HDV cho tour -->
    <div class="modal fade" id="addScheduleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Phân công HDV cho Tour</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" action="index.php?act=admin/hdv_add_schedule" class="modal-form">
                    <div class="modal-body">
                        <div class="modal-section-title">Thông tin phân công</div>
                        <div class="mb-3">
                            <label class="form-label">Tour *</label>
                            <select name="tour_id" class="form-select" required>
                                <option value="">-- Chọn Tour --</option>
                                <?php foreach (($tourOptions ?? []) as $tour): ?>
                                <option value="<?php echo $tour['tour_id'] ?? $tour['id']; ?>">
                                    <?php echo htmlspecialchars((string)($tour['ten_tour'] ?? 'Tour')); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="field-hint">Chọn tour cần phân công hướng dẫn viên.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">HDV *</label>
                            <select name="hdv_id" class="form-select" required>
                                <option value="">-- Chọn HDV --</option>
                                <?php if (!empty($hdv_list)): foreach($hdv_list as $hdv): ?>
                                <option value="<?php echo $hdv['nhan_su_id']; ?>">
                                    <?php echo htmlspecialchars($hdv['ho_ten']); ?>
                                    <?php if (!empty($hdv['loai_hdv'])): ?>
                                        <small>(<?php echo $hdv['loai_hdv']; ?>)</small>
                                    <?php endif; ?>
                                </option>
                                <?php endforeach; endif; ?>
                            </select>
                            <small class="field-hint">Ưu tiên HDV phù hợp chuyên tuyến và lịch trống.</small>
                        </div>
                        <div class="modal-grid-2">
                            <div class="mb-3">
                                <label class="form-label">Ngày khởi hành *</label>
                                <input type="date" name="ngay_khoi_hanh" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ngày kết thúc *</label>
                                <input type="date" name="ngay_ket_thuc" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-grid-2">
                            <div class="mb-3">
                                <label class="form-label">Điểm tập trung</label>
                                <input type="text" name="diem_tap_trung" class="form-control" placeholder="Ví dụ: Bến xe Miền Đông">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Trạng thái *</label>
                                <select name="trang_thai" class="form-select" required>
                                    <option value="DaXacNhan">Đã xác nhận</option>
                                    <option value="ChoXacNhan">Chờ xác nhận</option>
                                    <option value="Huy">Hủy</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-calendar-check"></i> Phân công
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Gửi thông báo -->
    <div class="modal fade" id="addNotificationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Gửi thông báo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" action="index.php?act=admin/hdv_send_notification" class="modal-form">
                    <div class="modal-body">
                        <div class="modal-section-title">Đối tượng nhận</div>
                        <div class="mb-3">
                            <label class="form-label">Gửi đến</label>
                            <select name="nhan_su_id" class="form-select">
                                <option value="">Tất cả HDV</option>
                                <?php if (!empty($hdv_list)): foreach($hdv_list as $hdv): ?>
                                <option value="<?php echo $hdv['nhan_su_id']; ?>">
                                    <?php echo htmlspecialchars($hdv['ho_ten']); ?>
                                </option>
                                <?php endforeach; endif; ?>
                            </select>
                            <small class="field-hint">Để trống nếu muốn gửi cho toàn bộ HDV.</small>
                        </div>

                        <div class="modal-section-title">Nội dung thông báo</div>
                        <div class="modal-grid-2">
                            <div class="mb-3">
                                <label class="form-label">Loại thông báo *</label>
                                <select name="loai_thong_bao" class="form-select" required>
                                    <option value="ThongBao">Thông báo</option>
                                    <option value="NhacNho">Nhắc nhở</option>
                                    <option value="CanhBao">Cảnh báo</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mức ưu tiên *</label>
                                <select name="uu_tien" class="form-select" required>
                                    <option value="TrungBinh">Trung bình</option>
                                    <option value="Cao">Cao</option>
                                    <option value="KhanCap">Khẩn cấp</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề *</label>
                            <input type="text" name="tieu_de" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nội dung *</label>
                            <textarea name="noi_dung" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-success">Gửi thông báo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js'></script>
    <script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
        function initAdvancedTabs() {
            const tabButtons = document.querySelectorAll('.nav-tabs .nav-link[data-tab-target]');
            const tabPanes = document.querySelectorAll('.tab-content .tab-pane');

            if (!tabButtons.length || !tabPanes.length) {
                return;
            }

            function activateTab(button) {
                const targetSelector = button.getAttribute('data-tab-target');
                const targetPane = targetSelector ? document.querySelector(targetSelector) : null;
                if (!targetPane) {
                    return;
                }

                tabButtons.forEach(btn => {
                    btn.classList.remove('active');
                    btn.setAttribute('aria-selected', 'false');
                });
                tabPanes.forEach(pane => pane.classList.remove('active'));

                button.classList.add('active');
                button.setAttribute('aria-selected', 'true');
                targetPane.classList.add('active');
            }

            tabButtons.forEach(button => {
                button.addEventListener('click', () => activateTab(button));
            });

            const defaultTab = document.querySelector('.nav-tabs .nav-link.active[data-tab-target]') || tabButtons[0];
            if (defaultTab) {
                activateTab(defaultTab);
            }
        }

        // Filter HDV
        document.getElementById('filterLoaiHDV').addEventListener('change', filterHDV);
        document.getElementById('filterTrangThai').addEventListener('change', filterHDV);
        document.getElementById('searchHDV').addEventListener('input', filterHDV);

        function filterHDV() {
            const loai = document.getElementById('filterLoaiHDV').value;
            const trangThai = document.getElementById('filterTrangThai').value;
            const search = document.getElementById('searchHDV').value.toLowerCase();

            document.querySelectorAll('.hdv-item').forEach(item => {
                const itemLoai = item.dataset.loai;
                const itemTrangThai = item.dataset.trangthai;
                const itemText = item.textContent.toLowerCase();

                const matchLoai = !loai || itemLoai === loai;
                const matchTrangThai = !trangThai || itemTrangThai === trangThai;
                const matchSearch = !search || itemText.includes(search);

                item.style.display = (matchLoai && matchTrangThai && matchSearch) ? '' : 'none';
            });
        }

        // Calendar
        document.addEventListener('DOMContentLoaded', function() {
            initAdvancedTabs();

            var calendarEl = document.getElementById('calendar');
            if (calendarEl) {
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'vi',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,listWeek'
                    },
                    buttonText: {
                        today: 'Hôm nay',
                        month: 'Tháng',
                        week: 'Tuần',
                        list: 'Danh sách'
                    },
                    events: function(info, successCallback, failureCallback) {
                        const hdvFilter = document.getElementById('calendarHDVFilter').value;
                        fetch(`index.php?act=admin/hdv_get_schedule${hdvFilter ? '&hdv_id=' + hdvFilter : ''}`)
                            .then(response => response.json())
                            .then(data => successCallback(data))
                            .catch(error => failureCallback(error));
                    },
                    eventClick: function(info) {
                        alert('Sự kiện: ' + info.event.title + '\n' + 
                              'Bắt đầu: ' + info.event.start + '\n' +
                              'Kết thúc: ' + info.event.end);
                    }
                });
                calendar.render();

                // Reload calendar when filter changes
                document.getElementById('calendarHDVFilter').addEventListener('change', function() {
                    calendar.refetchEvents();
                });
            }
        });

        function openScheduleModal(hdvId) {
            const modal = new bootstrap.Modal(document.getElementById('addScheduleModal'));
            const hdvSelect = document.querySelector('#addScheduleModal select[name="hdv_id"]');
            if (hdvSelect) {
                hdvSelect.value = String(hdvId);
            }
            modal.show();
        }

        function viewPerformance(hdvId) {
            window.location.href = `index.php?act=admin/hdv_detail&id=${hdvId}#performance`;
        }

        // Filter schedule table
        function filterScheduleTable() {
            const hdvFilter = document.getElementById('filterHDV').value;
            const statusFilter = document.getElementById('filterScheduleStatus').value;
            
            document.querySelectorAll('#scheduleTable tbody tr').forEach(row => {
                const hdvId = row.dataset.hdv;
                const status = row.dataset.status;
                
                let show = true;
                if (hdvFilter && hdvId !== hdvFilter) show = false;
                if (statusFilter && status !== statusFilter) show = false;
                
                row.style.display = show ? '' : 'none';
            });
        }
    </script>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
