            // 1. CUSTOM TAILWIND GRID LAYOUT BLOCKS (1, 2, 3, 4, 5, 6 COLUMNS)
            // =========================================================================
            const bm = editor.BlockManager;
            const gridCategory = { id: 'grid-layouts', label: 'Grid Layouts', open: true };

            // 1 Column Grid
            bm.add('grid-1-col', {
                label: '1 Column Grid',
                category: gridCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="2" fill="currentColor" fill-opacity="0.15"/></svg>`,
                content: `
                <section class="py-12 bg-white text-slate-800">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="grid grid-cols-1 gap-6">
                            <div class="p-8 rounded-2xl bg-slate-50 border border-slate-200/80 min-h-[140px] flex flex-col justify-center items-center text-center">
                                <span class="text-xs font-bold uppercase tracking-wider text-sky-600 mb-2">Column 1 of 1</span>
                                <h3 class="text-xl font-bold text-slate-800 mb-2">Full Width Container</h3>
                                <p class="text-sm text-slate-500 max-w-xl">Drop widgets, text, cards, or media here. Customize padding, colors, and borders freely.</p>
                            </div>
                        </div>
                    </div>
                </section>`
            });

            // 2 Columns Grid
            bm.add('grid-2-col', {
                label: '2 Columns Grid',
                category: gridCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="8.2" height="16" rx="1.5" fill="currentColor" fill-opacity="0.15"/><rect x="12.8" y="4" width="8.2" height="16" rx="1.5" fill="currentColor" fill-opacity="0.15"/></svg>`,
                content: `
                <section class="py-12 bg-white text-slate-800">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 min-h-[140px] flex flex-col justify-center">
                                <span class="text-xs font-bold uppercase tracking-wider text-sky-600 mb-2">Column 1 (50%)</span>
                                <h4 class="text-lg font-bold text-slate-800 mb-2">Left Column</h4>
                                <p class="text-sm text-slate-500">Insert your text, cards, media, or widgets here.</p>
                            </div>
                            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 min-h-[140px] flex flex-col justify-center">
                                <span class="text-xs font-bold uppercase tracking-wider text-sky-600 mb-2">Column 2 (50%)</span>
                                <h4 class="text-lg font-bold text-slate-800 mb-2">Right Column</h4>
                                <p class="text-sm text-slate-500">Insert your text, cards, media, or widgets here.</p>
                            </div>
                        </div>
                    </div>
                </section>`
            });

            // 3 Columns Grid
            bm.add('grid-3-col', {
                label: '3 Columns Grid',
                category: gridCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2.5" y="4" width="5.5" height="16" rx="1" fill="currentColor" fill-opacity="0.15"/><rect x="9.25" y="4" width="5.5" height="16" rx="1" fill="currentColor" fill-opacity="0.15"/><rect x="16" y="4" width="5.5" height="16" rx="1" fill="currentColor" fill-opacity="0.15"/></svg>`,
                content: `
                <section class="py-12 bg-white text-slate-800">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 min-h-[140px] flex flex-col justify-center">
                                <span class="text-xs font-bold uppercase tracking-wider text-sky-600 mb-2">Column 1 (33.3%)</span>
                                <h4 class="text-base font-bold text-slate-800 mb-1">Column 1</h4>
                                <p class="text-xs text-slate-500">Insert your content or widgets here.</p>
                            </div>
                            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 min-h-[140px] flex flex-col justify-center">
                                <span class="text-xs font-bold uppercase tracking-wider text-sky-600 mb-2">Column 2 (33.3%)</span>
                                <h4 class="text-base font-bold text-slate-800 mb-1">Column 2</h4>
                                <p class="text-xs text-slate-500">Insert your content or widgets here.</p>
                            </div>
                            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80 min-h-[140px] flex flex-col justify-center">
                                <span class="text-xs font-bold uppercase tracking-wider text-sky-600 mb-2">Column 3 (33.3%)</span>
                                <h4 class="text-base font-bold text-slate-800 mb-1">Column 3</h4>
                                <p class="text-xs text-slate-500">Insert your content or widgets here.</p>
                            </div>
                        </div>
                    </div>
                </section>`
            });

            // 4 Columns Grid
            bm.add('grid-4-col', {
                label: '4 Columns Grid',
                category: gridCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="2" y="4" width="4" height="16" rx="0.8" fill="currentColor" fill-opacity="0.15"/><rect x="7.33" y="4" width="4" height="16" rx="0.8" fill="currentColor" fill-opacity="0.15"/><rect x="12.66" y="4" width="4" height="16" rx="0.8" fill="currentColor" fill-opacity="0.15"/><rect x="18" y="4" width="4" height="16" rx="0.8" fill="currentColor" fill-opacity="0.15"/></svg>`,
                content: `
                <section class="py-12 bg-white text-slate-800">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 min-h-[130px] flex flex-col justify-center">
                                <span class="text-xs font-bold uppercase tracking-wider text-sky-600 mb-1">Col 1</span>
                                <h4 class="text-sm font-bold text-slate-800 mb-1">Column 1 (25%)</h4>
                                <p class="text-xs text-slate-500">Insert content here.</p>
                            </div>
                            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 min-h-[130px] flex flex-col justify-center">
                                <span class="text-xs font-bold uppercase tracking-wider text-sky-600 mb-1">Col 2</span>
                                <h4 class="text-sm font-bold text-slate-800 mb-1">Column 2 (25%)</h4>
                                <p class="text-xs text-slate-500">Insert content here.</p>
                            </div>
                            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 min-h-[130px] flex flex-col justify-center">
                                <span class="text-xs font-bold uppercase tracking-wider text-sky-600 mb-1">Col 3</span>
                                <h4 class="text-sm font-bold text-slate-800 mb-1">Column 3 (25%)</h4>
                                <p class="text-xs text-slate-500">Insert content here.</p>
                            </div>
                            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 min-h-[130px] flex flex-col justify-center">
                                <span class="text-xs font-bold uppercase tracking-wider text-sky-600 mb-1">Col 4</span>
                                <h4 class="text-sm font-bold text-slate-800 mb-1">Column 4 (25%)</h4>
                                <p class="text-xs text-slate-500">Insert content here.</p>
                            </div>
                        </div>
                    </div>
                </section>`
            });

            // 5 Columns Grid
            bm.add('grid-5-col', {
                label: '5 Columns Grid',
                category: gridCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="1.5" y="4" width="3.2" height="16" rx="0.6" fill="currentColor" fill-opacity="0.15"/><rect x="5.9" y="4" width="3.2" height="16" rx="0.6" fill="currentColor" fill-opacity="0.15"/><rect x="10.3" y="4" width="3.2" height="16" rx="0.6" fill="currentColor" fill-opacity="0.15"/><rect x="14.7" y="4" width="3.2" height="16" rx="0.6" fill="currentColor" fill-opacity="0.15"/><rect x="19.1" y="4" width="3.2" height="16" rx="0.6" fill="currentColor" fill-opacity="0.15"/></svg>`,
                content: `
                <section class="py-12 bg-white text-slate-800">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 min-h-[120px] flex flex-col justify-center text-center">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-sky-600 mb-1">Col 1</span>
                                <h4 class="text-xs font-bold text-slate-800 mb-1">Col 1 (20%)</h4>
                                <p class="text-[11px] text-slate-500">Insert content</p>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 min-h-[120px] flex flex-col justify-center text-center">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-sky-600 mb-1">Col 2</span>
                                <h4 class="text-xs font-bold text-slate-800 mb-1">Col 2 (20%)</h4>
                                <p class="text-[11px] text-slate-500">Insert content</p>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 min-h-[120px] flex flex-col justify-center text-center">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-sky-600 mb-1">Col 3</span>
                                <h4 class="text-xs font-bold text-slate-800 mb-1">Col 3 (20%)</h4>
                                <p class="text-[11px] text-slate-500">Insert content</p>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 min-h-[120px] flex flex-col justify-center text-center">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-sky-600 mb-1">Col 4</span>
                                <h4 class="text-xs font-bold text-slate-800 mb-1">Col 4 (20%)</h4>
                                <p class="text-[11px] text-slate-500">Insert content</p>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 min-h-[120px] flex flex-col justify-center text-center">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-sky-600 mb-1">Col 5</span>
                                <h4 class="text-xs font-bold text-slate-800 mb-1">Col 5 (20%)</h4>
                                <p class="text-[11px] text-slate-500">Insert content</p>
                            </div>
                        </div>
                    </div>
                </section>`
            });

            // 6 Columns Grid
            bm.add('grid-6-col', {
                label: '6 Columns Grid',
                category: gridCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="1.5" y="4" width="2.6" height="16" rx="0.5" fill="currentColor" fill-opacity="0.15"/><rect x="5" y="4" width="2.6" height="16" rx="0.5" fill="currentColor" fill-opacity="0.15"/><rect x="8.5" y="4" width="2.6" height="16" rx="0.5" fill="currentColor" fill-opacity="0.15"/><rect x="12" y="4" width="2.6" height="16" rx="0.5" fill="currentColor" fill-opacity="0.15"/><rect x="15.5" y="4" width="2.6" height="16" rx="0.5" fill="currentColor" fill-opacity="0.15"/><rect x="19" y="4" width="2.6" height="16" rx="0.5" fill="currentColor" fill-opacity="0.15"/></svg>`,
                content: `
                <section class="py-12 bg-white text-slate-800">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 min-h-[120px] flex flex-col justify-center text-center">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-sky-600 mb-1">Col 1</span>
                                <h4 class="text-xs font-bold text-slate-800 mb-1">Col 1 (16.6%)</h4>
                                <p class="text-[11px] text-slate-500">Insert content</p>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 min-h-[120px] flex flex-col justify-center text-center">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-sky-600 mb-1">Col 2</span>
                                <h4 class="text-xs font-bold text-slate-800 mb-1">Col 2 (16.6%)</h4>
                                <p class="text-[11px] text-slate-500">Insert content</p>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 min-h-[120px] flex flex-col justify-center text-center">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-sky-600 mb-1">Col 3</span>
                                <h4 class="text-xs font-bold text-slate-800 mb-1">Col 3 (16.6%)</h4>
                                <p class="text-[11px] text-slate-500">Insert content</p>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 min-h-[120px] flex flex-col justify-center text-center">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-sky-600 mb-1">Col 4</span>
                                <h4 class="text-xs font-bold text-slate-800 mb-1">Col 4 (16.6%)</h4>
                                <p class="text-[11px] text-slate-500">Insert content</p>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 min-h-[120px] flex flex-col justify-center text-center">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-sky-600 mb-1">Col 5</span>
                                <h4 class="text-xs font-bold text-slate-800 mb-1">Col 5 (16.6%)</h4>
                                <p class="text-[11px] text-slate-500">Insert content</p>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 min-h-[120px] flex flex-col justify-center text-center">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-sky-600 mb-1">Col 6</span>
                                <h4 class="text-xs font-bold text-slate-800 mb-1">Col 6 (16.6%)</h4>
                                <p class="text-[11px] text-slate-500">Insert content</p>
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
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 4v16M18 4v16M6 12h12" stroke-width="2.2" stroke-linecap="round"/></svg>`,
                content: `
                <div class="my-4">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-sky-50 text-sky-600 border border-sky-100 text-xs font-bold uppercase tracking-wider mb-2">
                        <span>Section Eyebrow</span>
                    </div>
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                        Crafting Tomorrow's Leaders Through Knowledge
                    </h2>
                    <div class="h-1 w-12 bg-sky-500 rounded-full mt-3"></div>
                </div>`
            });

            // 2. Subheading Block
            bm.add('widget-subheading', {
                label: 'Subheading',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6v12M14 6v12M4 12h10M18 10v8M18 14h4"/></svg>`,
                content: `
                <div class="my-3">
                    <h3 class="text-lg sm:text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                        <span>Inspiring Modern Education & Lifelong Learning</span>
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">A supportive secondary title to elaborate on your primary section heading.</p>
                </div>`
            });

            // 3. Text Block / Paragraph
            bm.add('widget-text', {
                label: 'Text Block',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><line x1="4" y1="6" x2="20" y2="6" stroke-width="2" stroke-linecap="round"/><line x1="4" y1="11" x2="20" y2="11" stroke-width="2" stroke-linecap="round"/><line x1="4" y1="16" x2="14" y2="16" stroke-width="2" stroke-linecap="round"/></svg>`,
                content: `
                <div class="my-3 text-slate-600 text-base leading-relaxed space-y-3">
                    <p>
                        Our academic programs provide a balanced education integrating science, technology, arts, and moral values. We prepare learners to thrive in modern society with critical thinking and genuine compassion.
                    </p>
                    <p class="text-sm text-slate-500">
                        Click any word in this paragraph to edit, rephrase, or format your text using the styling controls.
                    </p>
                </div>`
            });

            // 3. Blockquote
            bm.add('widget-blockquote', {
                label: 'Blockquote',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="currentColor"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>`,
                content: `
                <blockquote class="my-5 p-6 sm:p-7 rounded-2xl bg-slate-50 border-l-4 border-sky-500 border border-slate-200/80 shadow-xs relative">
                    <i class="fas fa-quote-left text-3xl text-sky-300/80 mb-2 block"></i>
                    <p class="text-base sm:text-lg italic font-medium text-slate-800 leading-relaxed mb-4">
                        "Education is not the learning of facts, but the training of the mind to think and innovate."
                    </p>
                    <footer class="flex items-center gap-3 pt-3 border-t border-slate-200/60">
                        <div class="w-10 h-10 rounded-full bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-xs uppercase shadow-xs">
                            AE
                        </div>
                        <div>
                            <cite class="not-italic font-bold text-sm text-slate-900 block">Albert Einstein</cite>
                            <span class="text-xs text-slate-500 block">Theoretical Physicist & Nobel Laureate</span>
                        </div>
                    </footer>
                </blockquote>`
            });

            // 5. Button Block
            bm.add('widget-button', {
                label: 'Action Button',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="12" rx="6"/><path d="M8 12h8M13 9l3 3-3 3" stroke-linecap="round" stroke-linejoin="round"/></svg>`,
                content: `
                <div class="my-4 inline-block">
                    <a href="/application" 
                       class="inline-flex items-center justify-center gap-2.5 px-7 py-3.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-sm shadow-lg shadow-sky-600/25 transition-all transform hover:-translate-y-0.5 cursor-pointer">
                        <span>Explore Programs</span>
                        <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>`
            });

            // 6. Double Button (Dual Action Group)
            bm.add('widget-double-button', {
                label: 'Double Button',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="7" width="9" height="10" rx="3"/><rect x="13" y="7" width="9" height="10" rx="3"/></svg>`,
                content: `
                <div class="my-4 flex flex-wrap items-center gap-3.5">
                    <a href="/application" 
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-sm shadow-md shadow-sky-600/25 transition-all transform hover:-translate-y-0.5 cursor-pointer">
                        <i class="fas fa-paper-plane text-xs"></i>
                        <span>Primary Action</span>
                    </a>
                    <a href="/page/about" 
                       class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-bold text-sm shadow-xs transition-all transform hover:-translate-y-0.5 cursor-pointer">
                        <i class="fas fa-info-circle text-xs text-slate-400"></i>
                        <span>Secondary Action</span>
                    </a>
                </div>`
            });

            // Divider Block
            bm.add('widget-divider', {
                label: 'Divider',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="2" y1="12" x2="9" y2="12"/><circle cx="12" cy="12" r="2.5" fill="currentColor"/><line x1="15" y1="12" x2="22" y2="12"/></svg>`,
                content: `
                <div class="my-6 py-2 flex items-center justify-center">
                    <div class="w-full border-t border-slate-200"></div>
                    <div class="px-4 text-slate-400">
                        <i class="fas fa-circle text-[8px] text-sky-400"></i>
                    </div>
                    <div class="w-full border-t border-slate-200"></div>
                </div>`
            });

            // Spacer Block
            bm.add('widget-spacer', {
                label: 'Spacer',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v18M8 6l4-3 4 3M8 18l4 3 4-3"/></svg>`,
                content: `
                <div class="w-full h-12 sm:h-16 pointer-events-none" aria-hidden="true"></div>`
            });

            // Icon Block
            bm.add('widget-icon', {
                label: 'Icon',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>`,
                content: `
                <div class="my-4 inline-flex items-center justify-center">
                    <div class="w-14 h-14 rounded-2xl bg-sky-50 text-sky-600 border border-sky-100 flex items-center justify-center text-2xl shadow-xs transition-transform hover:scale-110">
                        <i class="fas fa-award"></i>
                    </div>
                </div>`
            });

            // 7. Image Block
            bm.add('widget-image', {
                label: 'Image / Photo',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3" stroke-width="1.8"/><circle cx="8.5" cy="8.5" r="1.5" stroke-width="1.8"/><path d="M21 15l-5-5L5 21" stroke-width="1.8"/></svg>`,
                content: `
                <div class="my-4 group">
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 shadow-md bg-slate-100">
                        <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=1200&auto=format&fit=crop" 
                             alt="Campus Education" 
                             class="w-full h-auto max-h-[500px] object-cover transition-transform duration-500 group-hover:scale-105" />
                    </div>
                    <p class="mt-2.5 text-xs text-center text-slate-500 italic">Optional caption or photo description here</p>
                </div>`
            });

            // Image Box
            bm.add('widget-image-box', {
                label: 'Image Box',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="11" rx="2"/><line x1="3" y1="18" x2="21" y2="18"/><line x1="3" y1="21" x2="15" y2="21"/></svg>`,
                content: `
                <div class="my-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs overflow-hidden transition-all hover:shadow-lg group">
                    <div class="aspect-video w-full overflow-hidden bg-slate-100">
                        <img src="https://images.unsplash.com/photo-1541829070764-84a7d30dd3f3?q=80&w=800&auto=format&fit=crop" 
                             alt="Campus Library" 
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                    </div>
                    <div class="p-6">
                        <h4 class="text-lg font-bold text-slate-900 mb-2">Modern Digital Library</h4>
                        <p class="text-sm text-slate-600 leading-relaxed mb-4">Over 50,000 academic titles, quiet study pods, and high-speed digital research terminals.</p>
                        <a href="/application" class="text-xs font-bold text-sky-600 hover:text-sky-700 flex items-center gap-1.5">
                            <span>Explore Facilities</span>
                            <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>`
            });

            // Icon Box
            bm.add('widget-icon-box', {
                label: 'Icon Box',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><line x1="14" y1="5" x2="21" y2="5"/><line x1="14" y1="9" x2="19" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="3" y1="19" x2="17" y2="19"/></svg>`,
                content: `
                <div class="my-4 p-6 sm:p-7 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:shadow-lg transition-all text-left">
                    <div class="w-12 h-12 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-xl mb-4 shadow-xs">
                        <i class="fas fa-microscope"></i>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-2">Advanced STEM Labs</h4>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Equipped with cutting-edge robotics kits, 3D printers, and biotechnology simulation suites for practical experiments.
                    </p>
                </div>`
            });

            // Star Rating
            bm.add('widget-star-rating', {
                label: 'Star Rating',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>`,
                content: `
                <div class="my-3 inline-flex items-center gap-2">
                    <div class="flex items-center text-amber-400 text-base gap-0.5">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <span class="text-xs font-bold text-slate-700">4.9 / 5.0</span>
                    <span class="text-[11px] text-slate-400">(420+ Reviews)</span>
                </div>`
            });

            // Image Gallery
            bm.add('widget-image-gallery', {
                label: 'Image Gallery',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="8" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/><rect x="13" y="13" width="8" height="8" rx="1.5"/></svg>`,
                content: `
                <div class="my-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="aspect-square rounded-2xl overflow-hidden shadow-xs border border-slate-200 bg-slate-100 group">
                            <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=600&auto=format&fit=crop" 
                                 alt="Campus View" 
                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
                        </div>
                        <div class="aspect-square rounded-2xl overflow-hidden shadow-xs border border-slate-200 bg-slate-100 group">
                            <img src="https://images.unsplash.com/photo-1562774053-701939374585?q=80&w=600&auto=format&fit=crop" 
                                 alt="University Building" 
                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
                        </div>
                        <div class="aspect-square rounded-2xl overflow-hidden shadow-xs border border-slate-200 bg-slate-100 group">
                            <img src="https://images.unsplash.com/photo-1509062522246-3755977927d7?q=80&w=600&auto=format&fit=crop" 
                                 alt="Classroom" 
                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
                        </div>
                        <div class="aspect-square rounded-2xl overflow-hidden shadow-xs border border-slate-200 bg-slate-100 group">
                            <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?q=80&w=600&auto=format&fit=crop" 
                                 alt="Graduation" 
                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
                        </div>
                    </div>
                </div>`
            });

            // Image Carousel       
            bm.add('widget-image-carousel', {
                label: 'Image Carousel',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="4" width="14" height="16" rx="2"/><path d="M1 8h2v8H1zM21 8h2v8h-2z"/></svg>`,
                content: `
                <section 
                    x-data="{ 
                        activeSlide: 0, 
                        slides: [0, 1, 2], 
                        timer: null,
                        start() { this.timer = setInterval(() => { this.activeSlide = (this.activeSlide + 1) % this.slides.length }, 5000) },
                        stop() { clearInterval(this.timer) },
                        setSlide(index) { this.activeSlide = index; this.stop(); this.start(); }
                    }"
                    x-init="start()"
                    @mouseenter="stop()"
                    @mouseleave="start()"
                    class="relative bg-[color-mix(in_srgb,var(--storefront-text)_4%,var(--storefront-bg))] text-[var(--storefront-text)] overflow-hidden border-b border-[var(--storefront-border)]">

                    <!-- Carousel Container (Min-height ensures it stays open) -->
                    <div class="relative w-full max-w-7xl mx-auto min-h-[300px] sm:min-h-[340px] lg:min-h-[420px]">

                        <!-- Slide 1 (double-click the photo to pick another image from the media library) -->
                        <div x-show="activeSlide === 0" 
                             x-transition:enter="transition ease-out duration-500"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-500"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="absolute inset-0 flex items-center justify-between px-6 sm:px-12 lg:px-20 py-12 gap-8 lg:gap-16">
                            <div class="max-w-[520px]">
                                <p class="text-[12px] font-bold uppercase tracking-[0.12em] opacity-60 mb-3">Welcome to Hub Afrika</p>
                                <h1 class="text-[28px] sm:text-[36px] lg:text-[46px] font-light leading-[1.2] mb-6">Renewable energy for a better tomorrow</h1>
                                <a href="/shop" class="inline-block bg-[var(--storefront-primary)] text-white px-8 py-3 text-[13px] font-bold uppercase tracking-[0.08em] hover:opacity-90 transition-opacity">
                                    Shop now
                                </a>
                            </div>
                            <div class="hidden lg:block w-[42%] shrink-0">
                                <img src="/images/storefront/carousel-renewable.jpg" alt="Solar panels on a rooftop at sunset" class="w-full h-[320px] object-cover shadow-[0_24px_48px_-24px_rgba(0,0,0,0.45)]" />
                            </div>
                        </div>

                        <!-- Slide 2 -->
                        <div x-show="activeSlide === 1" 
                             x-transition:enter="transition ease-out duration-500"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-500"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             style="display: none;"
                             class="absolute inset-0 flex items-center justify-between px-6 sm:px-12 lg:px-20 py-12 gap-8 lg:gap-16">
                            <div class="max-w-[520px]">
                                <p class="text-[12px] font-bold uppercase tracking-[0.12em] opacity-60 mb-3">Power backup</p>
                                <h1 class="text-[28px] sm:text-[36px] lg:text-[46px] font-light leading-[1.2] mb-6">Lights on, even when the grid is off</h1>
                                <a href="/shop" class="inline-block bg-[var(--storefront-primary)] text-white px-8 py-3 text-[13px] font-bold uppercase tracking-[0.08em] hover:opacity-90 transition-opacity">
                                    Shop now
                                </a>
                            </div>
                            <div class="hidden lg:block w-[42%] shrink-0">
                                <img src="/images/storefront/carousel-backup.jpg" alt="Wall-mounted hybrid inverter and battery" class="w-full h-[320px] object-cover shadow-[0_24px_48px_-24px_rgba(0,0,0,0.45)]" />
                            </div>
                        </div>

                        <!-- Slide 3 -->
                        <div x-show="activeSlide === 2" 
                             x-transition:enter="transition ease-out duration-500"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-500"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             style="display: none;"
                             class="absolute inset-0 flex items-center justify-between px-6 sm:px-12 lg:px-20 py-12 gap-8 lg:gap-16">
                            <div class="max-w-[520px]">
                                <p class="text-[12px] font-bold uppercase tracking-[0.12em] opacity-60 mb-3">Security</p>
                                <h1 class="text-[28px] sm:text-[36px] lg:text-[46px] font-light leading-[1.2] mb-6">CCTV and electric fencing you can rely on</h1>
                                <a href="/shop" class="inline-block bg-[var(--storefront-primary)] text-white px-8 py-3 text-[13px] font-bold uppercase tracking-[0.08em] hover:opacity-90 transition-opacity">
                                    Shop now
                                </a>
                            </div>
                            <div class="hidden lg:block w-[42%] shrink-0">
                                <img src="/images/storefront/carousel-security.jpg" alt="CCTV camera on a building wall" class="w-full h-[320px] object-cover shadow-[0_24px_48px_-24px_rgba(0,0,0,0.45)]" />
                            </div>
                        </div>

                        <!-- Dots Indicators (Clickable) -->
                        <div class="absolute bottom-4 left-0 right-0 flex justify-center gap-2 z-20">
                            <template x-for="(slide, index) in slides" :key="index">
                                <button 
                                    @click="setSlide(index)"
                                    :aria-label="'Go to slide ' + (index + 1)"
                                    :class="activeSlide === index ? 'bg-[var(--storefront-primary)] opacity-100' : 'bg-[var(--storefront-text)] opacity-30 hover:opacity-60'"
                                    class="w-7 h-[3px] transition-all duration-300 pointer-events-auto">
                                </button>
                            </template>
                        </div>
                    </div>
                </section>`
            });

            // 2. Video Block
            bm.add('widget-video', {
                label: 'Video Player',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="3" stroke-width="1.8"/><polygon points="10 8 16 12 10 16 10 8" fill="currentColor"/></svg>`,
                content: `
                <div class="my-4">
                    <div class="relative w-full aspect-video rounded-2xl overflow-hidden shadow-xl border border-slate-200 bg-slate-900">
                        <iframe class="w-full h-full border-0" 
                                src="https://www.youtube.com/embed/dQw4w9WgXcQ" 
                                title="Video Player" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                allowfullscreen></iframe>
                    </div>
                    <p class="mt-2 text-xs text-center text-slate-500">Insert your YouTube or Vimeo video embed link</p>
                </div>`
            });

            // 3. Feature / Icon List Block
            bm.add('widget-list', {
                label: 'Icon List',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6" stroke-width="2"/><line x1="8" y1="12" x2="21" y2="12" stroke-width="2"/><line x1="8" y1="18" x2="21" y2="18" stroke-width="2"/><circle cx="4" cy="6" r="1.5" fill="currentColor"/><circle cx="4" cy="12" r="1.5" fill="currentColor"/><circle cx="4" cy="18" r="1.5" fill="currentColor"/></svg>`,
                content: `
                <div class="my-4 p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                    <h4 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-list-ul text-sky-500"></i>
                        <span>Key Highlights & Benefits</span>
                    </h4>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3 text-sm text-slate-700">
                            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs shrink-0 mt-0.5"><i class="fas fa-check"></i></span>
                            <span>Fully accredited academic curriculum with international recognition</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-slate-700">
                            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs shrink-0 mt-0.5"><i class="fas fa-check"></i></span>
                            <span>State-of-the-art computer labs, science centers, and athletic facilities</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-slate-700">
                            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs shrink-0 mt-0.5"><i class="fas fa-check"></i></span>
                            <span>Experienced certified teachers and dedicated student mentoring</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-slate-700">
                            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs shrink-0 mt-0.5"><i class="fas fa-check"></i></span>
                            <span>Scholarship opportunities and career guidance for graduating students</span>
                        </li>
                    </ul>
                </div>`
            });

            // Counter Block
            bm.add('widget-counter', {
                label: 'Counter',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="9" y1="4" x2="9" y2="20"/><line x1="15" y1="4" x2="15" y2="20"/></svg>`,
                content: `
                <div class="my-4 p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs text-center">
                    <div class="text-4xl sm:text-5xl font-black text-sky-600 tracking-tight">1,250+</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 mt-1">Certified Graduates</div>
                    <p class="text-xs text-slate-400 mt-2">Empowered learners achieving top university admissions worldwide.</p>
                </div>`
            });

            // Progress Bar Block
            bm.add('widget-progress-bar', {
                label: 'Progress Bar',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="8" width="20" height="8" rx="4"/><rect x="2" y="8" width="13" height="8" rx="4" fill="currentColor"/></svg>`,
                content: `
                <div class="my-4 space-y-2">
                    <div class="flex items-center justify-between text-xs font-bold text-slate-800">
                        <span>STEM & Robotics Curriculum Completion</span>
                        <span class="text-sky-600">85%</span>
                    </div>
                    <div class="w-full h-3 rounded-full bg-slate-100 border border-slate-200/60 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-sky-500 to-indigo-600 w-[85%] transition-all duration-1000"></div>
                    </div>
                </div>`
            });

            // Testimonial Block
            bm.add('widget-testimonial', {
                label: 'Testimonial',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>`,
                content: `
                <div class="my-5 p-7 rounded-3xl bg-white border border-slate-200/80 shadow-md relative">
                    <div class="flex items-center text-amber-400 text-sm gap-1 mb-4">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="text-sm sm:text-base text-slate-700 leading-relaxed italic mb-6">
                        “The curriculum and personalized faculty attention allowed our daughter to discover her true passion in computer science. She received direct entrance into a prestigious engineering university.”
                    </p>
                    <div class="flex items-center gap-3.5 pt-4 border-t border-slate-100">
                        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=150&auto=format&fit=crop" 
                             alt="Sarah Jenkins" 
                             class="w-11 h-11 rounded-full object-cover border-2 border-sky-400" />
                        <div>
                            <div class="font-bold text-sm text-slate-900">Dr. Sarah Jenkins</div>
                            <div class="text-xs text-slate-500">Parent of Class of 2025 Graduate</div>
                        </div>
                    </div>
                </div>`
            });

            // Alert Box Block
            bm.add('widget-alert', {
                label: 'Alert Box',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
                content: `
                <div class="my-4 p-4 sm:p-5 rounded-2xl bg-sky-50 border border-sky-200/80 text-sky-900 flex items-start gap-3.5 shadow-xs">
                    <i class="fas fa-info-circle text-sky-500 text-lg mt-0.5 shrink-0"></i>
                    <div class="flex-1 text-xs sm:text-sm">
                        <div class="font-bold mb-0.5">Important Announcement</div>
                        <p class="text-sky-800 leading-relaxed">
                            Early bird admissions for the upcoming Fall Semester are now open. Final scholarship applications close on Friday.
                        </p>
                    </div>
                </div>`
            });

            // 4. Interactive Accordion Block
            bm.add('widget-accordion', {
                label: 'Interactive Accordion',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="6" rx="1.5" stroke-width="1.8"/><rect x="3" y="14" width="18" height="6" rx="1.5" stroke-width="1.8"/><path d="M17 7l-2 2-2-2" stroke-width="1.8"/><path d="M17 17l-2 2-2-2" stroke-width="1.8"/></svg>`,
                content: `
                <div class="my-4 space-y-3">
                    <details class="group p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs open:ring-2 open:ring-sky-500/20 transition-all" open>
                        <summary class="flex items-center justify-between font-bold text-slate-900 cursor-pointer list-none">
                            <span class="flex items-center gap-2.5">
                                <i class="fas fa-question-circle text-sky-500"></i>
                                <span>What are the admission requirements?</span>
                            </span>
                            <span class="text-xs text-slate-400 group-open:rotate-180 transition-transform"><i class="fas fa-chevron-down"></i></span>
                        </summary>
                        <div class="mt-3 text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                            Applicants must provide previous academic records, identification, and a completed registration form. Our admissions office is available to assist you throughout the process.
                        </div>
                    </details>

                    <details class="group p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs open:ring-2 open:ring-sky-500/20 transition-all">
                        <summary class="flex items-center justify-between font-bold text-slate-900 cursor-pointer list-none">
                            <span class="flex items-center gap-2.5">
                                <i class="fas fa-graduation-cap text-sky-500"></i>
                                <span>Are scholarships and financial aid offered?</span>
                            </span>
                            <span class="text-xs text-slate-400 group-open:rotate-180 transition-transform"><i class="fas fa-chevron-down"></i></span>
                        </summary>
                        <div class="mt-3 text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                            Yes, we offer both merit-based scholarships and need-based financial assistance. Applications open prior to each academic semester.
                        </div>
                    </details>

                    <details class="group p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs open:ring-2 open:ring-sky-500/20 transition-all">
                        <summary class="flex items-center justify-between font-bold text-slate-900 cursor-pointer list-none">
                            <span class="flex items-center gap-2.5">
                                <i class="fas fa-clock text-sky-500"></i>
                                <span>What are the campus hours and facilities?</span>
                            </span>
                            <span class="text-xs text-slate-400 group-open:rotate-180 transition-transform"><i class="fas fa-chevron-down"></i></span>
                        </summary>
                        <div class="mt-3 text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                            Campus is open Monday through Saturday from 7:30 AM to 5:00 PM. Facilities include library, science and IT labs, cafeteria, and indoor sports hall.
                        </div>
                    </details>
                </div>`
            });

            // 7. Toggle (Single Collapsible Box)
            bm.add('widget-toggle', {
                label: 'Toggle Box',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="M8 12h8M12 8v8"/></svg>`,
                content: `
                <div class="my-3">
                    <details class="group p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs open:ring-2 open:ring-sky-500/20 transition-all">
                        <summary class="flex items-center justify-between font-bold text-slate-900 cursor-pointer list-none select-none">
                            <span class="flex items-center gap-3">
                                <span class="w-7 h-7 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center text-xs group-open:bg-sky-600 group-open:text-white transition-colors">
                                    <i class="fas fa-plus group-open:hidden"></i>
                                    <i class="fas fa-minus hidden group-open:inline"></i>
                                </span>
                                <span>Toggle Title - Click to Expand / Collapse</span>
                            </span>
                            <span class="text-xs text-slate-400 group-open:rotate-180 transition-transform">
                                <i class="fas fa-chevron-down"></i>
                            </span>
                        </summary>
                        <div class="mt-4 text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-4">
                            <p>This is a toggle box. You can put any content, paragraphs, images, or widget containers inside here. It expands and collapses cleanly when clicked.</p>
                        </div>
                    </details>
                </div>`
            });

            // 8. Tabs (Interactive Content Panels)
            bm.add('widget-tabs', {
                label: 'Tabs',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z"/><line x1="3" y1="11" x2="21" y2="11"/></svg>`,
                content: `
                <div x-data="{ activeTab: 'tab1' }" class="my-6 p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs">
                    <!-- Tab Navigation Header -->
                    <div class="flex items-center gap-2 p-1.5 rounded-2xl bg-slate-100 border border-slate-200/60 max-w-fit mb-6 overflow-x-auto">
                        <button type="button" 
                                @@click="activeTab = 'tab1'" 
                                :class="activeTab === 'tab1' ? 'bg-white text-sky-600 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                                class="px-5 py-2 rounded-xl text-xs sm:text-sm transition-all cursor-pointer flex items-center gap-2 shrink-0">
                            <i class="fas fa-graduation-cap text-xs"></i>
                            <span>Overview</span>
                        </button>

                        <button type="button" 
                                @@click="activeTab = 'tab2'" 
                                :class="activeTab === 'tab2' ? 'bg-white text-sky-600 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                                class="px-5 py-2 rounded-xl text-xs sm:text-sm transition-all cursor-pointer flex items-center gap-2 shrink-0">
                            <i class="fas fa-book-open text-xs"></i>
                            <span>Curriculum</span>
                        </button>

                        <button type="button" 
                                @@click="activeTab = 'tab3'" 
                                :class="activeTab === 'tab3' ? 'bg-white text-sky-600 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                                class="px-5 py-2 rounded-xl text-xs sm:text-sm transition-all cursor-pointer flex items-center gap-2 shrink-0">
                            <i class="fas fa-certificate text-xs"></i>
                            <span>Admissions</span>
                        </button>
                    </div>

                    <!-- Tab Panels -->
                    <div>
                        <!-- Panel 1 -->
                        <div x-show="activeTab === 'tab1'" class="space-y-3 transition-all duration-200">
                            <h4 class="text-lg font-bold text-slate-900">Academic Overview & Vision</h4>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Our institution provides holistic development through academic rigor, ethical mentorship, and modern technological integration. We prepare young minds to excel in higher education and global careers.
                            </p>
                            <div class="pt-2">
                                <a href="/application" class="inline-flex items-center gap-2 text-xs font-bold text-sky-600 hover:text-sky-700">
                                    <span>Learn more about our mission</span>
                                    <i class="fas fa-arrow-right text-[10px]"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Panel 2 -->
                        <div x-show="activeTab === 'tab2'" style="display: none;" class="space-y-3 transition-all duration-200">
                            <h4 class="text-lg font-bold text-slate-900">Comprehensive Modern Curriculum</h4>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Structured across Science, Technology, Humanities, and Arts. Our curriculum balances fundamental theoretical knowledge with hands-on laboratory experiments and project-based learning.
                            </p>
                            <ul class="text-xs text-slate-600 space-y-1.5 list-disc list-inside pt-1">
                                <li>STEM Foundation & Coding Labs</li>
                                <li>Bilingual Language Arts & Literature</li>
                                <li>Physical Education & Athletic Programs</li>
                            </ul>
                        </div>

                        <!-- Panel 3 -->
                        <div x-show="activeTab === 'tab3'" style="display: none;" class="space-y-3 transition-all duration-200">
                            <h4 class="text-lg font-bold text-slate-900">Admissions Criteria & Requirements</h4>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Applications are reviewed on a rolling basis. Candidates must submit certified copies of prior academic transcripts, two recommendation letters, and complete our digital entrance assessment.
                            </p>
                            <div class="pt-2">
                                <a href="/application" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-sky-600 text-white font-bold text-xs hover:bg-sky-500 transition-colors">
                                    <span>Begin Admission Application</span>
                                    <i class="fas fa-external-link-alt text-[10px]"></i>
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
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>`,
                content: `
                <div class="my-4 rounded-2xl overflow-hidden shadow-md border border-slate-200 aspect-[16/9] sm:aspect-[21/9] bg-slate-100">
                    <iframe class="w-full h-full border-0" 
                            src="https://maps.google.com/maps?q=Oxford+University,+Oxford,+UK&t=&z=14&ie=UTF8&iwloc=&output=embed" 
                            loading="lazy" 
                            allowfullscreen></iframe>
                </div>`
            });

            // Social Share Block
            bm.add('widget-social-share', {
                label: 'Social Share',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>`,
                content: `
                <div class="my-4">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2.5">Share this page</div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="https://www.facebook.com/sharer/sharer.php" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#1877F2] text-white text-xs font-bold shadow-xs hover:opacity-90 transition-opacity">
                            <i class="fab fa-facebook-f text-xs"></i>
                            <span>Facebook</span>
                        </a>
                        <a href="https://twitter.com/intent/tweet" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-black text-white text-xs font-bold shadow-xs hover:opacity-90 transition-opacity">
                            <i class="fab fa-x-twitter text-xs"></i>
                            <span>X</span>
                        </a>
                        <a href="https://www.linkedin.com/shareArticle" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#0A66C2] text-white text-xs font-bold shadow-xs hover:opacity-90 transition-opacity">
                            <i class="fab fa-linkedin-in text-xs"></i>
                            <span>LinkedIn</span>
                        </a>
                        <a href="https://api.whatsapp.com/send" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#25D366] text-white text-xs font-bold shadow-xs hover:opacity-90 transition-opacity">
                            <i class="fab fa-whatsapp text-xs"></i>
                            <span>WhatsApp</span>
                        </a>
                    </div>
                </div>`
            });

            // Contact Form Block
            bm.add('widget-contact-form', {
                label: 'Contact Form',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/><line x1="6" y1="14" x2="14" y2="14"/></svg>`,
                content: `
                <div class="my-6 p-6 sm:p-8 rounded-3xl bg-white border border-slate-200/80 shadow-md">
                    <div class="mb-6">
                        <span class="text-xs font-bold uppercase tracking-wider text-sky-600 bg-sky-50 px-3 py-1 rounded-full border border-sky-100">Get in Touch</span>
                        <h3 class="text-2xl font-extrabold text-slate-900 mt-2">Send Us an Inquiry</h3>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">Our academic counseling team will respond within 24 hours.</p>
                    </div>
                    <form onsubmit="event.preventDefault(); alert('Inquiry received. Thank you!');" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Full Name</label>
                                <input type="text" placeholder="John Doe" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-hidden focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 bg-slate-50/50" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Email Address</label>
                                <input type="email" placeholder="john@example.com" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-hidden focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 bg-slate-50/50" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Subject / Program of Interest</label>
                            <input type="text" placeholder="e.g. Undergraduate Science Admissions" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-hidden focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 bg-slate-50/50" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Your Message</label>
                            <textarea rows="4" placeholder="How can we help you?" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-hidden focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 bg-slate-50/50"></textarea>
                        </div>
                        <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-sm shadow-md shadow-sky-600/25 transition-all cursor-pointer">
                            <span>Send Message</span>
                            <i class="fas fa-paper-plane text-xs"></i>
                        </button>
                    </form>
                </div>`
            });

            // Flip Box Block
            bm.add('widget-flip-box', {
                label: 'Flip Box',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="M12 2v4M12 18v4M2 12h4M18 12h4"/></svg>`,
                content: `
                <div class="my-6 w-full max-w-sm mx-auto h-[320px] [perspective:1000px] group">
                    <div class="relative w-full h-full text-center transition-transform duration-700 [transform-style:preserve-3d] group-hover:[transform:rotateY(180deg)] rounded-3xl shadow-md">
                        <!-- Front Side -->
                        <div class="absolute inset-0 w-full h-full rounded-3xl bg-white border border-slate-200 p-8 flex flex-col items-center justify-center [backface-visibility:hidden]">
                            <div class="w-16 h-16 rounded-2xl bg-sky-100 text-sky-600 flex items-center justify-center text-3xl mb-5 shadow-xs">
                                <i class="fas fa-atom"></i>
                            </div>
                            <h4 class="text-xl font-extrabold text-slate-900 mb-2">Robotics & AI Program</h4>
                            <p class="text-xs text-slate-500 max-w-xs leading-relaxed mb-4">Hands-on micro-engineering and algorithm design for ambitious innovators.</p>
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-600 bg-sky-50 px-3 py-1 rounded-full border border-sky-100">
                                <span>Hover to View Details</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </span>
                        </div>

                        <!-- Back Side -->
                        <div class="absolute inset-0 w-full h-full rounded-3xl bg-gradient-to-br from-sky-600 to-indigo-700 text-white p-8 flex flex-col items-center justify-center [transform:rotateY(180deg)] [backface-visibility:hidden]">
                            <h4 class="text-xl font-extrabold mb-3">Curriculum Highlights</h4>
                            <p class="text-xs text-sky-100 mb-6 leading-relaxed">
                                Includes competitive tournament participation, ROS robotics frameworks, and cloud machine learning.
                            </p>
                            <a href="/application" class="px-6 py-2.5 rounded-xl bg-white text-sky-700 font-bold text-xs shadow-lg hover:bg-slate-100 transition-colors">
                                Apply for Admission
                            </a>
                        </div>
                    </div>
                </div>`
            });

            // Animated Headline Block
            bm.add('widget-animated-headline', {
                label: 'Animated Headline',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2l2.5 5.5L20 8.5l-4 4 1 5.5-5-3-5 3 1-5.5-4-4 5.5-1z"/><line x1="4" y1="22" x2="20" y2="22" stroke-width="2"/></svg>`,
                content: `
                <div class="my-6 text-center">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-50 text-sky-600 border border-sky-100 text-xs font-bold uppercase tracking-wider mb-3">
                        <i class="fas fa-sparkles text-xs"></i>
                        <span>Future Ready</span>
                    </div>
                    <h2 class="text-3xl sm:text-5xl font-black text-slate-900 tracking-tight leading-tight max-w-3xl mx-auto">
                        We Empower Students to 
                        <span class="relative inline-block text-transparent bg-clip-text bg-gradient-to-r from-sky-500 via-indigo-500 to-purple-600">
                            Innovate
                            <svg class="absolute -bottom-2 left-0 w-full h-2.5 text-sky-400 overflow-visible" viewBox="0 0 100 12" fill="none" preserveAspectRatio="none">
                                <path d="M2 9c25-6 60-6 96 1" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"/>
                            </svg>
                        </span>
                        and Lead.
                    </h2>
                    <p class="mt-4 text-sm sm:text-base text-slate-500 max-w-xl mx-auto">
                        Equipping tomorrow's pioneers with academic precision, ethical values, and digital prowess.
                    </p>
                </div>`
            });

            // Shape Divider Block
            bm.add('widget-shape-divider', {
                label: 'Shape Divider',
                category: widgetCategory,
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 16s4-8 10-8 10 8 10 8"/><line x1="2" y1="20" x2="22" y2="20" stroke-width="2"/></svg>`,
                content: `
                <div class="w-full overflow-hidden leading-none relative my-3">
                    <svg class="relative block w-full h-12 sm:h-20 text-slate-100" viewBox="0 0 1200 120" preserveAspectRatio="none">
                        <path d="M0,0 C150,90 350,-40 500,45 C650,130 900,10 1200,50 L1200,120 L0,120 Z" fill="currentColor"></path>
                    </svg>
                </div>`
            });

            // =========================================================================
            // 3. CUSTOM TAILWIND ACADEMIC SECTIONS LIBRARY
            // =========================================================================

            // 1. Hero Banner (Modern Gradient)
            bm.add('school-hero-gradient', {
                label: 'Hero Banner (Gradient)',
                category: 'Academic Sections',
                media: '<i class="fas fa-heading fa-2x"></i>',
                content: `
                <section class="py-24 bg-gradient-to-b from-slate-900 via-slate-900 to-slate-800 text-white relative overflow-hidden">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-sky-400 text-xs font-semibold mb-6">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Admissions Now Open for Academic Year</span>
                        </div>
                        <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight text-white max-w-4xl mx-auto leading-tight">
                            Empowering Minds, <span class="text-sky-400">Inspiring Futures</span>
                        </h1>
                        <p class="mt-6 text-base sm:text-xl text-slate-300 max-w-2xl mx-auto leading-relaxed">
                            Welcome to our institution, where academic distinction meets character building in an innovative and inspiring environment.
                        </p>
                        <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                            <a href="/application" class="px-8 py-3.5 text-sm font-bold text-white bg-sky-600 hover:bg-sky-500 rounded-xl shadow-lg shadow-sky-600/30 transition-all">
                                Apply for Admission
                            </a>
                            <a href="/student" class="px-6 py-3.5 text-sm font-bold text-slate-200 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl transition-all">
                                Student Portal
                            </a>
                        </div>
                    </div>
                </section>`
            });

            // 2. Logo Slider / Partners Marquee Carousel
            bm.add('school-logo-slider', {
                label: 'Logo Slider / Partners',
                category: 'Academic Sections',
                media: `<svg class="w-8 h-8 mx-auto text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="6" width="20" height="12" rx="2.5"/><circle cx="7" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="17" cy="12" r="2"/><path d="M2 10h20M2 14h20" stroke-width="1" stroke-dasharray="2 2"/></svg>`,
                content: `
                <section class="py-16 bg-white text-slate-800 border-y border-slate-100 overflow-hidden relative">
                    <style>
                        @keyframes logoMarquee {
                            0% { transform: translateX(0%); }
                            100% { transform: translateX(-50%); }
                        }
                        .logo-marquee-track {
                            display: flex;
                            width: max-content;
                            animation: logoMarquee 28s linear infinite;
                        }
                        .logo-marquee-track:hover {
                            animation-play-state: paused;
                        }
                    </style>
                    
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center mb-10">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-sky-50 text-sky-600 border border-sky-100 text-xs font-bold uppercase tracking-wider mb-2">
                            <i class="fas fa-handshake text-xs"></i>
                            <span>Global Academic Affiliations</span>
                        </span>
                        <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                            Trusted & Accredited by Leading Global Institutions
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-500 mt-2 max-w-xl mx-auto">
                            Our curriculum and certifications are recognized across top universities and educational bodies worldwide.
                        </p>
                    </div>

                    <!-- Infinite Carousel Container with Left/Right Fades -->
                    <div class="relative w-full overflow-hidden">
                        <div class="pointer-events-none absolute inset-y-0 left-0 w-20 sm:w-36 bg-gradient-to-r from-white via-white/80 to-transparent z-10"></div>
                        <div class="pointer-events-none absolute inset-y-0 right-0 w-20 sm:w-36 bg-gradient-to-l from-white via-white/80 to-transparent z-10"></div>

                        <div class="logo-marquee-track flex items-center gap-6 sm:gap-8 py-3">
                            <!-- Set 1 -->
                            <div class="flex items-center gap-3 px-6 py-3.5 rounded-2xl bg-slate-50 hover:bg-white border border-slate-200/80 shadow-xs hover:shadow-md transition-all grayscale opacity-75 hover:grayscale-0 hover:opacity-100 shrink-0 cursor-pointer">
                                <i class="fab fa-google text-2xl text-red-500"></i>
                                <div class="text-left">
                                    <div class="text-xs font-bold text-slate-800">Google</div>
                                    <div class="text-[10px] text-slate-400 font-medium">For Education</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 px-6 py-3.5 rounded-2xl bg-slate-50 hover:bg-white border border-slate-200/80 shadow-xs hover:shadow-md transition-all grayscale opacity-75 hover:grayscale-0 hover:opacity-100 shrink-0 cursor-pointer">
                                <i class="fab fa-microsoft text-2xl text-sky-600"></i>
                                <div class="text-left">
                                    <div class="text-xs font-bold text-slate-800">Microsoft</div>
                                    <div class="text-[10px] text-slate-400 font-medium">Showcase School</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 px-6 py-3.5 rounded-2xl bg-slate-50 hover:bg-white border border-slate-200/80 shadow-xs hover:shadow-md transition-all grayscale opacity-75 hover:grayscale-0 hover:opacity-100 shrink-0 cursor-pointer">
                                <i class="fas fa-university text-2xl text-indigo-600"></i>
                                <div class="text-left">
                                    <div class="text-xs font-bold text-slate-800">Cambridge</div>
                                    <div class="text-[10px] text-slate-400 font-medium">International Centre</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 px-6 py-3.5 rounded-2xl bg-slate-50 hover:bg-white border border-slate-200/80 shadow-xs hover:shadow-md transition-all grayscale opacity-75 hover:grayscale-0 hover:opacity-100 shrink-0 cursor-pointer">
                                <i class="fas fa-certificate text-2xl text-emerald-600"></i>
                                <div class="text-left">
                                    <div class="text-xs font-bold text-slate-800">Pearson</div>
                                    <div class="text-[10px] text-slate-400 font-medium">Edexcel Accredited</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 px-6 py-3.5 rounded-2xl bg-slate-50 hover:bg-white border border-slate-200/80 shadow-xs hover:shadow-md transition-all grayscale opacity-75 hover:grayscale-0 hover:opacity-100 shrink-0 cursor-pointer">
                                <i class="fab fa-aws text-2xl text-amber-500"></i>
                                <div class="text-left">
                                    <div class="text-xs font-bold text-slate-800">AWS Academy</div>
                                    <div class="text-[10px] text-slate-400 font-medium">Cloud Member</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 px-6 py-3.5 rounded-2xl bg-slate-50 hover:bg-white border border-slate-200/80 shadow-xs hover:shadow-md transition-all grayscale opacity-75 hover:grayscale-0 hover:opacity-100 shrink-0 cursor-pointer">
                                <i class="fas fa-globe-americas text-2xl text-blue-600"></i>
                                <div class="text-left">
                                    <div class="text-xs font-bold text-slate-800">UNESCO</div>
                                    <div class="text-[10px] text-slate-400 font-medium">Associated Network</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 px-6 py-3.5 rounded-2xl bg-slate-50 hover:bg-white border border-slate-200/80 shadow-xs hover:shadow-md transition-all grayscale opacity-75 hover:grayscale-0 hover:opacity-100 shrink-0 cursor-pointer">
                                <i class="fas fa-microchip text-2xl text-purple-600"></i>
                                <div class="text-left">
                                    <div class="text-xs font-bold text-slate-800">STEM Alliance</div>
                                    <div class="text-[10px] text-slate-400 font-medium">Certified Partner</div>
                                </div>
                            </div>

                            <!-- Set 2 (Exact Duplicate for Seamless Continuous Loop) -->
                            <div class="flex items-center gap-3 px-6 py-3.5 rounded-2xl bg-slate-50 hover:bg-white border border-slate-200/80 shadow-xs hover:shadow-md transition-all grayscale opacity-75 hover:grayscale-0 hover:opacity-100 shrink-0 cursor-pointer">
                                <i class="fab fa-google text-2xl text-red-500"></i>
                                <div class="text-left">
                                    <div class="text-xs font-bold text-slate-800">Google</div>
                                    <div class="text-[10px] text-slate-400 font-medium">For Education</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 px-6 py-3.5 rounded-2xl bg-slate-50 hover:bg-white border border-slate-200/80 shadow-xs hover:shadow-md transition-all grayscale opacity-75 hover:grayscale-0 hover:opacity-100 shrink-0 cursor-pointer">
                                <i class="fab fa-microsoft text-2xl text-sky-600"></i>
                                <div class="text-left">
                                    <div class="text-xs font-bold text-slate-800">Microsoft</div>
                                    <div class="text-[10px] text-slate-400 font-medium">Showcase School</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 px-6 py-3.5 rounded-2xl bg-slate-50 hover:bg-white border border-slate-200/80 shadow-xs hover:shadow-md transition-all grayscale opacity-75 hover:grayscale-0 hover:opacity-100 shrink-0 cursor-pointer">
                                <i class="fas fa-university text-2xl text-indigo-600"></i>
                                <div class="text-left">
                                    <div class="text-xs font-bold text-slate-800">Cambridge</div>
                                    <div class="text-[10px] text-slate-400 font-medium">International Centre</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 px-6 py-3.5 rounded-2xl bg-slate-50 hover:bg-white border border-slate-200/80 shadow-xs hover:shadow-md transition-all grayscale opacity-75 hover:grayscale-0 hover:opacity-100 shrink-0 cursor-pointer">
                                <i class="fas fa-certificate text-2xl text-emerald-600"></i>
                                <div class="text-left">
                                    <div class="text-xs font-bold text-slate-800">Pearson</div>
                                    <div class="text-[10px] text-slate-400 font-medium">Edexcel Accredited</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 px-6 py-3.5 rounded-2xl bg-slate-50 hover:bg-white border border-slate-200/80 shadow-xs hover:shadow-md transition-all grayscale opacity-75 hover:grayscale-0 hover:opacity-100 shrink-0 cursor-pointer">
                                <i class="fab fa-aws text-2xl text-amber-500"></i>
                                <div class="text-left">
                                    <div class="text-xs font-bold text-slate-800">AWS Academy</div>
                                    <div class="text-[10px] text-slate-400 font-medium">Cloud Member</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 px-6 py-3.5 rounded-2xl bg-slate-50 hover:bg-white border border-slate-200/80 shadow-xs hover:shadow-md transition-all grayscale opacity-75 hover:grayscale-0 hover:opacity-100 shrink-0 cursor-pointer">
                                <i class="fas fa-globe-americas text-2xl text-blue-600"></i>
                                <div class="text-left">
                                    <div class="text-xs font-bold text-slate-800">UNESCO</div>
                                    <div class="text-[10px] text-slate-400 font-medium">Associated Network</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 px-6 py-3.5 rounded-2xl bg-slate-50 hover:bg-white border border-slate-200/80 shadow-xs hover:shadow-md transition-all grayscale opacity-75 hover:grayscale-0 hover:opacity-100 shrink-0 cursor-pointer">
                                <i class="fas fa-microchip text-2xl text-purple-600"></i>
                                <div class="text-left">
                                    <div class="text-xs font-bold text-slate-800">STEM Alliance</div>
                                    <div class="text-[10px] text-slate-400 font-medium">Certified Partner</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>`
            });

            // 3. Features 3-Column Grid
            bm.add('school-features-grid', {
                label: '3-Column Features',
                category: 'Academic Sections',
                media: '<i class="fas fa-th-large fa-2x"></i>',
                content: `
                <section class="py-20 bg-white text-slate-800">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="text-center max-w-2xl mx-auto mb-14">
                            <span class="text-xs font-bold uppercase tracking-wider text-sky-600">Why Choose Us</span>
                            <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Designed for Complete Academic Excellence</h2>
                            <p class="text-sm text-slate-500 mt-3">We blend disciplined education with modern digital tools to nurture tomorrow's leaders.</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="p-8 rounded-2xl bg-slate-50 border border-slate-200/80 hover:shadow-xl transition-all">
                                <div class="w-12 h-12 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-xl mb-5">
                                    <i class="fas fa-book-reader"></i>
                                </div>
                                <h3 class="text-lg font-bold text-slate-900 mb-2">Comprehensive Curriculum</h3>
                                <p class="text-sm text-slate-600 leading-relaxed">Structured academic curriculum aligned with national and international standards, covering STEM, humanities, and arts.</p>
                            </div>
                            <div class="p-8 rounded-2xl bg-slate-50 border border-slate-200/80 hover:shadow-xl transition-all">
                                <div class="w-12 h-12 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-xl mb-5">
                                    <i class="fas fa-laptop-code"></i>
                                </div>
                                <h3 class="text-lg font-bold text-slate-900 mb-2">Modern Technology</h3>
                                <p class="text-sm text-slate-600 leading-relaxed">Fully equipped computer laboratories, high-speed digital resources, and a state-of-the-art student and parent portal.</p>
                            </div>
                            <div class="p-8 rounded-2xl bg-slate-50 border border-slate-200/80 hover:shadow-xl transition-all">
                                <div class="w-12 h-12 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-xl mb-5">
                                    <i class="fas fa-medal"></i>
                                </div>
                                <h3 class="text-lg font-bold text-slate-900 mb-2">Extracurricular Leadership</h3>
                                <p class="text-sm text-slate-600 leading-relaxed">Sports competitions, debate clubs, community outreach, and creative arts clubs to ensure well-rounded personal growth.</p>
                            </div>
                        </div>
                    </div>
                </section>`
            });

            // 4. Impact Statistics Counter
            bm.add('school-stats-counter', {
                label: 'Statistics Counter',
                category: 'Academic Sections',
                media: '<i class="fas fa-chart-line fa-2x"></i>',
                content: `
                <section class="py-16 bg-gradient-to-r from-sky-800 via-sky-700 to-indigo-800 text-white">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                            <div class="p-6 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/15">
                                <div class="text-4xl font-extrabold">100%</div>
                                <div class="text-xs text-white/80 mt-1 uppercase tracking-wider font-semibold">Academic Focus</div>
                            </div>
                            <div class="p-6 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/15">
                                <div class="text-4xl font-extrabold">50+</div>
                                <div class="text-xs text-white/80 mt-1 uppercase tracking-wider font-semibold">Certified Faculty</div>
                            </div>
                            <div class="p-6 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/15">
                                <div class="text-4xl font-extrabold">15+</div>
                                <div class="text-xs text-white/80 mt-1 uppercase tracking-wider font-semibold">Modern Laboratories</div>
                            </div>
                            <div class="p-6 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/15">
                                <div class="text-4xl font-extrabold">1,200+</div>
                                <div class="text-xs text-white/80 mt-1 uppercase tracking-wider font-semibold">Active Learners</div>
                            </div>
                        </div>
                    </div>
                </section>`
            });

            // 6. FAQ Accordion Section
            bm.add('school-faq-accordion', {
                label: 'FAQ Accordion',
                category: 'Academic Sections',
                media: '<i class="fas fa-question-circle fa-2x"></i>',
                content: `
                <section class="py-20 bg-slate-50 text-slate-800">
                    <div class="max-w-3xl mx-auto px-4">
                        <div class="text-center mb-12">
                            <span class="text-xs font-bold uppercase tracking-wider text-sky-600">Got Questions?</span>
                            <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Frequently Asked Questions</h2>
                        </div>
                        <div class="space-y-4">
                            <details class="group p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs open:ring-2 open:ring-sky-500/20">
                                <summary class="flex items-center justify-between font-bold text-slate-900 cursor-pointer list-none">
                                    <span>What are the admission requirements and deadlines?</span>
                                    <span class="text-xs text-slate-400 group-open:rotate-180 transition-transform"><i class="fas fa-chevron-down"></i></span>
                                </summary>
                                <p class="mt-3 text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                                    Applicants must submit their previous transcripts, birth certificate or national ID, and completed digital admission form before the closing date.
                                </p>
                            </details>
                            <details class="group p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs open:ring-2 open:ring-sky-500/20">
                                <summary class="flex items-center justify-between font-bold text-slate-900 cursor-pointer list-none">
                                    <span>Are scholarships or tuition assistance available?</span>
                                    <span class="text-xs text-slate-400 group-open:rotate-180 transition-transform"><i class="fas fa-chevron-down"></i></span>
                                </summary>
                                <p class="mt-3 text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                                    Yes, academic merit scholarships and need-based fee concessions are evaluated each academic year.
                                </p>
                            </details>
                        </div>
                    </div>
                </section>`
            });

            // 7. Two-Column Split Media Section
            bm.add('school-split-section', {
                label: 'Split Media & Text',
                category: 'Academic Sections',
                media: '<i class="fas fa-columns fa-2x"></i>',
                content: `
                <section class="py-20 bg-white text-slate-800">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-sky-600">Principal's Address</span>
                                <h2 class="text-3xl font-extrabold text-slate-900 mt-2 mb-4 leading-tight">Nurturing Tomorrow's Global Innovators</h2>
                                <p class="text-sm text-slate-600 leading-relaxed mb-4">
                                    At our academy, we believe education extends far beyond the textbook. We empower every learner to discover their unique potential through experiential learning and ethical guidance.
                                </p>
                                <p class="text-sm text-slate-600 leading-relaxed mb-6">
                                    Our dedicated faculty and world-class campus facilities provide students with an immersive educational journey.
                                </p>
                                <a href="/application" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-sky-600 text-white font-bold text-sm hover:bg-sky-500 transition-colors">
                                    <span>Learn More About Us</span>
                                    <i class="fas fa-arrow-right text-xs"></i>
                                </a>
                            </div>
                            <div class="rounded-3xl overflow-hidden shadow-2xl bg-slate-100 aspect-video flex items-center justify-center text-slate-400">
                                <i class="fas fa-school text-6xl text-sky-600"></i>
                            </div>
                        </div>
                    </div>
                </section>`
            });

            // =========================================================================
