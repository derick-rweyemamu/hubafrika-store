// 1. CUSTOM TAILWIND GRID LAYOUT BLOCKS (1, 2, 3, 4, 5, 6 COLUMNS)
// =========================================================================
const bm = editor.BlockManager;
const gridCategory = { id: 'grid-layouts', label: 'Grid Layouts', open: true };

// 1 Column Grid
bm.add('grid-1-col', {
label: '1 Column Grid',
category: gridCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.8">
    <rect x="3" y="4" width="18" height="16" rx="2" fill="currentColor" fill-opacity="0.15" />
</svg>`,
content: `
<section class="py-12 hubafrika-surface-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-6">
            <div
                class="p-8 rounded-2xl hubafrika-subcard min-h-[140px] flex flex-col justify-center items-center text-center">
                <span class="text-xs font-bold uppercase tracking-wider hubafrika-accent-text mb-2">Column 1 of 1</span>
                <h3 class="text-xl font-bold mb-2">Full Width Container</h3>
                <p class="text-sm opacity-75 max-w-xl">Drop widgets, text, cards, or media here. Customize padding,
                    colors, and borders freely.</p>
            </div>
        </div>
    </div>
</section>`
});

// 2 Columns Grid
bm.add('grid-2-col', {
label: '2 Columns Grid',
category: gridCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.8">
    <rect x="3" y="4" width="8.2" height="16" rx="1.5" fill="currentColor" fill-opacity="0.15" />
    <rect x="12.8" y="4" width="8.2" height="16" rx="1.5" fill="currentColor" fill-opacity="0.15" />
</svg>`,
content: `
<section class="py-12 hubafrika-surface-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div
                class="p-6 rounded-2xl hubafrika-subcard min-h-[140px] flex flex-col justify-center">
                <span class="text-xs font-bold uppercase tracking-wider hubafrika-accent-text mb-2">Column 1 (50%)</span>
                <h4 class="text-lg font-bold mb-2">Left Column</h4>
                <p class="text-sm opacity-75">Insert your text, cards, media, or widgets here.</p>
            </div>
            <div
                class="p-6 rounded-2xl hubafrika-subcard min-h-[140px] flex flex-col justify-center">
                <span class="text-xs font-bold uppercase tracking-wider hubafrika-accent-text mb-2">Column 2 (50%)</span>
                <h4 class="text-lg font-bold mb-2">Right Column</h4>
                <p class="text-sm opacity-75">Insert your text, cards, media, or widgets here.</p>
            </div>
        </div>
    </div>
</section>`
});

// 3 Columns Grid
bm.add('grid-3-col', {
label: '3 Columns Grid',
category: gridCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.8">
    <rect x="2.5" y="4" width="5.5" height="16" rx="1" fill="currentColor" fill-opacity="0.15" />
    <rect x="9.25" y="4" width="5.5" height="16" rx="1" fill="currentColor" fill-opacity="0.15" />
    <rect x="16" y="4" width="5.5" height="16" rx="1" fill="currentColor" fill-opacity="0.15" />
</svg>`,
content: `
<section class="py-12 hubafrika-surface-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div
                class="p-6 rounded-2xl hubafrika-subcard min-h-[140px] flex flex-col justify-center">
                <span class="text-xs font-bold uppercase tracking-wider hubafrika-accent-text mb-2">Column 1 (33.3%)</span>
                <h4 class="text-base font-bold mb-1">Column 1</h4>
                <p class="text-xs opacity-75">Insert your content or widgets here.</p>
            </div>
            <div
                class="p-6 rounded-2xl hubafrika-subcard min-h-[140px] flex flex-col justify-center">
                <span class="text-xs font-bold uppercase tracking-wider hubafrika-accent-text mb-2">Column 2 (33.3%)</span>
                <h4 class="text-base font-bold mb-1">Column 2</h4>
                <p class="text-xs opacity-75">Insert your content or widgets here.</p>
            </div>
            <div
                class="p-6 rounded-2xl hubafrika-subcard min-h-[140px] flex flex-col justify-center">
                <span class="text-xs font-bold uppercase tracking-wider hubafrika-accent-text mb-2">Column 3 (33.3%)</span>
                <h4 class="text-base font-bold mb-1">Column 3</h4>
                <p class="text-xs opacity-75">Insert your content or widgets here.</p>
            </div>
        </div>
    </div>
</section>`
});

// 4 Columns Grid
bm.add('grid-4-col', {
label: '4 Columns Grid',
category: gridCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.6">
    <rect x="2" y="4" width="4" height="16" rx="0.8" fill="currentColor" fill-opacity="0.15" />
    <rect x="7.33" y="4" width="4" height="16" rx="0.8" fill="currentColor" fill-opacity="0.15" />
    <rect x="12.66" y="4" width="4" height="16" rx="0.8" fill="currentColor" fill-opacity="0.15" />
    <rect x="18" y="4" width="4" height="16" rx="0.8" fill="currentColor" fill-opacity="0.15" />
</svg>`,
content: `
<section class="py-12 hubafrika-surface-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div
                class="p-5 rounded-2xl hubafrika-subcard min-h-[130px] flex flex-col justify-center">
                <span class="text-xs font-bold uppercase tracking-wider hubafrika-accent-text mb-1">Col 1</span>
                <h4 class="text-sm font-bold mb-1">Column 1 (25%)</h4>
                <p class="text-xs opacity-75">Insert content here.</p>
            </div>
            <div
                class="p-5 rounded-2xl hubafrika-subcard min-h-[130px] flex flex-col justify-center">
                <span class="text-xs font-bold uppercase tracking-wider hubafrika-accent-text mb-1">Col 2</span>
                <h4 class="text-sm font-bold mb-1">Column 2 (25%)</h4>
                <p class="text-xs opacity-75">Insert content here.</p>
            </div>
            <div
                class="p-5 rounded-2xl hubafrika-subcard min-h-[130px] flex flex-col justify-center">
                <span class="text-xs font-bold uppercase tracking-wider hubafrika-accent-text mb-1">Col 3</span>
                <h4 class="text-sm font-bold mb-1">Column 3 (25%)</h4>
                <p class="text-xs opacity-75">Insert content here.</p>
            </div>
            <div
                class="p-5 rounded-2xl hubafrika-subcard min-h-[130px] flex flex-col justify-center">
                <span class="text-xs font-bold uppercase tracking-wider hubafrika-accent-text mb-1">Col 4</span>
                <h4 class="text-sm font-bold mb-1">Column 4 (25%)</h4>
                <p class="text-xs opacity-75">Insert content here.</p>
            </div>
        </div>
    </div>
</section>`
});

