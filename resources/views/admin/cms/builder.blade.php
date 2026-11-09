<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Page builder | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }

        body {
            display: flex;
            flex-direction: column;
            font-family: 'Poppins', sans-serif;
            background: #020617;
            color: #e2e8f0;
        }

        #gjs {
            flex: 1 1 auto;
            min-height: 0;
            height: 100%;
            overflow: hidden;
        }

        .hba-toolbar {
            background: #0f172a;
            border-bottom: 1px solid #1e293b;
        }

        .hba-toolbar button,
        .hba-toolbar a {
            background: #1e293b;
            color: #cbd5e1;
            border: 0;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .hba-toolbar button:hover,
        .hba-toolbar a:hover {
            background: #334155;
            color: #fff;
        }

        .hba-toolbar button:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .hba-toolbar input[type="text"] {
            color: #f8fafc;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 6px 10px;
            font-size: 12px;
        }

        .hba-toolbar #save-page {
            background: #0284c7;
            color: #fff;
            font-weight: 700;
        }

        .hba-toolbar #save-page:hover {
            background: #0369a1;
        }

        #save-status[data-error="true"] {
            color: #fca5a5;
        }

        /* GrapesJS dark theme — same palette as before */
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

        .gjs-four-color,
        .gjs-four-color-h:hover {
            color: #38bdf8 !important;
        }

        .gjs-pn-panel {
            border-color: #1e293b !important;
        }

        .gjs-block {
            min-height: 80px !important;
            padding: 12px 10px !important;
            border-radius: 12px !important;
            background-color: #1e293b !important;
            border: 1px solid #334155 !important;
            color: #cbd5e1 !important;
            transition: all .2s ease !important;
        }

        .gjs-block:hover {
            border-color: #38bdf8 !important;
            color: #fff !important;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, .3) !important;
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
            letter-spacing: .05em !important;
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

        .gjs-pn-btn.gjs-pn-active {
            color: #38bdf8 !important;
            background-color: #1e293b !important;
        }

        .gjs-pn-btn:hover {
            color: #fff !important;
        }
    </style>
</head>

<body>
    <header class="hba-toolbar flex min-h-14 shrink-0 flex-wrap items-center justify-between gap-3 px-3 py-2">
        <div class="flex min-w-0 items-center gap-3">
            <a href="{{ $backUrl }}">← {{ ucfirst($entityName) }}s</a>
            <input id="page-title" type="text" value="{{ $page->title }}" maxlength="255" class="w-48"
                aria-label="Page title">
            <input id="page-slug" type="text" value="{{ $page->slug }}" maxlength="255" class="w-40 font-mono"
                aria-label="Page slug">
        </div>

        <div class="flex items-center gap-2">
            <span id="save-status" role="status" aria-live="polite" class="text-xs text-slate-300"></span>
            <label class="inline-flex items-center gap-1 text-xs text-slate-300">
                <input id="page-published" type="checkbox" @checked($published)> Published
            </label>
            <a id="preview-page" href="{{ $previewUrl }}" target="_blank" rel="noopener">Live</a>
            <button id="save-page" type="button">Save {{ ucfirst($entityName) }}</button>
        </div>
    </header>

    <div id="gjs"></div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // ---- shared CSS for the products block (saved into content.css) ----
            const productGridCss = `
            .hpg { --hpg-primary: var(--storefront-primary, #b45309); }
            .hpg__grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:28px 20px; }
            .hpg__card { display:flex; min-width:0; flex-direction:column; }
            .hpg__media { position:relative; display:block; overflow:hidden; aspect-ratio:1/1; background:#f4f4f5; border:1px solid #e4e4e7; color:inherit; text-decoration:none; }
            .hpg__media img { width:100%; height:100%; padding:14px; object-fit:contain; transition:transform .35s ease; }
            .hpg__media:hover img { transform:scale(1.04); }
            .hpg__media--empty { display:grid; place-items:center; height:100%; color:#a1a1aa; font-size:11px; font-weight:600; letter-spacing:.1em; text-transform:uppercase; }
            .hpg__badge { position:absolute; top:10px; left:10px; z-index:1; padding:4px 8px; background:#18181b; color:#fff; font-size:10.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
            .hpg__body { display:flex; flex:1 1 auto; flex-direction:column; padding-top:12px; }
            .hpg__eyebrow { margin:0; color:#71717a; font-size:11px; font-weight:600; letter-spacing:.09em; text-transform:uppercase; }
            .hpg__name { margin:4px 0 0; color:#18181b; font-size:13.5px; font-weight:700; line-height:1.4; }
            .hpg__price { display:flex; flex-wrap:wrap; align-items:baseline; gap:8px; margin:8px 0 0; color:#18181b; font-size:15px; font-weight:700; line-height:1.3; }
            .hpg__price-was { color:#71717a; font-size:13px; font-weight:500; text-decoration:line-through; }
            .hpg__cta-wrap { margin-top:auto; padding-top:16px; }
            .hpg__cta { display:flex; align-items:center; justify-content:center; min-height:42px; padding:10px 12px; border:1px solid var(--hpg-primary); background:var(--hpg-primary); color:#fff; font-size:12px; font-weight:700; letter-spacing:.08em; line-height:1.2; text-align:center; text-transform:uppercase; text-decoration:none; transition:filter .2s ease; }
            .hpg__cta:hover, .hpg__cta:focus-visible { filter:brightness(.9); color:#fff; }
            .hpg__empty { grid-column:1/-1; margin:0; padding:60px 20px; border:1px dashed #d4d4d8; color:#71717a; font-size:14px; text-align:center; }
        `;

            // ---- init editor ----
            const editor = grapesjs.init({
                container: '#gjs',
                height: '100%',
                storageManager: false,
                plugins: [],
                canvas: {
                    styles: [
                        'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap',
                    ],
                    scripts: [],
                },
                deviceManager: {
                    devices: [
                        { id: 'Desktop', name: 'Desktop', width: '' },
                        { id: 'Tablet', name: 'Tablet', width: '768px', widthMedia: '992px' },
                        { id: 'Mobile', name: 'Mobile', width: '375px', widthMedia: '480px' },
                    ],
                },
                styleManager: {
                    sectors: [
                        { name: 'General & Spacing', open: true, buildProps: ['display', 'flex-direction', 'justify-content', 'align-items', 'margin', 'padding', 'width', 'max-width', 'height'] },
                        { name: 'Typography', open: false, buildProps: ['font-family', 'font-size', 'font-weight', 'letter-spacing', 'color', 'line-height', 'text-align', 'text-decoration'] },
                        { name: 'Decorations', open: false, buildProps: ['background-color', 'border-radius', 'border', 'box-shadow', 'opacity'] },
                    ],
                },
            });

            // ---- custom component: product grid ----
            editor.DomComponents.addType('hubafrika-product-grid', {
                isComponent: (el) => el.nodeType === 1 && el.hasAttribute('data-hubafrika-products'),
                model: {
                    defaults: {
                        traits: [
                            {
                                type: 'select', name: 'data-sort', label: 'Sort by', options: [
                                    { id: 'newest', label: 'Newest first' },
                                    { id: 'name_asc', label: 'Name A–Z' },
                                    { id: 'brand_asc', label: 'Brand A–Z' },
                                    { id: 'category_asc', label: 'Category A–Z' },
                                    { id: 'price_asc', label: 'Price low → high' },
                                    { id: 'price_desc', label: 'Price high → low' },
                                ]
                            },
                            { type: 'number', name: 'data-limit', label: 'Products shown', min: 1, max: 24, step: 1 },
                        ],
                    },
                },
            });

            // ---- blocks ----
            editor.BlockManager.add('hubafrika-hero', {
                label: 'Storefront hero',
                category: 'Hubafrika',
                content: `<section style="padding:80px 24px;text-align:center;background:#fff7ed">
                <p style="color:#b45309;font-weight:700;letter-spacing:.12em;text-transform:uppercase">Hubafrika collection</p>
                <h1 style="margin:16px 0;font-size:clamp(36px,6vw,64px);font-weight:800;line-height:1.05">Made for your everyday</h1>
                <p style="margin:0 auto 24px;max-width:620px;color:#475569;font-size:18px;line-height:1.7">Discover thoughtful finds for home, style, and the people you love.</p>
                <a href="/shop" style="display:inline-block;border-radius:8px;background:#111827;padding:14px 22px;color:#fff;text-decoration:none;font-weight:700">Shop the collection</a>
            </section>`,
            });

            editor.BlockManager.add('hubafrika-split', {
                label: 'Image and story',
                category: 'Hubafrika',
                content: `<section style="display:flex;flex-wrap:wrap;align-items:center;gap:32px;padding:64px 24px">
                <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=1000&q=80" alt="" style="width:100%;max-width:480px;min-height:320px;flex:1;object-fit:cover;border-radius:16px">
                <div style="flex:1;min-width:260px">
                    <p style="color:#b45309;font-weight:700;letter-spacing:.12em;text-transform:uppercase">Our story</p>
                    <h2 style="font-size:clamp(28px,4vw,44px);font-weight:800;line-height:1.1">A little more meaning in every choice</h2>
                    <p style="color:#475569;font-size:16px;line-height:1.8">Tell customers what makes your products special and introduce the people behind your shop.</p>
                    <a href="/shop" style="color:#92400e;font-weight:700">Explore the shop</a>
                </div>
            </section>`,
            });

            editor.BlockManager.add('hubafrika-callout', {
                label: 'Call to action',
                category: 'Hubafrika',
                content: `<section style="padding:56px 24px;text-align:center;background:#111827;color:#fff">
                <h2 style="font-size:clamp(28px,4vw,42px);font-weight:800">Find your next favourite</h2>
                <p style="margin:12px auto 24px;max-width:560px;color:#d1d5db;line-height:1.7">Browse the latest pieces and bring something special home.</p>
                <a href="/shop" style="display:inline-block;border-radius:8px;background:#f59e0b;padding:14px 22px;color:#111827;text-decoration:none;font-weight:700">Browse products</a>
            </section>`,
            });

            // Products block: <style> travels with the block, so getCss() picks it up.
            editor.BlockManager.add('hubafrika-products', {
                label: 'Featured products',
                category: 'Hubafrika',
                content: `
                <style>${productGridCss}</style>
                <section style="padding:48px 24px">
                    <h2 style="margin:0 0 24px;font-size:32px;font-weight:800">Featured products</h2>
                    <div data-hubafrika-products="true" data-sort="newest" data-limit="8" style="min-height:120px;border-radius:12px;background:#f3f4f6"></div>
                </section>`,
            });

            // ---- load existing content ----
            const projectData = @json($projectData);
            if (projectData && Object.keys(projectData).length) {
                editor.loadProjectData(projectData);
            } else {
                editor.setComponents(@json($initialHtml));
            }

            // ---- save ----
            const saveBtn = document.getElementById('save-page');
            const status = document.getElementById('save-status');

            saveBtn.addEventListener('click', async () => {
                saveBtn.disabled = true;
                status.textContent = 'Saving…';
                status.dataset.error = 'false';

                try {
                    const res = await fetch(@json($updateUrl), {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            title: document.getElementById('page-title').value,
                            slug: document.getElementById('page-slug').value,
                            published: document.getElementById('page-published').checked,
                            project_data: editor.getProjectData(),
                            html: editor.getHtml(),
                            css: editor.getCss(),
                        }),
                    });

                    const result = await res.json();
                    if (!res.ok) {
                        const errors = Object.values(result.errors || {}).flat().join(' ');
                        throw new Error(errors || result.message || 'Save failed.');
                    }

                    document.getElementById('preview-page').href = result.url;
                    status.textContent = result.message;
                } catch (e) {
                    status.textContent = e.message;
                    status.dataset.error = 'true';
                } finally {
                    saveBtn.disabled = false;
                }
            });
        });
    </script>
</body>

</html>