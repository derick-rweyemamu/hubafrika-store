<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\PicksLibraryMedia;
use App\Models\Category;
use App\Models\CmsMenu;
use App\Models\CmsMenuItem;
use App\Models\CmsPage;
use App\Models\CmsPost;
use App\Models\Product;
use App\Support\RichText;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class CmsMenus extends Component
{
    use PicksLibraryMedia, WithFileUploads;

    /**
     * Actions can be invoked on their own, without passing through the route
     * middleware, so the permission is re-checked on every request.
     */
    public function boot(): void
    {
        abort_unless(auth()->user()?->can('cms.manage'), 403);
    }

    // Active Menu
    public ?int $selectedMenuId = null;

    public string $location = 'header';

    // School-style Menu Form State (Create / Edit Menu)
    public bool $showMenuModal = false;

    public bool $isEditingMenu = false;

    public ?int $editingMenuId = null;

    public string $menu_title = '';

    public string $menu_slug = '';

    public string $menu_location = 'header'; // 'header', 'footer', 'topbar', 'sidebar', 'store', 'mobile'

    public ?string $menu_description = '';

    public ?string $menu_css_class = '';

    public int $menu_status = 1;

    // Test & Legacy Menu Form State
    public string $menuName = '';

    public string $menuSlug = '';

    public ?string $menuCssClass = null;

    public ?string $menuDescription = null;

    // School-style Item Form State
    public bool $showMenuItemModal = false;

    public ?int $editingMenuItemId = null;

    public ?int $item_parent_id = null;

    public string $item_title = '';

    public ?string $item_description = '';

    public ?string $item_content = '';

    public string $item_type = 'custom'; // 'site', 'page', 'post', 'category', 'product', 'custom', 'image', 'html'

    public bool $item_is_mega_menu = false;

    public int $item_mega_menu_columns = 4;

    public int $item_mega_column = 1;

    public $item_image = null;

    public ?string $item_existing_image = null;

    public ?string $item_existing_image_url = null;

    public bool $item_image_cleared = false;

    public ?int $item_page_id = null;

    public ?int $item_post_id = null;

    public ?int $item_category_id = null;

    public ?int $item_product_id = null;

    public ?string $item_url = '';

    /** Path of a storefront page that is built in code, e.g. "/shop". */
    public ?string $item_site_path = null;

    public string $item_target = '_self';

    public ?string $item_icon = '';

    public ?string $item_badge_text = '';

    public ?string $item_badge_color = '#ef4444';

    public int $item_order = 1;

    public int $item_status = 1;

    // Legacy / Test Item Properties (Synchronized)
    public string $label = '';

    public ?string $badge = null;

    public ?string $cssClass = null;

    public ?int $pageId = null;

    public ?int $postId = null;

    public ?int $parentId = null;

    public string $url = '';

    public bool $isMegaMenu = false;

    public int $megaMenuColumns = 4;

    public int $megaMenuColumn = 1;

    public string $megaMenuType = 'columns';

    public ?string $megaMenuImage = null;

    public mixed $megaMenuImageUpload = null;

    public string $megaMenuImagePosition = 'left';

    public ?string $megaMenuImageTitle = null;

    public ?string $megaMenuImageLink = null;

    public ?int $editingItemId = null;

    // Visual Mega Menu Multi-Column Builder State
    public bool $showMegaBuilderModal = false;

    public ?int $megaBuilderItemId = null;

    public string $mega_header_title = '';

    public array $mega_columns = [];

    // Live Dropdown Preview State
    public bool $showMegaPreviewModal = false;

    public ?int $previewMegaItemId = null;

    // Mega Designer state (for test compatibility)
    public ?int $activeMegaDesignItemId = null;

    public function mount(): void
    {
        $headerMenu = CmsMenu::where('slug', 'header')->first() ?? CmsMenu::orderBy('id')->first();
        if ($headerMenu) {
            $this->selectedMenuId = $headerMenu->id;
            $this->location = $headerMenu->slug;
        }
    }

    public function selectMenu(int $id): void
    {
        $menu = CmsMenu::find($id);
        if ($menu) {
            $this->selectedMenuId = $menu->id;
            $this->location = $menu->slug;
        }
        $this->resetItemForm();
    }

    public function selectLocation(string $slug): void
    {
        $this->location = $slug;
        $menu = CmsMenu::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => ucfirst($slug).' Menu',
                'title' => ucfirst($slug).' Menu',
                'location' => in_array($slug, ['header', 'footer', 'store', 'topbar', 'sidebar', 'mobile'], true) ? $slug : 'header',
                'status' => 1,
            ]
        );
        $this->selectedMenuId = $menu->id;
        $this->resetItemForm();
    }

    /* -------------------------------------------------------------
     | MENU (CONTAINER) ACTIONS
     | ------------------------------------------------------------- */

    public function openCreateMenu(): void
    {
        $this->reset(['editingMenuId', 'menu_title', 'menu_slug', 'menu_description', 'menu_css_class', 'menuName', 'menuSlug', 'menuCssClass', 'menuDescription']);
        $this->isEditingMenu = false;
        $this->menu_location = 'header';
        $this->menu_status = 1;
        $this->resetValidation();
        $this->showMenuModal = true;
    }

    public function openCreateMenuModal(): void
    {
        $this->openCreateMenu();
    }

    public function openEditMenuModal(): void
    {
        if ($this->selectedMenuId) {
            $this->editMenu($this->selectedMenuId);
        }
    }

    public function editMenu(int $id): void
    {
        $menu = CmsMenu::findOrFail($id);
        $this->editingMenuId = $menu->id;
        $this->isEditingMenu = true;
        $this->menu_title = $menu->title ?: ($menu->name ?? '');
        $this->menu_slug = $menu->slug;
        $this->menu_location = $menu->location ?: 'header';
        $this->menu_description = $menu->description;
        $this->menu_css_class = $menu->css_class;
        $this->menu_status = (int) ($menu->status ?? 1);

        $this->menuName = $this->menu_title;
        $this->menuSlug = $this->menu_slug;
        $this->menuCssClass = $this->menu_css_class;
        $this->menuDescription = $this->menu_description;

        $this->resetValidation();
        $this->showMenuModal = true;
    }

    public function updatedMenuTitle(string $value): void
    {
        if (! $this->editingMenuId) {
            $this->menu_slug = Str::slug($value);
            $this->menuSlug = $this->menu_slug;
        }
        $this->menuName = $value;
    }

    public function updatedMenuName(string $value): void
    {
        $this->menu_title = $value;
        if (! $this->editingMenuId) {
            $this->menu_slug = Str::slug($value);
            $this->menuSlug = $this->menu_slug;
        }
    }

    public function saveMenuDetails(): void
    {
        $this->saveMenu();
    }

    public function closeMenuModal(): void
    {
        $this->showMenuModal = false;
    }

    public function updated(string $property, mixed $value): void
    {
        if ($property === 'menu_description') {
            $this->menuDescription = $value;
        }
    }

    public function saveMenu(): void
    {
        $title = $this->menu_title ?: $this->menuName;
        $slug = $this->menu_slug ?: $this->menuSlug;
        $cssClass = $this->menu_css_class ?: $this->menuCssClass;
        $desc = $this->menu_description !== null && $this->menu_description !== ''
            ? $this->menu_description
            : ($this->editingMenuId && $this->menu_description === '' ? '' : $this->menuDescription);

        $this->menu_title = $title;
        $this->menu_slug = $slug;
        $this->menuName = $title;
        $this->menuSlug = $slug;
        $this->menu_description = $desc;

        $this->validate([
            'menu_title' => 'required|string|max:191',
            'menu_slug' => 'required|string|max:100|alpha_dash|unique:cms_menus,slug,'.($this->editingMenuId ?: 'NULL').',id',
            'menu_location' => 'nullable|string|in:header,footer,topbar,sidebar,store,mobile',
            'menu_description' => 'nullable|string|max:5000',
            'menu_status' => 'nullable|in:0,1',
        ]);

        $sanitizedDescription = RichText::sanitize($desc);

        $data = [
            'name' => $title,
            'title' => $title,
            'slug' => $slug,
            'location' => $this->menu_location ?: 'header',
            'description' => $sanitizedDescription,
            'css_class' => $cssClass ?: null,
            'status' => $this->menu_status ?? 1,
        ];

        if ($this->editingMenuId) {
            $menu = CmsMenu::findOrFail($this->editingMenuId);
            $menu->update($data);
            session()->flash('message', __('Menu updated successfully.'));
        } else {
            $menu = CmsMenu::create($data);
            $this->selectedMenuId = $menu->id;
            $this->location = $menu->slug;
            session()->flash('message', __('Menu created successfully.'));
        }

        $this->showMenuModal = false;
    }

    public function deleteMenu(int $id): void
    {
        $menu = CmsMenu::findOrFail($id);
        $menu->delete();

        if ($this->selectedMenuId === $id) {
            $first = CmsMenu::orderBy('id')->first();
            $this->selectedMenuId = $first?->id;
            $this->location = $first?->slug ?? 'header';
        }

        session()->flash('message', __('Menu deleted.'));
    }

    /* -------------------------------------------------------------
     | MENU ITEM ACTIONS (CRUD & COMPATIBILITY)
     | ------------------------------------------------------------- */

    public function openCreateMenuItem(?int $parentId = null, ?int $column = 1): void
    {
        if (! $this->selectedMenuId) {
            return;
        }

        $nextOrder = (CmsMenuItem::where('menu_id', $this->selectedMenuId)
            ->where('parent_id', $parentId)
            ->max('order') ?? 0) + 1;

        $this->resetItemForm();
        $this->item_parent_id = $parentId;
        $this->parentId = $parentId;
        $this->item_mega_column = $column > 0 ? $column : 1;
        $this->megaMenuColumn = $this->item_mega_column;
        $this->item_order = $nextOrder;
        $this->item_status = 1;
        $this->showMenuItemModal = true;
    }

    public function editMenuItem(int $id): void
    {
        $item = CmsMenuItem::findOrFail($id);
        $this->editingMenuItemId = $item->id;
        $this->editingItemId = $item->id;

        $this->item_parent_id = $item->parent_id;
        $this->parentId = $item->parent_id;

        $this->item_title = $item->title ?: ($item->label ?? '');
        $this->label = $this->item_title;

        $this->item_description = $item->description;
        $this->item_content = $item->content;
        $this->item_type = $item->type ?: ($item->page_id ? 'page' : ($item->post_id ? 'post' : 'custom'));

        $this->item_is_mega_menu = (bool) $item->is_mega_menu;
        $this->isMegaMenu = $this->item_is_mega_menu;

        $this->item_mega_menu_columns = (int) ($item->mega_menu_columns ?: 4);
        $this->megaMenuColumns = $this->item_mega_menu_columns;

        $this->item_mega_column = (int) ($item->mega_column ?: ($item->mega_menu_column ?: 1));
        $this->megaMenuColumn = $this->item_mega_column;

        $this->item_existing_image = $item->image ?: $item->mega_menu_image;
        $this->item_existing_image_url = $item->megaMenuImageUrl();
        $this->item_image_cleared = false;
        $this->megaMenuImage = $this->item_existing_image;
        $this->item_image = null;
        $this->resetPickedMedia();

        $this->item_page_id = $item->page_id;
        $this->pageId = $item->page_id;

        $this->item_post_id = $item->post_id;
        $this->postId = $item->post_id;

        $this->item_url = $item->url ?: '';
        $this->url = $this->item_url;
        $this->item_site_path = $item->type === 'site' ? $item->url : null;

        $this->item_target = $item->target ?: '_self';
        $this->item_icon = $item->icon;

        $this->item_badge_text = $item->badge_text ?: ($item->badge ?? '');
        $this->badge = $this->item_badge_text;

        $this->cssClass = $item->css_class;
        $this->item_badge_color = $item->badge_color ?: '#ef4444';
        $this->item_order = (int) $item->order;
        $this->item_status = (int) ($item->status ?? 1);

        $this->resetValidation();
        $this->showMenuItemModal = true;
    }

    public function editItem(int $id): void
    {
        $this->editMenuItem($id);
    }

    public function removeMenuItemImage(): void
    {
        $this->item_existing_image = null;
        $this->item_existing_image_url = null;
        $this->item_image_cleared = true;
        $this->megaMenuImage = null;
        $this->item_image = null;
        $this->resetPickedMedia();
    }

    public function toggleMegaMenu(int $id): void
    {
        $item = CmsMenuItem::findOrFail($id);
        if ($item->parent_id) {
            session()->flash('message', __('Mega menus can only be enabled on top-level navigation links.'));

            return;
        }

        $item->is_mega_menu = ! $item->is_mega_menu;
        if ($item->is_mega_menu && ! $item->mega_menu_columns) {
            $item->mega_menu_columns = 4;
        }
        $item->save();

        session()->flash('message', $item->is_mega_menu ? __('Mega menu layout enabled.') : __('Mega menu layout disabled.'));
    }

    public function addItem(): void
    {
        $this->syncLegacyPropertiesToSchool();
        $this->saveMenuItem();
    }

    public function saveMenuItem(): void
    {
        $this->syncLegacyPropertiesToSchool();

        if (! $this->selectedMenuId) {
            $first = CmsMenu::where('slug', $this->location)->first() ?? CmsMenu::first();
            if ($first) {
                $this->selectedMenuId = $first->id;
            }
        }

        // Custom validation for URL safety
        $urlRule = function ($attribute, $value, $fail) {
            if ($value === null || $value === '') {
                return;
            }
            if (str_starts_with(strtolower($value), 'javascript:')) {
                $fail(__('The :attribute format is invalid or unsafe.', ['attribute' => $attribute]));

                return;
            }
            if (! CmsMenuItem::isSafeUrl($value)) {
                $fail(__('The :attribute format is invalid.', ['attribute' => $attribute]));
            }
        };

        // Determine effective parent
        $parentId = $this->item_parent_id ?: $this->parentId;

        $this->validate([
            'item_title' => 'required|string|max:191',
            'item_description' => 'nullable|string|max:255',
            'item_content' => 'nullable|string',
            'item_parent_id' => [
                'nullable',
                Rule::exists('cms_menu_items', 'id')->where(fn ($q) => $q->where('menu_id', $this->selectedMenuId)),
            ],
            'item_type' => 'nullable|string|in:site,page,post,category,product,custom,image,html',
            'item_is_mega_menu' => [
                'boolean',
                function ($attribute, $value, $fail) use ($parentId) {
                    if ($value && $parentId) {
                        $fail(__('Mega menu cannot be enabled on a nested item.'));
                    }
                },
            ],
            'item_mega_menu_columns' => 'nullable|integer|in:2,3,4,5,6',
            'item_mega_column' => 'nullable|integer|min:1|max:6',
            'item_page_id' => 'nullable|required_if:item_type,page|exists:cms_pages,id',
            'item_site_path' => [
                'nullable',
                'required_if:item_type,site',
                Rule::in(array_keys(CmsMenuItem::sitePages())),
            ],
            'item_post_id' => 'nullable|required_if:item_type,post|exists:cms_posts,id',
            'item_category_id' => 'nullable|required_if:item_type,category|exists:categories,id',
            'item_product_id' => 'nullable|required_if:item_type,product|exists:products,id',
            'item_url' => ['nullable', $urlRule],
            'url' => ['nullable', $urlRule],
            'item_target' => 'nullable|string|in:_self,_blank',
            'item_icon' => 'nullable|string|max:100',
            'item_image' => 'nullable|image|max:3072',
            'item_badge_text' => 'nullable|string|max:50',
            'item_badge_color' => 'nullable|string|max:30',
            'item_order' => 'nullable|integer|min:0',
            'item_status' => 'nullable|in:0,1',
        ]);

        $editingId = $this->editingMenuItemId ?: $this->editingItemId;

        // Prevent circular parent dependency
        if ($editingId && $parentId) {
            if ((int) $parentId === (int) $editingId) {
                $this->addError('parentId', __('An item cannot be its own parent.'));
                $this->addError('item_parent_id', __('An item cannot be its own parent.'));

                return;
            }

            // Check recursive descendants
            $descendants = $this->getDescendantIds((int) $editingId);
            if (in_array((int) $parentId, $descendants, true)) {
                $this->addError('parentId', __('Cannot set a descendant as the parent.'));
                $this->addError('item_parent_id', __('Cannot set a descendant as the parent.'));

                return;
            }
        }

        $isMega = $parentId ? false : (bool) ($this->item_is_mega_menu || $this->isMegaMenu);

        // Process image uploads
        $uploadedImage = $this->item_image ?: $this->megaMenuImageUpload;
        $previousImage = $this->item_existing_image ?: $this->megaMenuImage;
        $pickedImage = $this->pickedMediaIdsFor('item_image') !== [];
        $imagePath = $uploadedImage !== null || $pickedImage || $this->item_image_cleared ? null : $previousImage;

        // URL destination
        $finalUrl = ($this->item_url !== null && $this->item_url !== '') ? $this->item_url : (($this->url !== '') ? $this->url : null);
        if ($this->item_type === 'custom' && ($finalUrl === null || $finalUrl === '')) {
            $finalUrl = '#';
        }
        if ($this->item_type === 'site' && $this->item_site_path) {
            $finalUrl = $this->item_site_path;
        } elseif ($this->item_type === 'category' && $this->item_category_id) {
            $cat = Category::find($this->item_category_id);
            $finalUrl = $cat ? '/category/'.$cat->slug : $finalUrl;
        } elseif ($this->item_type === 'product' && $this->item_product_id) {
            $prod = Product::find($this->item_product_id);
            $finalUrl = $prod ? '/product/'.$prod->slug : $finalUrl;
        }

        $title = $this->item_title ?: $this->label;
        $badgeText = $this->item_badge_text ?: $this->badge;
        $order = $this->item_order ?: ((CmsMenuItem::where('menu_id', $this->selectedMenuId)->where('parent_id', $parentId)->max('order') ?? 0) + 1);

        $data = [
            'menu_id' => $this->selectedMenuId,
            'parent_id' => $parentId ?: null,
            'title' => $title,
            'label' => $title,
            'description' => $this->item_description ?: null,
            'content' => $this->item_content ?: null,
            'type' => $this->item_type ?: 'custom',
            'is_mega_menu' => $isMega,
            'mega_menu_columns' => $this->item_mega_menu_columns ?: ($this->megaMenuColumns ?: 4),
            'mega_column' => $parentId ? ($this->item_mega_column ?: ($this->megaMenuColumn ?: 1)) : 1,
            'mega_menu_column' => $parentId ? ($this->item_mega_column ?: ($this->megaMenuColumn ?: 1)) : 1,
            'page_id' => ($this->item_type === 'page' || $this->pageId) ? ($this->item_page_id ?: $this->pageId) : null,
            'post_id' => ($this->item_type === 'post' || $this->postId) ? ($this->item_post_id ?: $this->postId) : null,
            'url' => $finalUrl ?: null,
            'target' => $this->item_target ?: '_self',
            'icon' => $this->item_icon ?: null,
            'image' => $imagePath,
            'mega_menu_image' => $imagePath,
            'badge' => $badgeText ?: null,
            'badge_text' => $badgeText ?: null,
            'badge_color' => $this->item_badge_color ?: '#ef4444',
            'css_class' => $this->cssClass ?: null,
            'order' => $order,
            'status' => $this->item_status ?? 1,
        ];

        if ($editingId) {
            $item = CmsMenuItem::findOrFail($editingId);
            $item->update($data);
            session()->flash('message', __('Menu link updated successfully.'));
        } else {
            $item = CmsMenuItem::create($data);
            session()->flash('message', __('Menu link created successfully.'));
        }

        if ($uploadedImage !== null) {
            $item->addLibraryMedia($uploadedImage, 'image', 'menu-'.$item->menu_id);
            // A fresh upload replaces a picture that was only pointed at.
            $item->clearLibraryMediaReferences('image');
            $this->deleteLegacyImage($previousImage);
        } elseif ($pickedImage) {
            $this->referencePickedMedia($item, 'item_image', 'image');
            $this->deleteLegacyImage($previousImage);
        } elseif ($this->item_image_cleared) {
            $item->clearLibraryMedia('image');
            $this->deleteLegacyImage($previousImage);
        }

        $this->showMenuItemModal = false;
        $this->resetItemForm();
    }

    public function moveMenuItem(int $id, string $direction): void
    {
        $item = CmsMenuItem::findOrFail($id);
        $query = CmsMenuItem::where('menu_id', $item->menu_id);

        if ($item->parent_id) {
            $query->where('parent_id', $item->parent_id);
        } else {
            $query->whereNull('parent_id');
        }

        if ($direction === 'up') {
            $target = (clone $query)->where('order', '<', $item->order)->orderBy('order', 'desc')->first();
        } else {
            $target = (clone $query)->where('order', '>', $item->order)->orderBy('order', 'asc')->first();
        }

        if ($target) {
            $temp = $item->order;
            $item->order = $target->order;
            $target->order = $temp;
            $item->save();
            $target->save();
        }
    }

    public function moveItem(int $id, string $direction): void
    {
        $this->moveMenuItem($id, $direction);
    }

    public function moveUp(int $id): void
    {
        $this->moveMenuItem($id, 'up');
    }

    public function moveDown(int $id): void
    {
        $this->moveMenuItem($id, 'down');
    }

    public function deleteMenuItem(int $id): void
    {
        CmsMenuItem::findOrFail($id)->delete();
        session()->flash('message', __('Navigation link deleted.'));
    }

    public function deleteItem(int $id): void
    {
        $this->deleteMenuItem($id);
    }

    /* -------------------------------------------------------------
     | VISUAL MULTI-COLUMN MEGA MENU BUILDER ACTIONS
     | ------------------------------------------------------------- */

    public function openMegaBuilder(int $id): void
    {
        $item = CmsMenuItem::findOrFail($id);
        $this->megaBuilderItemId = $item->id;
        $data = $item->mega_menu_data ?: [];
        $this->mega_header_title = $data['header_title'] ?? $item->description ?? '';
        $this->mega_columns = $data['columns'] ?? [];

        if (empty($this->mega_columns)) {
            $this->mega_columns = [
                [
                    'type' => 'links',
                    'heading' => 'Featured Section #1',
                    'items' => [
                        ['label' => 'Explore Items', 'url' => '/shop', 'badge' => 'New'],
                        ['label' => 'Best Sellers', 'url' => '/shop', 'badge' => 'Hot'],
                    ],
                    'bottom_button_label' => 'View All',
                    'bottom_button_url' => '/shop',
                ],
                [
                    'type' => 'links',
                    'heading' => 'Featured Section #2',
                    'items' => [
                        ['label' => 'Special Deals', 'url' => '/deals', 'badge' => 'Sale'],
                        ['label' => 'Trending Collections', 'url' => '/collections', 'badge' => ''],
                    ],
                    'bottom_button_label' => 'View Collections',
                    'bottom_button_url' => '/collections',
                ],
                [
                    'type' => 'thumbnails',
                    'heading' => 'Featured Spotlight',
                    'items' => [
                        ['title' => 'Special Selection', 'subtitle' => 'Handcrafted goods with free shipping', 'image' => '', 'url' => '/shop', 'badge' => 'Popular'],
                    ],
                    'bottom_button_label' => 'Discover More',
                    'bottom_button_url' => '/shop',
                ],
            ];
        }

        $this->showMegaBuilderModal = true;
    }

    public function addMegaColumn(string $type): void
    {
        if (count($this->mega_columns) >= 4) {
            session()->flash('message', __('Mega menu cannot exceed 4 columns.'));

            return;
        }

        $num = count($this->mega_columns) + 1;
        if ($type === 'thumbnails') {
            $this->mega_columns[] = [
                'type' => 'thumbnails',
                'heading' => 'Featured Cards #'.$num,
                'items' => [
                    ['title' => 'Featured Item 1', 'subtitle' => 'Brief overview caption', 'image' => '', 'url' => '#', 'badge' => 'Hot'],
                ],
                'bottom_button_label' => 'Explore More',
                'bottom_button_url' => '#',
            ];
        } elseif ($type === 'promo') {
            $this->mega_columns[] = [
                'type' => 'promo',
                'heading' => 'Special Spotlight #'.$num,
                'content' => 'Highlight your top products, promotion, or season offer.',
                'image' => '',
                'button_label' => 'Learn More',
                'button_url' => '#',
                'badge' => 'Featured',
            ];
        } else {
            $this->mega_columns[] = [
                'type' => 'links',
                'heading' => 'Section Heading #'.$num,
                'items' => [
                    ['label' => 'Navigation Item #1', 'url' => '#', 'badge' => ''],
                ],
                'bottom_button_label' => 'View All Packages',
                'bottom_button_url' => '#',
            ];
        }
    }

    public function moveMegaColumn(int $index, string $direction): void
    {
        if ($direction === 'left' && $index > 0) {
            $temp = $this->mega_columns[$index];
            $this->mega_columns[$index] = $this->mega_columns[$index - 1];
            $this->mega_columns[$index - 1] = $temp;
        } elseif ($direction === 'right' && $index < count($this->mega_columns) - 1) {
            $temp = $this->mega_columns[$index];
            $this->mega_columns[$index] = $this->mega_columns[$index + 1];
            $this->mega_columns[$index + 1] = $temp;
        }
    }

    public function removeMegaColumn(int $index): void
    {
        unset($this->mega_columns[$index]);
        $this->mega_columns = array_values($this->mega_columns);
    }

    public function addMegaColumnItem(int $colIndex): void
    {
        if (! isset($this->mega_columns[$colIndex])) {
            return;
        }

        if (($this->mega_columns[$colIndex]['type'] ?? '') === 'thumbnails') {
            $this->mega_columns[$colIndex]['items'][] = [
                'title' => 'New Card Item',
                'subtitle' => '',
                'image' => '',
                'url' => '#',
                'badge' => '',
            ];
        } else {
            $this->mega_columns[$colIndex]['items'][] = [
                'label' => 'New Navigation Item',
                'url' => '#',
                'badge' => '',
            ];
        }
    }

    public function removeMegaColumnItem(int $colIndex, int $itemIndex): void
    {
        if (isset($this->mega_columns[$colIndex]['items'][$itemIndex])) {
            unset($this->mega_columns[$colIndex]['items'][$itemIndex]);
            $this->mega_columns[$colIndex]['items'] = array_values($this->mega_columns[$colIndex]['items']);
        }
    }

    public function saveMegaBuilder(): void
    {
        if (! $this->megaBuilderItemId) {
            return;
        }

        $item = CmsMenuItem::findOrFail($this->megaBuilderItemId);
        $colsCount = min(4, max(1, count($this->mega_columns)));
        $columns = array_map(function (array $column): array {
            if (($column['type'] ?? '') === 'promo' && array_key_exists('content', $column)) {
                $column['content'] = RichText::sanitize((string) $column['content']) ?? '';
            }

            return $column;
        }, array_slice($this->mega_columns, 0, 4));
        $this->mega_columns = $columns;

        $item->update([
            'is_mega_menu' => true,
            'mega_menu_columns' => $colsCount,
            'description' => $this->mega_header_title ?: $item->description,
            'mega_menu_data' => [
                'header_title' => $this->mega_header_title,
                'columns' => $columns,
            ],
        ]);

        $this->showMegaBuilderModal = false;
        session()->flash('message', __('Mega menu multi-column layout saved successfully.'));
    }

    /* -------------------------------------------------------------
     | LIVE DROPDOWN PREVIEW ACTIONS
     | ------------------------------------------------------------- */

    public function openMegaPreview(int $id): void
    {
        $this->previewMegaItemId = $id;
        $this->showMegaPreviewModal = true;
    }

    /* -------------------------------------------------------------
     | MEGA DESIGNER (FOR TEST COMPATIBILITY)
     | ------------------------------------------------------------- */

    public function openMegaDesigner(int $id): void
    {
        $this->activeMegaDesignItemId = $id;
        $this->openMegaBuilder($id);
    }

    public function closeMegaDesigner(): void
    {
        $this->activeMegaDesignItemId = null;
        $this->showMegaBuilderModal = false;
    }

    public function setItemColumn(int $itemId, int $column): void
    {
        $item = CmsMenuItem::findOrFail($itemId);
        $item->update([
            'mega_menu_column' => $column,
            'mega_column' => $column,
        ]);
        session()->flash('message', __('Item moved to column :col.', ['col' => $column]));
    }

    public function setMegaColumns(int $megaId, int $columns): void
    {
        $item = CmsMenuItem::findOrFail($megaId);
        $item->update([
            'mega_menu_columns' => $columns,
        ]);
        session()->flash('message', __('Columns updated to :count.', ['count' => $columns]));
    }

    /* -------------------------------------------------------------
     | HELPERS
     | ------------------------------------------------------------- */

    protected function syncLegacyPropertiesToSchool(): void
    {
        if ($this->label !== '' && $this->item_title === '') {
            $this->item_title = $this->label;
        } elseif ($this->item_title !== '' && $this->label === '') {
            $this->label = $this->item_title;
        }

        if ($this->pageId !== null && $this->item_page_id === null) {
            $this->item_page_id = $this->pageId;
            $this->item_type = 'page';
        }
        if ($this->postId !== null && $this->item_post_id === null) {
            $this->item_post_id = $this->postId;
            $this->item_type = 'post';
        }
        if ($this->parentId !== null && $this->item_parent_id === null) {
            $this->item_parent_id = $this->parentId;
        } elseif ($this->item_parent_id !== null && $this->parentId === null) {
            $this->parentId = $this->item_parent_id;
        }

        if ($this->url !== '' && $this->item_url === '') {
            $this->item_url = $this->url;
        } elseif ($this->item_url !== '' && $this->url === '') {
            $this->url = $this->item_url;
        }

        if ($this->isMegaMenu && ! $this->item_is_mega_menu) {
            $this->item_is_mega_menu = true;
        } elseif ($this->item_is_mega_menu && ! $this->isMegaMenu) {
            $this->isMegaMenu = true;
        }
        if ($this->megaMenuColumns && ! $this->item_mega_menu_columns) {
            $this->item_mega_menu_columns = $this->megaMenuColumns;
        } elseif ($this->item_mega_menu_columns && ! $this->megaMenuColumns) {
            $this->megaMenuColumns = $this->item_mega_menu_columns;
        }
        if ($this->megaMenuColumn && ! $this->item_mega_column) {
            $this->item_mega_column = $this->megaMenuColumn;
        } elseif ($this->item_mega_column && ! $this->megaMenuColumn) {
            $this->megaMenuColumn = $this->item_mega_column;
        }
        if ($this->badge && ! $this->item_badge_text) {
            $this->item_badge_text = $this->badge;
        }
    }

    /**
     * Remove a file that was uploaded before the media library existed.
     */
    private function deleteLegacyImage(?string $path): void
    {
        $path = trim((string) $path);

        if (
            $path === ''
            || str_starts_with($path, 'http://')
            || str_starts_with($path, 'https://')
            || str_starts_with($path, '/')
        ) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    protected function resetItemForm(): void
    {
        $this->reset([
            'editingMenuItemId', 'editingItemId', 'item_title', 'label', 'item_description', 'item_content',
            'item_page_id', 'pageId', 'item_post_id', 'postId', 'item_category_id', 'item_product_id',
            'item_url', 'url', 'item_site_path', 'item_icon', 'item_badge_text', 'badge', 'cssClass', 'item_image', 'item_existing_image',
            'item_existing_image_url', 'item_image_cleared', 'megaMenuImage', 'megaMenuImageUpload',
        ]);
        $this->item_parent_id = null;
        $this->parentId = null;
        $this->item_mega_column = 1;
        $this->megaMenuColumn = 1;
        $this->item_type = 'custom';
        $this->item_is_mega_menu = false;
        $this->isMegaMenu = false;
        $this->item_mega_menu_columns = 4;
        $this->megaMenuColumns = 4;
        $this->item_badge_color = '#ef4444';
        $this->item_target = '_self';
        $this->item_order = 1;
        $this->item_status = 1;
        $this->resetPickedMedia();
        $this->resetValidation();
    }

    protected function getDescendantIds(int $parentId): array
    {
        $ids = [];
        $children = CmsMenuItem::where('parent_id', $parentId)->pluck('id')->all();
        foreach ($children as $childId) {
            $ids[] = $childId;
            $ids = array_merge($ids, $this->getDescendantIds($childId));
        }

        return $ids;
    }

    public function render()
    {
        $menus = CmsMenu::withCount('items')->orderBy('id')->get();
        $selectedMenu = $this->selectedMenuId ? CmsMenu::find($this->selectedMenuId) : $menus->first();

        if (! $selectedMenu && $menus->isEmpty()) {
            $selectedMenu = CmsMenu::create([
                'name' => 'Main Header Navigation',
                'title' => 'Main Header Navigation',
                'slug' => 'header',
                'location' => 'header',
                'status' => 1,
            ]);
            $menus = CmsMenu::withCount('items')->orderBy('id')->get();
            $this->selectedMenuId = $selectedMenu->id;
        }

        $activeMenuItems = new EloquentCollection;
        $rootMenuItems = new EloquentCollection;

        if ($selectedMenu) {
            $activeMenuItems = $selectedMenu->rootItems()
                ->with(['children' => fn ($q) => $q->orderBy('order')->orderBy('id')])
                ->get();
            $rootMenuItems = $activeMenuItems;
        }

        $previewMegaMenu = $this->previewMegaItemId ? CmsMenuItem::find($this->previewMegaItemId) : null;
        $activeMegaDesignItem = $this->activeMegaDesignItemId ? CmsMenuItem::find($this->activeMegaDesignItemId) : null;

        $allPages = CmsPage::query()->orderBy('title')->get(['id', 'title', 'slug']);
        $allPosts = CmsPost::query()->orderBy('title')->get(['id', 'title', 'slug']);
        $allCategories = Category::query()->orderBy('name')->get(['id', 'name', 'slug']);
        $allProducts = Product::query()->orderBy('name')->take(60)->get(['id', 'name', 'slug']);
        $sitePages = CmsMenuItem::sitePages();

        $selectedMegaMenuParent = null;
        $currentParentId = $this->item_parent_id ?: $this->parentId;
        if ($currentParentId) {
            $parent = CmsMenuItem::find($currentParentId);
            if ($parent && $parent->is_mega_menu) {
                $selectedMegaMenuParent = $parent;
            }
        }

        return view('livewire.admin.cms-menus', [
            'menus' => $menus,
            'allMenus' => $menus,
            'menu' => $selectedMenu,
            'selectedMenu' => $selectedMenu,
            'activeMenuItems' => $activeMenuItems,
            'rootMenuItems' => $rootMenuItems,
            'previewMegaMenu' => $previewMegaMenu,
            'activeMegaDesignItem' => $activeMegaDesignItem,
            'selectedMegaMenuParent' => $selectedMegaMenuParent,
            'parentOptions' => $rootMenuItems->map(fn ($r) => ['id' => $r->id, 'label' => $r->title ?: $r->label]),
            'pages' => $allPages,
            'posts' => $allPosts,
            'allPages' => $allPages,
            'allPosts' => $allPosts,
            'allCategories' => $allCategories,
            'allProducts' => $allProducts,
            'sitePages' => $sitePages,
        ])->layout('components.layouts.admin', [
            'title' => __('CMS Navigation Menu Builder'),
        ]);
    }
}
