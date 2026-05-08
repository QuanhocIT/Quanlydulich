<?php
$isCompletedView = !empty($isCompletedView);
$pageTitle = $isCompletedView ? 'Booking đã hoàn thành' : 'Quản lý Booking';
$currentPage = 'booking';
ob_start();
?>

<style>
    .booking-admin-shell {
        display: grid;
        gap: 24px;
    }

    .flash-stack {
        display: grid;
        gap: 14px;
    }

    .page-header-section {
        position: relative;
        background: linear-gradient(90deg, #2d2d2d 0%, #3a2e13 100%);
        border-radius: 8px;
        padding: 24px 32px;
        margin-bottom: 28px;
        box-shadow: 0 2px 12px rgba(212,175,55,0.10);
        backdrop-filter: blur(10px);
        overflow: hidden;
    }

    .page-header-glow {
        position: absolute;
        top: 0;
        left: -60%;
        width: 60%;
        height: 100%;
        background: linear-gradient(120deg, rgba(255,236,140,0.18) 0%, rgba(255,236,140,0.45) 50%, rgba(255,236,140,0.18) 100%);
        filter: blur(2px);
        animation: booking-header-glow-move 2.8s linear infinite;
        z-index: 1;
        pointer-events: none;
    }

    @keyframes booking-header-glow-move {
        0% { left: -60%; }
        100% { left: 100%; }
    }

    .page-header-inner {
        position: relative;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 20px;
    }

    .page-header-main {
        display: flex;
        align-items: flex-start;
        gap: 16px;
    }

    .header-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }

    .header-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        text-decoration: none !important;
    }

    .page-header-avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, #d4af37 60%, #fffde7 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.1rem;
        box-shadow: 0 0 0 4px rgba(212,175,55,0.12);
        flex-shrink: 0;
    }

    .page-header-title h1 {
        margin: 0;
        color: #ffe082;
        font-size: 1.7rem;
        font-weight: 700;
        text-shadow: 0 2px 8px #2d2d2d;
    }

    .page-header-title p {
        color: #fffde7;
        font-size: 1rem;
        margin-top: 6px;
        text-shadow: 0 1px 4px #2d2d2d;
    }

    .booking-view-switch {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 28px;
    }

    .view-switch-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        border-radius: 2px;
        border: 2px solid rgba(255, 255, 255, 0.1);
        background: rgba(45, 45, 45, 0.3);
        color: var(--text-light);
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
    }

    .view-switch-link:hover {
        background: rgba(45, 45, 45, 0.5);
        border-color: var(--accent-gold);
        color: var(--accent-gold);
    }

    .view-switch-link.active {
        border-color: var(--accent-gold);
        background: rgba(212, 175, 55, 0.16);
        color: var(--accent-gold);
    }

    .section-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid rgba(255, 255, 255, 0.08);
    }

    .section-header .icon {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        background: rgba(212, 175, 55, 0.2);
        color: var(--accent-gold);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        margin-right: 14px;
        flex-shrink: 0;
    }

    .section-header h3 {
        margin: 0;
        color: var(--text-light);
        font-size: 18px;
        font-weight: 600;
    }

    .section-header small {
        display: block;
        margin-top: 4px;
        color: var(--text-muted);
        font-size: 12px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .stat-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-left: 4px solid;
        border-radius: 8px;
        padding: 22px;
        backdrop-filter: blur(10px);
        transition: all 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(212,175,55,0.15);
    }

    .stat-card.border-primary { border-left-color: #0d6efd; }
    .stat-card.border-warning { border-left-color: #ffc107; }
    .stat-card.border-info { border-left-color: #0dcaf0; }
    .stat-card.border-success { border-left-color: #198754; }
    .stat-card.border-danger { border-left-color: #dc3545; }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.7rem;
        margin-bottom: 15px;
        transition: all 0.3s;
    }

    .stat-card:hover .stat-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .stat-icon.bg-primary { background: rgba(13, 110, 253, 0.2); color: #0d6efd; }
    .stat-icon.bg-warning { background: rgba(255, 193, 7, 0.2); color: #ffc107; }
    .stat-icon.bg-info { background: rgba(13, 202, 240, 0.2); color: #0dcaf0; }
    .stat-icon.bg-success { background: rgba(25, 135, 84, 0.2); color: #198754; }
    .stat-icon.bg-danger { background: rgba(220, 53, 69, 0.2); color: #dc3545; }

    .stat-value {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 12px;
        color: var(--text-muted);
        letter-spacing: 0.5px;
    }

    .stats-note {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: -8px;
    }

    .stats-note-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        background: rgba(45, 45, 45, 0.45);
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: var(--text-muted);
        font-size: 12px;
    }

    .filter-section {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(212, 175, 55, 0.2);
        border-radius: 8px;
        padding: 22px 24px;
        margin-bottom: 24px;
        backdrop-filter: blur(10px);
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        align-items: end;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 600;
    }

    .form-group .input,
    .form-group .select {
        width: 100%;
        background: rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: var(--text-light);
        border-radius: 4px;
        padding: 10px 12px;
        font-size: 13px;
        transition: all 0.2s;
    }

    .form-group .input::placeholder {
        color: rgba(255, 255, 255, 0.45);
    }

    .form-group .input:focus,
    .form-group .select:focus {
        outline: none;
        border-color: var(--accent-gold);
        box-shadow: 0 0 0 2px rgba(212,175,55,0.15);
    }

    .table-wrapper {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(212, 175, 55, 0.18);
        border-radius: 8px;
        overflow: hidden;
        backdrop-filter: blur(10px);
    }

    .table-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 18px 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        color: var(--text-muted);
        font-size: 12px;
    }

    .table-summary strong {
        color: var(--text-light);
    }

    .table-hint {
        color: var(--text-muted);
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table thead {
        background: rgba(212, 175, 55, 0.1);
    }

    .table th {
        padding: 15px;
        text-align: left;
        font-size: 12px;
        letter-spacing: 1px;
        color: var(--accent-gold);
        font-weight: 600;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .table td {
        padding: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        color: var(--text-light);
        font-size: 13px;
    }

    .table tbody tr:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 2px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .status-ChoXacNhan {
        background: rgba(255, 193, 7, 0.2);
        color: #ffc107;
        border: 1px solid rgba(255, 193, 7, 0.3);
    }

    .status-DaCoc {
        background: rgba(13, 202, 240, 0.2);
        color: #0dcaf0;
        border: 1px solid rgba(13, 202, 240, 0.3);
    }

    .status-HoanTat {
        background: rgba(25, 135, 84, 0.2);
        color: #198754;
        border: 1px solid rgba(25, 135, 84, 0.3);
    }

    .status-Huy {
        background: rgba(220, 53, 69, 0.2);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.3);
    }

    .btn-group {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .btn-icon {
        min-width: 38px;
        min-height: 34px;
        padding: 6px 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        border: 1px solid transparent;
        text-decoration: none !important;
        transition: all 0.2s;
    }

    .btn-icon:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.16);
    }

    .btn-icon.view {
        background: rgba(13, 202, 240, 0.16);
        color: #0dcaf0;
        border-color: rgba(13, 202, 240, 0.28);
    }

    .btn-icon.assign {
        background: rgba(255, 193, 7, 0.16);
        color: #ffc107;
        border-color: rgba(255, 193, 7, 0.28);
    }

    .btn-icon.edit {
        background: rgba(13, 110, 253, 0.16);
        color: #0d6efd;
        border-color: rgba(13, 110, 253, 0.28);
    }

    .btn-icon.delete {
        background: rgba(220, 53, 69, 0.16);
        color: #dc3545;
        border-color: rgba(220, 53, 69, 0.28);
    }

    .pagination-shell {
        padding: 18px 20px 22px;
        border-top: 1px solid rgba(255,255,255,0.08);
    }

    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .page-item {
        margin: 0;
    }

    .page-link {
        min-width: 40px;
        height: 40px;
        padding: 0 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.12);
        background: rgba(255, 255, 255, 0.04);
        color: var(--text-light);
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.2s;
    }

    .page-link:hover {
        border-color: rgba(212, 175, 55, 0.45);
        color: var(--accent-gold);
        background: rgba(212, 175, 55, 0.08);
    }

    .page-item.active .page-link {
        border-color: rgba(212, 175, 55, 0.65);
        background: rgba(212, 175, 55, 0.16);
        color: var(--accent-gold);
        box-shadow: 0 10px 24px rgba(212, 175, 55, 0.10);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted);
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 2px;
        margin-bottom: 20px;
        border: 1px solid;
    }

    .alert-success {
        background: rgba(25, 135, 84, 0.1);
        border-color: rgba(25, 135, 84, 0.3);
        color: #198754;
    }

    .alert-danger {
        background: rgba(220, 53, 69, 0.1);
        border-color: rgba(220, 53, 69, 0.3);
        color: #dc3545;
    }

    @media (max-width: 700px) {
        .page-header-section {
            padding: 20px;
        }

        .page-header-main {
            width: 100%;
        }

        .page-header-title h1 {
            font-size: 1.4rem;
        }

        .table-toolbar {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 576px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .booking-view-switch {
            display: grid;
            grid-template-columns: 1fr;
        }

        .view-switch-link {
            justify-content: center;
        }
    }

    body.page-booking .content-area:has(.booking-admin-shell) {
        padding: 34px 48px 56px;
        background:
            radial-gradient(circle at 10% 0%, rgba(13, 202, 240, 0.08), transparent 28%),
            radial-gradient(circle at 100% 10%, rgba(212, 175, 55, 0.11), transparent 30%),
            linear-gradient(180deg, rgba(255,255,255,0.018), transparent 260px);
    }

    body.page-booking .booking-admin-shell {
        gap: 24px;
    }

    body.page-booking .booking-admin-shell .page-header-section {
        min-height: 164px;
        padding: 28px 34px;
        background:
            linear-gradient(100deg, rgba(28, 31, 33, 0.96) 0%, rgba(30, 42, 45, 0.94) 52%, rgba(119, 102, 45, 0.84) 100%),
            url("<?php echo BASE_URL; ?>public/images/logos/hinh-nen-viet-nam-4k10.jpg");
        background-size: cover;
        background-position: center;
        border: 1px solid rgba(255, 255, 255, 0.09);
        box-shadow: 0 22px 60px rgba(0, 0, 0, 0.28);
    }

    body.page-booking .booking-admin-shell .page-header-section::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(0,0,0,0.18), rgba(0,0,0,0.04));
        pointer-events: none;
    }

    body.page-booking .booking-admin-shell .page-header-glow {
        display: none;
    }

    body.page-booking .booking-admin-shell .page-header-inner {
        position: relative;
        z-index: 2;
        align-items: center;
    }

    body.page-booking .booking-admin-shell .page-header-main {
        align-items: center;
        gap: 18px;
    }

    body.page-booking .booking-admin-shell .page-header-avatar {
        width: 74px;
        height: 74px;
        border-radius: 8px;
        background: rgba(212, 175, 55, 0.18);
        border: 1px solid rgba(255, 224, 130, 0.32);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.16), 0 16px 34px rgba(0,0,0,0.24);
    }

    body.page-booking .booking-admin-shell .page-header-title h1 {
        font-size: 2rem;
        line-height: 1.18;
        letter-spacing: 0;
        text-shadow: none;
    }

    body.page-booking .booking-admin-shell .page-header-title p {
        max-width: 680px;
        color: rgba(255,255,255,0.86);
        text-shadow: none;
    }

    body.page-booking .booking-admin-shell .header-actions .btn,
    body.page-booking .booking-admin-shell .view-switch-link,
    body.page-booking .booking-admin-shell .filter-section .btn {
        border-radius: 8px;
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    body.page-booking .booking-admin-shell .header-actions .btn {
        min-height: 46px;
        padding-inline: 18px;
    }

    body.page-booking .booking-admin-shell .booking-view-switch {
        gap: 12px;
        margin-bottom: 10px;
    }

    body.page-booking .booking-admin-shell .view-switch-link {
        min-height: 58px;
        padding: 14px 22px;
        background: rgba(255,255,255,0.045);
        border: 1px solid rgba(255,255,255,0.12);
        box-shadow: 0 10px 24px rgba(0,0,0,0.12);
    }

    body.page-booking .booking-admin-shell .view-switch-link:hover,
    body.page-booking .booking-admin-shell .view-switch-link.active {
        background: rgba(212, 175, 55, 0.14);
        border-color: rgba(212, 175, 55, 0.6);
        box-shadow: 0 16px 34px rgba(0,0,0,0.18);
    }

    body.page-booking .booking-admin-shell .stats-grid {
        grid-template-columns: repeat(5, minmax(150px, 1fr));
        gap: 16px;
    }

    body.page-booking .booking-admin-shell .stat-card {
        min-height: 178px;
        padding: 28px 22px 24px;
        background: linear-gradient(180deg, rgba(255,255,255,0.07), rgba(255,255,255,0.025));
        border-color: rgba(255, 255, 255, 0.1);
        border-left-width: 3px;
        box-shadow: 0 14px 32px rgba(0,0,0,0.18);
    }

    body.page-booking .booking-admin-shell .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 24px 54px rgba(0,0,0,0.24);
    }

    body.page-booking .booking-admin-shell .stat-icon {
        margin-bottom: 22px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.12);
    }

    body.page-booking .booking-admin-shell .stat-value {
        font-size: 2.35rem;
        line-height: 1;
        letter-spacing: 0;
    }

    body.page-booking .booking-admin-shell .stat-label {
        margin-top: 14px;
        line-height: 1.5;
        letter-spacing: 0.02em;
    }

    body.page-booking .booking-admin-shell .stats-note {
        margin-top: -4px;
    }

    body.page-booking .booking-admin-shell .stats-note-chip {
        border-radius: 8px;
        padding: 10px 16px;
        background: rgba(255,255,255,0.045);
        border-color: rgba(255,255,255,0.1);
    }

    body.page-booking .booking-admin-shell .filter-section,
    body.page-booking .booking-admin-shell .table-wrapper {
        background: rgba(28, 30, 31, 0.78);
        border-color: rgba(212, 175, 55, 0.22);
        box-shadow: 0 14px 36px rgba(0,0,0,0.18);
    }

    body.page-booking .booking-admin-shell .filter-section {
        padding: 24px;
    }

    body.page-booking .booking-admin-shell .section-header {
        border-bottom-color: rgba(255,255,255,0.09);
    }

    body.page-booking .booking-admin-shell .section-header .icon {
        background: rgba(212, 175, 55, 0.14);
        border: 1px solid rgba(212, 175, 55, 0.24);
    }

    body.page-booking .booking-admin-shell .filter-row {
        grid-template-columns: minmax(220px, 0.8fr) minmax(260px, 1.3fr) minmax(180px, 0.7fr);
        gap: 16px;
    }

    body.page-booking .booking-admin-shell .form-group label {
        color: rgba(245,245,245,0.78);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    body.page-booking .booking-admin-shell .form-group .input,
    body.page-booking .booking-admin-shell .form-group .select {
        min-height: 52px;
        border-radius: 8px;
        border-color: rgba(255,255,255,0.14);
        background-color: rgba(255,255,255,0.08);
    }

    body.page-booking .booking-admin-shell .form-group .input:focus,
    body.page-booking .booking-admin-shell .form-group .select:focus {
        border-color: rgba(13, 202, 240, 0.58);
        box-shadow: 0 0 0 3px rgba(13, 202, 240, 0.12);
    }

    body.page-booking .booking-admin-shell .filter-section .btn {
        min-height: 52px;
    }

    body.page-booking .booking-admin-shell .table-wrapper {
        border-radius: 8px;
        overflow-x: auto;
    }

    body.page-booking .booking-admin-shell .table {
        min-width: 1040px;
    }

    body.page-booking .booking-admin-shell .table thead {
        background: linear-gradient(90deg, rgba(212, 175, 55, 0.14), rgba(13, 202, 240, 0.06));
    }

    body.page-booking .booking-admin-shell .table th {
        padding: 16px;
        letter-spacing: 0.06em;
        white-space: nowrap;
    }

    body.page-booking .booking-admin-shell .table td {
        padding: 16px;
        vertical-align: middle;
    }

    body.page-booking .booking-admin-shell .table tbody tr {
        transition: background 0.2s ease;
    }

    body.page-booking .booking-admin-shell .table tbody tr:hover {
        background: rgba(255,255,255,0.065);
    }

    body.page-booking .booking-admin-shell .status-badge {
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        min-height: 30px;
    }

    body.page-booking .booking-admin-shell .btn-group {
        gap: 7px;
    }

    body.page-booking .booking-admin-shell .btn-icon {
        width: 38px;
        min-width: 38px;
        height: 38px;
        border-radius: 8px;
    }

    body.page-booking .booking-admin-shell .table-toolbar,
    body.page-booking .booking-admin-shell .pagination-shell {
        background: rgba(255,255,255,0.02);
    }

    body.page-booking .booking-admin-shell .page-link {
        border-radius: 8px;
    }

    body.theme-light.page-booking .booking-admin-shell .stat-card,
    body.theme-light.page-booking .booking-admin-shell .filter-section,
    body.theme-light.page-booking .booking-admin-shell .table-wrapper {
        background: rgba(255,255,255,0.9) !important;
    }

    body.theme-light.page-booking .booking-admin-shell .page-header-title p {
        color: rgba(255,255,255,0.86);
    }

    @media (max-width: 1500px) {
        body.page-booking .booking-admin-shell .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        }
    }

    @media (max-width: 900px) {
        body.page-booking .content-area:has(.booking-admin-shell) {
            padding: 24px 18px 42px;
        }

        body.page-booking .booking-admin-shell .page-header-section {
            padding: 24px;
        }

        body.page-booking .booking-admin-shell .page-header-inner,
        body.page-booking .booking-admin-shell .page-header-main {
            align-items: flex-start;
        }

        body.page-booking .booking-admin-shell .page-header-avatar {
            width: 58px;
            height: 58px;
            font-size: 1.8rem;
        }

        body.page-booking .booking-admin-shell .header-actions {
            width: 100%;
        }

        body.page-booking .booking-admin-shell .header-actions .btn {
            flex: 1 1 180px;
        }

        body.page-booking .booking-admin-shell .filter-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php 
$bookingsPage = $bookings ?? [];
$total = (int)($totalBookings ?? count($bookingsPage));
$currentPageBookings = count($bookingsPage);
$choXacNhan = count(array_filter($bookingsPage, fn($b) => $b['trang_thai'] === 'ChoXacNhan'));
$daCoc = count(array_filter($bookingsPage, fn($b) => $b['trang_thai'] === 'DaCoc'));
$hoanTat = count(array_filter($bookingsPage, fn($b) => $b['trang_thai'] === 'HoanTat'));
$huy = count(array_filter($bookingsPage, fn($b) => $b['trang_thai'] === 'Huy'));
?>

<div class="booking-admin-shell">
    <div class="page-header-section">
        <div class="page-header-glow"></div>
        <div class="page-header-inner">
            <div class="page-header-main">
                <div class="page-header-avatar">📋</div>
                <div class="page-header-title">
                    <h1><?php echo $isCompletedView ? 'Booking Đã Hoàn Thành' : 'Quản Lý Booking'; ?></h1>
                    <p><?php echo $isCompletedView ? 'Xem lại các booking hoàn tất, bao gồm booking đã ẩn khỏi danh sách chính' : 'Quản lý đặt tour, theo dõi trạng thái và xử lý booking của khách hàng'; ?></p>
                </div>
            </div>
            <div class="header-actions">
                <a href="index.php?act=admin/lichSuXoaBooking" class="btn btn-secondary">
                    🕐 Lịch sử xóa
                </a>
                <a href="index.php?act=admin/quanLyYeuCauTour" class="btn btn-secondary">
                    ⭐ Yêu cầu đặt tour
                </a>
                <a href="index.php?act=booking/datTourChoKhach" class="btn btn-primary">
                    ➕ Đặt tour cho khách
                </a>
            </div>
        </div>
    </div>

    <div class="flash-stack">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                ✓ <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                ⚠ <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="booking-view-switch">
        <a href="index.php?act=admin/quanLyBooking" class="view-switch-link <?php echo !$isCompletedView ? 'active' : ''; ?>">
            📋 Danh sách booking
        </a>
        <a href="index.php?act=admin/bookingDaHoanThanh" class="view-switch-link <?php echo $isCompletedView ? 'active' : ''; ?>">
            ✅ Booking đã hoàn thành
        </a>
        <a href="index.php?act=booking/datTourChoKhach" class="view-switch-link">
            ➕ Đặt tour cho khách
        </a>
    </div>

    <div class="stats-grid">
        <div class="stat-card border-primary">
            <div class="stat-icon bg-primary">📅</div>
            <div class="stat-value"><?php echo $total; ?></div>
            <div class="stat-label">Tổng booking theo bộ lọc</div>
        </div>
        <div class="stat-card border-warning">
            <div class="stat-icon bg-warning">⏳</div>
            <div class="stat-value" id="bkStatChoXacNhan" style="color: #ffc107;"><?php echo $choXacNhan; ?></div>
            <div class="stat-label">Chờ xác nhận trên trang hiện tại</div>
        </div>
        <div class="stat-card border-info">
            <div class="stat-icon bg-info">💰</div>
            <div class="stat-value" style="color: #0dcaf0;"><?php echo $daCoc; ?></div>
            <div class="stat-label">Đã cọc trên trang hiện tại</div>
        </div>
        <div class="stat-card border-success">
            <div class="stat-icon bg-success">✓</div>
            <div class="stat-value" style="color: #198754;"><?php echo $hoanTat; ?></div>
            <div class="stat-label">Hoàn tất trên trang hiện tại</div>
        </div>
        <div class="stat-card border-danger">
            <div class="stat-icon bg-danger">✕</div>
            <div class="stat-value" style="color: #dc3545;"><?php echo $huy; ?></div>
            <div class="stat-label">Đã hủy trên trang hiện tại</div>
        </div>
    </div>

    <div class="stats-note">
        <span class="stats-note-chip">📄 Đang hiển thị <?php echo $currentPageBookings; ?> booking trên trang này</span>
        <span class="stats-note-chip">🔎 Bộ lọc hiện tại được áp dụng lên toàn bộ danh sách</span>
    </div>

    <div class="filter-section">
        <div class="section-header">
            <div class="icon">🔎</div>
            <div>
                <h3>Bộ lọc booking</h3>
                <small>Lọc theo trạng thái và từ khóa để tìm booking nhanh hơn</small>
            </div>
        </div>
        <form method="GET" action="index.php">
            <input type="hidden" name="act" value="<?php echo $isCompletedView ? 'admin/bookingDaHoanThanh' : 'admin/quanLyBooking'; ?>">
            <div class="filter-row">
                <div class="form-group">
                    <label>Lọc theo trạng thái</label>
                    <select name="trang_thai" class="select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="ChoXacNhan" <?php echo (isset($_GET['trang_thai']) && $_GET['trang_thai'] == 'ChoXacNhan') ? 'selected' : ''; ?>>Chờ xác nhận</option>
                        <option value="DaCoc" <?php echo (isset($_GET['trang_thai']) && $_GET['trang_thai'] == 'DaCoc') ? 'selected' : ''; ?>>Đã cọc</option>
                        <option value="HoanTat" <?php echo ((isset($_GET['trang_thai']) && $_GET['trang_thai'] == 'HoanTat') || $isCompletedView) ? 'selected' : ''; ?>>Hoàn tất</option>
                        <option value="Huy" <?php echo (isset($_GET['trang_thai']) && $_GET['trang_thai'] == 'Huy') ? 'selected' : ''; ?>>Hủy</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tìm kiếm</label>
                    <input type="text" name="search" class="input"
                           placeholder="Mã booking, tên khách..."
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        🔍 Lọc dữ liệu
                    </button>
                </div>
            </div>
        </form>
    </div>

    <?php if (!empty($bookings)): ?>
        <div class="table-wrapper">
            <div class="section-header" style="padding: 20px 20px 16px; margin-bottom: 0; border-bottom: 1px solid rgba(255,255,255,0.08);">
                <div class="icon">📑</div>
                <div>
                    <h3>Danh sách booking</h3>
                    <small>Hiển thị các booking theo bộ lọc và phân trang hiện tại</small>
                </div>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã Booking</th>
                        <th>Khách hàng</th>
                        <th>Tour</th>
                        <th>Ngày khởi hành</th>
                        <th>Số người</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th style="text-align: center;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                    <tr data-href="index.php?act=booking/chiTiet&id=<?php echo (int)($booking['booking_id'] ?? 0); ?>">
                        <td>
                            <span style="font-family: monospace; font-weight: 600; color: var(--accent-gold);">
                                #<?php echo htmlspecialchars($booking['booking_id'] ?? 'N/A'); ?>
                            </span>
                        </td>
                        <td>
                            <div style="line-height: 1.6;">
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($booking['ho_ten'] ?? $booking['ten_khach_hang'] ?? 'N/A'); ?></div>
                                <small style="color: var(--text-muted); font-size: 11px;">
                                    <?php echo htmlspecialchars($booking['email'] ?? ''); ?>
                                </small>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($booking['ten_tour'] ?? 'N/A'); ?></td>
                        <td>
                            <?php 
                            if (!empty($booking['ngay_khoi_hanh'])) {
                                echo date('d/m/Y', strtotime($booking['ngay_khoi_hanh']));
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </td>
                        <td style="text-align: center;"><?php echo $booking['so_nguoi'] ?? 0; ?></td>
                        <td style="font-weight: 600; color: var(--accent-gold);">
                            <?php echo number_format((float)($booking['tong_tien'] ?? 0)); ?>đ
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo htmlspecialchars($booking['trang_thai'] ?? ''); ?>">
                                <?php
                                $statusLabels = [
                                    'ChoXacNhan' => 'Chờ xác nhận',
                                    'DaCoc' => 'Đã cọc',
                                    'HoanTat' => 'Hoàn tất',
                                    'Huy' => 'Hủy'
                                ];
                                echo $statusLabels[$booking['trang_thai']] ?? $booking['trang_thai'];
                                ?>
                            </span>
                            <?php if (!empty($booking['is_hidden'])): ?>
                                <div style="margin-top: 6px; font-size: 11px; color: #f7d36d;">Đã ẩn khỏi danh sách chính</div>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <div class="btn-group">
                                <a href="index.php?act=booking/chiTiet&id=<?php echo $booking['booking_id']; ?>"
                                   class="btn-icon view"
                                   title="Xem chi tiết">
                                    👁️
                                </a>
                                <?php if (!empty($booking['tour_id'])): ?>
                                <a href="index.php?act=tour/phanBoNhanSuLichKhoiHanh&id=<?php echo $booking['tour_id']; ?>"
                                   class="btn-icon assign"
                                   title="Phân bổ nhân sự và dịch vụ">
                                    👥
                                </a>
                                <?php endif; ?>
                                <?php if (hasRole(['Admin', 'HDV'])): ?>
                                <a href="index.php?act=booking/chiTiet&id=<?php echo $booking['booking_id']; ?>"
                                   class="btn-icon edit"
                                   title="Chỉnh sửa booking">
                                    ✏️
                                </a>
                                <?php endif; ?>
                                <?php if (hasRole('Admin') && !$isCompletedView && ($booking['trang_thai'] ?? '') === 'HoanTat' && empty($booking['is_hidden'])): ?>
                                <form method="POST" action="index.php" style="display:inline; margin:0;">
                                    <input type="hidden" name="act" value="booking/hideCompleted">
                                    <input type="hidden" name="booking_id" value="<?php echo (int)$booking['booking_id']; ?>">
                                    <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars(csrfToken('booking_hide'), ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit"
                                            class="btn-icon delete"
                                            title="Ẩn khỏi danh sách booking"
                                            onclick="return confirm('Ẩn booking hoàn tất này khỏi danh sách chính?');"
                                            style="border:0; cursor:pointer;">
                                        🙈
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="table-toolbar">
                <div class="table-summary">
                    Tổng số theo bộ lọc: <strong><?php echo $totalBookings ?? count($bookings); ?></strong> booking
                </div>
                <div class="table-hint">
                    Trang hiện tại: <?php echo $currentPageBookings; ?> booking
                </div>
            </div>

            <?php $pageNumber = isset($pageNumber) ? (int)$pageNumber : max(1, (int)($_GET['page'] ?? 1)); ?>
            <?php if (($totalPages ?? 1) > 1): ?>
            <nav class="pagination-shell" aria-label="Phân trang booking">
                <ul class="pagination">
                    <?php if ($pageNumber > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pageNumber - 1])); ?>">‹</a>
                    </li>
                    <?php endif; ?>
                    <?php
                    $pageStart = max(1, $pageNumber - 2);
                    $pageEnd   = min($totalPages, $pageNumber + 2);
                    for ($p = $pageStart; $p <= $pageEnd; $p++):
                    ?>
                    <li class="page-item <?php echo $p === $pageNumber ? 'active' : ''; ?>">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $p])); ?>"><?php echo $p; ?></a>
                    </li>
                    <?php endfor; ?>
                    <?php if ($pageNumber < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pageNumber + 1])); ?>">›</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <div class="section-header" style="padding: 20px 20px 16px; margin-bottom: 0; border-bottom: 1px solid rgba(255,255,255,0.08);">
                <div class="icon">📭</div>
                <div>
                    <h3>Danh sách booking</h3>
                    <small>Chưa có dữ liệu phù hợp với bộ lọc hiện tại</small>
                </div>
            </div>
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <h4 style="margin-bottom: 15px;">Chưa có booking nào</h4>
                <p>Hiện tại chưa có booking nào trong hệ thống hoặc bộ lọc không trả về kết quả.</p>
            </div>
        </div>
    <?php endif; ?>
