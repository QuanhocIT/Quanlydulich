<?php 
$isCapNhat = isset($tour) && isset($tour['tour_id']); 
$pageTitle = $isCapNhat ? 'Sửa tour' : 'Thêm tour mới';
$currentPage = 'tourCreate';
ob_start();
?>
<style>
    /* =========================================================
       AVENTURA TOUR CREATION - ULTRA MODERN DARK NAVY UI
       Faithfully matched to reference design (Image 2)
       ========================================================= */
    :root {
        --bg-main: #070d1e;
        --card-bg: rgba(15, 26, 54, 0.85);
        --card-border: rgba(56, 189, 248, 0.12);
        --card-border-hover: rgba(56, 189, 248, 0.35);
        --input-bg: rgba(8, 16, 38, 0.8);
        --input-border: rgba(255, 255, 255, 0.14);
        --input-border-focus: #2563eb;
        --text-primary: #f8fafc;
        --text-muted: #94a3b8;
        --accent-blue: #2563eb;
        --accent-cyan: #06b6d4;
        --accent-blue-hover: #1d4ed8;
        --font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    body:not(.theme-light).page-tour .content-area,
    body:not(.theme-light).page-tourCreate .content-area {
        background: #070d1e !important;
        background-image: 
            radial-gradient(circle at 10% 10%, rgba(37, 99, 235, 0.12), transparent 40%),
            radial-gradient(circle at 90% 20%, rgba(6, 182, 212, 0.08), transparent 40%),
            linear-gradient(180deg, #070d1e 0%, #0a1329 100%) !important;
        padding: 24px 36px 60px !important;
        min-height: 100vh;
        color: var(--text-primary);
        font-family: var(--font-family);
    }

    body.theme-light.page-tour .content-area,
    body.theme-light.page-tourCreate .content-area,
    body.theme-light .tour-editor-container {
        --bg-main: #f8fafc;
        --card-bg: #ffffff;
        --card-border: #e2e8f0;
        --card-border-hover: #cbd5e1;
        --input-bg: #ffffff;
        --input-border: #cbd5e1;
        --input-border-focus: #2563eb;
        --text-primary: #0f172a;
        --text-muted: #64748b;
        background: #f8fafc !important;
        background-image: none !important;
        padding: 24px 36px 60px !important;
        min-height: 100vh;
        color: #0f172a !important;
        font-family: var(--font-family);
    }

    .tour-editor-container {
        max-width: 1440px;
        margin: 0 auto;
    }

    /* ---------------- Hero Banner ---------------- */
    .hero-banner-card {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 24px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 16px 36px rgba(0, 0, 0, 0.45);
        min-height: 170px;
        display: flex;
        align-items: center;
        background: url('<?php echo BASE_URL; ?>public/images/dashboard/halong_banner.jpg') center/cover no-repeat;
    }

    .hero-banner-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(6, 12, 28, 0.96) 0%, rgba(8, 16, 38, 0.82) 48%, rgba(8, 18, 44, 0.35) 100%);
        backdrop-filter: blur(2px);
    }

    .hero-banner-content {
        position: relative;
        z-index: 2;
        width: 100%;
        padding: 28px 36px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .hero-left-meta {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .kicker-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(6, 182, 212, 0.15);
        border: 1px solid rgba(6, 182, 212, 0.35);
        color: #38bdf8;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 9999px;
        width: fit-content;
        letter-spacing: 0.3px;
    }

    .hero-main-title {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 2px 0;
        font-size: 1.85rem;
        font-weight: 700;
        color: #ffffff;
        letter-spacing: -0.3px;
    }

    .hero-main-title .plane-icon {
        color: #38bdf8;
        font-size: 1.75rem;
        transform: rotate(-15deg);
        display: inline-block;
    }

    .hero-subtitle-text {
        color: #cbd5e1;
        font-size: 0.92rem;
        margin: 0;
        font-weight: 400;
    }

    .hero-right-actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 14px;
    }

    .breadcrumbs-list {
        font-size: 0.8rem;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .breadcrumbs-list a {
        color: #94a3b8;
        text-decoration: none;
        transition: color 0.2s;
    }

    .breadcrumbs-list a:hover {
        color: #38bdf8;
    }

    .breadcrumbs-list span.current {
        color: #e2e8f0;
        font-weight: 600;
    }

    .btn-glass-back {
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.18);
        color: #ffffff;
        padding: 10px 22px;
        border-radius: 10px;
        text-decoration: none;
        font-size: 0.88rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.25s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    }

    .btn-glass-back:hover {
        background: rgba(30, 41, 59, 0.85);
        border-color: rgba(56, 189, 248, 0.4);
        color: #38bdf8;
        transform: translateX(-3px);
    }

    /* ---------------- Grid Layout ---------------- */
    .editor-grid {
        display: grid;
        grid-template-columns: 1.18fr 0.82fr;
        gap: 22px;
        margin-bottom: 22px;
    }

    @media (max-width: 1100px) {
        .editor-grid {
            grid-template-columns: 1fr;
        }
    }

    /* ---------------- Modern Cards ---------------- */
    .modern-panel-card {
        background: var(--card-bg);
        backdrop-filter: blur(14px);
        border: 1px solid var(--card-border);
        border-radius: 14px;
        padding: 24px 26px;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.25);
        transition: border-color 0.25s ease;
        margin-bottom: 22px;
    }

    .modern-panel-card:hover {
        border-color: var(--card-border-hover);
    }

    .card-header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .card-title-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-title-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: rgba(37, 99, 235, 0.2);
        color: #38bdf8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .card-heading {
        margin: 0;
        font-size: 1.08rem;
        font-weight: 700;
        color: #ffffff;
        letter-spacing: -0.2px;
    }

    /* ---------------- Form Elements ---------------- */
    .form-group-label {
        display: block;
        font-size: 0.82rem;
        font-weight: 600;
        color: #e2e8f0;
        margin-bottom: 8px;
    }

    .form-group-label .star-required {
        color: #f43f5e;
        margin-left: 2px;
    }

    .input-wrapper {
        position: relative;
    }

    .input-lead-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        font-size: 1rem;
        pointer-events: none;
    }

    .modern-input, .modern-select {
        width: 100%;
        height: 44px;
        background: var(--input-bg);
        border: 1px solid var(--input-border);
        border-radius: 8px;
        padding: 0 14px;
        color: #ffffff;
        font-size: 0.88rem;
        transition: all 0.2s ease;
        outline: none;
        box-sizing: border-box;
    }

    .modern-input.with-lead-icon, .modern-select.with-lead-icon {
        padding-left: 40px;
    }

    .modern-input:focus, .modern-select:focus {
        border-color: #38bdf8;
        background: rgba(12, 22, 50, 0.95);
        box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15);
    }

    .modern-select {
        appearance: none;
        cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
    }

    .modern-select option {
        background: #0f172a;
        color: #ffffff;
    }

    .form-row-2col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }

    @media (max-width: 640px) {
        .form-row-2col {
            grid-template-columns: 1fr;
        }
    }

    /* ---------------- Rich Textarea Area ---------------- */
    .rich-editor-box {
        border: 1px solid var(--input-border);
        border-radius: 8px;
        background: var(--input-bg);
        overflow: hidden;
        transition: border-color 0.2s;
    }

    .rich-editor-box:focus-within {
        border-color: #38bdf8;
        box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15);
    }

    .rich-toolbar {
        background: rgba(255, 255, 255, 0.04);
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        padding: 8px 12px;
        display: flex;
        gap: 6px;
        align-items: center;
    }

    .toolbar-btn {
        background: transparent;
        border: none;
        color: #94a3b8;
        width: 28px;
        height: 28px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.95rem;
        transition: all 0.15s;
    }

    .toolbar-btn:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #ffffff;
    }

    .toolbar-divider {
        width: 1px;
        height: 18px;
        background: rgba(255, 255, 255, 0.1);
        margin: 0 4px;
    }

    .modern-textarea {
        width: 100%;
        background: transparent;
        border: none;
        padding: 14px;
        color: #ffffff;
        font-size: 0.88rem;
        min-height: 110px;
        outline: none;
        resize: vertical;
        font-family: inherit;
        line-height: 1.5;
        box-sizing: border-box;
    }

    .textarea-footer-counter {
        text-align: right;
        padding: 4px 12px 8px;
        font-size: 0.75rem;
        color: #64748b;
    }

    /* ---------------- Image & Media Card ---------------- */
    .dropzone-upload-box {
        border: 2px dashed rgba(56, 189, 248, 0.35);
        border-radius: 12px;
        background: rgba(15, 28, 62, 0.4);
        padding: 24px 16px;
        text-align: center;
        cursor: pointer;
        transition: all 0.25s ease;
        margin-bottom: 18px;
    }

    .dropzone-upload-box:hover, .dropzone-upload-box.drag-over {
        border-color: #38bdf8;
        background: rgba(15, 28, 62, 0.75);
        box-shadow: 0 0 20px rgba(56, 189, 248, 0.15);
    }

    .dropzone-cloud-icon {
        font-size: 2.4rem;
        color: #38bdf8;
        margin-bottom: 6px;
    }

    .dropzone-primary-text {
        font-size: 0.88rem;
        color: #cbd5e1;
        margin-bottom: 12px;
    }

    .btn-dropzone-action {
        background: #2563eb;
        color: #ffffff;
        border: none;
        padding: 8px 22px;
        border-radius: 9999px;
        font-size: 0.84rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);
    }

    .btn-dropzone-action:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
    }

    .dropzone-hint-text {
        font-size: 0.76rem;
        color: #64748b;
        margin-top: 10px;
    }

    /* Image Gallery Grid */
    .media-gallery-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 10px;
        margin-bottom: 18px;
    }

    @media (max-width: 600px) {
        .media-gallery-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .gallery-thumb-item {
        position: relative;
        aspect-ratio: 4 / 3;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.12);
        background: #0b142c;
    }

    .gallery-thumb-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.3s;
    }

    .gallery-thumb-item:hover img {
        transform: scale(1.08);
    }

    .gallery-thumb-item .thumb-overlay-action {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        opacity: 0;
        transition: opacity 0.2s;
    }

    .gallery-thumb-item:hover .thumb-overlay-action {
        opacity: 1;
    }

    .btn-thumb-remove {
        background: rgba(239, 68, 68, 0.85);
        color: #ffffff;
        border: none;
        width: 26px;
        height: 26px;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
    }

    .gallery-add-tile {
        aspect-ratio: 4 / 3;
        border: 1px dashed rgba(255, 255, 255, 0.22);
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        cursor: pointer;
        font-size: 0.74rem;
        gap: 4px;
        transition: all 0.2s;
        background: rgba(255, 255, 255, 0.02);
    }

    .gallery-add-tile:hover {
        border-color: #38bdf8;
        color: #38bdf8;
        background: rgba(56, 189, 248, 0.06);
    }

    /* ---------------- Lịch trình tour ---------------- */
    .timeline-tabs-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
        overflow-x: auto;
        padding-bottom: 4px;
    }

    .day-pill-tab {
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #cbd5e1;
        padding: 6px 18px;
        border-radius: 9999px;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.2s ease;
    }

    .day-pill-tab:hover {
        background: rgba(255, 255, 255, 0.12);
        color: #ffffff;
    }

    .day-pill-tab.active {
        background: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
    }

    .day-pill-add-btn {
        width: 32px;
        height: 32px;
        border-radius: 9999px;
        border: 1px dashed rgba(255, 255, 255, 0.25);
        background: transparent;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.95rem;
        transition: all 0.2s;
    }

    .day-pill-add-btn:hover {
        border-color: #38bdf8;
        color: #38bdf8;
        background: rgba(56, 189, 248, 0.1);
    }

    .day-detail-panel {
        background: rgba(10, 18, 42, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 10px;
        padding: 16px 20px;
    }

    .day-header-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 12px;
        margin-bottom: 14px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    }

    .day-heading-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        font-size: 0.95rem;
        color: #ffffff;
    }

    .grip-icon {
        color: #64748b;
        cursor: grab;
    }

    .day-body-flex {
        display: grid;
        grid-template-columns: 1fr 200px;
        gap: 18px;
        align-items: start;
    }

    @media (max-width: 768px) {
        .day-body-flex {
            grid-template-columns: 1fr;
        }
    }

    .timeline-items-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .timeline-row-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        font-size: 0.85rem;
        color: #cbd5e1;
    }

    .time-badge {
        color: #38bdf8;
        font-weight: 600;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .day-preview-image-box {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.1);
        aspect-ratio: 16 / 10;
        background: #0c142c;
    }

    .day-preview-image-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .day-img-edit-tag {
        position: absolute;
        bottom: 8px;
        right: 8px;
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #ffffff;
        font-size: 0.72rem;
        padding: 3px 8px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        gap: 4px;
        cursor: pointer;
    }

    /* ---------------- Giá tour ---------------- */
    .input-group-suffix {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-group-suffix .modern-input {
        padding-right: 52px;
    }

    .input-suffix-tag {
        position: absolute;
        right: 12px;
        font-size: 0.78rem;
        color: #94a3b8;
        font-weight: 600;
        pointer-events: none;
    }

    /* ---------------- Checkbox Amenities ---------------- */
    .amenities-check-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
    }

    @media (max-width: 600px) {
        .amenities-check-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .modern-checkbox-label {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.86rem;
        color: #cbd5e1;
        cursor: pointer;
        user-select: none;
    }

    .modern-checkbox-label input[type="checkbox"] {
        appearance: none;
        width: 18px;
        height: 18px;
        background: rgba(15, 23, 42, 0.8);
        border: 1.5px solid rgba(255, 255, 255, 0.22);
        border-radius: 5px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        margin: 0;
    }

    .modern-checkbox-label input[type="checkbox"]:checked {
        background: #2563eb;
        border-color: #2563eb;
    }

    .modern-checkbox-label input[type="checkbox"]:checked::after {
        content: "✓";
        color: #ffffff;
        font-size: 12px;
        font-weight: bold;
    }

    /* ---------------- Status Switch ---------------- */
    .status-toggle-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .status-text-info h5 {
        margin: 0 0 4px 0;
        font-size: 0.95rem;
        font-weight: 600;
        color: #ffffff;
    }

    .status-text-info p {
        margin: 0;
        font-size: 0.8rem;
        color: #94a3b8;
    }

    .modern-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 26px;
    }

    .modern-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .switch-slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background-color: rgba(255, 255, 255, 0.15);
        transition: 0.3s;
        border-radius: 9999px;
    }

    .switch-slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.3s;
        border-radius: 50%;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    }

    .modern-switch input:checked + .switch-slider {
        background-color: #2563eb;
        box-shadow: 0 0 10px rgba(37, 99, 235, 0.5);
    }

    .modern-switch input:checked + .switch-slider:before {
        transform: translateX(24px);
    }

    /* ---------------- Sticky Action Footer ---------------- */
    .editor-sticky-footer {
        position: sticky;
        bottom: 16px;
        background: rgba(10, 18, 42, 0.92);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(56, 189, 248, 0.2);
        border-radius: 12px;
        padding: 14px 24px;
        margin-top: 24px;
        box-shadow: 0 12px 36px rgba(0, 0, 0, 0.4);
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 14px;
        z-index: 50;
    }

    .btn-action-primary {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #ffffff;
        border: none;
        padding: 10px 28px;
        border-radius: 8px;
        font-size: 0.92rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.25s ease;
        box-shadow: 0 4px 18px rgba(37, 99, 235, 0.45);
    }

    .btn-action-primary:hover {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 22px rgba(37, 99, 235, 0.6);
    }

    .btn-action-secondary {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.16);
        color: #cbd5e1;
        padding: 10px 22px;
        border-radius: 8px;
        font-size: 0.92rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .btn-action-secondary:hover {
        background: rgba(255, 255, 255, 0.14);
        color: #ffffff;
        border-color: rgba(255, 255, 255, 0.28);
    }

    .btn-action-cancel {
        background: rgba(239, 68, 68, 0.08);
        border: 1px solid rgba(239, 68, 68, 0.35);
        color: #f87171;
        padding: 10px 22px;
        border-radius: 8px;
        font-size: 0.92rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .btn-action-cancel:hover {
        background: rgba(239, 68, 68, 0.18);
        color: #fca5a5;
        border-color: #ef4444;
    }

    /* Accordion Details for Additional Fields */
    .extra-details-accordion {
        margin-top: 14px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        padding-top: 12px;
    }

    .extra-details-toggle {
        font-size: 0.8rem;
        color: #38bdf8;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        user-select: none;
    }
</style>

<div class="tour-editor-container">
    <!-- Top Hero Banner (Faithfully matches Image 2) -->
    <div class="hero-banner-card">
        <div class="hero-banner-overlay"></div>
        <div class="hero-banner-content">
            <div class="hero-left-meta">
                <div class="kicker-badge">
                    <i class="bi bi-folder2-open"></i> Quản lý tour
                </div>
                <h1 class="hero-main-title">
                    <i class="bi bi-send-fill plane-icon"></i>
                    <?php echo $isCapNhat ? 'Sửa thông tin tour' : 'Thêm tour mới'; ?>
                </h1>
                <p class="hero-subtitle-text">
                    <?php echo $isCapNhat ? 'Cập nhật nội dung hành trình, biểu giá và dịch vụ tour.' : 'Tạo hành trình mới và mang đến những trải nghiệm tuyệt vời cho khách hàng'; ?>
                </p>
            </div>
            <div class="hero-right-actions">
                <div class="breadcrumbs-list">
                    <a href="<?php echo BASE_URL; ?>index.php?act=admin/dashboard">Trang chủ</a>
                    <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
                    <a href="<?php echo BASE_URL; ?>index.php?act=admin/quanLyTour">Quản lý tour</a>
                    <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
                    <span class="current"><?php echo $isCapNhat ? 'Sửa tour' : 'Thêm tour'; ?></span>
                </div>
                <a href="<?php echo BASE_URL; ?>index.php?act=admin/quanLyTour" class="btn-glass-back">
                    <i class="bi bi-arrow-left"></i> Quay lại danh sách
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Messages if any -->
    <?php if (isset($_SESSION['error'])): ?>
        <div style="background: rgba(220, 53, 69, 0.2); border: 1px solid rgba(220, 53, 69, 0.5); color: #f87171; padding: 14px 20px; border-radius: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <div><i class="bi bi-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: inherit; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Main Tour Form -->
    <form id="tourMasterForm" method="post" enctype="multipart/form-data" 
          action="<?php echo BASE_URL; ?>index.php?act=<?php echo $isCapNhat ? 'tour/update' : 'tour/create'; ?>">
        <?php if ($isCapNhat): ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($tour['tour_id']); ?>">
        <?php endif; ?>

        <!-- Hidden input for Status Toggle -->
        <input type="hidden" id="tourStatusInput" name="trang_thai" value="<?php echo htmlspecialchars($tour['trang_thai'] ?? 'HoatDong'); ?>">

        <!-- ================= TOP ROW: Thông tin cơ bản & Hình ảnh video ================= -->
        <div class="editor-grid">
            <!-- Left Card: Thông tin cơ bản -->
            <div class="modern-panel-card">
                <div class="card-header-bar">
                    <div class="card-title-group">
                        <div class="card-title-icon">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <h3 class="card-heading">Thông tin cơ bản</h3>
                    </div>
                </div>

                <div class="form-row-2col">
                    <div>
                        <label class="form-group-label">Tên tour <span class="star-required">*</span></label>
                        <input type="text" name="ten_tour" class="modern-input" 
                               value="<?php echo htmlspecialchars($tour['ten_tour'] ?? ''); ?>" 
                               placeholder="Nhập tên tour (VD: Hà Nội - Hạ Long 3N2Đ)" required>
                    </div>
                    <div>
                        <label class="form-group-label">Danh mục <span class="star-required">*</span></label>
                        <div class="input-wrapper">
                            <i class="bi bi-grid input-lead-icon"></i>
                            <select name="danh_muc" class="modern-select with-lead-icon">
                                <option value="bien_dao">Tour biển đảo</option>
                                <option value="van_hoa">Tour văn hóa - lịch sử</option>
                                <option value="sinh_thai">Tour sinh thái - khám phá</option>
                                <option value="nghi_duong">Tour nghỉ dưỡng cao cấp</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row-2col">
                    <div>
                        <label class="form-group-label">Loại tour <span class="star-required">*</span></label>
                        <div class="input-wrapper">
                            <i class="bi bi-geo-alt input-lead-icon"></i>
                            <select name="loai_tour" class="modern-select with-lead-icon">
                                <?php $loai = $tour['loai_tour'] ?? 'TrongNuoc'; ?>
                                <option value="TrongNuoc" <?php echo $loai === 'TrongNuoc' ? 'selected' : ''; ?>>Trong nước</option>
                                <option value="QuocTe" <?php echo $loai === 'QuocTe' ? 'selected' : ''; ?>>Quốc tế</option>
                                <option value="TheoYeuCau" <?php echo $loai === 'TheoYeuCau' ? 'selected' : ''; ?>>Theo yêu cầu</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="form-group-label">Thời gian (ngày đêm) <span class="star-required">*</span></label>
                        <div class="input-wrapper">
                            <i class="bi bi-calendar3 input-lead-icon"></i>
                            <input type="text" name="thoi_gian" class="modern-input with-lead-icon" 
                                   value="<?php echo htmlspecialchars($tour['thoi_gian'] ?? '3 ngày 2 đêm'); ?>" 
                                   placeholder="VD: 3 ngày 2 đêm">
                        </div>
                    </div>
                </div>

                <!-- Secondary Route Metadata (Kept collapsed or visible seamlessly) -->
                <div class="form-row-2col">
                    <div>
                        <label class="form-group-label">Điểm khởi hành</label>
                        <div class="input-wrapper">
                            <i class="bi bi-pin-map input-lead-icon"></i>
                            <input type="text" name="diem_khoi_hanh" class="modern-input with-lead-icon" 
                                   value="<?php echo htmlspecialchars($tour['diem_khoi_hanh'] ?? 'Hà Nội'); ?>" 
                                   placeholder="VD: Hà Nội, TP.HCM">
                        </div>
                    </div>
                    <div>
                        <label class="form-group-label">Điểm đến</label>
                        <div class="input-wrapper">
                            <i class="bi bi-flag input-lead-icon"></i>
                            <input type="text" name="diem_den" class="modern-input with-lead-icon" 
                                   value="<?php echo htmlspecialchars($tour['diem_den'] ?? 'Hạ Long'); ?>" 
                                   placeholder="VD: Vịnh Hạ Long">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="form-group-label">Mô tả tour <span class="star-required">*</span></label>
                    <div class="rich-editor-box">
                        <div class="rich-toolbar">
                            <button type="button" class="toolbar-btn" title="Bold" onclick="execFormat('bold')"><i class="bi bi-type-bold"></i></button>
                            <button type="button" class="toolbar-btn" title="Italic" onclick="execFormat('italic')"><i class="bi bi-type-italic"></i></button>
                            <button type="button" class="toolbar-btn" title="Underline" onclick="execFormat('underline')"><i class="bi bi-type-underline"></i></button>
                            <div class="toolbar-divider"></div>
                            <button type="button" class="toolbar-btn" title="Bullet List" onclick="execFormat('insertUnorderedList')"><i class="bi bi-list-ul"></i></button>
                            <button type="button" class="toolbar-btn" title="Numbered List" onclick="execFormat('insertOrderedList')"><i class="bi bi-list-ol"></i></button>
                            <div class="toolbar-divider"></div>
                            <button type="button" class="toolbar-btn" title="Insert Link" onclick="promptLink()"><i class="bi bi-link-45deg"></i></button>
                            <button type="button" class="toolbar-btn" title="Image" onclick="document.getElementById('tourImageFileInput').click()"><i class="bi bi-image"></i></button>
                        </div>
                        <textarea name="mo_ta" id="tourMoTaInput" class="modern-textarea" rows="4" 
                                  placeholder="Nhập mô tả chi tiết về tour, lịch trình, điểm nổi bật..."
                                  oninput="updateCharCount(this)"><?php echo htmlspecialchars($tour['mo_ta'] ?? ''); ?></textarea>
                        <div class="textarea-footer-counter" id="charCounter">0/2000</div>
                    </div>
                </div>
            </div>

            <!-- Right Card: Hình ảnh & Video -->
            <div class="modern-panel-card">
                <div class="card-header-bar">
                    <div class="card-title-group">
                        <div class="card-title-icon">
                            <i class="bi bi-images"></i>
                        </div>
                        <h3 class="card-heading">Hình ảnh & Video</h3>
                    </div>
                </div>

                <!-- Hidden file input triggered by dropzone -->
                <input type="file" id="tourImageFileInput" name="hinh_anh_file[]" multiple accept="image/*" style="display:none" onchange="handleFileSelect(this)">

                <!-- Dropzone Box -->
                <div class="dropzone-upload-box" id="dropzoneArea" onclick="document.getElementById('tourImageFileInput').click()">
                    <div class="dropzone-cloud-icon">
                        <i class="bi bi-cloud-arrow-up"></i>
                    </div>
                    <div class="dropzone-primary-text">Kéo thả ảnh vào đây hoặc</div>
                    <button type="button" class="btn-dropzone-action">
                        <i class="bi bi-folder-plus"></i> Chọn ảnh
                    </button>
                    <div class="dropzone-hint-text">Hỗ trợ JPG, PNG, WebP (Tối đa 10MB/ảnh)</div>
                </div>

                <!-- Media Gallery Grid Preview -->
                <div class="media-gallery-grid" id="mediaGalleryGrid">
                    <!-- Default sample preview images matching Image 2 -->
                    <div class="gallery-thumb-item" title="Vịnh Hạ Long">
                        <img src="<?php echo BASE_URL; ?>public/images/dashboard/halong_banner.jpg" alt="Thumb 1">
                        <div class="thumb-overlay-action">
                            <button type="button" class="btn-thumb-remove" onclick="this.closest('.gallery-thumb-item').remove()"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    <div class="gallery-thumb-item" title="Biển đảo">
                        <img src="<?php echo BASE_URL; ?>public/images/dashboard/beach_banner.jpg" alt="Thumb 2">
                        <div class="thumb-overlay-action">
                            <button type="button" class="btn-thumb-remove" onclick="this.closest('.gallery-thumb-item').remove()"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    <div class="gallery-thumb-item" title="Đà Nẵng">
                        <img src="<?php echo BASE_URL; ?>public/images/dashboard/danang.jpg" alt="Thumb 3">
                        <div class="thumb-overlay-action">
                            <button type="button" class="btn-thumb-remove" onclick="this.closest('.gallery-thumb-item').remove()"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    <div class="gallery-thumb-item" title="Sa Pa">
                        <img src="<?php echo BASE_URL; ?>public/images/dashboard/sapa.jpg" alt="Thumb 4">
                        <div class="thumb-overlay-action">
                            <button type="button" class="btn-thumb-remove" onclick="this.closest('.gallery-thumb-item').remove()"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    <!-- Add more tile -->
                    <div class="gallery-add-tile" onclick="document.getElementById('tourImageFileInput').click()">
                        <i class="bi bi-plus-lg" style="font-size: 1.2rem;"></i>
                        <span>Thêm ảnh</span>
                    </div>
                </div>

                <!-- Video Link Input -->
                <div style="margin-top: 18px;">
                    <label class="form-group-label">Video (Link YouTube hoặc Vimeo)</label>
                    <div class="input-wrapper">
                        <i class="bi bi-play-btn input-lead-icon"></i>
                        <input type="text" name="video_url" class="modern-input with-lead-icon" 
                               value="" placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= MIDDLE ROW: Lịch trình tour & Giá tour ================= -->
        <div class="editor-grid">
            <!-- Left Card: Lịch trình tour -->
            <div class="modern-panel-card">
                <div class="card-header-bar">
                    <div class="card-title-group">
                        <div class="card-title-icon">
                            <i class="bi bi-calendar3-range"></i>
                        </div>
                        <h3 class="card-heading">Lịch trình tour</h3>
                    </div>
                    <button type="button" class="btn-dropzone-action" onclick="themLichTrinhMoi()">
                        <i class="bi bi-plus-lg"></i> Thêm ngày
                    </button>
                </div>

                <!-- Days Pill Tab Navigation -->
                <div class="timeline-tabs-row" id="dayTabsContainer">
                    <button type="button" class="day-pill-tab active" onclick="switchDayTab(1, this)">Ngày 1</button>
                    <button type="button" class="day-pill-tab" onclick="switchDayTab(2, this)">Ngày 2</button>
                    <button type="button" class="day-pill-tab" onclick="switchDayTab(3, this)">Ngày 3</button>
                    <button type="button" class="day-pill-add-btn" title="Thêm ngày" onclick="themLichTrinhMoi()">
                        <i class="bi bi-plus"></i>
                    </button>
                </div>

                <!-- Active Day Detail Card -->
                <div class="day-detail-panel" id="dayContentPanel">
                    <div class="day-header-meta">
                        <div class="day-heading-title">
                            <i class="bi bi-grip-vertical grip-icon"></i>
                            <span id="activeDayLabel">Ngày 1: Hà Nội - Hạ Long</span>
                        </div>
                        <button type="button" class="btn-thumb-remove" title="Xóa ngày này" onclick="xoaActiveDay()">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>

                    <div class="day-body-flex">
                        <!-- Timeline activities view -->
                        <div class="timeline-items-list" id="timelineActivitiesView">
                            <div class="timeline-row-item">
                                <span class="time-badge"><i class="bi bi-clock"></i> 08:00</span>
                                <span>Xe đón đoàn tại điểm hẹn trung tâm Hà Nội</span>
                            </div>
                            <div class="timeline-row-item">
                                <span class="time-badge"><i class="bi bi-geo-alt"></i> 12:00</span>
                                <span>Đến Hạ Long, làm thủ tục nhận phòng khách sạn</span>
                            </div>
                            <div class="timeline-row-item">
                                <span class="time-badge"><i class="bi bi-cup-hot"></i> 13:00</span>
                                <span>Thưởng thức bữa trưa hải sản tươi sống tại nhà hàng</span>
                            </div>
                            <div class="timeline-row-item">
                                <span class="time-badge"><i class="bi bi-camera"></i> 15:00</span>
                                <span>Tham quan Vịnh Hạ Long, du ngoạn hang Sửng Sốt & Đảo Titop</span>
                            </div>
                        </div>

                        <!-- Day Thumbnail with Edit Tag -->
                        <div class="day-preview-image-box">
                            <img src="<?php echo BASE_URL; ?>public/images/dashboard/halong_banner.jpg" alt="Day image preview">
                            <div class="day-img-edit-tag" onclick="document.getElementById('tourImageFileInput').click()">
                                <i class="bi bi-pencil-square"></i> Chỉnh sửa
                            </div>
                        </div>
                    </div>

                    <!-- Hidden inputs preserving backend array structure: lich_trinh[i][...] -->
                    <div id="hiddenScheduleFields">
                        <input type="hidden" name="lich_trinh[0][ngay_thu]" value="1">
                        <input type="hidden" name="lich_trinh[0][dia_diem]" value="Hà Nội - Hạ Long">
                        <input type="hidden" name="lich_trinh[0][hoat_dong]" value="08:00 Xe đón tại Hà Nội. 12:00 Đến Hạ Long nhận phòng. 13:00 Ăn trưa. 15:00 Tham quan vịnh Hạ Long.">
                        <input type="hidden" name="lich_trinh[1][ngay_thu]" value="2">
                        <input type="hidden" name="lich_trinh[1][dia_diem]" value="Vịnh Bái Tử Long - Làng chài Cửa Vạn">
                        <input type="hidden" name="lich_trinh[1][hoat_dong]" value="07:00 Điểm tâm sáng. 09:00 Chèo thuyền kayak. 12:30 Bữa trưa trên tàu. 16:00 Check in ngắm hoàng hôn.">
                        <input type="hidden" name="lich_trinh[2][ngay_thu]" value="3">
                        <input type="hidden" name="lich_trinh[2][dia_diem]" value="Hạ Long - Hà Nội">
                        <input type="hidden" name="lich_trinh[2][hoat_dong]" value="08:00 Tắm biển Bãi Cháy. 11:30 Trả phòng. 14:00 Xe khởi hành về lại Hà Nội.">
                    </div>
                </div>
            </div>

            <!-- Right Card: Giá tour -->
            <div class="modern-panel-card">
                <div class="card-header-bar">
                    <div class="card-title-group">
                        <div class="card-title-icon">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <h3 class="card-heading">Giá tour</h3>
                    </div>
                </div>

                <div class="form-row-2col">
                    <div>
                        <label class="form-group-label">Giá người lớn <span class="star-required">*</span></label>
                        <div class="input-group-suffix">
                            <input type="number" name="gia_co_ban" class="modern-input" 
                                   value="<?php echo htmlspecialchars((string)($tour['gia_co_ban'] ?? '4500000')); ?>" 
                                   step="1000" min="0" required>
                            <span class="input-suffix-tag">VNĐ</span>
                        </div>
                    </div>
                    <div>
                        <label class="form-group-label">Giá trẻ em (6 - 11 tuổi)</label>
                        <div class="input-group-suffix">
                            <input type="number" name="gia_tre_em" class="modern-input" 
                                   value="2800000" step="1000" min="0">
                            <span class="input-suffix-tag">VNĐ</span>
                        </div>
                    </div>
                </div>

                <div class="form-row-2col">
                    <div>
                        <label class="form-group-label">Giá trẻ nhỏ (&lt; 6 tuổi)</label>
                        <div class="input-group-suffix">
                            <input type="number" name="gia_em_be" class="modern-input" 
                                   value="1500000" step="1000" min="0">
                            <span class="input-suffix-tag">VNĐ</span>
                        </div>
                    </div>
                    <div>
                        <label class="form-group-label">Số chỗ tối đa</label>
                        <input type="number" name="so_cho_toi_da" class="modern-input" 
                               value="<?php echo htmlspecialchars($tour['so_cho_toi_da'] ?? '30'); ?>" 
                               placeholder="VD: 30 chỗ">
                    </div>
                </div>

                <div class="form-row-2col">
                    <div>
                        <label class="form-group-label">Giá khuyến mãi (nếu có)</label>
                        <div class="input-group-suffix">
                            <input type="number" name="gia_khuyen_mai" class="modern-input" 
                                   value="0" step="1000" min="0">
                            <span class="input-suffix-tag">VNĐ</span>
                        </div>
                    </div>
                    <div>
                        <label class="form-group-label">Thời gian áp dụng</label>
                        <div class="input-wrapper">
                            <i class="bi bi-calendar-range input-lead-icon"></i>
                            <input type="text" name="thoi_gian_ap_dung" class="modern-input with-lead-icon" 
                                   value="01/10/2026 → 31/12/2026" placeholder="dd/mm/yyyy → dd/mm/yyyy">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= BOTTOM ROW: Tiện ích & dịch vụ / Trạng thái ================= -->
        <div class="editor-grid">
            <!-- Left Card: Tiện ích & Dịch vụ -->
            <div class="modern-panel-card">
                <div class="card-header-bar">
                    <div class="card-title-group">
                        <div class="card-title-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3 class="card-heading">Tiện ích & Dịch vụ</h3>
                    </div>
                </div>

                <div class="amenities-check-grid">
                    <label class="modern-checkbox-label">
                        <input type="checkbox" name="tien_ich[]" value="xe_dua_don" checked>
                        <span>Xe đưa đón</span>
                    </label>
                    <label class="modern-checkbox-label">
                        <input type="checkbox" name="tien_ich[]" value="ve_tham_quan" checked>
                        <span>Vé tham quan</span>
                    </label>
                    <label class="modern-checkbox-label">
                        <input type="checkbox" name="tien_ich[]" value="an_uong" checked>
                        <span>Ăn uống</span>
                    </label>
                    <label class="modern-checkbox-label">
                        <input type="checkbox" name="tien_ich[]" value="khach_san" checked>
                        <span>Khách sạn</span>
                    </label>
                    <label class="modern-checkbox-label">
                        <input type="checkbox" name="tien_ich[]" value="huong_dan_vien">
                        <span>Hướng dẫn viên</span>
                    </label>
                    <label class="modern-checkbox-label">
                        <input type="checkbox" name="tien_ich[]" value="bao_hiem">
                        <span>Bảo hiểm</span>
                    </label>
                </div>

                <!-- Collapsible Policy Details (Maintains backend dieu_kien_huy, bao_gom, etc.) -->
                <div class="extra-details-accordion">
                    <div class="extra-details-toggle" onclick="toggleExtraPolicies()">
                        <i class="bi bi-chevron-down" id="policyChevron"></i>
                        <span>Chi tiết điều khoản & chính sách hủy</span>
                    </div>
                    <div id="extraPoliciesContainer" style="display: none; margin-top: 12px;">
                        <div class="form-row-2col">
                            <div>
                                <label class="form-group-label">Bao gồm</label>
                                <textarea name="bao_gom" class="modern-textarea" style="border: 1px solid var(--input-border); border-radius: 6px;" rows="2" placeholder="VD: Khách sạn 4 sao, xe Limousine..."><?php echo htmlspecialchars($tour['bao_gom'] ?? ''); ?></textarea>
                            </div>
                            <div>
                                <label class="form-group-label">Không bao gồm</label>
                                <textarea name="khong_bao_gom" class="modern-textarea" style="border: 1px solid var(--input-border); border-radius: 6px;" rows="2" placeholder="VD: Chi phí cá nhân, đồ uống..."><?php echo htmlspecialchars($tour['khong_bao_gom'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Card: Trạng thái -->
            <div class="modern-panel-card">
                <div class="card-header-bar">
                    <div class="card-title-group">
                        <div class="card-title-icon">
                            <i class="bi bi-sliders"></i>
                        </div>
                        <h3 class="card-heading">Trạng thái</h3>
                    </div>
                </div>

                <div class="status-toggle-container">
                    <div class="status-text-info">
                        <h5>Hiển thị tour</h5>
                        <p>Tour sẽ được hiển thị trên website</p>
                    </div>
                    <label class="modern-switch">
                        <?php $isActive = ($tour['trang_thai'] ?? 'HoatDong') === 'HoatDong'; ?>
                        <input type="checkbox" id="statusCheckboxToggle" <?php echo $isActive ? 'checked' : ''; ?> onchange="handleStatusToggle(this)">
                        <span class="switch-slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- ================= STICKY FOOTER ACTIONS ================= -->
        <div class="editor-sticky-footer">
            <button type="submit" name="hanh_dong" value="<?php echo $isCapNhat ? 'update' : 'create'; ?>" class="btn-action-primary">
                <i class="bi bi-send-fill" style="transform: rotate(-20deg);"></i> Lưu tour
            </button>
            <button type="submit" name="hanh_dong" value="draft" class="btn-action-secondary">
                <i class="bi bi-archive"></i> Lưu nháp
            </button>
            <a href="<?php echo BASE_URL; ?>index.php?act=admin/quanLyTour" class="btn-action-cancel">
                <i class="bi bi-trash3"></i> Hủy bỏ
            </a>
        </div>
    </form>
</div>

<!-- ================= JAVASCRIPT LOGIC ================= -->
<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
    // Character counter for Description
    function updateCharCount(textarea) {
        const len = textarea.value.length;
        document.getElementById('charCounter').textContent = len + '/2000';
    }

    // Toggle Extra Policy fields
    function toggleExtraPolicies() {
        const container = document.getElementById('extraPoliciesContainer');
        const chevron = document.getElementById('policyChevron');
        if (container.style.display === 'none') {
            container.style.display = 'block';
            chevron.className = 'bi bi-chevron-up';
        } else {
            container.style.display = 'none';
            chevron.className = 'bi bi-chevron-down';
        }
    }

    // Status switch toggle
    function handleStatusToggle(checkbox) {
        const statusInput = document.getElementById('tourStatusInput');
        statusInput.value = checkbox.checked ? 'HoatDong' : 'TamDung';
    }

    // Rich text editor dummy actions
    function execFormat(command) {
        const textarea = document.getElementById('tourMoTaInput');
        textarea.focus();
    }

    function promptLink() {
        const url = prompt('Nhập đường dẫn liên kết (URL):', 'https://');
        if (url) {
            const textarea = document.getElementById('tourMoTaInput');
            textarea.value += ' ' + url;
            updateCharCount(textarea);
        }
    }

    // Handle Image file selection & instant preview
    function handleFileSelect(input) {
        const files = input.files;
        if (!files || files.length === 0) return;

        const grid = document.getElementById('mediaGalleryGrid');
        const addTile = grid.querySelector('.gallery-add-tile');

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const reader = new FileReader();
            reader.onload = function(e) {
                const item = document.createElement('div');
                item.className = 'gallery-thumb-item';
                item.innerHTML = `
                    <img src="${e.target.result}" alt="Uploaded image">
                    <div class="thumb-overlay-action">
                        <button type="button" class="btn-thumb-remove" onclick="this.closest('.gallery-thumb-item').remove()"><i class="bi bi-trash"></i></button>
                    </div>
                `;
                grid.insertBefore(item, addTile);
            };
            reader.readAsDataURL(file);
        }
    }

    // Schedule / Day Tabs interaction
    const scheduleData = {
        1: {
            title: "Ngày 1: Hà Nội - Hạ Long",
            img: "<?php echo BASE_URL; ?>public/images/dashboard/halong_banner.jpg",
            items: [
                { time: "08:00", icon: "bi-clock", text: "Xe đón đoàn tại điểm hẹn trung tâm Hà Nội" },
                { time: "12:00", icon: "bi-geo-alt", text: "Đến Hạ Long, làm thủ tục nhận phòng khách sạn" },
                { time: "13:00", icon: "bi-cup-hot", text: "Thưởng thức bữa trưa hải sản tươi sống tại nhà hàng" },
                { time: "15:00", icon: "bi-camera", text: "Tham quan Vịnh Hạ Long, du ngoạn hang Sửng Sốt & Đảo Titop" }
            ]
        },
        2: {
            title: "Ngày 2: Vịnh Bái Tử Long - Làng chài Cửa Vạn",
            img: "<?php echo BASE_URL; ?>public/images/dashboard/beach_banner.jpg",
            items: [
                { time: "07:00", icon: "bi-cup-hot", text: "Dùng điểm tâm sáng ngắm bình minh trên vịnh" },
                { time: "09:00", icon: "bi-water", text: "Chèo thuyền kayak khám phá hang Luồn và làng chài" },
                { time: "12:30", icon: "bi-egg-fried", text: "Dùng bữa trưa trên du thuyền 5 sao" },
                { time: "16:00", icon: "bi-sunset", text: "Tiệc trà chiều hoàng hôn trên boong tàu" }
            ]
        },
        3: {
            title: "Ngày 3: Hạ Long - Mua sắm đặc sản - Hà Nội",
            img: "<?php echo BASE_URL; ?>public/images/dashboard/danang.jpg",
            items: [
                { time: "08:00", icon: "bi-sun", text: "Tắm biển tự do tại Bãi Cháy" },
                { time: "11:00", icon: "bi-bag-check", text: "Làm thủ tục trả phòng, mua đặc sản chả mực" },
                { time: "14:00", icon: "bi-bus-front", text: "Khởi hành quay trở lại thủ đô Hà Nội" }
            ]
        }
    };

    function switchDayTab(dayNum, btn) {
        document.querySelectorAll('.day-pill-tab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const data = scheduleData[dayNum] || {
            title: `Ngày ${dayNum}: Hoạt động tham quan`,
            img: "<?php echo BASE_URL; ?>public/images/dashboard/halong_banner.jpg",
            items: [
                { time: "08:00", icon: "bi-clock", text: "Khởi hành chương trình tham quan" },
                { time: "12:00", icon: "bi-cup-hot", text: "Ăn trưa tại nhà hàng địa phương" }
            ]
        };

        document.getElementById('activeDayLabel').textContent = data.title;
        const view = document.getElementById('timelineActivitiesView');
        view.innerHTML = data.items.map(item => `
            <div class="timeline-row-item">
                <span class="time-badge"><i class="bi ${item.icon}"></i> ${item.time}</span>
                <span>${item.text}</span>
            </div>
        `).join('');

        const previewImg = document.querySelector('.day-preview-image-box img');
        if (previewImg) {
            previewImg.src = data.img;
        }
    }

    let totalDays = 3;
    function themLichTrinhMoi() {
        totalDays++;
        const container = document.getElementById('dayTabsContainer');
        const addBtn = container.querySelector('.day-pill-add-btn');

        const newTab = document.createElement('button');
        newTab.type = 'button';
        newTab.className = 'day-pill-tab';
        newTab.textContent = `Ngày ${totalDays}`;
        newTab.onclick = function() {
            switchDayTab(totalDays, this);
        };

        container.insertBefore(newTab, addBtn);
        switchDayTab(totalDays, newTab);
    }

    function xoaActiveDay() {
        if (confirm('Bạn có chắc muốn xóa lịch trình ngày này?')) {
            const activeTab = document.querySelector('.day-pill-tab.active');
            if (activeTab) {
                activeTab.remove();
                const firstTab = document.querySelector('.day-pill-tab');
                if (firstTab) firstTab.click();
            }
        }
    }

    // Initialize character counter on page load
    document.addEventListener('DOMContentLoaded', () => {
        const textarea = document.getElementById('tourMoTaInput');
        if (textarea) updateCharCount(textarea);
    });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
