<style>
:root {
            --gjs-primary-color: #0284c7;
            --gjs-secondary-color: #0369a1;
            --gjs-font-family: 'Poppins', sans-serif;
        }

        

        /* GrapesJS UI Customizations to match modern Elementor Dark theme */
        .gjs-one-bg {
            background-color: #0f172a !important;
        }
        .gjs-two-color {
            color: #e2e8f0 !important;
        }
        .gjs-three-bg {
            background-color: #1e293b !important;
            color: #f8fafc !important;
        }
        .gjs-four-color, .gjs-four-color-h:hover {
            color: #38bdf8 !important;
        }
        .gjs-pn-panel {
            border-color: #1e293b !important;
        }
        .gjs-block {
            user-select: none;
            min-height: 80px !important;
            padding: 12px 10px !important;
            border-radius: 12px !important;
            background-color: #1e293b !important;
            border: 1px solid #334155 !important;
            color: #cbd5e1 !important;
            transition: all 0.2s ease !important;
        }
        .gjs-block:hover {
            border-color: #38bdf8 !important;
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3) !important;
        }
        .gjs-block-label {
            font-size: 11px !important;
            font-weight: 600 !important;
            margin-top: 6px !important;
        }
        .gjs-category-title {
            background-color: #1e293b !important;
            color: #94a3b8 !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            border-bottom: 1px solid #334155 !important;
            padding: 10px 14px !important;
        }
        .gjs-sm-sector .gjs-sm-sector-title {
            background-color: #1e293b !important;
            color: #cbd5e1 !important;
            border-bottom: 1px solid #334155 !important;
            font-size: 11px !important;
            font-weight: 700 !important;
        }
        .gjs-field {
            background-color: #0f172a !important;
            border: 1px solid #334155 !important;
            border-radius: 8px !important;
            color: #f8fafc !important;
        }
        .gjs-clm-tags #gjs-clm-tags-c {
            background-color: #0f172a !important;
            border: 1px solid #334155 !important;
            border-radius: 8px !important;
            padding: 4px !important;
        }
        .gjs-clm-tag {
            background-color: #0284c7 !important;
            color: #ffffff !important;
            border-radius: 6px !important;
            font-size: 11px !important;
            font-family: monospace !important;
        }
        .gjs-pn-btn.gjs-pn-active {
            color: #38bdf8 !important;
            background-color: #1e293b !important;
        }
        .gjs-pn-btn:hover {
            color: #ffffff !important;
        }

        /* =========================================================================
           LAYER MANAGER (Element Tree) SELECTED & INTERACTION STYLES
        ========================================================================== */
        .gjs-layers {
            background-color: #0f172a !important;
            padding-bottom: 30px;
        }
        .gjs-layer {
            position: relative;
        }
        .gjs-layer-item {
            background-color: #0f172a !important;
            border-bottom: 1px solid #1e293b !important;
            padding: 7px 10px !important;
            transition: all 0.15s ease !important;
            border-left: 3px solid transparent !important;
            color: #94a3b8 !important;
            font-size: 12px !important;
        }
        .gjs-layer-item:hover,
        .gjs-layer.gjs-hovered > .gjs-layer-item {
            background-color: #1e293b !important;
            color: #38bdf8 !important;
        }
        /* Active / Selected Layer Highlight */
        .gjs-layer.gjs-selected > .gjs-layer-item,
        .gjs-layer.gjs-selected > [data-toggle-select],
        .gjs-layer.gjs-layer-selected > .gjs-layer-item {
            background: linear-gradient(90deg, #0369a1 0%, #0284c7 100%) !important;
            border-left: 3px solid #38bdf8 !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            box-shadow: 0 2px 8px rgba(2, 132, 199, 0.4) !important;
        }
        .gjs-layer.gjs-selected > .gjs-layer-item .gjs-layer-name,
        .gjs-layer.gjs-selected > .gjs-layer-item .gjs-layer-title,
        .gjs-layer.gjs-layer-selected > .gjs-layer-item .gjs-layer-name,
        .gjs-layer.gjs-layer-selected > .gjs-layer-item .gjs-layer-title {
            color: #ffffff !important;
            font-weight: 700 !important;
            letter-spacing: 0.01em !important;
        }
        .gjs-layer.gjs-selected > .gjs-layer-item .gjs-layer-vis,
        .gjs-layer.gjs-selected > .gjs-layer-item .gjs-layer-caret,
        .gjs-layer.gjs-selected > .gjs-layer-item .gjs-layer-move,
        .gjs-layer.gjs-selected > .gjs-layer-item svg {
            color: #e0f2fe !important;
            fill: #e0f2fe !important;
        }
        .gjs-layer.gjs-selected > .gjs-layer-item [data-count] {
            background-color: rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
            border-radius: 9999px !important;
            padding: 1px 6px !important;
            font-size: 10px !important;
            font-weight: 700 !important;
        }
        /* Parent of selected element (breadcrumb hierarchy) */
        .gjs-layer.gjs-selected-parent > .gjs-layer-item {
            border-left: 3px solid #0284c7 !important;
            color: #e2e8f0 !important;
            background-color: #13233c !important;
        }
        .gjs-layer.gjs-selected-parent > .gjs-layer-item .gjs-layer-name {
            font-weight: 600 !important;
            color: #e2e8f0 !important;
        }
</style>