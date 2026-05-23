@push('style')
    <style>
        [x-cloak] { display: none !important; }
        .suggestion-list {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            z-index: 1050 !important;
        }
        .active {
            background-color: #f8f9fa;
        }
        .container-that-wraps-the-dropdown {
            overflow: visible !important;
        }
    </style>
@endpush

<div style="position: relative; overflow: visible; z-index: 1;"
 x-data="{
    open: false,
    selectedIndex: -1,
    init() {
        // Reopen suggestions when input changes
        const input = this.$el.querySelector('.productInput');
        input.addEventListener('input', () => {
            if (!this.open) {
                this.open = true;
                this.selectedIndex = -1;
            }
        });
    },
    scrollIntoView() {
        this.$nextTick(() => {
            const items = this.$refs.suggestionList?.children;
            if (items && items.length > 0 && this.selectedIndex >= 0) {
                items[this.selectedIndex].scrollIntoView({ block: 'nearest' });
            }
        });
    }
}"
     x-on:keydown.escape="open = false; selectedIndex = -1"
     x-on:click.away="open = false; selectedIndex = -1"
     class="position-relative w-100 h-100"
>
    <input class="form-control productInput" type="text"
           wire:model.live.debounce.300ms="query"
           x-on:focus="open = true; selectedIndex = -1"
           x-on:keydown.arrow-down.prevent="if (open) {
            const items = $refs.suggestionList?.children;
            if (!items || items.length === 0) return;
            selectedIndex = (selectedIndex >= items.length - 1) ? 0 : selectedIndex + 1;
            scrollIntoView();
        }"
           x-on:keydown.arrow-up.prevent="if (open) {
            const items = $refs.suggestionList?.children;
            if (!items || items.length === 0) return;
            selectedIndex = (selectedIndex <= 0) ? items.length - 1 : selectedIndex - 1;
            scrollIntoView();
        }"
           x-on:keydown.enter.prevent="if (open && selectedIndex !== -1) {
            const items = $refs.suggestionList?.children;
            if (items && items[selectedIndex]) {
                items[selectedIndex].click();
                open = false;
                selectedIndex = -1;
            }
        }"
           placeholder="Search products..."
           role="combobox"
           aria-expanded="false"
           aria-haspopup="listbox"
           required
    >

    @if(!empty($products))
        <ul x-ref="suggestionList"
            x-show="open"
            class="list-group position-absolute w-100 shadow-sm suggestionList"
            style="top: 100%; left: 0; z-index: 1050; background: white; width: 100%; max-height: 250px; overflow-y: auto; margin-top: 2px; border: 1px solid #dee2e6; border-radius: 0.25rem;">
            @foreach($products as $key => $product)
                <li wire:key="product-{{ $product['id'] }}"
                    class="list-group-item list-group-item-action cursor-pointer"
                    :class="{ 'active': selectedIndex === {{ $key }} }"
                    x-on:mouseenter="selectedIndex = {{ $key }}"
                    wire:click="selectProduct({{ $product['id'] }}, {{ $index }})"
                    role="option"
                >
                    {{ $product['name'] }} {{\Illuminate\Support\Str::limit($product['strength'],3,'')}} {{ \Illuminate\Support\Str::limit($product['type'],3,'') }} - {{\Illuminate\Support\Str::limit($product['company']['name'],3,'')}}
                </li>
            @endforeach
        </ul>
    @endif

</div>
