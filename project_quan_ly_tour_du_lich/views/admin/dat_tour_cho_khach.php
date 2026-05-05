<?php
$pageTitle = 'Đặt Tour Cho Khách';
$currentPage = 'booking';
ob_start();
?>

<style>
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
        animation: dat-tour-header-glow-move 2.8s linear infinite;
        z-index: 1;
        pointer-events: none;
    }

    @keyframes dat-tour-header-glow-move {
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
        font-size: 1.7rem;
        font-weight: 700;
        margin: 0;
        color: #ffe082;
        text-shadow: 0 2px 8px #2d2d2d;
    }

    .page-header-title p {
        color: #fffde7;
        margin: 6px 0 0;
        font-size: 1rem;
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

    .step-indicator {
        display: flex;
        justify-content: space-between;
        margin-bottom: 28px;
        position: relative;
        padding: 0 20px;
    }

    .step-indicator::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 2px;
        background: rgba(255, 255, 255, 0.1);
        z-index: 0;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 1;
        flex: 1;
    }

    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(45, 45, 45, 0.5);
        border: 2px solid rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-bottom: 10px;
        transition: all 0.3s;
        color: var(--text-muted);
    }

    .step.active .step-circle {
        background: var(--accent-gold);
        color: var(--primary-dark);
        border-color: var(--accent-gold);
    }

    .step-label {
        font-size: 12px;
        color: var(--text-muted);
        text-align: center;
    }

    .step.active .step-label {
        color: var(--accent-gold);
        font-weight: 600;
    }

    .form-section {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(212, 175, 55, 0.18);
        border-radius: 8px;
        padding: 30px;
        margin-bottom: 24px;
        backdrop-filter: blur(10px);
    }

    .form-section-header {
        display: flex;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    }

    .form-section-header .icon {
        width: 56px;
        height: 56px;
        border-radius: 8px;
        background: rgba(212, 175, 55, 0.2);
        color: var(--accent-gold);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        margin-right: 15px;
    }

    .form-section-header h3 {
        margin: 0;
        color: var(--text-light);
        font-weight: 600;
        font-size: 18px;
    }

    .form-section-header small {
        color: var(--text-muted);
        font-size: 12px;
        display: block;
        margin-top: 5px;
    }

    .availability-status {
        padding: 15px;
        border-radius: 2px;
        margin-top: 15px;
        display: none;
    }

    .availability-status.success {
        background: rgba(25, 135, 84, 0.1);
        color: #198754;
        border: 1px solid rgba(25, 135, 84, 0.3);
    }

    .availability-status.error {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.3);
    }

    .availability-status.loading {
        background: rgba(13, 202, 240, 0.1);
        color: #0dcaf0;
        border: 1px solid rgba(13, 202, 240, 0.3);
    }


    .customer-type-card {
        border: 3px solid rgba(255, 255, 255, 0.18);
        border-radius: 12px;
        padding: 32px 20px 20px 20px;
        cursor: pointer;
        transition: all 0.2s;
        text-align: center;
        background: rgba(45, 45, 45, 0.45);
        box-shadow: 0 2px 12px 0 rgba(0,0,0,0.08);
        min-width: 180px;
        min-height: 170px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .customer-type-card:hover {
        border-color: var(--accent-gold);
        background: rgba(212, 175, 55, 0.08);
        box-shadow: 0 4px 24px 0 rgba(212,175,55,0.08);
        transform: translateY(-2px) scale(1.03);
    }

    .customer-type-card.active {
        border-color: var(--accent-gold);
        background: rgba(212, 175, 55, 0.18);
        color: #222;
        box-shadow: 0 4px 32px 0 rgba(212,175,55,0.12);
    }

    .customer-type-card .icon {
        font-size: 56px;
        margin-bottom: 18px;
        color: var(--accent-gold);
        background: rgba(212,175,55,0.12);
        border-radius: 50%;
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px 0 rgba(212,175,55,0.08);
    }

    .customer-type-card .title {
        font-weight: 600;
        margin-bottom: 8px;
        color: var(--text-light);
        font-size: 16px;
    }

    .customer-type-card .description {
        font-size: 12px;
        color: var(--text-muted);
    }

    .tour-summary-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(212, 175, 55, 0.18);
        border-radius: 8px;
        padding: 25px;
        margin-bottom: 20px;
        backdrop-filter: blur(10px);
    }

    .tour-summary-card .summary-card-header {
        background: rgba(212, 175, 55, 0.2);
        color: var(--accent-gold);
        padding: 15px;
        border-radius: 8px 8px 0 0;
        margin: -25px -25px 20px -25px;
        font-weight: 600;
        font-size: 14px;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .summary-item:last-child {
        border-bottom: none;
    }

    .summary-label {
        color: var(--text-muted);
        font-size: 13px;
    }

    .summary-value {
        font-weight: 600;
        color: var(--text-light);
        font-size: 13px;
    }

    .total-price {
        background: rgba(212, 175, 55, 0.1);
        padding: 20px;
        border-radius: 2px;
        margin-top: 15px;
        border: 1px solid rgba(212, 175, 55, 0.2);
    }

    .total-price .label {
        font-size: 12px;
        color: var(--text-muted);
        margin-bottom: 8px;
    }

    .total-price .amount {
        font-size: 24px;
        font-weight: bold;
        color: var(--accent-gold);
    }

    .quick-tips {
        background: rgba(255, 193, 7, 0.1);
        border: 1px solid rgba(255, 193, 7, 0.3);
        border-radius: 8px;
        padding: 20px;
    }

    .quick-tips .title {
        font-weight: 600;
        color: #ffc107;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }

    .quick-tips ul {
        margin: 0;
        padding-left: 20px;
        color: var(--text-muted);
    }

    .quick-tips li {
        margin-bottom: 8px;
        font-size: 12px;
    }

    .submit-section {
        background: rgba(45, 45, 45, 0.7);
        padding: 25px;
        border-top: 1px solid rgba(212, 175, 55, 0.2);
        margin: 30px -30px -30px -30px;
        border-radius: 0 0 8px 8px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: var(--text-light);
        font-size: 13px;
        font-weight: 600;
    }

    .form-group .input,
    .form-group .select,
    .form-group textarea {
        background: rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: var(--text-light);
        padding: 10px 12px;
        font-size: 13px;
        border-radius: 4px;
        transition: all 0.3s;
        width: 100%;
        font-family: inherit;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }

    .form-group .input::placeholder,
    .form-group textarea::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

    .form-group .input:focus,
    .form-group .select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--accent-gold);
        box-shadow: 0 0 0 2px rgba(212,175,55,0.15);
    }

    .form-group .select {
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23d4af37' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        padding-right: 30px;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
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

    .two-column-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
    }

    .booking-side-panel {
        position: sticky;
        top: 88px;
    }

    @media (max-width: 992px) {
        .two-column-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .page-header-section {
            padding: 20px;
        }

        .page-header-title h1 {
            font-size: 1.35rem;
        }

        .page-header-avatar {
            width: 54px;
            height: 54px;
            font-size: 1.8rem;
        }
    }

    .hidden {
        display: none !important;
    }

    body.page-booking .content-area:has(#datTourForm) {
        padding: 34px 48px 56px;
        background:
            radial-gradient(circle at 10% 0%, rgba(13, 202, 240, 0.08), transparent 28%),
            radial-gradient(circle at 100% 10%, rgba(212, 175, 55, 0.11), transparent 30%),
            linear-gradient(180deg, rgba(255,255,255,0.018), transparent 260px);
    }

    body.page-booking .content-area:has(#datTourForm) .page-header-section {
        min-height: 154px;
        padding: 28px 34px;
        background:
            linear-gradient(100deg, rgba(28, 31, 33, 0.96) 0%, rgba(30, 42, 45, 0.94) 52%, rgba(119, 102, 45, 0.84) 100%),
            url("<?php echo BASE_URL; ?>public/images/logos/hinh-nen-viet-nam-4k10.jpg");
        background-size: cover;
        background-position: center;
        border: 1px solid rgba(255, 255, 255, 0.09);
        box-shadow: 0 22px 60px rgba(0, 0, 0, 0.28);
    }

    body.page-booking .content-area:has(#datTourForm) .page-header-section::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(0,0,0,0.18), rgba(0,0,0,0.04));
        pointer-events: none;
    }

    body.page-booking .content-area:has(#datTourForm) .page-header-glow {
        display: none;
    }

    body.page-booking .content-area:has(#datTourForm) .page-header-inner {
        position: relative;
        z-index: 2;
        align-items: center;
    }

    body.page-booking .content-area:has(#datTourForm) .page-header-main {
        align-items: center;
        gap: 18px;
    }

    body.page-booking .content-area:has(#datTourForm) .page-header-avatar {
        width: 74px;
        height: 74px;
        border-radius: 8px;
        background: rgba(212, 175, 55, 0.18);
        border: 1px solid rgba(255, 224, 130, 0.32);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.16), 0 16px 34px rgba(0,0,0,0.24);
    }

    body.page-booking .content-area:has(#datTourForm) .page-header-title h1 {
        font-size: 2rem;
        line-height: 1.18;
        letter-spacing: 0;
        text-shadow: none;
    }

    body.page-booking .content-area:has(#datTourForm) .page-header-title p {
        max-width: 680px;
        color: rgba(255,255,255,0.86);
        text-shadow: none;
    }

    body.page-booking .content-area:has(#datTourForm) .page-header-section .btn,
    body.page-booking .content-area:has(#datTourForm) .view-switch-link,
    body.page-booking .content-area:has(#datTourForm) .form-section .btn {
        border-radius: 8px;
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    body.page-booking .content-area:has(#datTourForm) .page-header-section .btn {
        min-height: 46px;
        padding-inline: 28px;
    }

    body.page-booking .content-area:has(#datTourForm) .booking-view-switch {
        gap: 12px;
        margin-bottom: 34px;
    }

    body.page-booking .content-area:has(#datTourForm) .view-switch-link {
        min-height: 58px;
        padding: 14px 22px;
        background: rgba(255,255,255,0.045);
        border: 1px solid rgba(255,255,255,0.12);
        box-shadow: 0 10px 24px rgba(0,0,0,0.12);
    }

    body.page-booking .content-area:has(#datTourForm) .view-switch-link:hover,
    body.page-booking .content-area:has(#datTourForm) .view-switch-link.active {
        background: rgba(212, 175, 55, 0.14);
        border-color: rgba(212, 175, 55, 0.6);
        color: var(--accent-gold);
        box-shadow: 0 16px 34px rgba(0,0,0,0.18);
    }

    body.page-booking .content-area:has(#datTourForm) .step-indicator {
        margin: 6px 0 34px;
        padding: 0 44px;
    }

    body.page-booking .content-area:has(#datTourForm) .step-indicator::before {
        top: 24px;
        background: linear-gradient(90deg, rgba(212, 175, 55, 0.45), rgba(255,255,255,0.12), rgba(255,255,255,0.12));
    }

    body.page-booking .content-area:has(#datTourForm) .step-circle {
        width: 50px;
        height: 50px;
        border-radius: 999px;
        background: rgba(255,255,255,0.045);
        box-shadow: 0 12px 28px rgba(0,0,0,0.18);
    }

    body.page-booking .content-area:has(#datTourForm) .step.active .step-circle {
        box-shadow: 0 14px 30px rgba(212,175,55,0.18);
    }

    body.page-booking .content-area:has(#datTourForm) .step-label {
        margin-top: 4px;
        font-size: 13px;
    }

    body.page-booking .content-area:has(#datTourForm) .two-column-layout {
        grid-template-columns: minmax(0, 2.1fr) minmax(320px, 0.9fr);
        gap: 38px;
        align-items: start;
    }

    body.page-booking .content-area:has(#datTourForm) .form-section,
    body.page-booking .content-area:has(#datTourForm) .tour-summary-card,
    body.page-booking .content-area:has(#datTourForm) .quick-tips {
        background: rgba(28, 30, 31, 0.78);
        border-color: rgba(212, 175, 55, 0.22);
        box-shadow: 0 14px 36px rgba(0,0,0,0.18);
    }

    body.page-booking .content-area:has(#datTourForm) .form-section {
        padding: 32px 38px;
    }

    body.page-booking .content-area:has(#datTourForm) .form-section-header {
        border-bottom-color: rgba(255,255,255,0.09);
    }

    body.page-booking .content-area:has(#datTourForm) .form-section-header .icon {
        width: 70px;
        height: 70px;
        background: rgba(212, 175, 55, 0.14);
        border: 1px solid rgba(212, 175, 55, 0.24);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.12);
    }

    body.page-booking .content-area:has(#datTourForm) .form-section-header h3 {
        font-size: 21px;
        line-height: 1.3;
    }

    body.page-booking .content-area:has(#datTourForm) .form-row {
        gap: 22px 24px;
    }

    body.page-booking .content-area:has(#datTourForm) .form-group label {
        color: rgba(245,245,245,0.78);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    body.page-booking .content-area:has(#datTourForm) .form-group .input,
    body.page-booking .content-area:has(#datTourForm) .form-group .select,
    body.page-booking .content-area:has(#datTourForm) .form-group textarea {
        min-height: 52px;
        border-radius: 8px;
        border-color: rgba(255,255,255,0.14);
        background-color: rgba(255,255,255,0.08);
    }

    body.page-booking .content-area:has(#datTourForm) .form-group textarea {
        min-height: 130px;
    }

    body.page-booking .content-area:has(#datTourForm) .form-group .input:focus,
    body.page-booking .content-area:has(#datTourForm) .form-group .select:focus,
    body.page-booking .content-area:has(#datTourForm) .form-group textarea:focus {
        border-color: rgba(13, 202, 240, 0.58);
        box-shadow: 0 0 0 3px rgba(13, 202, 240, 0.12);
    }

    body.page-booking .content-area:has(#datTourForm) .customer-type-card {
        border-radius: 8px;
        border-width: 1px;
        background: rgba(255,255,255,0.045);
        box-shadow: 0 14px 30px rgba(0,0,0,0.16);
    }

    body.page-booking .content-area:has(#datTourForm) .customer-type-card:hover,
    body.page-booking .content-area:has(#datTourForm) .customer-type-card.active {
        transform: translateY(-4px);
        box-shadow: 0 20px 44px rgba(0,0,0,0.22);
    }

    body.page-booking .content-area:has(#datTourForm) .submit-section {
        background: rgba(255,255,255,0.035);
        border-top-color: rgba(255,255,255,0.09);
    }

    body.page-booking .content-area:has(#datTourForm) .booking-side-panel {
        position: sticky;
        top: 88px;
    }

    body.page-booking .content-area:has(#datTourForm) .tour-summary-card {
        border-radius: 8px;
        overflow: hidden;
    }

    body.page-booking .content-area:has(#datTourForm) .tour-summary-card .summary-card-header {
        position: static;
        inset: auto;
        width: auto;
        min-height: 0;
        display: block;
        margin: -25px -25px 22px;
        border-radius: 0;
        padding: 16px 20px;
        background: linear-gradient(90deg, rgba(212, 175, 55, 0.22), rgba(13, 202, 240, 0.08));
        border-bottom: 1px solid rgba(212, 175, 55, 0.22);
        box-shadow: none;
        backdrop-filter: none;
    }

    body.page-booking .content-area:has(#datTourForm) .summary-item {
        gap: 16px;
        border-bottom-color: rgba(255,255,255,0.08);
    }

    body.page-booking .content-area:has(#datTourForm) .summary-value {
        text-align: right;
        word-break: break-word;
    }

    body.page-booking .content-area:has(#datTourForm) .total-price {
        border-radius: 8px;
        background: rgba(212, 175, 55, 0.12);
    }

    body.page-booking .content-area:has(#datTourForm) .quick-tips {
        border-radius: 8px;
        background: rgba(255, 193, 7, 0.08);
    }

    body.theme-light.page-booking .content-area:has(#datTourForm) .form-section,
    body.theme-light.page-booking .content-area:has(#datTourForm) .tour-summary-card,
    body.theme-light.page-booking .content-area:has(#datTourForm) .quick-tips {
        background: rgba(255,255,255,0.9) !important;
    }

    @media (max-width: 1200px) {
        body.page-booking .content-area:has(#datTourForm) .two-column-layout {
            grid-template-columns: 1fr;
        }

        body.page-booking .content-area:has(#datTourForm) .booking-side-panel {
            position: static;
        }
    }

    @media (max-width: 900px) {
        body.page-booking .content-area:has(#datTourForm) {
            padding: 24px 18px 42px;
        }

        body.page-booking .content-area:has(#datTourForm) .page-header-section,
        body.page-booking .content-area:has(#datTourForm) .form-section {
            padding: 24px;
        }

        body.page-booking .content-area:has(#datTourForm) .page-header-inner,
        body.page-booking .content-area:has(#datTourForm) .page-header-main {
            align-items: flex-start;
        }

        body.page-booking .content-area:has(#datTourForm) .page-header-avatar {
            width: 58px;
            height: 58px;
            font-size: 1.8rem;
        }

        body.page-booking .content-area:has(#datTourForm) .step-indicator {
            padding: 0;
        }

        body.page-booking .content-area:has(#datTourForm) .step-label {
            font-size: 12px;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header-section">
    <div class="page-header-glow"></div>
    <div class="page-header-inner">
        <div class="page-header-main">
            <div class="page-header-avatar">➕</div>
            <div class="page-header-title">
                <h1>Đặt Tour Cho Khách Hàng</h1>
                <p>Tạo booking mới và quản lý thông tin đặt tour</p>
            </div>
        </div>
        <div>
            <a href="index.php?act=admin/quanLyBooking" class="btn btn-secondary">
                ← Quay lại
            </a>
        </div>
    </div>
</div>

<!-- Alerts -->
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

<?php $validationPayload = getValidationErrors(); ?>
<?php if (!empty($validationPayload['message'])): ?>
    <div class="alert alert-danger">
        ⚠ <?php echo htmlspecialchars((string)$validationPayload['message']); ?>
    </div>
<?php endif; ?>

<div class="booking-view-switch">
    <a href="index.php?act=admin/quanLyBooking" class="view-switch-link">
        📋 Danh sách booking
    </a>
    <a href="index.php?act=booking/datTourChoKhach" class="view-switch-link active">
        ➕ Đặt tour cho khách
    </a>
</div>

<!-- Step Indicator -->
<div class="step-indicator">
    <div class="step active" data-step="1">
        <div class="step-circle">1</div>
        <div class="step-label">Chọn tour</div>
    </div>
    <div class="step" data-step="2">
        <div class="step-circle">2</div>
        <div class="step-label">Thông tin khách</div>
    </div>
    <div class="step" data-step="3">
        <div class="step-circle">3</div>
        <div class="step-label">Yêu cầu & Ghi chú</div>
    </div>
</div>

<div class="two-column-layout">
    <!-- Main Form -->
    <div>
        <form method="POST" action="index.php?act=booking/datTourChoKhach" id="datTourForm">
            <?php echo csrfField('booking_staff_create'); ?>
            <!-- Step 1: Chọn Tour -->
            <div class="form-section">
                <div class="form-section-header">
                    <div class="icon">📍</div>
                    <div>
                        <h3>Bước 1: Chọn Tour & Lịch Trình</h3>
                        <small>Chọn tour và ngày khởi hành phù hợp</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>📍 Chọn tour <span style="color: #dc3545;">*</span></label>
                        <select name="tour_id" id="tour_id" class="select" required>
                            <option value="">-- Vui lòng chọn tour --</option>
                            <?php foreach ($tours as $t): ?>
                                <option value="<?php echo $t['tour_id']; ?>" 
                                    data-gia="<?php echo $t['gia_co_ban']; ?>"
                                    <?php echo (isset($formData['tour_id']) && $formData['tour_id'] == $t['tour_id']) || (isset($tour) && $tour['tour_id'] == $t['tour_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($t['ten_tour']); ?> - <?php echo number_format($t['gia_co_ban']); ?> ₫
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>📅 Ngày khởi hành <span style="color: #dc3545;">*</span></label>
                        <input type="date" name="ngay_khoi_hanh" id="ngay_khoi_hanh" class="input"
                            value="<?php echo $formData['ngay_khoi_hanh'] ?? ''; ?>" 
                            min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>✅ Ngày kết thúc</label>
                        <input type="date" name="ngay_ket_thuc" id="ngay_ket_thuc" class="input"
                            value="<?php echo $formData['ngay_ket_thuc'] ?? ''; ?>">
                        <small style="color: var(--text-muted); font-size: 11px; margin-top: 5px; display: block;">
                            Nếu bỏ trống, hệ thống sẽ dùng ngày khởi hành
                        </small>
                    </div>

                    <div class="form-group">
                        <label>👥 Số lượng người <span style="color: #dc3545;">*</span></label>
                        <input type="number" name="so_nguoi" id="so_nguoi" class="input"
                            value="<?php echo $formData['so_nguoi'] ?? '1'; ?>" 
                            min="1" required>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <div id="cho-trong-info" class="availability-status">
                            <div style="display: flex; align-items: center;">
                                <span id="status-text">Đang kiểm tra chỗ trống...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Thông tin Khách hàng -->
            <div class="form-section">
                <div class="form-section-header">
                    <div class="icon">👤</div>
                    <div>
                        <h3>Bước 2: Thông Tin Khách Hàng</h3>
                        <small>Nhập thông tin liên hệ của khách hàng</small>
                    </div>
                </div>

                <!-- Loại khách -->
                <div style="margin-bottom: 30px;">
                    <label class="form-group" style="margin-bottom: 15px;">
                        <span style="display: block; margin-bottom: 10px;">👥 Loại khách hàng</span>
                    </label>
                    <div class="form-row">
                        <div>
                            <input type="radio" name="loai_khach" value="le" id="loai_le" class="hidden"
                                <?php echo (!isset($formData['loai_khach']) || $formData['loai_khach'] == 'le') ? 'checked' : ''; ?>>
                            <label for="loai_le" class="customer-type-card <?php echo (!isset($formData['loai_khach']) || $formData['loai_khach'] == 'le') ? 'active' : ''; ?>">
                                <div class="icon">👤</div>
                                <div class="title">Khách lẻ</div>
                                <div class="description">Cá nhân hoặc gia đình (1-2 người)</div>
                            </label>
                        </div>
                        <div>
                            <input type="radio" name="loai_khach" value="doan" id="loai_doan" class="hidden"
                                <?php echo (isset($formData['loai_khach']) && $formData['loai_khach'] == 'doan') ? 'checked' : ''; ?>>
                            <label for="loai_doan" class="customer-type-card <?php echo (isset($formData['loai_khach']) && $formData['loai_khach'] == 'doan') ? 'active' : ''; ?>">
                                <div class="icon">👥</div>
                                <div class="title">Đoàn khách</div>
                                <div class="description">Công ty hoặc tổ chức (3+ người)</div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Tên công ty (ẩn mặc định) -->
                <div id="cong-ty-field" class="form-group <?php echo (isset($formData['loai_khach']) && $formData['loai_khach'] == 'doan') ? '' : 'hidden'; ?>">
                    <label>🏢 Tên công ty/Tổ chức</label>
                    <input type="text" name="ten_cong_ty" id="ten_cong_ty" class="input"
                        value="<?php echo htmlspecialchars($formData['ten_cong_ty'] ?? ''); ?>"
                        placeholder="Nhập tên công ty hoặc tổ chức...">
                </div>

                <!-- Thông tin cá nhân -->
                <div class="form-row">
                    <div class="form-group">
                        <label>👤 Họ và tên <span style="color: #dc3545;">*</span></label>
                        <input type="text" name="ho_ten" id="ho_ten" class="input"
                            value="<?php echo htmlspecialchars($formData['ho_ten'] ?? ''); ?>"
                            placeholder="Nguyễn Văn A" required>
                    </div>

                    <div class="form-group">
                        <label>⚧️ Giới tính</label>
                        <select name="gioi_tinh" id="gioi_tinh" class="select">
                            <option value="">-- Chọn giới tính --</option>
                            <option value="Nam" <?php echo (isset($formData['gioi_tinh']) && $formData['gioi_tinh'] == 'Nam') ? 'selected' : ''; ?>>Nam</option>
                            <option value="Nữ" <?php echo (isset($formData['gioi_tinh']) && $formData['gioi_tinh'] == 'Nữ') ? 'selected' : ''; ?>>Nữ</option>
                            <option value="Khác" <?php echo (isset($formData['gioi_tinh']) && $formData['gioi_tinh'] == 'Khác') ? 'selected' : ''; ?>>Khác</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>✉️ Email <span style="color: #dc3545;">*</span></label>
                        <input type="email" name="email" id="email" class="input"
                            value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>"
                            placeholder="email@example.com">
                    </div>

                    <div class="form-group">
                        <label>📞 Số điện thoại <span style="color: #dc3545;">*</span></label>
                        <input type="tel" name="so_dien_thoai" id="so_dien_thoai" class="input"
                            value="<?php echo htmlspecialchars($formData['so_dien_thoai'] ?? ''); ?>"
                            placeholder="0987654321">
                    </div>

                    <div class="form-group">
                        <label>📍 Địa chỉ</label>
                        <input type="text" name="dia_chi" id="dia_chi" class="input"
                            value="<?php echo htmlspecialchars($formData['dia_chi'] ?? ''); ?>"
                            placeholder="Số nhà, Đường, Quận, Thành phố">
                    </div>

                    <div class="form-group">
                        <label>🎂 Ngày sinh</label>
                        <input type="date" name="ngay_sinh" id="ngay_sinh" class="input"
                            value="<?php echo $formData['ngay_sinh'] ?? ''; ?>">
                    </div>
                </div>
            </div>

            <!-- Step 3: Yêu cầu đặc biệt -->
            <div class="form-section">
                <div class="form-section-header">
                    <div class="icon">💬</div>
                    <div>
                        <h3>Bước 3: Yêu Cầu Đặc Biệt & Ghi Chú</h3>
                        <small>Thêm yêu cầu đặc biệt hoặc ghi chú nếu có</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>⭐ Yêu cầu đặc biệt</label>
                        <textarea name="yeu_cau_dac_biet" id="yeu_cau_dac_biet" class="textarea" rows="4"
                            placeholder="VD: Ăn chay, dị ứng thực phẩm, cần xe lăn, phòng riêng..."><?php echo htmlspecialchars($formData['yeu_cau_dac_biet'] ?? ''); ?></textarea>
                        <small style="color: var(--text-muted); font-size: 11px; margin-top: 5px; display: block;">
                            ℹ️ Thông tin này sẽ được gửi cho đội ngũ điều phối
                        </small>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>📝 Ghi chú nội bộ</label>
                        <textarea name="ghi_chu" id="ghi_chu" class="textarea" rows="3"
                            placeholder="Ghi chú dành cho nhân viên nội bộ..."><?php echo htmlspecialchars($formData['ghi_chu'] ?? ''); ?></textarea>
                        <small style="color: var(--text-muted); font-size: 11px; margin-top: 5px; display: block;">
                            🔒 Ghi chú này chỉ hiển thị cho nhân viên
                        </small>
                    </div>
                </div>

                <!-- Submit Section -->
                <div class="submit-section">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <small style="color: var(--text-muted); font-size: 11px;">
                                🛡️ Thông tin được mã hóa và bảo mật
                            </small>
                        </div>
                        <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">
                            ✓ Xác nhận đặt tour
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Sidebar -->
    <div>
        <div class="booking-side-panel">
            <!-- Tour Summary -->
            <div class="tour-summary-card">
                <div class="summary-card-header">
                    📋 Tóm tắt đặt tour
                </div>
                <div id="tour-summary">
                    <div class="summary-item">
                        <span class="summary-label">Tour:</span>
                        <span class="summary-value" id="summary-tour">Chưa chọn</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Ngày khởi hành:</span>
                        <span class="summary-value" id="summary-date">--/--/----</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Số người:</span>
                        <span class="summary-value" id="summary-people">0 người</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Giá/người:</span>
                        <span class="summary-value" id="summary-price">0 ₫</span>
                    </div>
                    <div class="total-price">
                        <div class="label">Tổng cộng:</div>
                        <div class="amount" id="summary-total">0 ₫</div>
                    </div>
                </div>
            </div>

            <!-- Quick Tips -->
            <div class="quick-tips">
                <div class="title">
                    💡 Lưu ý quan trọng
                </div>
                <ul>
                    <li>Email hoặc số điện thoại là bắt buộc</li>
                    <li>Kiểm tra kỹ thông tin trước khi xác nhận</li>
                    <li>Chỗ trống được cập nhật theo thời gian thực</li>
                    <li>Đối với đoàn, vui lòng nhập tên công ty/tổ chức</li>
                    <li>Yêu cầu đặc biệt giúp chúng tôi phục vụ tốt hơn</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
    // Customer type selection
    document.querySelectorAll('input[name="loai_khach"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.customer-type-card').forEach(card => {
                card.classList.remove('active');
            });
            this.parentElement.querySelector('.customer-type-card').classList.add('active');
            
            const congTyField = document.getElementById('cong-ty-field');
            if (this.value === 'doan') {
                congTyField.classList.remove('hidden');
            } else {
                congTyField.classList.add('hidden');
            }
        });
    });

    // Update summary
    function updateSummary() {
        const tourSelect = document.getElementById('tour_id');
        const selectedOption = tourSelect.options[tourSelect.selectedIndex];
        const soNguoi = parseInt(document.getElementById('so_nguoi').value) || 0;
        const ngayKhoiHanh = document.getElementById('ngay_khoi_hanh').value;
        const gia = parseInt(selectedOption.dataset.gia) || 0;

        document.getElementById('summary-tour').textContent = selectedOption.text.split(' - ')[0] || 'Chưa chọn';
        document.getElementById('summary-date').textContent = ngayKhoiHanh ? new Date(ngayKhoiHanh).toLocaleDateString('vi-VN') : '--/--/----';
        document.getElementById('summary-people').textContent = soNguoi + ' người';
        document.getElementById('summary-price').textContent = gia.toLocaleString('vi-VN') + ' ₫';
        document.getElementById('summary-total').textContent = (gia * soNguoi).toLocaleString('vi-VN') + ' ₫';
    }

    // Check availability
    function kiemTraChoTrong() {
        const tourId = document.getElementById('tour_id').value;
        const ngayKhoiHanh = document.getElementById('ngay_khoi_hanh').value;
        const soNguoi = document.getElementById('so_nguoi').value;
        const infoDiv = document.getElementById('cho-trong-info');
        const statusText = document.getElementById('status-text');

        if (!tourId || !ngayKhoiHanh || !soNguoi) {
            infoDiv.style.display = 'none';
            return;
        }

        infoDiv.style.display = 'block';
        infoDiv.className = 'availability-status loading';
        statusText.innerHTML = '⏳ Đang kiểm tra chỗ trống...';

        fetch(`index.php?act=booking/kiemTraChoTrong&tour_id=${tourId}&ngay_khoi_hanh=${ngayKhoiHanh}&so_nguoi=${soNguoi}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    infoDiv.style.display = 'none';
                    return;
                }

                if (data.co_cho) {
                    infoDiv.className = 'availability-status success';
                    statusText.innerHTML = `✓ <strong>Còn ${data.cho_trong} chỗ trống</strong> - Đã đặt: ${data.da_dat}/${data.toi_da} người`;
                } else {
                    infoDiv.className = 'availability-status error';
                    statusText.innerHTML = `✗ <strong>Không đủ chỗ!</strong> Chỉ còn ${data.cho_trong} chỗ trống (Đã đặt: ${data.da_dat}/${data.toi_da})`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                infoDiv.style.display = 'none';
            });
    }

    // Event listeners
    function syncNgayKetThucMin() {
        const endInput = document.getElementById('ngay_ket_thuc');
        const startInput = document.getElementById('ngay_khoi_hanh');
        if (!endInput || !startInput) return;
        if (startInput.value) {
            endInput.min = startInput.value;
            if (!endInput.value || endInput.value < startInput.value) {
                endInput.value = startInput.value;
            }
        }
    }

    document.getElementById('tour_id').addEventListener('change', function() {
        updateSummary();
        kiemTraChoTrong();
    });
    document.getElementById('ngay_khoi_hanh').addEventListener('change', function() {
        syncNgayKetThucMin();
        kiemTraChoTrong();
    });
    document.getElementById('so_nguoi').addEventListener('input', function() {
        updateSummary();
        kiemTraChoTrong();
    });

    // Form validation
    document.getElementById('datTourForm').addEventListener('submit', function(e) {
        const email = document.getElementById('email').value;
        const soDienThoai = document.getElementById('so_dien_thoai').value;

        if (!email && !soDienThoai) {
            e.preventDefault();
            alert('Vui lòng nhập email hoặc số điện thoại.');
            return false;
        }

        const infoDiv = document.getElementById('cho-trong-info');
        if (infoDiv.style.display !== 'none' && infoDiv.classList.contains('error')) {
            e.preventDefault();
            alert('Không đủ chỗ trống. Vui lòng chọn ngày khác hoặc giảm số lượng người.');
            return false;
        }
    });

    // Initialize
    updateSummary();
    syncNgayKetThucMin();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