// 5 Columns Grid
bm.add('grid-5-col', {
label: '5 Columns Grid',
category: gridCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.4">
    <rect x="1.5" y="4" width="3.2" height="16" rx="0.6" fill="currentColor" fill-opacity="0.15" />
    <rect x="5.9" y="4" width="3.2" height="16" rx="0.6" fill="currentColor" fill-opacity="0.15" />
    <rect x="10.3" y="4" width="3.2" height="16" rx="0.6" fill="currentColor" fill-opacity="0.15" />
    <rect x="14.7" y="4" width="3.2" height="16" rx="0.6" fill="currentColor" fill-opacity="0.15" />
    <rect x="19.1" y="4" width="3.2" height="16" rx="0.6" fill="currentColor" fill-opacity="0.15" />
</svg>`,
content: `
<section class="py-12 hubafrika-surface-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <div
                class="p-4 rounded-xl hubafrika-subcard min-h-[120px] flex flex-col justify-center text-center">
                <span class="text-[11px] font-bold uppercase tracking-wider hubafrika-accent-text mb-1">Col 1</span>
                <h4 class="text-xs font-bold mb-1">Col 1 (20%)</h4>
                <p class="text-[11px] opacity-75">Insert content</p>
            </div>
            <div
                class="p-4 rounded-xl hubafrika-subcard min-h-[120px] flex flex-col justify-center text-center">
                <span class="text-[11px] font-bold uppercase tracking-wider hubafrika-accent-text mb-1">Col 2</span>
                <h4 class="text-xs font-bold mb-1">Col 2 (20%)</h4>
                <p class="text-[11px] opacity-75">Insert content</p>
            </div>
            <div
                class="p-4 rounded-xl hubafrika-subcard min-h-[120px] flex flex-col justify-center text-center">
                <span class="text-[11px] font-bold uppercase tracking-wider hubafrika-accent-text mb-1">Col 3</span>
                <h4 class="text-xs font-bold mb-1">Col 3 (20%)</h4>
                <p class="text-[11px] opacity-75">Insert content</p>
            </div>
            <div
                class="p-4 rounded-xl hubafrika-subcard min-h-[120px] flex flex-col justify-center text-center">
                <span class="text-[11px] font-bold uppercase tracking-wider hubafrika-accent-text mb-1">Col 4</span>
                <h4 class="text-xs font-bold mb-1">Col 4 (20%)</h4>
                <p class="text-[11px] opacity-75">Insert content</p>
            </div>
            <div
                class="p-4 rounded-xl hubafrika-subcard min-h-[120px] flex flex-col justify-center text-center">
                <span class="text-[11px] font-bold uppercase tracking-wider hubafrika-accent-text mb-1">Col 5</span>
                <h4 class="text-xs font-bold mb-1">Col 5 (20%)</h4>
                <p class="text-[11px] opacity-75">Insert content</p>
            </div>
        </div>
    </div>
</section>`
});

// 6 Columns Grid
bm.add('grid-6-col', {
label: '6 Columns Grid',
category: gridCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.2">
    <rect x="1.5" y="4" width="2.6" height="16" rx="0.5" fill="currentColor" fill-opacity="0.15" />
    <rect x="5" y="4" width="2.6" height="16" rx="0.5" fill="currentColor" fill-opacity="0.15" />
    <rect x="8.5" y="4" width="2.6" height="16" rx="0.5" fill="currentColor" fill-opacity="0.15" />
    <rect x="12" y="4" width="2.6" height="16" rx="0.5" fill="currentColor" fill-opacity="0.15" />
    <rect x="15.5" y="4" width="2.6" height="16" rx="0.5" fill="currentColor" fill-opacity="0.15" />
    <rect x="19" y="4" width="2.6" height="16" rx="0.5" fill="currentColor" fill-opacity="0.15" />
</svg>`,
content: `
<section class="py-12 hubafrika-surface-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <div
                class="p-4 rounded-xl hubafrika-subcard min-h-[120px] flex flex-col justify-center text-center">
                <span class="text-[11px] font-bold uppercase tracking-wider hubafrika-accent-text mb-1">Col 1</span>
                <h4 class="text-xs font-bold mb-1">Col 1 (16.6%)</h4>
                <p class="text-[11px] opacity-75">Insert content</p>
            </div>
            <div
                class="p-4 rounded-xl hubafrika-subcard min-h-[120px] flex flex-col justify-center text-center">
                <span class="text-[11px] font-bold uppercase tracking-wider hubafrika-accent-text mb-1">Col 2</span>
                <h4 class="text-xs font-bold mb-1">Col 2 (16.6%)</h4>
                <p class="text-[11px] opacity-75">Insert content</p>
            </div>
            <div
                class="p-4 rounded-xl hubafrika-subcard min-h-[120px] flex flex-col justify-center text-center">
                <span class="text-[11px] font-bold uppercase tracking-wider hubafrika-accent-text mb-1">Col 3</span>
                <h4 class="text-xs font-bold mb-1">Col 3 (16.6%)</h4>
                <p class="text-[11px] opacity-75">Insert content</p>
            </div>
            <div
                class="p-4 rounded-xl hubafrika-subcard min-h-[120px] flex flex-col justify-center text-center">
                <span class="text-[11px] font-bold uppercase tracking-wider hubafrika-accent-text mb-1">Col 4</span>
                <h4 class="text-xs font-bold mb-1">Col 4 (16.6%)</h4>
                <p class="text-[11px] opacity-75">Insert content</p>
            </div>
            <div
                class="p-4 rounded-xl hubafrika-subcard min-h-[120px] flex flex-col justify-center text-center">
                <span class="text-[11px] font-bold uppercase tracking-wider hubafrika-accent-text mb-1">Col 5</span>
                <h4 class="text-xs font-bold mb-1">Col 5 (16.6%)</h4>
                <p class="text-[11px] opacity-75">Insert content</p>
            </div>
            <div
                class="p-4 rounded-xl hubafrika-subcard min-h-[120px] flex flex-col justify-center text-center">
                <span class="text-[11px] font-bold uppercase tracking-wider hubafrika-accent-text mb-1">Col 6</span>
                <h4 class="text-xs font-bold mb-1">Col 6 (16.6%)</h4>
                <p class="text-[11px] opacity-75">Insert content</p>
            </div>
        </div>
    </div>
</section>`
});

// =========================================================================
// 2. BASIC WIDGETS & MEDIA BLOCKS (HEADING, TEXT, IMAGE, VIDEO, LIST, ACCORDION, TOGGLE)
// =========================================================================
const widgetCategory = { id: 'basic-widgets', label: 'Basic Widgets & Media', open: true };

// 1. Heading Block
bm.add('widget-heading', {
label: 'Heading',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path d="M6 4v16M18 4v16M6 12h12" stroke-width="2.2" stroke-linecap="round" />
</svg>`,
content: `
<div class="my-4">
    <div
        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border hubafrika-accent-soft text-xs font-bold uppercase tracking-wider mb-2">
        <span>Featured Solutions</span>
    </div>
    <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight leading-tight">
        Clean Energy Solutions Built for Reliability
    </h2>
    <div class="h-1 w-12 hubafrika-accent-bar rounded-full mt-3"></div>
</div>`
});

// 2. Subheading Block
bm.add('widget-subheading', {
label: 'Subheading',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="2">
    <path d="M4 6v12M14 6v12M4 12h10M18 10v8M18 14h4" />
</svg>`,
content: `
<div class="my-3">
    <h3 class="text-lg sm:text-xl font-bold tracking-tight flex items-center gap-2">
        <span class="w-2 h-2 rounded-full hubafrika-accent-bar"></span>
        <span>Smart Inverters, Solar Arrays & Energy Storage</span>
    </h3>
    <p class="text-xs sm:text-sm opacity-75 mt-1">Engineered for seamless power transitions across homes, offices, and
        off-grid sites.</p>
</div>`
});

// 3. Text Block / Paragraph
bm.add('widget-text', {
label: 'Text Block',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <line x1="4" y1="6" x2="20" y2="6" stroke-width="2" stroke-linecap="round" />
    <line x1="4" y1="11" x2="20" y2="11" stroke-width="2" stroke-linecap="round" />
    <line x1="4" y1="16" x2="14" y2="16" stroke-width="2" stroke-linecap="round" />
</svg>`,
content: `
<div class="my-3 text-base leading-relaxed space-y-3 opacity-90">
    <p>
        Our certified energy engineers design and deliver Tier-1 solar modules, hybrid inverters, and lithium battery
        banks built for demanding conditions. Every component is tested for longevity, efficiency, and everyday
        dependability.
    </p>
    <p class="text-sm opacity-75">
        Click any word in this paragraph to edit, rephrase, or format your copy using the styling controls.
    </p>
</div>`
});

// 3. Blockquote
bm.add('widget-blockquote', {
label: 'Blockquote',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="currentColor">
    <path
        d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
</svg>`,
content: `
<blockquote class="my-5 p-6 sm:p-7 rounded-2xl hubafrika-subcard hubafrika-blockquote shadow-xs relative">
    <i class="fas fa-quote-left text-3xl hubafrika-accent-text opacity-45 mb-2 block"></i>
    <p class="text-base sm:text-lg italic font-medium leading-relaxed mb-4">
        "Reliable clean power is no longer a luxury—it is the backbone of modern productivity, resilience, and peace of
        mind."
    </p>
    <footer class="flex items-center gap-3 pt-3 border-t border-slate-200/60">
        <div
            class="w-10 h-10 rounded-full hubafrika-accent-soft border flex items-center justify-center font-bold text-xs uppercase shadow-xs">
            DO
        </div>
        <div>
            <cite class="not-italic font-bold text-sm block">David Okafor</cite>
            <span class="text-xs opacity-70 block">Lead Solar Systems Engineer, Hub Afrika</span>
        </div>
    </footer>
</blockquote>`
});

// 5. Button Block
bm.add('widget-button', {
label: 'Action Button',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.8">
    <rect x="3" y="6" width="18" height="12" rx="6" />
    <path d="M8 12h8M13 9l3 3-3 3" stroke-linecap="round" stroke-linejoin="round" />
</svg>`,
content: `
<div class="my-4 inline-block">
    <a href="/shop" class="hubafrika-btn-primary">
        <span>Shop Energy Systems</span>
        <i class="fas fa-arrow-right text-xs"></i>
    </a>
</div>`
});

// 6. Double Button (Dual Action Group)
bm.add('widget-double-button', {
label: 'Double Button',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.8">
    <rect x="2" y="7" width="9" height="10" rx="3" />
    <rect x="13" y="7" width="9" height="10" rx="3" />
</svg>`,
content: `
<div class="my-4 flex flex-wrap items-center gap-3.5">
    <a href="/shop" class="hubafrika-btn-primary">
        <i class="fas fa-bolt text-xs"></i>
        <span>Browse Catalog</span>
    </a>
    <a href="/installation" class="hubafrika-btn-outline">
        <i class="fas fa-screwdriver-wrench text-xs opacity-70"></i>
        <span>Book Installation</span>
    </a>
</div>`
});

