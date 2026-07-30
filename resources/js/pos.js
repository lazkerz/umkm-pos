document.addEventListener('alpine:init', () => {
    Alpine.data('posCart', (config) => ({
        products: config.products,
        categories: config.categories,
        customers: config.customers,
        promotions: config.promotions,
        channel: config.channel,
        checkoutUrl: config.checkoutUrl,
        customerStoreUrl: config.customerStoreUrl,
        csrfToken: config.csrfToken,

        search: '',
        activeCategory: null,
        cart: {}, // { [product_id]: qty }
        customerId: '',
        promotionId: '',
        paymentMethod: 'cash',
        submitting: false,
        errors: {},

        quickAdd: { open: false, name: '', phone: '', email: '', submitting: false, error: null },

        get filteredProducts() {
            const term = this.search.trim().toLowerCase();
            return this.products.filter((p) => {
                const matchesCategory = !this.activeCategory || p.category_id === this.activeCategory;
                const matchesSearch = !term || p.name.toLowerCase().includes(term);
                return matchesCategory && matchesSearch;
            });
        },

        get cartLines() {
            return Object.entries(this.cart)
                .filter(([, qty]) => qty > 0)
                .map(([id, qty]) => {
                    const product = this.products.find((p) => p.id === Number(id));
                    return {
                        id: Number(id),
                        product,
                        qty,
                        lineTotal: product ? product.price * qty : 0,
                    };
                });
        },

        get isEmpty() {
            return this.cartLines.length === 0;
        },

        get subtotal() {
            return this.cartLines.reduce((sum, line) => sum + line.lineTotal, 0);
        },

        get selectedPromotion() {
            return this.promotions.find((p) => p.id === Number(this.promotionId)) || null;
        },

        get discount() {
            const promo = this.selectedPromotion;
            if (!promo) return 0;
            return promo.type === 'percentage'
                ? this.subtotal * (promo.value / 100)
                : Math.min(promo.value, this.subtotal);
        },

        get total() {
            return Math.max(this.subtotal - this.discount, 0);
        },

        // Agregasi kebutuhan bahan baku per stock_item_id, mirip perhitungan server -
        // ini cuma warning non-blocking, validasi final tetap di server saat submit.
        get stockWarnings() {
            const needed = {}; // stock_item_id -> { qty, availableQty }

            this.cartLines.forEach((line) => {
                if (!line.product) return;
                line.product.recipes.forEach((recipe) => {
                    const entry = needed[recipe.stock_item_id] || { qty: 0, available: recipe.available_qty };
                    entry.qty += recipe.quantity_needed * line.qty;
                    needed[recipe.stock_item_id] = entry;
                });
            });

            return Object.entries(needed)
                .filter(([, entry]) => entry.qty > entry.available)
                .map(([stockItemId, entry]) => ({ stockItemId, ...entry }));
        },

        addToCart(id) {
            this.cart[id] = (this.cart[id] || 0) + 1;
        },

        decrement(id) {
            if (!this.cart[id]) return;
            this.cart[id] -= 1;
            if (this.cart[id] <= 0) delete this.cart[id];
        },

        formatMoney(amount) {
            return 'Rp ' + Math.round(amount).toLocaleString('id-ID');
        },

        async submit() {
            if (this.isEmpty || this.submitting) return;

            this.submitting = true;
            this.errors = {};

            const payload = {
                channel: this.channel,
                customer_id: this.customerId || null,
                promotion_id: this.promotionId || null,
                payment_method: this.paymentMethod,
                items: this.cartLines.map((line) => ({
                    product_id: line.id,
                    quantity: line.qty,
                })),
            };

            try {
                const response = await fetch(this.checkoutUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify(payload),
                });

                const data = await response.json();

                if (response.ok) {
                    window.location = data.redirect;
                    return;
                }

                if (response.status === 422) {
                    this.errors = data.errors || {};
                } else {
                    this.errors = { items: [data.message || 'Terjadi kesalahan, coba lagi.'] };
                }
            } catch (e) {
                this.errors = { items: ['Gagal terhubung ke server. Cek koneksi lalu coba lagi.'] };
            } finally {
                this.submitting = false;
            }
        },

        openQuickAdd() {
            this.quickAdd = { open: true, name: '', phone: '', email: '', submitting: false, error: null };
        },

        async submitQuickAdd() {
            if (!this.quickAdd.name.trim() || this.quickAdd.submitting) return;

            this.quickAdd.submitting = true;
            this.quickAdd.error = null;

            try {
                const response = await fetch(this.customerStoreUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({
                        name: this.quickAdd.name,
                        phone: this.quickAdd.phone,
                        email: this.quickAdd.email,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    this.quickAdd.error = data.message || 'Gagal menambah customer.';
                    return;
                }

                this.customers.push(data);
                this.customerId = String(data.id);
                this.quickAdd.open = false;
            } catch (e) {
                this.quickAdd.error = 'Gagal terhubung ke server.';
            } finally {
                this.quickAdd.submitting = false;
            }
        },
    }));
});
