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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

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

        .hba-device-switcher {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            padding: 3px;
            background: #020617;
            border: 1px solid #1e293b;
            border-radius: 10px;
        }

        .hba-toolbar .hba-device-switcher button {
            background: transparent;
            color: #94a3b8;
            border-radius: 7px;
            padding: 6px 10px;
            font-weight: 500;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .hba-toolbar .hba-device-switcher button:hover {
            background: #1e293b;
            color: #f8fafc;
        }

        .hba-toolbar .hba-device-switcher button.is-active {
            background: #0284c7;
            color: #ffffff;
            font-weight: 600;
        }
    </style>
    @include('admin.cms.builder-styles')
</head>

<body>
    <header class="hba-toolbar flex min-h-14 shrink-0 flex-wrap items-center justify-between gap-3 px-3 py-2 xl:grid xl:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] xl:items-center">
        <div class="flex min-w-0 items-center gap-3">
            <a href="{{ $backUrl }}">← {{ ucfirst($entityName) }}s</a>
            <input id="page-title" type="text" value="{{ $page->title }}" maxlength="255" class="w-48 min-w-0"
                aria-label="Page title">
            <input id="page-slug" type="text" value="{{ $page->slug }}" maxlength="255" class="w-40 min-w-0 font-mono"
                aria-label="Page slug">
        </div>

        <div class="hba-device-switcher xl:justify-self-center" role="group" aria-label="Preview device">
            <button type="button" data-builder-device="Desktop" class="is-active" title="Desktop view"
                aria-pressed="true">
                <i class="fas fa-desktop" aria-hidden="true"></i>
                <span class="hidden md:inline">Desktop</span>
            </button>
            <button type="button" data-builder-device="Tablet" title="Tablet view" aria-pressed="false">
                <i class="fas fa-tablet-alt" aria-hidden="true"></i>
                <span class="hidden md:inline">Tablet</span>
            </button>
            <button type="button" data-builder-device="Mobile" title="Mobile view" aria-pressed="false">
                <i class="fas fa-mobile-alt" aria-hidden="true"></i>
                <span class="hidden md:inline">Mobile</span>
            </button>
        </div>

        <div class="flex items-center gap-2 xl:justify-end">
            <span id="save-status" role="status" aria-live="polite" class="text-xs text-slate-300"></span>
            <label class="inline-flex items-center gap-1 text-xs text-slate-300">
                <input id="page-published" type="checkbox" @checked($published)> Published
            </label>
            <button id="open-media-library" type="button" title="Browse images from Media Library">
                <i class="fas fa-images" aria-hidden="true"></i>
                <span class="hidden sm:inline">Media Library</span>
            </button>
            <a id="preview-page" href="{{ $previewUrl }}" target="_blank" rel="noopener">Live</a>
            <button id="save-page" type="button">Save {{ ucfirst($entityName) }}</button>
        </div>
    </header>

    <div id="gjs"></div>

    <div id="hubafrika-media-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true"
        aria-labelledby="hubafrika-media-title">
        <div id="hubafrika-media-backdrop" class="fixed inset-0 bg-slate-950/75 backdrop-blur-xs"></div>

        <div class="relative flex min-h-full items-center justify-center p-4">
            <div
                class="w-full max-w-5xl overflow-hidden rounded-2xl border border-slate-700 bg-slate-900 text-slate-100 shadow-2xl">
                <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-800 px-5 py-4">
                    <div>
                        <h2 id="hubafrika-media-title" class="text-base font-semibold text-white">Media Library</h2>
                        <p class="mt-0.5 text-xs text-slate-400">Pick an existing image stored in your Media Library or upload a new one.</p>
                    </div>

                    <div class="flex items-center gap-2">
                        @if ($canUploadMedia)
                            <input id="hubafrika-media-upload-input" type="file"
                                accept="image/jpeg,image/png,image/webp,image/gif" class="hidden">
                            <button id="hubafrika-media-upload-btn" type="button"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-sky-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-sky-500">
                                <i class="fas fa-upload" aria-hidden="true"></i>
                                <span>Upload image</span>
                            </button>
                        @endif
                        <button id="hubafrika-media-close" type="button" aria-label="Close media library"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-700 bg-slate-800 text-slate-300 transition hover:bg-slate-700 hover:text-white">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-3 border-b border-slate-800 px-5 py-3">
                    <div id="hubafrika-media-tabs" class="flex flex-wrap gap-1.5">
                        <button type="button" data-library="all"
                            class="rounded-full bg-sky-600 px-3 py-1 text-xs font-semibold text-white transition">
                            All content
                        </button>
                        @foreach ($mediaLibraries as $key => $label)
                            <button type="button" data-library="{{ $key }}"
                                class="rounded-full bg-slate-800 px-3 py-1 text-xs font-semibold text-slate-300 transition hover:bg-slate-700 hover:text-white">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <div class="relative flex-1">
                            <input id="hubafrika-media-search" type="search"
                                placeholder="Search by file name or folder..."
                                aria-label="Search media library"
                                class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-slate-100 placeholder-slate-500 focus:border-sky-500 focus:outline-none">
                        </div>

                        <div class="flex items-center gap-2 sm:w-80">
                            <input id="hubafrika-media-url-input" type="url" placeholder="Or paste image URL..."
                                aria-label="Image URL"
                                class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-slate-100 placeholder-slate-500 focus:border-sky-500 focus:outline-none">
                            <button id="hubafrika-media-url-use" type="button"
                                class="shrink-0 rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-xs font-semibold text-slate-200 transition hover:bg-slate-700 hover:text-white">
                                Use URL
                            </button>
                        </div>
                    </div>

                    <p id="hubafrika-media-status" class="hidden text-xs text-sky-300" role="status" aria-live="polite"></p>
                </div>

                <div class="max-h-[58vh] overflow-y-auto p-5">
                    <div id="hubafrika-media-grid" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4"></div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-800 px-5 py-3.5 text-xs text-slate-400">
                    <div class="flex items-center gap-2">
                        <button id="hubafrika-media-prev" type="button"
                            class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-1.5 font-semibold text-slate-200 transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-40">
                            Previous
                        </button>
                        <span id="hubafrika-media-page-info">Page 1 of 1</span>
                        <button id="hubafrika-media-next" type="button"
                            class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-1.5 font-semibold text-slate-200 transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-40">
                            Next
                        </button>
                    </div>
                    <button id="hubafrika-media-cancel" type="button"
                        class="rounded-lg border border-slate-700 bg-slate-800 px-4 py-2 font-semibold text-slate-200 transition hover:bg-slate-700 hover:text-white">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // ---- shared CSS for the products block (saved into content.css) ----
            const productGridCss = `
            .sec { padding: 52px 0; }
            .w { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
            .st { text-align: center; text-transform: uppercase; letter-spacing: .12em; font-size: 18px; font-weight: 600; margin-bottom: 6px; }
            .ss { text-align: center; color: var(--mut, #777); margin: 0 0 30px; }
            .grid { display: grid; gap: 22px; grid-template-columns: repeat(1, minmax(0, 1fr)); }
            @media (min-width: 640px) {
                .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            }
            @media (min-width: 1024px) {
                .grid, .hubafrika-product-grid--cols-4, [data-cols="4"] { grid-template-columns: repeat(4, minmax(0, 1fr)) !important; }
                .hubafrika-product-grid--cols-3, [data-cols="3"] { grid-template-columns: repeat(3, minmax(0, 1fr)) !important; }
                .hubafrika-product-grid--cols-2, [data-cols="2"] { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
                .hubafrika-product-grid--cols-5, [data-cols="5"] { grid-template-columns: repeat(5, minmax(0, 1fr)) !important; }
            }
            .pc { position: relative; display: flex; flex-direction: column; background: transparent !important; }
            .pc .pi { background: transparent !important; aspect-ratio: 1/1; display: grid; place-items: center; border: 1px solid var(--bd, #e3e3e3); overflow: hidden; text-decoration: none; color: inherit; }
            .pc .pi img { width: 100%; height: 100%; object-fit: contain; padding: 12px; transition: transform .3s ease; }
            .pc .pi:hover img { transform: scale(1.04); }
            .pc .pi-empty { display: grid; place-items: center; height: 100%; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .08em; color: var(--mut, #777); }
            .pc .sale { position: absolute; top: 10px; left: 10px; background: var(--ac, var(--storefront-primary, #307F3E)); color: #fff; font-size: 11px; font-weight: 700; padding: 2px 10px; text-transform: uppercase; letter-spacing: .08em; z-index: 2; line-height: 1.4; }
            .pc .br { font-size: 12px; color: var(--mut, #777); text-transform: uppercase; letter-spacing: .06em; margin-top: 12px; }
            .pc h4 { margin: 4px 0 2px; font-size: 15px; font-weight: 500; line-height: 1.35; }
            .pc h4 a { color: inherit; text-decoration: none; }
            .pc h4 a:hover { color: var(--ac, var(--storefront-primary, #307F3E)); }
            .pc .pr { margin: 4px 0 12px; font-weight: 600; font-size: 15px; display: flex; flex-wrap: wrap; align-items: baseline; gap: 6px; }
            .pc .pr s { color: var(--mut, #777); font-weight: 400; margin-right: 6px; font-size: 13.5px; }
            .btn { display: inline-block; background: var(--ac, var(--storefront-primary, #307F3E)); color: #fff; border: 1px solid var(--ac, var(--storefront-primary, #307F3E)); padding: 11px 26px; font: 600 13px inherit; font-family: inherit; text-transform: uppercase; letter-spacing: .08em; cursor: pointer; text-decoration: none; text-align: center; box-sizing: border-box; }
            .btn:hover { background: #25632f; border-color: #25632f; color: #fff; }
            .btn.o { background: none; color: var(--ac, var(--storefront-primary, #307F3E)); }
            .btn.o:hover { background: var(--ac, var(--storefront-primary, #307F3E)); color: #fff; }
            .pc .btn { width: 100%; margin-top: auto; }
            .hpg__empty { grid-column:1/-1; margin:0; padding:60px 20px; border:1px dashed #d4d4d8; color:#71717a; font-size:14px; text-align:center; }
        `;

            const initialLibraryAssets = @json($assetUrls);
            const mediaLibraryUrl = @json($mediaLibraryUrl);
            const uploadMediaUrl = @json($uploadUrl);
            const mediaOwnerType = @json($entityName);
            const mediaOwnerId = @json($page->getKey());
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            let activeAssetProps = null;
            let mediaModalState = {
                library: 'all',
                search: '',
                page: 1,
                lastPage: 1,
                total: initialLibraryAssets.length,
                loading: false,
            };

            const mediaModal = document.getElementById('hubafrika-media-modal');
            const mediaBackdrop = document.getElementById('hubafrika-media-backdrop');
            const mediaGrid = document.getElementById('hubafrika-media-grid');
            const mediaSearchInput = document.getElementById('hubafrika-media-search');
            const mediaUrlInput = document.getElementById('hubafrika-media-url-input');
            const mediaUrlUseBtn = document.getElementById('hubafrika-media-url-use');
            const mediaStatus = document.getElementById('hubafrika-media-status');
            const mediaPageInfo = document.getElementById('hubafrika-media-page-info');
            const mediaPrevBtn = document.getElementById('hubafrika-media-prev');
            const mediaNextBtn = document.getElementById('hubafrika-media-next');
            const mediaCloseBtn = document.getElementById('hubafrika-media-close');
            const mediaCancelBtn = document.getElementById('hubafrika-media-cancel');
            const mediaUploadBtn = document.getElementById('hubafrika-media-upload-btn');
            const mediaUploadInput = document.getElementById('hubafrika-media-upload-input');
            const mediaTabButtons = [].slice.call(document.querySelectorAll('#hubafrika-media-tabs [data-library]'));

            function setMediaStatus(message, isError = false) {
                if (!mediaStatus) {
                    return;
                }
                if (!message) {
                    mediaStatus.textContent = '';
                    mediaStatus.classList.add('hidden');
                    return;
                }
                mediaStatus.textContent = message;
                mediaStatus.classList.remove('hidden', 'text-sky-300', 'text-rose-300');
                mediaStatus.classList.add(isError ? 'text-rose-300' : 'text-sky-300');
            }

            function syncMediaTabs() {
                mediaTabButtons.forEach((button) => {
                    const active = button.getAttribute('data-library') === mediaModalState.library;
                    button.classList.toggle('bg-sky-600', active);
                    button.classList.toggle('text-white', active);
                    button.classList.toggle('bg-slate-800', !active);
                    button.classList.toggle('text-slate-300', !active);
                });
            }

            function renderMediaItems(items) {
                if (!mediaGrid) {
                    return;
                }
                mediaGrid.innerHTML = '';

                if (!items || !items.length) {
                    const empty = document.createElement('div');
                    empty.className = 'col-span-full rounded-xl border border-dashed border-slate-700 px-6 py-12 text-center text-sm text-slate-400';
                    empty.textContent = mediaModalState.loading
                        ? 'Loading images from Media Library...'
                        : 'No matching images found in the Media Library.';
                    mediaGrid.appendChild(empty);
                    return;
                }

                items.forEach((item) => {
                    const card = document.createElement('button');
                    card.type = 'button';
                    card.className = 'group overflow-hidden rounded-xl border border-slate-700 bg-slate-950 text-left transition hover:border-sky-400 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-sky-500';

                    const preview = document.createElement('span');
                    preview.className = 'flex h-32 items-center justify-center overflow-hidden bg-slate-800/70';

                    const img = document.createElement('img');
                    img.src = item.src;
                    img.alt = item.original_name || item.name || 'Media image';
                    img.loading = 'lazy';
                    img.className = 'h-full w-full object-contain transition-transform duration-200 group-hover:scale-105';
                    preview.appendChild(img);

                    const meta = document.createElement('span');
                    meta.className = 'block space-y-1 p-2.5';

                    const title = document.createElement('span');
                    title.className = 'block truncate text-xs font-semibold text-slate-100';
                    title.title = item.original_name || item.name || '';
                    title.textContent = item.original_name || item.name || 'Image';

                    const badges = document.createElement('span');
                    badges.className = 'flex flex-wrap items-center gap-1 text-[10px] text-slate-400';

                    if (item.library_label) {
                        const libBadge = document.createElement('span');
                        libBadge.className = 'rounded-full bg-slate-800 px-2 py-0.5 font-semibold text-slate-200';
                        libBadge.textContent = item.library_label;
                        badges.appendChild(libBadge);
                    }
                    if (item.folder) {
                        const folderBadge = document.createElement('span');
                        folderBadge.className = 'truncate';
                        folderBadge.title = item.folder;
                        folderBadge.textContent = item.folder;
                        badges.appendChild(folderBadge);
                    }

                    meta.appendChild(title);
                    meta.appendChild(badges);
                    card.appendChild(preview);
                    card.appendChild(meta);
                    card.addEventListener('click', () => applyPickedAsset(item));
                    mediaGrid.appendChild(card);
                });
            }

            async function loadMediaLibrary() {
                mediaModalState.loading = true;
                setMediaStatus('');
                if (mediaGrid && !mediaGrid.children.length) {
                    renderMediaItems([]);
                }

                try {
                    const params = new URLSearchParams({
                        library: mediaModalState.library,
                        search: mediaModalState.search,
                        page: String(mediaModalState.page),
                        per_page: '24',
                    });

                    const response = await fetch(`${mediaLibraryUrl}?${params.toString()}`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    if (!response.ok) {
                        throw new Error('Could not load Media Library images.');
                    }

                    const payload = await response.json();
                    const items = Array.isArray(payload.data) ? payload.data : [];
                    const meta = payload.meta || {};
                    mediaModalState.loading = false;
                    mediaModalState.page = meta.current_page || 1;
                    mediaModalState.lastPage = Math.max(1, meta.last_page || 1);
                    mediaModalState.total = meta.total ?? items.length;

                    if (items.length) {
                        editor.AssetManager.add(items);
                    }

                    renderMediaItems(items);
                    if (mediaPageInfo) {
                        mediaPageInfo.textContent = `Page ${mediaModalState.page} of ${mediaModalState.lastPage} (${mediaModalState.total} images)`;
                    }
                    if (mediaPrevBtn) {
                        mediaPrevBtn.disabled = mediaModalState.page <= 1;
                    }
                    if (mediaNextBtn) {
                        mediaNextBtn.disabled = mediaModalState.page >= mediaModalState.lastPage;
                    }
                } catch (error) {
                    mediaModalState.loading = false;
                    renderMediaItems(initialLibraryAssets);
                    setMediaStatus(error.message || 'Unable to load Media Library.', true);
                }
            }

            function openMediaLibraryModal(props = null) {
                activeAssetProps = props;
                syncMediaTabs();
                mediaModal.classList.remove('hidden');
                loadMediaLibrary();
                if (mediaSearchInput) {
                    setTimeout(() => mediaSearchInput.focus(), 50);
                }
            }

            function closeMediaLibraryModal(fromCustom = false) {
                if (!mediaModal || mediaModal.classList.contains('hidden')) {
                    activeAssetProps = null;
                    return;
                }
                mediaModal.classList.add('hidden');
                const pendingProps = activeAssetProps;
                activeAssetProps = null;
                if (!fromCustom && pendingProps && typeof pendingProps.close === 'function') {
                    pendingProps.close();
                }
            }

            // ---- init editor ----
            const editor = grapesjs.init({
                container: '#gjs',
                height: '100%',
                storageManager: false,
                plugins: [],
                assetManager: {
                    assets: initialLibraryAssets,
                    upload: uploadMediaUrl,
                    uploadName: 'file',
                    multiUpload: false,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    params: {
                        owner_type: mediaOwnerType,
                        owner_id: mediaOwnerId,
                    },
                    custom: {
                        open(props) {
                            openMediaLibraryModal(props);
                        },
                        close() {
                            closeMediaLibraryModal(true);
                        },
                    },
                },
                canvas: {
                    styles: [
                        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700;800&display=swap',
                        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
                        '{{ Vite::asset('resources/css/app.css') }}',
                    ],
                    scripts: [
                        'https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js',
                    ],
                },
                showDevices: false,
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
                        { name: 'Decorations', open: false, buildProps: ['background-color', 'background-image', 'background-size', 'background-position', 'background-repeat', 'border-radius', 'border', 'box-shadow', 'opacity'] },
                    ],
                },
            });

            function applyPickedAsset(asset) {
                const normalized = {
                    src: asset.src,
                    name: asset.original_name || asset.name || asset.src,
                    type: 'image',
                };

                editor.AssetManager.add(normalized);

                if (activeAssetProps && typeof activeAssetProps.select === 'function') {
                    const select = activeAssetProps.select;
                    const propsRef = activeAssetProps;
                    activeAssetProps = null;
                    select(normalized, true);
                    if (typeof propsRef.close === 'function') {
                        propsRef.close();
                    }
                    closeMediaLibraryModal(true);
                    return;
                }

                const selected = editor.getSelected();
                if (selected && (selected.is('image') || (selected.get('tagName') || '').toLowerCase() === 'img')) {
                    selected.set({ src: normalized.src });
                    const attrs = selected.getAttributes ? selected.getAttributes() : {};
                    if (!attrs.alt) {
                        selected.addAttributes({ alt: normalized.name });
                    }
                } else {
                    const markup = `<div class="my-4"><img src="${escapeHtml(normalized.src)}" alt="${escapeHtml(normalized.name)}" class="w-full h-auto max-h-[500px] object-cover rounded-xl" /></div>`;
                    const added = selected && typeof selected.append === 'function'
                        ? selected.append(markup)
                        : editor.getWrapper().append(markup);
                    const target = Array.isArray(added) ? added[0] : added;
                    if (target) {
                        editor.select(target);
                    }
                }

                closeMediaLibraryModal(true);
            }

            document.getElementById('open-media-library')?.addEventListener('click', () => {
                openMediaLibraryModal(null);
            });
            mediaCloseBtn?.addEventListener('click', () => closeMediaLibraryModal(false));
            mediaCancelBtn?.addEventListener('click', () => closeMediaLibraryModal(false));
            mediaBackdrop?.addEventListener('click', () => closeMediaLibraryModal(false));

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && mediaModal && !mediaModal.classList.contains('hidden')) {
                    closeMediaLibraryModal(false);
                }
            });

            mediaTabButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    mediaModalState.library = button.getAttribute('data-library') || 'all';
                    mediaModalState.page = 1;
                    syncMediaTabs();
                    loadMediaLibrary();
                });
            });

            let mediaSearchTimer = null;
            mediaSearchInput?.addEventListener('input', () => {
                clearTimeout(mediaSearchTimer);
                mediaSearchTimer = setTimeout(() => {
                    mediaModalState.search = mediaSearchInput.value.trim();
                    mediaModalState.page = 1;
                    loadMediaLibrary();
                }, 250);
            });

            mediaPrevBtn?.addEventListener('click', () => {
                if (mediaModalState.page > 1) {
                    mediaModalState.page -= 1;
                    loadMediaLibrary();
                }
            });

            mediaNextBtn?.addEventListener('click', () => {
                if (mediaModalState.page < mediaModalState.lastPage) {
                    mediaModalState.page += 1;
                    loadMediaLibrary();
                }
            });

            mediaUrlUseBtn?.addEventListener('click', () => {
                const url = (mediaUrlInput?.value || '').trim();
                if (!url) {
                    setMediaStatus('Enter an image URL first.', true);
                    return;
                }
                mediaUrlInput.value = '';
                applyPickedAsset({ src: url, original_name: url });
            });

            mediaUploadBtn?.addEventListener('click', () => mediaUploadInput?.click());
            mediaUploadInput?.addEventListener('change', async () => {
                const file = mediaUploadInput.files?.[0];
                if (!file) {
                    return;
                }

                setMediaStatus('Uploading to Media Library...');
                mediaUploadBtn.disabled = true;

                try {
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('owner_type', mediaOwnerType);
                    formData.append('owner_id', String(mediaOwnerId));

                    const response = await fetch(uploadMediaUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    const payload = await response.json();
                    if (!response.ok) {
                        const errors = Object.values(payload.errors || {}).flat().join(' ');
                        throw new Error(errors || payload.message || 'Image upload failed.');
                    }

                    const uploaded = Array.isArray(payload.data) ? payload.data[0] : null;
                    if (uploaded) {
                        editor.AssetManager.add(uploaded);
                        mediaModalState.page = 1;
                        await loadMediaLibrary();
                        setMediaStatus('Uploaded to Media Library. Click it below to use.');
                    }
                } catch (error) {
                    setMediaStatus(error.message || 'Image upload failed.', true);
                } finally {
                    mediaUploadBtn.disabled = false;
                    mediaUploadInput.value = '';
                }
            });

            const deviceButtons = [].slice.call(document.querySelectorAll('[data-builder-device]'));
            function syncDeviceButtons() {
                const currentDevice = editor.getDevice();
                deviceButtons.forEach((button) => {
                    const active = button.getAttribute('data-builder-device') === currentDevice;
                    button.classList.toggle('is-active', active);
                    button.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
            }
            deviceButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    editor.setDevice(button.getAttribute('data-builder-device'));
                });
            });
            editor.on('device:select', syncDeviceButtons);
            syncDeviceButtons();

            // ---- custom component: carousel ----
            editor.DomComponents.addType('hubafrika-carousel', {
                isComponent: (el) => el.nodeType === 1 && (el.hasAttribute('data-hubafrika-carousel') || el.classList?.contains('hubafrika-carousel')),
                model: {
                    defaults: {
                        traits: [
                            {
                                type: 'select',
                                name: 'data-edit-slide',
                                label: 'Canvas slide',
                                options: [
                                    { id: '0', label: 'Slide 1 (Solar & Renewable)' },
                                    { id: '1', label: 'Slide 2 (Power Backup)' },
                                    { id: '2', label: 'Slide 3 (Smart Security)' },
                                    { id: 'all', label: 'All Slides (Stacked for Editing)' },
                                ],
                                default: '0',
                            }
                        ]
                    },
                    init() {
                        this.listenTo(this, 'change:attributes:data-edit-slide', this.handleSlideChange);
                    },
                    handleSlideChange() {
                        const val = this.getAttributes()['data-edit-slide'] || '0';
                        const el = this.view?.el;
                        if (!el) return;
                        const slides = el.querySelectorAll('[data-carousel-slide]');
                        slides.forEach((s, idx) => {
                            if (val === 'all') {
                                s.style.display = 'flex';
                                s.style.position = 'relative';
                            } else {
                                s.style.position = 'absolute';
                                s.style.display = (idx === parseInt(val, 10)) ? 'flex' : 'none';
                            }
                        });
                    }
                }
            });

            // ---- custom component: looping logo slider ----
            function syncLogoSliderColumns(component) {
                if (!component) {
                    return;
                }
                const visible = Math.max(1, Math.min(8, parseInt(component.getAttributes()['data-slides-visible'] || '6', 10) || 6));
                component.addStyle({
                    '--hubafrika-logo-visible': String(visible),
                    '--hubafrika-logo-cols': String(Math.min(2, visible)),
                    '--hubafrika-logo-cols-sm': String(Math.min(4, visible)),
                    '--hubafrika-logo-cols-lg': String(visible),
                });
            }

            editor.DomComponents.addType('hubafrika-logo-slider', {
                isComponent: (el) => el.nodeType === 1 && el.hasAttribute('data-hubafrika-logo-slider'),
                model: {
                    defaults: {
                        name: 'Logo Slider',
                        traits: [
                            {
                                type: 'select',
                                name: 'data-slides-visible',
                                label: 'Logos visible (desktop)',
                                options: [
                                    { id: '2', label: '2 logos' },
                                    { id: '3', label: '3 logos' },
                                    { id: '4', label: '4 logos' },
                                    { id: '5', label: '5 logos' },
                                    { id: '6', label: '6 logos' },
                                    { id: '7', label: '7 logos' },
                                    { id: '8', label: '8 logos' },
                                ],
                                default: '6',
                            },
                            {
                                type: 'select',
                                name: 'data-slide-step',
                                label: 'Images to slide at a time',
                                options: [
                                    { id: '1', label: '1 image' },
                                    { id: '2', label: '2 images' },
                                    { id: '3', label: '3 images' },
                                    { id: '4', label: '4 images' },
                                    { id: '5', label: '5 images' },
                                    { id: '6', label: '6 images' },
                                ],
                                default: '1',
                            },
                            {
                                type: 'select',
                                name: 'data-slide-interval',
                                label: 'Slide speed',
                                options: [
                                    { id: '2000', label: 'Fast (2s)' },
                                    { id: '3500', label: 'Normal (3.5s)' },
                                    { id: '5000', label: 'Slow (5s)' },
                                    { id: '7000', label: 'Very slow (7s)' },
                                ],
                                default: '3500',
                            },
                            {
                                type: 'checkbox',
                                name: 'data-slide-loop',
                                label: 'Loop slider',
                                valueTrue: 'true',
                                valueFalse: 'false',
                                default: 'true',
                            },
                        ],
                    },
                    init() {
                        this.find('.hubafrika-logo-nav, [data-logo-prev], [data-logo-next]').forEach((nav) => nav.remove());
                        this.listenTo(this, 'change:attributes:data-slides-visible', () => syncLogoSliderColumns(this));
                        const toolbar = this.get('toolbar') || [];
                        if (!toolbar.some((item) => item.id === 'hubafrika-add-logo-slide')) {
                            this.set('toolbar', [
                                ...toolbar,
                                {
                                    id: 'hubafrika-add-logo-slide',
                                    attributes: { class: 'fa fa-plus', title: 'Add logo slide from Media Library' },
                                    command: () => {
                                        const track = this.find('.hubafrika-logo-slider-track, .hubafrika-marquee-track')[0];
                                        if (!track) {
                                            return;
                                        }
                                        const added = track.append(`<div class="hubafrika-logo-slide">
                                            <a href="" target="_blank" rel="noopener noreferrer" data-logo-slide="true"
                                                class="hubafrika-logo-slide-card">
                                                <img src="/images/storefront/carousel-renewable.jpg" alt="Partner Logo" />
                                            </a>
                                        </div>`);
                                        const slideComp = Array.isArray(added) ? added[0] : added;
                                        const imgComp = slideComp?.find('img')?.[0];
                                        if (imgComp) {
                                            editor.select(imgComp);
                                            openMediaLibraryModal(null);
                                        }
                                    },
                                },
                            ]);
                        }
                    },
                },
            });

            editor.DomComponents.addType('hubafrika-logo-slide', {
                extend: 'link',
                isComponent: (el) => el.nodeType === 1 && el.hasAttribute('data-logo-slide'),
                model: {
                    defaults: {
                        name: 'Logo Slide Link',
                        traits: [
                            {
                                type: 'text',
                                name: 'href',
                                label: 'Link URL',
                                placeholder: 'https://partner.example.com',
                            },
                            {
                                type: 'checkbox',
                                name: 'target',
                                label: 'Open in new window',
                                valueTrue: '_blank',
                                valueFalse: '_self',
                                default: '_blank',
                            },
                        ],
                    },
                    init() {
                        this.listenTo(this, 'change:attributes:target', () => {
                            const target = this.getAttributes().target;
                            if (target === '_blank') {
                                this.addAttributes({ rel: 'noopener noreferrer' });
                            } else {
                                this.removeAttributes(['rel']);
                            }
                        });
                        const toolbar = this.get('toolbar') || [];
                        if (!toolbar.some((item) => item.id === 'hubafrika-slide-pick-media')) {
                            this.set('toolbar', [
                                ...toolbar,
                                {
                                    id: 'hubafrika-slide-pick-media',
                                    attributes: { class: 'fa fa-images', title: 'Choose logo from Media Library' },
                                    command: () => {
                                        const imgComp = this.find('img')?.[0];
                                        if (imgComp) {
                                            editor.select(imgComp);
                                            openMediaLibraryModal(null);
                                        }
                                    },
                                },
                            ]);
                        }
                    },
                },
            });

            const categoriesOptions = [
                { id: '', label: 'All categories' },
                ...@json($categories->map(fn ($c) => ['id' => (string) $c->id, 'label' => $c->name]))
            ];
            const brandsOptions = [
                { id: '', label: 'All brands' },
                ...@json($brands->map(fn ($b) => ['id' => (string) $b->id, 'label' => $b->name]))
            ];

            // ---- custom component: product grid ----
            editor.DomComponents.addType('hubafrika-product-grid', {
                isComponent: (el) => el.nodeType === 1 && el.hasAttribute('data-hubafrika-products'),
                model: {
                    defaults: {
                        name: 'Product Grid',
                        traits: [
                            {
                                type: 'select',
                                name: 'data-category',
                                label: 'Category',
                                options: categoriesOptions,
                            },
                            {
                                type: 'select',
                                name: 'data-brand',
                                label: 'Brand',
                                options: brandsOptions,
                            },
                            {
                                type: 'number',
                                name: 'data-limit',
                                label: 'Products shown',
                                min: 1,
                                max: 24,
                                step: 1,
                            },
                            {
                                type: 'select',
                                name: 'data-sort',
                                label: 'Sort by',
                                options: [
                                    { id: 'newest', label: 'Newest first' },
                                    { id: 'name_asc', label: 'Name A–Z' },
                                    { id: 'brand_asc', label: 'Brand A–Z' },
                                    { id: 'category_asc', label: 'Category A–Z' },
                                    { id: 'price_asc', label: 'Price low → high' },
                                    { id: 'price_desc', label: 'Price high → low' },
                                ],
                            },
                        ],
                    },
                    init() {
                        this.on('change:attributes:data-limit', this.updateGridColumns);
                        this.updateGridColumns();
                    },
                    updateGridColumns() {
                        const limit = parseInt(this.getAttributes()['data-limit'], 10) || 8;
                        const cols = (limit === 3 || limit === 6 || limit === 9) ? 3 : (limit === 5 || limit === 10 ? 5 : (limit <= 2 ? limit : 4));
                        this.addAttributes({ 'data-cols': String(cols) });
                    },
                },
            });

            const defaultBuilderPageTitle = @json($page->title);

            function currentPageTitle() {
                const input = document.getElementById('page-title');
                const value = (input ? input.value : defaultBuilderPageTitle) || '';

                return value.trim() || 'Page Title';
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;

                return div.innerHTML;
            }

            function syncPageHeaderComponent(component) {
                if (!component || component.get('type') !== 'hubafrika-page-header') {
                    return;
                }

                const attrs = component.getAttributes();
                const autoTitle = attrs['data-auto-title'] === 'true' || attrs['data-auto-title'] === true || attrs['data-auto-title'] === '1';
                const customTitle = (attrs['data-header-title'] || '').trim();
                const titleText = autoTitle ? currentPageTitle() : (customTitle || currentPageTitle());
                const parentText = (attrs['data-header-parent'] || '').trim();
                const trailText = parentText ? `${parentText} / ${titleText}` : titleText;

                const titleComp = component.find('[data-page-header-title]')[0];
                if (titleComp) {
                    titleComp.components(escapeHtml(titleText));
                }

                const trailComp = component.find('[data-page-header-trail]')[0];
                if (trailComp) {
                    trailComp.components(escapeHtml(trailText));
                }
            }

            // ---- custom component: page header ----
            editor.DomComponents.addType('hubafrika-page-header', {
                isComponent: (el) => el.nodeType === 1 && el.hasAttribute('data-hubafrika-page-header'),
                model: {
                    defaults: {
                        name: 'Page Header',
                        traits: [
                            {
                                type: 'checkbox',
                                name: 'data-auto-title',
                                label: 'Sync with page title',
                                valueTrue: 'true',
                                valueFalse: 'false',
                                default: 'true',
                            },
                            {
                                type: 'text',
                                name: 'data-header-title',
                                label: 'Custom title',
                                placeholder: 'Matches page title when synced',
                            },
                            {
                                type: 'text',
                                name: 'data-header-parent',
                                label: 'Parent section',
                                placeholder: 'e.g. Services',
                            },
                        ],
                    },
                    init() {
                        this.listenTo(this, 'change:attributes:data-auto-title', () => syncPageHeaderComponent(this));
                        this.listenTo(this, 'change:attributes:data-header-parent', () => syncPageHeaderComponent(this));
                        this.listenTo(this, 'change:attributes:data-header-title', () => {
                            const attrs = this.getAttributes();
                            const customTitle = (attrs['data-header-title'] || '').trim();
                            if (customTitle && customTitle !== currentPageTitle() && attrs['data-auto-title'] !== 'false') {
                                this.addAttributes({ 'data-auto-title': 'false' });
                            }
                            syncPageHeaderComponent(this);
                        });
                    },
                },
            });

            // ---- blocks ----
            editor.BlockManager.add('hubafrika-page-header', {
                label: 'Page Header',
                category: 'Hubafrika',
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="2" y="4" width="20" height="10" rx="2" fill="currentColor" fill-opacity="0.15" />
                    <line x1="6" y1="8" x2="14" y2="8" stroke-linecap="round" />
                    <line x1="6" y1="11" x2="11" y2="11" stroke-linecap="round" />
                </svg>`,
                content: `<section class="hubafrika-page-header" data-hubafrika-page-header="true" data-auto-title="true" data-header-parent="">
                <div class="hubafrika-page-header-inner">
                    <h1 class="hubafrika-page-header-title" data-page-header-title="true">${escapeHtml(currentPageTitle())}</h1>
                    <p class="hubafrika-page-header-crumb">
                        <a href="/">Home</a>
                        <span aria-hidden="true"> / </span>
                        <span data-page-header-trail="true">${escapeHtml(currentPageTitle())}</span>
                    </p>
                </div>
            </section>`,
            });

            editor.BlockManager.add('hubafrika-hero', {
                label: 'Storefront hero',
                category: 'Hubafrika',
                content: `<section style="padding:80px 24px;text-align:center;background:color-mix(in srgb,var(--storefront-primary,#16a34a) 8%,var(--storefront-surface,#ffffff));color:var(--storefront-text,#111827)">
                <p style="color:var(--storefront-primary,#16a34a);font-weight:700;letter-spacing:.12em;text-transform:uppercase">Hubafrika collection</p>
                <h1 style="margin:16px 0;font-size:clamp(36px,6vw,64px);font-weight:800;line-height:1.05">Clean power for every space</h1>
                <p style="margin:0 auto 24px;max-width:620px;opacity:.78;font-size:18px;line-height:1.7">Discover solar arrays, hybrid inverters, lithium storage, and smart security engineered for daily reliability.</p>
                <a href="/shop" style="display:inline-block;border-radius:10px;background:var(--storefront-primary,#16a34a);padding:14px 24px;color:#fff;text-decoration:none;font-weight:700">Shop the collection</a>
            </section>`,
            });

            editor.BlockManager.add('hubafrika-split', {
                label: 'Image and story',
                category: 'Hubafrika',
                content: `<section style="display:flex;flex-wrap:wrap;align-items:center;gap:32px;padding:64px 24px;background:var(--storefront-surface,#ffffff);color:var(--storefront-text,#111827)">
                <img src="/images/storefront/carousel-renewable.jpg" alt="Hub Afrika solar installation" style="width:100%;max-width:480px;min-height:320px;flex:1;object-fit:cover;border-radius:16px">
                <div style="flex:1;min-width:260px">
                    <p style="color:var(--storefront-primary,#16a34a);font-weight:700;letter-spacing:.12em;text-transform:uppercase">Our story</p>
                    <h2 style="font-size:clamp(28px,4vw,44px);font-weight:800;line-height:1.1">Engineered for energy independence</h2>
                    <p style="opacity:.78;font-size:16px;line-height:1.8">Share what powers your products and how your engineering team supports homes and commercial sites end-to-end.</p>
                    <a href="/shop" style="color:var(--storefront-primary,#16a34a);font-weight:700;text-decoration:none">Explore the shop →</a>
                </div>
            </section>`,
            });

            editor.BlockManager.add('hubafrika-callout', {
                label: 'Call to action',
                category: 'Hubafrika',
                content: `<section style="padding:56px 24px;text-align:center;background:var(--storefront-secondary,#0f172a);color:#fff">
                <h2 style="font-size:clamp(28px,4vw,42px);font-weight:800">Find your next energy upgrade</h2>
                <p style="margin:12px auto 24px;max-width:560px;color:#d1d5db;line-height:1.7">Browse Tier-1 solar modules, inverters, battery backup kits, and accessories ready to ship.</p>
                <a href="/shop" style="display:inline-block;border-radius:10px;background:var(--storefront-accent,#10b981);padding:14px 24px;color:#0f172a;text-decoration:none;font-weight:700">Browse products</a>
            </section>`,
            });

            // Products block: <style> travels with the block, so getCss() picks it up.
            editor.BlockManager.add('hubafrika-products', {
                label: 'Newest products',
                category: 'Hubafrika',
                content: `
                <style>${productGridCss}</style>
                <section class="sec" style="padding:52px 0">
                    <div class="w" style="max-width:1200px;margin:0 auto;padding:0 20px">
                        <h2 class="st" style="text-align:center;text-transform:uppercase;letter-spacing:.12em;font-size:18px;font-weight:600;margin-bottom:6px">Newest products</h2>
                        <p class="ss" style="text-align:center;color:var(--mut,#777);margin:0 0 30px">Solar, backup power, water and security for homes and businesses.</p>
                        <div data-hubafrika-products="true" data-sort="newest" data-limit="8" data-category="" data-brand="" class="grid" style="min-height:140px;border:1px dashed #cbd5e1;padding:16px;border-radius:4px;background:transparent">
                            <div class="pc" style="pointer-events:none;opacity:0.9">
                                <span class="sale">Sale</span>
                                <div class="pi"><span class="pi-empty">Product Preview</span></div>
                                <div class="br">Solar Panels</div>
                                <h4>Sample Solar Module</h4>
                                <div class="pr"><s>$450</s> $380</div>
                                <span class="btn o" style="display:block;text-align:center">Add to cart</span>
                            </div>
                            <div class="pc" style="pointer-events:none;opacity:0.9">
                                <div class="pi"><span class="pi-empty">Product Preview</span></div>
                                <div class="br">Inverters</div>
                                <h4>Sample Hybrid Inverter</h4>
                                <div class="pr">$520</div>
                                <span class="btn o" style="display:block;text-align:center">Add to cart</span>
                            </div>
                            <div class="pc" style="pointer-events:none;opacity:0.9">
                                <span class="sale">Sale</span>
                                <div class="pi"><span class="pi-empty">Product Preview</span></div>
                                <div class="br">Lithium Batteries</div>
                                <h4>Sample Battery Pack</h4>
                                <div class="pr"><s>$920</s> $750</div>
                                <span class="btn o" style="display:block;text-align:center">Add to cart</span>
                            </div>
                            <div class="pc" style="pointer-events:none;opacity:0.9">
                                <div class="pi"><span class="pi-empty">Product Preview</span></div>
                                <div class="br">Accessories</div>
                                <h4>Sample Solar Cable</h4>
                                <div class="pr">$120</div>
                                <span class="btn o" style="display:block;text-align:center">Add to cart</span>
                            </div>
                        </div>
                        <p style="text-align:center;margin-top:30px"><a class="btn" href="/shop">View all products</a></p>
                    </div>
                </section>`,
            });

            // ---- Extended block library (widgets, grid layouts, carousels) ----
            @include('admin.cms.builder-blocks')

            // ---- load existing content ----
            const projectData = @json($projectData);
            if (projectData && Object.keys(projectData).length) {
                editor.loadProjectData(projectData);
            } else {
                editor.setComponents(@json($initialHtml));
            }
            if (initialLibraryAssets.length) {
                editor.AssetManager.add(initialLibraryAssets);
            }

            @php
                $builderPalette = \App\Support\Storefront\Palette::frontend();
                $builderBodyFont = \App\Support\Storefront\Palette::bodyFont();
            @endphp
            const storefrontPalette = @json($builderPalette);
            const storefrontBodyFont = @json($builderBodyFont);

            function syncCanvasTheme() {
                const frameDoc = editor.Canvas.getDocument();
                if (!frameDoc || !frameDoc.head) {
                    return;
                }
                let styleEl = frameDoc.getElementById('hubafrika-canvas-theme');
                if (!styleEl) {
                    styleEl = frameDoc.createElement('style');
                    styleEl.id = 'hubafrika-canvas-theme';
                    frameDoc.head.appendChild(styleEl);
                }
                styleEl.textContent = `
                    :root {
                        --storefront-primary: ${storefrontPalette.primary};
                        --storefront-secondary: ${storefrontPalette.secondary};
                        --storefront-accent: ${storefrontPalette.accent};
                        --storefront-bg: ${storefrontPalette.bg};
                        --storefront-surface: ${storefrontPalette.surface};
                        --storefront-text: ${storefrontPalette.text};
                    }
                    body {
                        background-color: var(--storefront-bg);
                        color: var(--storefront-text);
                        font-family: ${storefrontBodyFont};
                    }
                    [data-hubafrika-spacer] {
                        outline: 1px dashed rgba(100, 116, 139, 0.45);
                        background: rgba(148, 163, 184, 0.08);
                    }
                    [data-hubafrika-logo-slider] .hubafrika-logo-slider-viewport {
                        overflow-x: auto;
                    }
                `;

                if (!frameDoc.documentElement.dataset.hubafrikaCanvasReady) {
                    frameDoc.documentElement.dataset.hubafrikaCanvasReady = '1';
                    frameDoc.addEventListener('click', (event) => {
                        const target = event.target;
                        if (!(target instanceof Element)) {
                            return;
                        }
                        if (target.closest('a[data-logo-slide]')) {
                            event.preventDefault();
                        }
                        const tabBtn = target.closest('[data-hubafrika-tabs] [data-tab-btn]');
                        if (tabBtn) {
                            const tabsRoot = tabBtn.closest('[data-hubafrika-tabs]');
                            const id = tabBtn.getAttribute('data-tab-btn');
                            tabsRoot.querySelectorAll('[data-tab-btn]').forEach((btn) => {
                                const active = btn.getAttribute('data-tab-btn') === id;
                                btn.classList.toggle('is-active', active);
                                btn.setAttribute('aria-selected', active ? 'true' : 'false');
                            });
                            tabsRoot.querySelectorAll('[data-tab-panel]').forEach((panel) => {
                                panel.classList.toggle('is-active', panel.getAttribute('data-tab-panel') === id);
                            });
                            return;
                        }
                        const dotBtn = target.closest('[data-hubafrika-carousel] [data-carousel-dot]');
                        if (dotBtn) {
                            const carouselRoot = dotBtn.closest('[data-hubafrika-carousel]');
                            const dots = [].slice.call(carouselRoot.querySelectorAll('[data-carousel-dot]'));
                            const slides = [].slice.call(carouselRoot.querySelectorAll('[data-carousel-slide]'));
                            const idx = dots.indexOf(dotBtn);
                            if (idx >= 0) {
                                slides.forEach((s, i) => s.classList.toggle('is-active', i === idx));
                                dots.forEach((d, i) => d.classList.toggle('is-active', i === idx));
                            }
                        }
                    });
                }
            }

            editor.on('load', () => {
                if (initialLibraryAssets.length) {
                    editor.AssetManager.add(initialLibraryAssets);
                }
                syncCanvasTheme();
                editor.getWrapper()?.find('[data-hubafrika-page-header]')?.forEach((header) => {
                    const attrs = header.getAttributes();
                    if (attrs['data-auto-title'] === 'true' || attrs['data-auto-title'] === true || attrs['data-auto-title'] === '1') {
                        syncPageHeaderComponent(header);
                    }
                });
            });
            editor.on('canvas:frame:load', syncCanvasTheme);

            editor.on('component:selected', (component) => {
                if (!component || (!component.is('image') && (component.get('tagName') || '').toLowerCase() !== 'img')) {
                    return;
                }
                const toolbar = component.get('toolbar') || [];
                const alreadyHasMedia = toolbar.some((item) => item.id === 'hubafrika-pick-media');
                if (!alreadyHasMedia) {
                    component.set('toolbar', [
                        ...toolbar,
                        {
                            id: 'hubafrika-pick-media',
                            attributes: { class: 'fa fa-images', title: 'Choose image from Media Library' },
                            command: () => openMediaLibraryModal(null),
                        },
                    ]);
                }
            });

            editor.on('component:add', (component) => {
                if (!component) {
                    return;
                }
                if (component.get('type') === 'hubafrika-page-header') {
                    const attrs = component.getAttributes();
                    if (attrs['data-auto-title'] === 'true' || attrs['data-auto-title'] === true || attrs['data-auto-title'] === '1') {
                        syncPageHeaderComponent(component);
                    }
                    return;
                }
                if (typeof component.find === 'function') {
                    component.find('[data-hubafrika-page-header]').forEach((child) => {
                        const attrs = child.getAttributes();
                        if (attrs['data-auto-title'] === 'true' || attrs['data-auto-title'] === true || attrs['data-auto-title'] === '1') {
                            syncPageHeaderComponent(child);
                        }
                    });
                }
            });

            editor.on('rte:disable', (view) => {
                const comp = view?.model;
                const header = typeof comp?.closestType === 'function' ? comp.closestType('hubafrika-page-header') : null;
                if (!header || !comp) {
                    return;
                }
                const compAttrs = typeof comp.getAttributes === 'function' ? comp.getAttributes() : {};
                if (Object.prototype.hasOwnProperty.call(compAttrs, 'data-page-header-title')) {
                    const editedTitle = (view.el?.textContent || '').trim();
                    if (editedTitle) {
                        const isAuto = editedTitle === currentPageTitle();
                        header.addAttributes({
                            'data-auto-title': isAuto ? 'true' : 'false',
                            'data-header-title': isAuto ? '' : editedTitle,
                        });
                        const parentText = (header.getAttributes()['data-header-parent'] || '').trim();
                        const trailComp = header.find('[data-page-header-trail]')[0];
                        if (trailComp) {
                            trailComp.components(escapeHtml(parentText ? `${parentText} / ${editedTitle}` : editedTitle));
                        }
                    }
                }
            });

            const pageTitleInput = document.getElementById('page-title');
            if (pageTitleInput) {
                pageTitleInput.addEventListener('input', () => {
                    editor.getWrapper().find('[data-hubafrika-page-header]').forEach((header) => {
                        const attrs = header.getAttributes();
                        if (attrs['data-auto-title'] === 'true' || attrs['data-auto-title'] === true || attrs['data-auto-title'] === '1') {
                            syncPageHeaderComponent(header);
                        }
                    });
                });
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