// Divider Block
bm.add('widget-divider', {
label: 'Divider',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="2">
    <line x1="2" y1="12" x2="9" y2="12" />
    <circle cx="12" cy="12" r="2.5" fill="currentColor" />
    <line x1="15" y1="12" x2="22" y2="12" />
</svg>`,
content: `
<div class="my-6 py-2 flex items-center justify-center">
    <div class="w-full border-t border-slate-200"></div>
    <div class="px-4">
        <i class="fas fa-circle text-[8px] hubafrika-accent-text"></i>
    </div>
    <div class="w-full border-t border-slate-200"></div>
</div>`
});

// Spacer Block
bm.add('widget-spacer', {
label: 'Spacer',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="2">
    <path d="M12 3v18M8 6l4-3 4 3M8 18l4 3 4-3" />
</svg>`,
content: `
<div data-hubafrika-spacer="true" class="w-full h-12 sm:h-16 pointer-events-none" aria-hidden="true"></div>`
});

// Icon Block
bm.add('widget-icon', {
label: 'Icon',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="2">
    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
</svg>`,
content: `
<div class="my-4 inline-flex items-center justify-center">
    <div
        class="w-14 h-14 rounded-2xl hubafrika-accent-soft border flex items-center justify-center text-2xl shadow-xs transition-transform hover:scale-110">
        <i class="fas fa-solar-panel"></i>
    </div>
</div>`
});

// 7. Image Block
bm.add('widget-image', {
label: 'Image / Photo',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <rect x="3" y="3" width="18" height="18" rx="3" stroke-width="1.8" />
    <circle cx="8.5" cy="8.5" r="1.5" stroke-width="1.8" />
    <path d="M21 15l-5-5L5 21" stroke-width="1.8" />
</svg>`,
content: `
<div class="my-4 group">
    <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 shadow-md bg-slate-100">
        <img src="/images/storefront/carousel-renewable.jpg"
            alt="High-efficiency rooftop solar installation"
            class="w-full h-auto max-h-[500px] object-cover transition-transform duration-500 group-hover:scale-105" />
    </div>
    <p class="mt-2.5 text-xs text-center opacity-70 italic">Tier-1 solar arrays engineered for residential and commercial uptime</p>
</div>`
});

// Image Box
bm.add('widget-image-box', {
label: 'Image Box',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.8">
    <rect x="3" y="3" width="18" height="11" rx="2" />
    <line x1="3" y1="18" x2="21" y2="18" />
    <line x1="3" y1="21" x2="15" y2="21" />
</svg>`,
content: `
<div class="my-4 rounded-2xl hubafrika-card shadow-xs overflow-hidden transition-all hover:shadow-lg group">
    <div class="aspect-video w-full overflow-hidden bg-slate-100">
        <img src="/images/storefront/carousel-backup.jpg"
            alt="Hybrid inverter and lithium battery backup system"
            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
    </div>
    <div class="p-6">
        <h4 class="text-lg font-bold mb-2">Hybrid Inverters & Lithium Storage</h4>
        <p class="text-sm opacity-75 leading-relaxed mb-4">Instant 10ms switchover and deep-cycle LiFePO4 battery
            storage that keeps essential loads powered around the clock.</p>
        <a href="/shop" class="text-xs font-bold hubafrika-accent-text hover:underline inline-flex items-center gap-1.5">
            <span>View Backup Systems</span>
            <i class="fas fa-arrow-right text-[10px]"></i>
        </a>
    </div>
</div>`
});

// Icon Box
bm.add('widget-icon-box', {
label: 'Icon Box',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.8">
    <rect x="3" y="3" width="7" height="7" rx="1.5" />
    <line x1="14" y1="5" x2="21" y2="5" />
    <line x1="14" y1="9" x2="19" y2="9" />
    <line x1="3" y1="15" x2="21" y2="15" />
    <line x1="3" y1="19" x2="17" y2="19" />
</svg>`,
content: `
<div class="my-4 p-6 sm:p-7 rounded-2xl hubafrika-card shadow-xs hover:shadow-lg transition-all text-left">
    <div class="w-12 h-12 rounded-xl hubafrika-accent-soft border flex items-center justify-center text-xl mb-4 shadow-xs">
        <i class="fas fa-shield-halved"></i>
    </div>
    <h4 class="text-lg font-bold mb-2">Smart Protection & Telemetry</h4>
    <p class="text-sm opacity-75 leading-relaxed">
        Equipped with integrated surge protection, remote performance monitoring, and automated fault diagnostics for
        worry-free operation.
    </p>
</div>`
});

// Star Rating
bm.add('widget-star-rating', {
label: 'Star Rating',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="currentColor">
    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
</svg>`,
content: `
<div class="my-3 inline-flex items-center gap-2">
    <div class="flex items-center text-amber-400 text-base gap-0.5">
        <i class="fas fa-star"></i>
        <i class="fas fa-star"></i>
        <i class="fas fa-star"></i>
        <i class="fas fa-star"></i>
        <i class="fas fa-star-half-alt"></i>
    </div>
    <span class="text-xs font-bold">4.9 / 5.0</span>
    <span class="text-[11px] opacity-65">(420+ Verified Customers)</span>
</div>`
});

// Image Gallery
bm.add('widget-image-gallery', {
label: 'Image Gallery',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.8">
    <rect x="3" y="3" width="8" height="8" rx="1.5" />
    <rect x="13" y="3" width="8" height="8" rx="1.5" />
    <rect x="3" y="13" width="8" height="8" rx="1.5" />
    <rect x="13" y="13" width="8" height="8" rx="1.5" />
</svg>`,
content: `
<div class="my-6">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="aspect-square rounded-2xl overflow-hidden shadow-xs border border-slate-200 bg-slate-100 group">
            <img src="/images/storefront/carousel-renewable.jpg"
                alt="Solar Panel Installation"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
        </div>
        <div class="aspect-square rounded-2xl overflow-hidden shadow-xs border border-slate-200 bg-slate-100 group">
            <img src="/images/storefront/carousel-backup.jpg"
                alt="Hybrid Inverter & Battery Bank"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
        </div>
        <div class="aspect-square rounded-2xl overflow-hidden shadow-xs border border-slate-200 bg-slate-100 group">
            <img src="/images/storefront/carousel-security.jpg"
                alt="Smart Security & Monitoring System"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
        </div>
        <div class="aspect-square rounded-2xl overflow-hidden shadow-xs border border-slate-200 bg-slate-100 group">
            <img src="/images/storefront/carousel-renewable.jpg"
                alt="Commercial Clean Power Array"
                class="w-full h-full object-cover object-right transition-transform duration-500 group-hover:scale-110" />
        </div>
    </div>
</div>`
});