</div>
<style>tr[data-href]{cursor:pointer;}tr[data-href]:hover td{background:rgba(255,255,255,0.04)!important;}</style>
<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
document.querySelectorAll('tr[data-href]').forEach(function(row){
    row.addEventListener('click',function(e){
        if(e.target.closest('a,button,form,input,select,textarea')) return;
        window.location.assign(row.dataset.href);
    });
});
(function() {
    var prevPaymentCount = null;
    document.addEventListener('adminNotification', function(e) {
        var payload = e && e.detail;
        if (!payload || payload.success !== true) return;
        var payments = Number(payload.payments || 0);
        if (prevPaymentCount !== null && payments > prevPaymentCount) {
            var el = document.getElementById('bkStatChoXacNhan');
            if (el) {
                var cur = parseInt(el.textContent, 10) || 0;
                el.textContent = String(cur + (payments - prevPaymentCount));
                el.style.animation = 'none';
                el.offsetHeight; // reflow
                el.style.animation = '';
            }
            var existing = document.getElementById('bkNewBookingToast');
            if (!existing) {
                existing = document.createElement('div');
                existing.id = 'bkNewBookingToast';
                existing.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#1e293b;border:1px solid #ffc107;color:#ffc107;padding:12px 20px;border-radius:8px;z-index:9999;cursor:pointer;font-size:14px;box-shadow:0 4px 12px rgba(0,0,0,.4)';
                existing.innerHTML = '⚠️ Có booking mới &mdash; <u>Tải lại</u>';
                existing.onclick = function() { window.location.reload(); };
                document.body.appendChild(existing);
                window.setTimeout(function() { if (existing.parentNode) existing.parentNode.removeChild(existing); }, 8000);
            }
        }
        prevPaymentCount = payments;
    });
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