// Image Carousel
bm.add('widget-image-carousel', {
label: 'Image Carousel',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.8">
    <rect x="5" y="4" width="14" height="16" rx="2" />
    <path d="M1 8h2v8H1zM21 8h2v8h-2z" />
</svg>`,
content: `
<section data-hubafrika-carousel="true"
    class="hubafrika-carousel">

    <!-- Carousel Container -->
    <div class="hubafrika-carousel-inner">

        <!-- Slide 1: Solar & Renewable -->
        <div data-carousel-slide="0"
           
           
           
            class="hubafrika-carousel-slide is-active">
            <div class="hubafrika-carousel-content">
                <span class="hubafrika-carousel-badge">
                    Welcome to Hub Afrika
                </span>
                <h1 class="hubafrika-carousel-title">
                    Renewable energy for a <span class="accent">better tomorrow</span>
                </h1>
                <p class="hubafrika-carousel-desc">
                    Premium solar panels, hybrid systems, and expert engineering designed for African homes, farms, and businesses.
                </p>
                <div class="hubafrika-carousel-actions">
                    <a href="/shop" class="hubafrika-carousel-btn-primary">
                        Shop now
                    </a>
                    <a href="/installation" class="hubafrika-carousel-btn-outline">
                        Solar installation
                    </a>
                </div>
            </div>
            <div class="hubafrika-carousel-media">
                <div class="hubafrika-carousel-card">
                    <div class="hubafrika-carousel-img-wrap">
                        <img src="/images/storefront/carousel-renewable.jpg" alt="Solar panels on a rooftop at sunset" />
                    </div>
                    <div class="hubafrika-carousel-overlay">
                        <div>
                            <span style="display:block;font-weight:700;">Tier-1 Solar Modules</span>
                            <span style="opacity:0.8;font-size:11px;">25-Year Linear Warranty</span>
                        </div>
                        <span class="hubafrika-carousel-overlay-tag">Verified</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 2: Power Backup -->
        <div data-carousel-slide="1"
           
           
           
            class="hubafrika-carousel-slide">
            <div class="hubafrika-carousel-content">
                <span class="hubafrika-carousel-badge">
                    Power Backup &amp; Storage
                </span>
                <h1 class="hubafrika-carousel-title">
                    Lights on, even when the <span class="accent">grid is off</span>
                </h1>
                <p class="hubafrika-carousel-desc">
                    Reliable hybrid inverters and deep-cycle lithium batteries that keep your critical loads running 24/7 without interruption.
                </p>
                <div class="hubafrika-carousel-actions">
                    <a href="/shop" class="hubafrika-carousel-btn-primary">
                        Shop now
                    </a>
                    <a href="/maintenance" class="hubafrika-carousel-btn-outline">
                        Battery storage
                    </a>
                </div>
            </div>
            <div class="hubafrika-carousel-media">
                <div class="hubafrika-carousel-card">
                    <div class="hubafrika-carousel-img-wrap">
                        <img src="/images/storefront/carousel-backup.jpg" alt="Wall-mounted hybrid inverter and battery" />
                    </div>
                    <div class="hubafrika-carousel-overlay">
                        <div>
                            <span style="display:block;font-weight:700;">Hybrid Inverters &amp; Storage</span>
                            <span style="opacity:0.8;font-size:11px;">Instant 10ms Auto Transfer</span>
                        </div>
                        <span class="hubafrika-carousel-overlay-tag">Essential</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 3: Security & CCTV -->
        <div data-carousel-slide="2"
           
           
           
            class="hubafrika-carousel-slide">
            <div class="hubafrika-carousel-content">
                <span class="hubafrika-carousel-badge">
                    Smart Security &amp; Surveillance
                </span>
                <h1 class="hubafrika-carousel-title">
                    CCTV &amp; perimeter fencing <span class="accent">you can rely on</span>
                </h1>
                <p class="hubafrika-carousel-desc">
                    24/7 intelligent surveillance, electric fencing, and solar-powered cameras engineered for total security and remote monitoring.
                </p>
                <div class="hubafrika-carousel-actions">
                    <a href="/shop" class="hubafrika-carousel-btn-primary">
                        Shop now
                    </a>
                    <a href="/installation" class="hubafrika-carousel-btn-outline">
                        Security survey
                    </a>
                </div>
            </div>
            <div class="hubafrika-carousel-media">
                <div class="hubafrika-carousel-card">
                    <div class="hubafrika-carousel-img-wrap">
                        <img src="/images/storefront/carousel-security.jpg" alt="CCTV camera on a building wall" />
                    </div>
                    <div class="hubafrika-carousel-overlay">
                        <div>
                            <span style="display:block;font-weight:700;">Smart AI Surveillance</span>
                            <span style="opacity:0.8;font-size:11px;">Night Vision &amp; Remote View</span>
                        </div>
                        <span class="hubafrika-carousel-overlay-tag">24/7 Safe</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dots Indicators (Clickable) -->
        <div class="hubafrika-carousel-dots">
            <button type="button" data-carousel-dot="0" aria-label="Go to slide 1"
                class="hubafrika-carousel-dot is-active"></button>
            <button type="button" data-carousel-dot="1" aria-label="Go to slide 2"
                class="hubafrika-carousel-dot"></button>
            <button type="button" data-carousel-dot="2" aria-label="Go to slide 3"
                class="hubafrika-carousel-dot"></button>
        </div>
    </div>
</section>`
});

// 2. Video Block
bm.add('widget-video', {
label: 'Video Player',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <rect x="2" y="4" width="20" height="16" rx="3" stroke-width="1.8" />
    <polygon points="10 8 16 12 10 16 10 8" fill="currentColor" />
</svg>`,
content: `
<div class="my-4">
    <div
        class="relative w-full aspect-video rounded-2xl overflow-hidden shadow-xl border border-slate-200 bg-slate-900">
        <iframe class="w-full h-full border-0" src="https://www.youtube-nocookie.com/embed/ysz5S6PUM-U"
            title="Product Walkthrough Video"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen></iframe>
    </div>
    <p class="mt-2 text-xs text-center opacity-70">Replace the iframe src URL with your YouTube or Vimeo embed link</p>
</div>`
});

// 3. Feature / Icon List Block
bm.add('widget-list', {
label: 'Icon List',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <line x1="8" y1="6" x2="21" y2="6" stroke-width="2" />
    <line x1="8" y1="12" x2="21" y2="12" stroke-width="2" />
    <line x1="8" y1="18" x2="21" y2="18" stroke-width="2" />
    <circle cx="4" cy="6" r="1.5" fill="currentColor" />
    <circle cx="4" cy="12" r="1.5" fill="currentColor" />
    <circle cx="4" cy="18" r="1.5" fill="currentColor" />
</svg>`,
content: `
<div class="my-4 p-6 rounded-2xl hubafrika-card shadow-xs">
    <h4 class="text-base font-bold mb-4 flex items-center gap-2">
        <i class="fas fa-list-ul hubafrika-accent-text"></i>
        <span>Key Highlights & Benefits</span>
    </h4>
    <ul class="space-y-3">
        <li class="flex items-start gap-3 text-sm opacity-90">
            <span
                class="w-5 h-5 rounded-full hubafrika-accent-soft border flex items-center justify-center text-xs shrink-0 mt-0.5"><i
                    class="fas fa-check"></i></span>
            <span>Tier-1 monocrystalline solar panels backed by a 25-year performance warranty</span>
        </li>
        <li class="flex items-start gap-3 text-sm opacity-90">
            <span
                class="w-5 h-5 rounded-full hubafrika-accent-soft border flex items-center justify-center text-xs shrink-0 mt-0.5"><i
                    class="fas fa-check"></i></span>
            <span>Pure sine-wave hybrid inverters with instant 10ms automatic grid transfer</span>
        </li>
        <li class="flex items-start gap-3 text-sm opacity-90">
            <span
                class="w-5 h-5 rounded-full hubafrika-accent-soft border flex items-center justify-center text-xs shrink-0 mt-0.5"><i
                    class="fas fa-check"></i></span>
            <span>Deep-cycle LiFePO4 battery packs rated for over 6,000 charge cycles</span>
        </li>
        <li class="flex items-start gap-3 text-sm opacity-90">
            <span
                class="w-5 h-5 rounded-full hubafrika-accent-soft border flex items-center justify-center text-xs shrink-0 mt-0.5"><i
                    class="fas fa-check"></i></span>
            <span>Certified technician commissioning, surge protection, and ongoing support</span>
        </li>
    </ul>
</div>`
});

// Counter Block
bm.add('widget-counter', {
label: 'Counter',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.8">
    <rect x="3" y="4" width="18" height="16" rx="2" />
    <line x1="3" y1="12" x2="21" y2="12" />
    <line x1="9" y1="4" x2="9" y2="20" />
    <line x1="15" y1="4" x2="15" y2="20" />
</svg>`,
content: `
<div class="my-4 p-6 rounded-2xl hubafrika-card shadow-xs text-center">
    <div data-hubafrika-counter="true" data-target="1250" data-suffix="+"
        class="text-4xl sm:text-5xl font-black hubafrika-accent-text tracking-tight">1,250+</div>
    <div class="text-xs font-bold uppercase tracking-wider opacity-70 mt-1">Systems Installed</div>
    <p class="text-xs opacity-60 mt-2">Powering homes, clinics, and commercial facilities across the region.</p>
</div>`
});

// Progress Bar Block
bm.add('widget-progress-bar', {
label: 'Progress Bar',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="2">
    <rect x="2" y="8" width="20" height="8" rx="4" />
    <rect x="2" y="8" width="13" height="8" rx="4" fill="currentColor" />
</svg>`,
content: `
<div data-hubafrika-progress="true" data-value="85" class="my-4 space-y-2">
    <div class="flex items-center justify-between text-xs font-bold">
        <span>Clean Energy Readiness & Storage Autonomy</span>
        <span class="hubafrika-accent-text">85%</span>
    </div>
    <div class="w-full h-3 rounded-full hubafrika-subcard overflow-hidden">
        <div data-progress-bar="true" class="hubafrika-progress-fill w-[85%]"></div>
    </div>
</div>`
});

// Testimonial Block
bm.add('widget-testimonial', {
label: 'Testimonial',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.8">
    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
</svg>`,
content: `
<div class="my-5 p-7 rounded-3xl hubafrika-card shadow-md relative">
    <div class="flex items-center text-amber-400 text-sm gap-1 mb-4">
        <i class="fas fa-star"></i>
        <i class="fas fa-star"></i>
        <i class="fas fa-star"></i>
        <i class="fas fa-star"></i>
        <i class="fas fa-star"></i>
    </div>
    <p class="text-sm sm:text-base opacity-90 leading-relaxed italic mb-6">
        “Switching our facility to a Hub Afrika hybrid inverter and lithium battery bank eliminated power interruptions
        overnight and reduced monthly fuel bills dramatically.”
    </p>
    <div class="flex items-center gap-3.5 pt-4 border-t border-slate-200/60">
        <div
            class="w-11 h-11 rounded-full hubafrika-accent-soft border flex items-center justify-center font-bold text-xs uppercase shrink-0">
            GA
        </div>
        <div>
            <div class="font-bold text-sm">Grace Adebayo</div>
            <div class="text-xs opacity-65">Operations Director, Meridian Clinics</div>
        </div>
    </div>
</div>`
});

// Alert Box Block
bm.add('widget-alert', {
label: 'Alert Box',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.8">
    <circle cx="12" cy="12" r="10" />
    <line x1="12" y1="8" x2="12" y2="12" />
    <line x1="12" y1="16" x2="12.01" y2="16" />
</svg>`,
content: `
<div class="my-4 p-4 sm:p-5 rounded-2xl border hubafrika-accent-soft flex items-start gap-3.5 shadow-xs">
    <i class="fas fa-circle-info hubafrika-accent-text text-lg mt-0.5 shrink-0"></i>
    <div class="flex-1 text-xs sm:text-sm" style="color: var(--storefront-text, #1e293b);">
        <div class="font-bold mb-0.5 hubafrika-accent-text">Installation & Dispatch Notice</div>
        <p class="leading-relaxed opacity-85">
            Same-week dispatch for stocked inverters and complimentary system sizing consultations are available Monday
            through Saturday.
        </p>
    </div>
</div>`
});

// 4. Interactive Accordion Block
bm.add('widget-accordion', {
label: 'Interactive Accordion',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <rect x="3" y="4" width="18" height="6" rx="1.5" stroke-width="1.8" />
    <rect x="3" y="14" width="18" height="6" rx="1.5" stroke-width="1.8" />
    <path d="M17 7l-2 2-2-2" stroke-width="1.8" />
    <path d="M17 17l-2 2-2-2" stroke-width="1.8" />
</svg>`,
content: `
<div class="my-4 space-y-3">
    <details class="hubafrika-details p-5 rounded-2xl hubafrika-card shadow-xs transition-all" open>
        <summary class="flex items-center justify-between font-bold cursor-pointer list-none">
            <span class="flex items-center gap-2.5">
                <i class="fas fa-circle-question hubafrika-accent-text"></i>
                <span>What inverter and battery capacity do I need?</span>
            </span>
            <span class="text-xs opacity-60 hubafrika-chevron transition-transform"><i
                    class="fas fa-chevron-down"></i></span>
        </summary>
        <div class="mt-3 text-sm opacity-80 leading-relaxed border-t border-slate-200/60 pt-3">
            Share your essential appliances and daily backup hours with our team, and we will size an inverter and
            LiFePO4 battery bank tailored to your load profile.
        </div>
    </details>

    <details class="hubafrika-details p-5 rounded-2xl hubafrika-card shadow-xs transition-all">
        <summary class="flex items-center justify-between font-bold cursor-pointer list-none">
            <span class="flex items-center gap-2.5">
                <i class="fas fa-screwdriver-wrench hubafrika-accent-text"></i>
                <span>How long does certified installation take?</span>
            </span>
            <span class="text-xs opacity-60 hubafrika-chevron transition-transform"><i
                    class="fas fa-chevron-down"></i></span>
        </summary>
        <div class="mt-3 text-sm opacity-80 leading-relaxed border-t border-slate-200/60 pt-3">
            Most residential backup and rooftop solar packages are installed, tested, and handed over within 1 to 2
            business days following site assessment.
        </div>
    </details>

    <details class="hubafrika-details p-5 rounded-2xl hubafrika-card shadow-xs transition-all">
        <summary class="flex items-center justify-between font-bold cursor-pointer list-none">
            <span class="flex items-center gap-2.5">
                <i class="fas fa-shield-halved hubafrika-accent-text"></i>
                <span>What warranty and maintenance support are included?</span>
            </span>
            <span class="text-xs opacity-60 hubafrika-chevron transition-transform"><i
                    class="fas fa-chevron-down"></i></span>
        </summary>
        <div class="mt-3 text-sm opacity-80 leading-relaxed border-t border-slate-200/60 pt-3">
            Every system includes manufacturer hardware warranties, workmanship coverage, and scheduled preventive
            maintenance options across our service hubs.
        </div>
    </details>
</div>`
});

// 7. Toggle (Single Collapsible Box)
bm.add('widget-toggle', {
label: 'Toggle Box',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.8">
    <rect x="3" y="5" width="18" height="14" rx="3" />
    <path d="M8 12h8M12 8v8" />
</svg>`,
content: `
<div class="my-3">
    <details class="hubafrika-details p-5 rounded-2xl hubafrika-card shadow-xs transition-all">
        <summary class="flex items-center justify-between font-bold cursor-pointer list-none select-none">
            <span class="flex items-center gap-3">
                <span
                    class="hubafrika-toggle-badge w-7 h-7 rounded-lg hubafrika-accent-soft border flex items-center justify-center text-xs transition-colors">
                    <i class="fas fa-plus hubafrika-toggle-plus"></i>
                    <i class="fas fa-minus hubafrika-toggle-minus"></i>
                </span>
                <span>Technical Specifications & Warranty Checklist</span>
            </span>
            <span class="text-xs opacity-60 hubafrika-chevron transition-transform">
                <i class="fas fa-chevron-down"></i>
            </span>
        </summary>
        <div class="mt-4 text-sm opacity-80 leading-relaxed border-t border-slate-200/60 pt-4">
            <p>Include datasheet highlights, wiring prerequisites, or delivery guidance inside this collapsible box. It
                expands and collapses cleanly on every screen.</p>
        </div>
    </details>
</div>`
});

// 8. Tabs (Interactive Content Panels)
bm.add('widget-tabs', {
label: 'Tabs',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.8">
    <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" />
    <line x1="3" y1="11" x2="21" y2="11" />
</svg>`,
content: `
<div data-hubafrika-tabs="true" class="my-6 p-6 sm:p-7 rounded-3xl hubafrika-card shadow-xs">
    <!-- Tab Navigation Header -->
    <div role="tablist"
        class="flex items-center gap-2 p-1.5 rounded-2xl hubafrika-subcard max-w-fit mb-6 overflow-x-auto">
        <button type="button" role="tab" aria-selected="true" data-tab-btn="tab1"
            class="is-active px-5 py-2 rounded-xl text-xs sm:text-sm transition-all cursor-pointer flex items-center gap-2 shrink-0">
            <i class="fas fa-solar-panel text-xs"></i>
            <span>Solar Arrays</span>
        </button>

        <button type="button" role="tab" aria-selected="false" data-tab-btn="tab2"
            class="px-5 py-2 rounded-xl text-xs sm:text-sm transition-all cursor-pointer flex items-center gap-2 shrink-0">
            <i class="fas fa-battery-full text-xs"></i>
            <span>Backup Storage</span>
        </button>

        <button type="button" role="tab" aria-selected="false" data-tab-btn="tab3"
            class="px-5 py-2 rounded-xl text-xs sm:text-sm transition-all cursor-pointer flex items-center gap-2 shrink-0">
            <i class="fas fa-screwdriver-wrench text-xs"></i>
            <span>Installation</span>
        </button>
    </div>

    <!-- Tab Panels -->
    <div>
        <!-- Panel 1 -->
        <div role="tabpanel" data-tab-panel="tab1" class="is-active space-y-3">
            <h4 class="text-lg font-bold">High-Efficiency Solar Arrays</h4>
            <p class="text-sm opacity-80 leading-relaxed">
                Monocrystalline Tier-1 modules engineered for high yield under warm climates and variable cloud cover,
                delivering consistent daytime generation.
            </p>
            <div class="pt-2">
                <a href="/shop"
                    class="inline-flex items-center gap-2 text-xs font-bold hubafrika-accent-text hover:underline">
                    <span>Explore solar packages</span>
                    <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>

        <!-- Panel 2 -->
        <div role="tabpanel" data-tab-panel="tab2" class="space-y-3">
            <h4 class="text-lg font-bold">Intelligent Hybrid Storage</h4>
            <p class="text-sm opacity-80 leading-relaxed">
                Pair pure sine-wave inverters with scalable LiFePO4 battery banks for silent, fume-free backup across
                homes and commercial workloads.
            </p>
            <ul class="text-xs opacity-80 space-y-1.5 list-disc list-inside pt-1">
                <li>10ms automatic transfer switchover</li>
                <li>Modular 5kWh to 60kWh battery expansion</li>
                <li>Mobile and web telemetry monitoring</li>
            </ul>
        </div>

        <!-- Panel 3 -->
        <div role="tabpanel" data-tab-panel="tab3" class="space-y-3">
            <h4 class="text-lg font-bold">Turnkey Site Installation</h4>
            <p class="text-sm opacity-80 leading-relaxed">
                Our certified engineers handle load auditing, protection gear, mounting, and commissioning so your
                power system operates safely from day one.
            </p>
            <div class="pt-2">
                <a href="/installation" class="hubafrika-btn-primary text-xs px-4 py-2">
                    <span>Schedule a Site Assessment</span>
                    <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>
    </div>
</div>`
});

// Google Maps Block
bm.add('widget-google-maps', {
label: 'Google Maps',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.8">
    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
    <circle cx="12" cy="10" r="3" />
</svg>`,
content: `
<div
    class="my-4 w-full overflow-hidden border border-slate-200 hubafrika-map-container bg-slate-100" style="height: 450px;">
    <iframe class="w-full h-full border-0" height="450" style="height: 450px;"
        src="https://maps.google.com/maps?q=Pangani+Street,+Mjini+Kati,+Arusha,+Tanzania&t=&z=15&ie=UTF8&iwloc=&output=embed"
        loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
</div>`
});

// Social Share Block
bm.add('widget-social-share', {
label: 'Social Share',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.8">
    <circle cx="18" cy="5" r="3" />
    <circle cx="6" cy="12" r="3" />
    <circle cx="18" cy="19" r="3" />
    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" />
    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
</svg>`,
content: `
<div class="my-4">
    <div class="text-xs font-bold uppercase tracking-wider opacity-60 mb-2.5">Share this page</div>
    <div class="flex flex-wrap items-center gap-2">
        <a data-hubafrika-share="facebook" href="https://www.facebook.com/sharer/sharer.php" target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#1877F2] text-white text-xs font-bold shadow-xs hover:opacity-90 transition-opacity">
            <i class="fab fa-facebook-f text-xs"></i>
            <span>Facebook</span>
        </a>
        <a data-hubafrika-share="twitter" href="https://twitter.com/intent/tweet" target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-black text-white text-xs font-bold shadow-xs hover:opacity-90 transition-opacity">
            <i class="fab fa-x-twitter text-xs"></i>
            <span>X</span>
        </a>
        <a data-hubafrika-share="linkedin" href="https://www.linkedin.com/sharing/share-offsite/" target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#0A66C2] text-white text-xs font-bold shadow-xs hover:opacity-90 transition-opacity">
            <i class="fab fa-linkedin-in text-xs"></i>
            <span>LinkedIn</span>
        </a>
        <a data-hubafrika-share="whatsapp" href="https://wa.me/" target="_blank" rel="noopener noreferrer"
            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#25D366] text-white text-xs font-bold shadow-xs hover:opacity-90 transition-opacity">
            <i class="fab fa-whatsapp text-xs"></i>
            <span>WhatsApp</span>
        </a>
        <button type="button" data-hubafrika-copy-link="true"
            class="hubafrika-btn-outline inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold">
            <i class="fas fa-link text-xs opacity-75"></i>
            <span data-copy-label="true">Copy link</span>
        </button>
    </div>
</div>`
});

// Newsletter Subscription Block
bm.add('widget-newsletter', {
label: 'Newsletter Signup',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.8">
    <path d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
</svg>`,
content: `
<section class="my-8 p-8 sm:p-10 rounded-3xl bg-slate-900 text-white text-center shadow-xl max-w-3xl mx-auto">
    <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-800 text-lime-400 flex items-center justify-center text-2xl mb-4 shadow-sm">
        <i class="fas fa-paper-plane"></i>
    </div>
    <h3 class="text-2xl sm:text-3xl font-black mb-2 tracking-tight">Stay Powered Up & Informed</h3>
    <p class="text-xs sm:text-sm text-slate-300 mb-6 max-w-lg mx-auto leading-relaxed">
        Subscribe to HubAfrika updates for clean energy insights, commercial equipment launches, and exclusive member discounts.
    </p>
    <form action="/newsletter" method="POST" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
        <input type="email" name="email" placeholder="Enter your email address..." required
            class="flex-1 px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-slate-400 text-sm focus:outline-hidden focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400" />
        <button type="submit"
            class="px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm shadow-md transition-all cursor-pointer">
            Subscribe
        </button>
    </form>
</section>`
});

// Contact Form Block
bm.add('widget-contact-form', {
label: 'Contact Form',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.8">
    <rect x="2" y="4" width="20" height="16" rx="2" />
    <path d="M22 6l-10 7L2 6" />
    <line x1="6" y1="14" x2="14" y2="14" />
</svg>`,
content: `
<section class="py-12 hubafrika-surface-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="hubafrika-contact-grid">
            <!-- Left Info Column -->
            <div class="hubafrika-contact-info space-y-6">
                <div>
                    <h2 class="text-3xl font-extrabold text-emerald-800 tracking-tight">Get in touch</h2>
                    <p class="mt-3 text-sm text-slate-500 leading-relaxed">
                        We'd love to hear from you! Whether you have a question, suggestion, or need more information about our products and services, feel free to reach out to us.
                    </p>
                </div>

                <div class="space-y-5 pt-2">
                    <!-- Location -->
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-800 text-lime-400 flex items-center justify-center shrink-0 shadow-xs">
                            <i class="fas fa-map-marker-alt text-xl"></i>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-emerald-800">Location</h4>
                            <p class="text-xs sm:text-sm text-slate-500 mt-0.5 leading-relaxed">
                                Pangani Street/Mjini Kati Arusha<br>Arusha - Tanzania.
                            </p>
                        </div>
                    </div>

                    <!-- Email us -->
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-800 text-lime-400 flex items-center justify-center shrink-0 shadow-xs">
                            <i class="fas fa-envelope text-lg"></i>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-emerald-800">Email us</h4>
                            <p class="text-xs sm:text-sm text-slate-500 mt-0.5 space-y-0.5">
                                <a href="mailto:info@hubafrika.net" class="block hover:text-emerald-700 transition">info@hubafrika.net</a>
                                <a href="mailto:sales@hubafrika.net" class="block hover:text-emerald-700 transition">sales@hubafrika.net</a>
                            </p>
                        </div>
                    </div>

                    <!-- Call us -->
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-800 text-lime-400 flex items-center justify-center shrink-0 shadow-xs">
                            <i class="fas fa-phone-alt text-lg"></i>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-emerald-800">Call us</h4>
                            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                                <a href="tel:+255625003073" class="hover:text-emerald-700 transition font-medium">+255 625 003 073</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Form Column -->
            <div class="hubafrika-contact-card">
                <div class="mb-6">
                    <span
                        class="text-xs font-bold uppercase tracking-wider text-emerald-800 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">Contact Us</span>
                    <h3 class="text-2xl font-extrabold text-slate-900 mt-2">Send Us an Inquiry</h3>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Our clean energy specialists will respond within 24 hours.</p>
                </div>
                <form onsubmit="event.preventDefault(); alert('Inquiry received. Thank you!');" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Full Name</label>
                            <input type="text" placeholder="John Doe" required
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-hidden focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-slate-50/50" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Address</label>
                            <input type="email" placeholder="john@example.com" required
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-hidden focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-slate-50/50" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Subject / System of Interest</label>
                        <input type="text" placeholder="e.g. Solar Home System / Hybrid Inverters"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-hidden focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-slate-50/50" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Your Message</label>
                        <textarea rows="4" placeholder="How can we help you?" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-hidden focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-slate-50/50"></textarea>
                    </div>
                    <button type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl bg-emerald-800 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-900/20 transition-all cursor-pointer">
                        <span>Send Message</span>
                        <i class="fas fa-paper-plane text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>`
});

// Flip Box Block
bm.add('widget-flip-box', {
label: 'Flip Box',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.8">
    <rect x="4" y="4" width="16" height="16" rx="3" />
    <path d="M12 2v4M12 18v4M2 12h4M18 12h4" />
</svg>`,
content: `
<div data-hubafrika-flip="true" class="hubafrika-flip-scene my-6 w-full max-w-sm mx-auto h-[320px]">
    <div class="hubafrika-flip-card text-center rounded-3xl shadow-md">
        <!-- Front Side -->
        <div
            class="hubafrika-flip-face rounded-3xl hubafrika-card p-8 flex flex-col items-center justify-center">
            <div
                class="w-16 h-16 rounded-2xl hubafrika-accent-soft border flex items-center justify-center text-3xl mb-5 shadow-xs">
                <i class="fas fa-solar-panel"></i>
            </div>
            <h4 class="text-xl font-extrabold mb-2">Commercial Solar Kits</h4>
            <p class="text-xs opacity-70 max-w-xs leading-relaxed mb-4">Turnkey photovoltaic arrays, hybrid inverters,
                and monitoring built for rapid deployment.</p>
            <span
                class="inline-flex items-center gap-1.5 text-xs font-bold hubafrika-accent-soft px-3 py-1 rounded-full border">
                <span>Hover or Tap to Explore</span>
                <i class="fas fa-arrow-right text-[10px]"></i>
            </span>
        </div>

        <!-- Back Side -->
        <div
            class="hubafrika-flip-face hubafrika-flip-back rounded-3xl p-8 flex flex-col items-center justify-center">
            <h4 class="text-xl font-extrabold mb-3">What Is Included</h4>
            <p class="text-xs text-white/85 mb-6 leading-relaxed">
                High-efficiency solar panels, MPPT hybrid inverter, LiFePO4 battery bank, and certified commissioning.
            </p>
            <a href="/shop"
                class="px-6 py-2.5 rounded-xl bg-white text-slate-900 font-bold text-xs shadow-lg hover:bg-slate-100 transition-colors">
                Explore Solar Kits
            </a>
        </div>
    </div>
</div>`
});

// Animated Headline Block
bm.add('widget-animated-headline', {
label: 'Animated Headline',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.8">
    <path d="M12 2l2.5 5.5L20 8.5l-4 4 1 5.5-5-3-5 3 1-5.5-4-4 5.5-1z" />
    <line x1="4" y1="22" x2="20" y2="22" stroke-width="2" />
</svg>`,
content: `
<div class="my-6 text-center">
    <div
        class="inline-flex items-center gap-2 px-3 py-1 rounded-full hubafrika-accent-soft border text-xs font-bold uppercase tracking-wider mb-3">
        <i class="fas fa-bolt text-xs"></i>
        <span>Future Ready Power</span>
    </div>
    <h2 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight max-w-3xl mx-auto">
        Power Your Property with
        <span class="relative inline-block hubafrika-headline-highlight">
            Clean
            <svg class="absolute -bottom-2 left-0 w-full h-2.5 overflow-visible" viewBox="0 0 100 12" fill="none"
                preserveAspectRatio="none">
                <path class="hubafrika-headline-stroke" d="M2 9c25-6 60-6 96 1" stroke-width="3.5"
                    stroke-linecap="round" />
            </svg>
        </span>
        Energy.
    </h2>
    <p class="mt-4 text-sm sm:text-base opacity-70 max-w-xl mx-auto">
        Engineered solar generation, lithium storage, and smart protection tailored for homes and enterprises.
    </p>
</div>`
});

// Shape Divider Block
bm.add('widget-shape-divider', {
label: 'Shape Divider',
category: widgetCategory,
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.8">
    <path d="M2 16s4-8 10-8 10 8 10 8" />
    <line x1="2" y1="20" x2="22" y2="20" stroke-width="2" />
</svg>`,
content: `
<div class="w-full overflow-hidden leading-none relative my-3">
    <svg class="relative block w-full h-12 sm:h-20" style="color: var(--storefront-surface, #f1f5f9);"
        viewBox="0 0 1200 120" preserveAspectRatio="none">
        <path d="M0,0 C150,90 350,-40 500,45 C650,130 900,10 1200,50 L1200,120 L0,120 Z" fill="currentColor"></path>
    </svg>
</div>`
});

// =========================================================================
// 3. HUB AFRIKA STOREFRONT SECTIONS LIBRARY
// =========================================================================

// 1. Hero Banner (Modern Gradient)
bm.add('school-hero-gradient', {
label: 'Hero Banner (Gradient)',
category: 'Storefront Sections',
media: '<i class="fas fa-heading fa-2x"></i>',
content: `
<section class="py-24 hubafrika-hero-dark relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div
            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-white/90 text-xs font-semibold mb-6">
            <span class="w-2 h-2 rounded-full hubafrika-accent-bg"></span>
            <span>Clean Energy • Backup Storage • Smart Security</span>
        </div>
        <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight text-white max-w-4xl mx-auto leading-tight">
            Powering Every Day, <span class="hubafrika-accent-text">Without Interruption</span>
        </h1>
        <p class="mt-6 text-base sm:text-xl text-slate-300 max-w-2xl mx-auto leading-relaxed">
            From residential hybrid inverters to commercial solar arrays and site security, discover dependable hardware
            engineered for nonstop performance.
        </p>
        <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
            <a href="/shop" class="hubafrika-btn-primary px-8 py-3.5 text-sm">
                <span>Shop Energy Systems</span>
                <i class="fas fa-arrow-right text-xs"></i>
            </a>
            <a href="/installation"
                class="px-6 py-3.5 text-sm font-bold text-slate-100 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl transition-all">
                Book Installation
            </a>
        </div>
    </div>
</section>`
});

// 2. Looping Partner Logo Slider
bm.add('school-logo-slider', {
label: 'Logo Slider',
category: 'Storefront Sections',
media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.8">
    <rect x="2" y="6" width="20" height="12" rx="2.5" />
    <circle cx="7" cy="12" r="2" />
    <circle cx="12" cy="12" r="2" />
    <circle cx="17" cy="12" r="2" />
    <path d="M2 10h20M2 14h20" stroke-width="1" stroke-dasharray="2 2" />
</svg>`,
content: `
<section class="py-16 hubafrika-surface-section border-y border-slate-200/60 overflow-hidden relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center mb-10">
        <span
            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full hubafrika-accent-soft border text-xs font-bold uppercase tracking-wider mb-2">
            <i class="fas fa-award text-xs"></i>
            <span>Trusted Brands & Partners</span>
        </span>
        <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
            Built Around Proven Power & Security Engineering
        </h3>
        <p class="text-xs sm:text-sm opacity-70 mt-2">
            Every system pairs internationally certified power electronics with tested safety hardware.
        </p>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="hubafrika-logo-slider relative" data-hubafrika-logo-slider="true"
            data-slides-visible="6" data-slide-step="1" data-slide-interval="3500" data-slide-loop="true"
            style="--hubafrika-logo-visible: 6; --hubafrika-logo-cols: 2; --hubafrika-logo-cols-sm: 4; --hubafrika-logo-cols-lg: 6;">
            <div class="hubafrika-logo-slider-viewport overflow-hidden">
                <div class="hubafrika-marquee-track hubafrika-logo-slider-track py-2">
                    <div class="hubafrika-logo-slide">
                        <a href="" target="_blank" rel="noopener noreferrer" data-logo-slide="true"
                            class="hubafrika-logo-slide-card">
                            <img src="/images/storefront/carousel-renewable.jpg" alt="Partner Logo 1" />
                        </a>
                    </div>
                    <div class="hubafrika-logo-slide">
                        <a href="" target="_blank" rel="noopener noreferrer" data-logo-slide="true"
                            class="hubafrika-logo-slide-card">
                            <img src="/images/storefront/carousel-backup.jpg" alt="Partner Logo 2" />
                        </a>
                    </div>
                    <div class="hubafrika-logo-slide">
                        <a href="" target="_blank" rel="noopener noreferrer" data-logo-slide="true"
                            class="hubafrika-logo-slide-card">
                            <img src="/images/storefront/carousel-security.jpg" alt="Partner Logo 3" />
                        </a>
                    </div>
                    <div class="hubafrika-logo-slide">
                        <a href="" target="_blank" rel="noopener noreferrer" data-logo-slide="true"
                            class="hubafrika-logo-slide-card">
                            <img src="/images/storefront/carousel-renewable.jpg" alt="Partner Logo 4" />
                        </a>
                    </div>
                    <div class="hubafrika-logo-slide">
                        <a href="" target="_blank" rel="noopener noreferrer" data-logo-slide="true"
                            class="hubafrika-logo-slide-card">
                            <img src="/images/storefront/carousel-backup.jpg" alt="Partner Logo 5" />
                        </a>
                    </div>
                    <div class="hubafrika-logo-slide">
                        <a href="" target="_blank" rel="noopener noreferrer" data-logo-slide="true"
                            class="hubafrika-logo-slide-card">
                            <img src="/images/storefront/carousel-security.jpg" alt="Partner Logo 6" />
                        </a>
                    </div>
                    <div class="hubafrika-logo-slide">
                        <a href="" target="_blank" rel="noopener noreferrer" data-logo-slide="true"
                            class="hubafrika-logo-slide-card">
                            <img src="/images/storefront/carousel-renewable.jpg" alt="Partner Logo 7" />
                        </a>
                    </div>
                    <div class="hubafrika-logo-slide">
                        <a href="" target="_blank" rel="noopener noreferrer" data-logo-slide="true"
                            class="hubafrika-logo-slide-card">
                            <img src="/images/storefront/carousel-backup.jpg" alt="Partner Logo 8" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>`
});

// 3. Features 3-Column Grid
bm.add('school-features-grid', {
label: '3-Column Features',
category: 'Storefront Sections',
media: '<i class="fas fa-th-large fa-2x"></i>',
content: `
<section class="py-20 hubafrika-surface-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <span class="text-xs font-bold uppercase tracking-wider hubafrika-accent-text">Why Choose Hub Afrika</span>
            <h2 class="text-3xl font-extrabold mt-2">Engineered for Enduring Energy Independence</h2>
            <p class="text-sm opacity-70 mt-3">We pair proven solar and backup hardware with certified installation and
                responsive local support.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="p-8 rounded-2xl hubafrika-subcard hover:shadow-xl transition-all">
                <div class="w-12 h-12 rounded-xl hubafrika-accent-soft border flex items-center justify-center text-xl mb-5">
                    <i class="fas fa-solar-panel"></i>
                </div>
                <h3 class="text-lg font-bold mb-2">Hybrid Solar Generation</h3>
                <p class="text-sm opacity-75 leading-relaxed">High-yield photovoltaic arrays sized for rooftops,
                    commercial facilities, and off-grid installations.</p>
            </div>
            <div class="p-8 rounded-2xl hubafrika-subcard hover:shadow-xl transition-all">
                <div class="w-12 h-12 rounded-xl hubafrika-accent-soft border flex items-center justify-center text-xl mb-5">
                    <i class="fas fa-battery-full"></i>
                </div>
                <h3 class="text-lg font-bold mb-2">Automatic Power Backup</h3>
                <p class="text-sm opacity-75 leading-relaxed">Pure sine-wave hybrid inverters and deep-cycle lithium
                    storage keep critical loads online 24/7.</p>
            </div>
            <div class="p-8 rounded-2xl hubafrika-subcard hover:shadow-xl transition-all">
                <div class="w-12 h-12 rounded-xl hubafrika-accent-soft border flex items-center justify-center text-xl mb-5">
                    <i class="fas fa-screwdriver-wrench"></i>
                </div>
                <h3 class="text-lg font-bold mb-2">Certified Installation</h3>
                <p class="text-sm opacity-75 leading-relaxed">End-to-end site surveys, protective switchgear, clean
                    cabling, and scheduled maintenance contracts.</p>
            </div>
        </div>
    </div>
</section>`
});

// 4. Impact Statistics Counter
bm.add('school-stats-counter', {
label: 'Statistics Counter',
category: 'Storefront Sections',
media: '<i class="fas fa-chart-line fa-2x"></i>',
content: `
<section class="py-16 hubafrika-stats-dark">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <div class="p-6 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/15">
                <div data-hubafrika-counter="true" data-target="99" data-suffix="%"
                    class="text-4xl font-extrabold">99%</div>
                <div class="text-xs text-white/80 mt-1 uppercase tracking-wider font-semibold">Uptime Reliability</div>
            </div>
            <div class="p-6 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/15">
                <div data-hubafrika-counter="true" data-target="50" data-suffix="+"
                    class="text-4xl font-extrabold">50+</div>
                <div class="text-xs text-white/80 mt-1 uppercase tracking-wider font-semibold">Certified Installers</div>
            </div>
            <div class="p-6 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/15">
                <div data-hubafrika-counter="true" data-target="25" data-suffix=" Yr"
                    class="text-4xl font-extrabold">25 Yr</div>
                <div class="text-xs text-white/80 mt-1 uppercase tracking-wider font-semibold">Panel Warranty</div>
            </div>
            <div class="p-6 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/15">
                <div data-hubafrika-counter="true" data-target="1200" data-suffix="+"
                    class="text-4xl font-extrabold">1,200+</div>
                <div class="text-xs text-white/80 mt-1 uppercase tracking-wider font-semibold">Systems Powered</div>
            </div>
        </div>
    </div>
</section>`
});

// 6. FAQ Accordion Section
bm.add('school-faq-accordion', {
label: 'FAQ Accordion',
category: 'Storefront Sections',
media: '<i class="fas fa-question-circle fa-2x"></i>',
content: `
<section class="py-20 hubafrika-muted-section">
    <div class="max-w-3xl mx-auto px-4">
        <div class="text-center mb-12">
            <span class="text-xs font-bold uppercase tracking-wider hubafrika-accent-text">Got Questions?</span>
            <h2 class="text-3xl font-extrabold mt-2">Frequently Asked Questions</h2>
        </div>
        <div class="space-y-4">
            <details class="hubafrika-details p-5 rounded-2xl hubafrika-card shadow-xs transition-all">
                <summary class="flex items-center justify-between font-bold cursor-pointer list-none">
                    <span>How do I size the right inverter and battery system?</span>
                    <span class="text-xs opacity-60 hubafrika-chevron transition-transform"><i
                            class="fas fa-chevron-down"></i></span>
                </summary>
                <p class="mt-3 text-sm opacity-80 leading-relaxed border-t border-slate-200/60 pt-3">
                    Contact our sizing specialists with your critical appliance wattage and desired autonomy hours, and
                    we will recommend a balanced package with headroom for future loads.
                </p>
            </details>
            <details class="hubafrika-details p-5 rounded-2xl hubafrika-card shadow-xs transition-all">
                <summary class="flex items-center justify-between font-bold cursor-pointer list-none">
                    <span>Do you offer nationwide delivery and certified installation?</span>
                    <span class="text-xs opacity-60 hubafrika-chevron transition-transform"><i
                            class="fas fa-chevron-down"></i></span>
                </summary>
                <p class="mt-3 text-sm opacity-80 leading-relaxed border-t border-slate-200/60 pt-3">
                    Yes, stocked orders ship rapidly, and our field engineers provide turnkey mounting, protection
                    wiring, commissioning, and ongoing maintenance nationwide.
                </p>
            </details>
        </div>
    </div>
</section>`
});

// 7. Two-Column Split Media Section
bm.add('school-split-section', {
label: 'Split Media & Text',
category: 'Storefront Sections',
media: '<i class="fas fa-columns fa-2x"></i>',
content: `
<section class="py-20 hubafrika-surface-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider hubafrika-accent-text">Engineered for Africa</span>
                <h2 class="text-3xl font-extrabold mt-2 mb-4 leading-tight">Dependable Clean Power Without Compromise
                </h2>
                <p class="text-sm opacity-80 leading-relaxed mb-4">
                    At Hub Afrika, we believe resilient energy and property security should be simple to specify, fast to
                    deploy, and backed by transparent warranties.
                </p>
                <p class="text-sm opacity-80 leading-relaxed mb-6">
                    Explore ready-to-ship solar kits, hybrid storage systems, and enterprise maintenance plans built
                    around your daily operations.
                </p>
                <a href="/about-us" class="hubafrika-btn-primary text-sm">
                    <span>Learn More About Us</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="rounded-3xl overflow-hidden shadow-2xl border border-slate-200/60 aspect-video bg-slate-900">
                <img src="/images/storefront/carousel-backup.jpg"
                    alt="Hub Afrika hybrid inverter and battery storage wall mount"
                    class="w-full h-full object-cover" />
            </div>
        </div>
    </div>
</section>`
});

// 8. Contact & Inquiry Section (Polished Two-Column Layout)
bm.add('hubafrika-contact-section', {
label: 'Contact & Inquiry Section',
category: 'Storefront Sections',
media: '<i class="fas fa-address-book fa-2x"></i>',
content: `
<section class="py-16 hubafrika-surface-section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="hubafrika-contact-grid">
            <!-- Left Info Column -->
            <div class="hubafrika-contact-info space-y-6">
                <div>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-emerald-800 tracking-tight">Get in touch</h2>
                    <p class="mt-3 text-sm text-slate-500 leading-relaxed">
                        We'd love to hear from you! Whether you have a question, suggestion, or need more information about our products and services, feel free to reach out to us.
                    </p>
                </div>

                <div class="space-y-6 pt-2">
                    <!-- Location -->
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-800 text-lime-400 flex items-center justify-center shrink-0 shadow-xs">
                            <i class="fas fa-map-marker-alt text-xl"></i>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-emerald-800">Location</h4>
                            <p class="text-xs sm:text-sm text-slate-500 mt-0.5 leading-relaxed">
                                Pangani Street/Mjini Kati Arusha<br>Arusha - Tanzania.
                            </p>
                        </div>
                    </div>

                    <!-- Email us -->
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-800 text-lime-400 flex items-center justify-center shrink-0 shadow-xs">
                            <i class="fas fa-envelope text-lg"></i>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-emerald-800">Email us</h4>
                            <p class="text-xs sm:text-sm text-slate-500 mt-0.5 space-y-0.5">
                                <a href="mailto:info@hubafrika.net" class="block hover:text-emerald-700 transition">info@hubafrika.net</a>
                                <a href="mailto:sales@hubafrika.net" class="block hover:text-emerald-700 transition">sales@hubafrika.net</a>
                            </p>
                        </div>
                    </div>

                    <!-- Call us -->
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-800 text-lime-400 flex items-center justify-center shrink-0 shadow-xs">
                            <i class="fas fa-phone-alt text-lg"></i>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-emerald-800">Call us</h4>
                            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                                <a href="tel:+255625003073" class="hover:text-emerald-700 transition font-medium">+255 625 003 073</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Form Column -->
            <div class="hubafrika-contact-card">
                <div class="mb-6">
                    <span
                        class="text-xs font-bold uppercase tracking-wider text-emerald-800 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">Contact Us</span>
                    <h3 class="text-2xl font-extrabold text-slate-900 mt-2">Send Us an Inquiry</h3>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Our clean energy specialists will respond within 24 hours.</p>
                </div>
                <form onsubmit="event.preventDefault(); alert('Inquiry received. Thank you!');" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Full Name</label>
                            <input type="text" placeholder="John Doe" required
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-hidden focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-slate-50/50" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Address</label>
                            <input type="email" placeholder="john@example.com" required
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-hidden focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-slate-50/50" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Subject / System of Interest</label>
                        <input type="text" placeholder="e.g. Solar Home System / Hybrid Inverters"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-hidden focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-slate-50/50" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Your Message</label>
                        <textarea rows="4" placeholder="How can we help you?" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-hidden focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-slate-50/50"></textarea>
                    </div>
                    <button type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl bg-emerald-800 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-900/20 transition-all cursor-pointer">
                        <span>Send Message</span>
                        <i class="fas fa-paper-plane text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>`
});

// =========================================================================