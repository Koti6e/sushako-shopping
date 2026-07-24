const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const miniCart = document.querySelector('[data-mini-cart]');
const miniCartItems = document.querySelector('[data-mini-cart-items]');
const miniCartSubtotal = document.querySelector('[data-mini-cart-subtotal]');
const toast = document.querySelector('[data-toast]');

function hidePageLoader() {
    document.documentElement.classList.remove('page-loading', 'page-transitioning');
    document.body?.classList.add('page-ready');
}

function showPageTransition() {
    document.body?.classList.remove('page-ready');
    document.documentElement.classList.add('page-transitioning');
}

window.addEventListener('load', hidePageLoader);
window.addEventListener('pageshow', hidePageLoader);
requestAnimationFrame(() => requestAnimationFrame(hidePageLoader));
setTimeout(hidePageLoader, 800);

function formatCurrency(value) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 0,
    }).format(value);
}

function updateCartBadges(count) {
    document.querySelectorAll('[data-cart-count]').forEach((badge) => {
        badge.textContent = count;
        badge.classList.add('badge-pulse');
        setTimeout(() => badge.classList.remove('badge-pulse'), 420);
    });
}

function showToast(message) {
    if (!toast) return;
    toast.textContent = message;
    toast.classList.add('toast--visible');
    setTimeout(() => toast.classList.remove('toast--visible'), 2200);
}

function renderMiniCart(cart, subtotal) {
    if (!miniCart || !miniCartItems || !miniCartSubtotal) return;

    miniCartItems.innerHTML = cart.map((item) => `
        <article class="mini-cart-line">
            <img src="${item.image}" alt="${item.product}">
            <div>
                <h3>${item.product}</h3>
                <p>${item.colour} / ${item.size} · Qty ${item.quantity}</p>
                <strong>${formatCurrency(item.price * item.quantity)}</strong>
            </div>
        </article>
    `).join('');
    miniCartSubtotal.textContent = formatCurrency(subtotal);
    miniCart.classList.add('mini-cart--open');
    miniCart.setAttribute('aria-hidden', 'false');
}

async function postJson(url, payload) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(payload),
    });

    if (!response.ok) {
        throw new Error('Request failed');
    }

    return response.json();
}

async function sendJson(url, method, payload = {}) {
    const response = await fetch(url, {
        method,
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(payload),
    });

    if (!response.ok) {
        throw new Error('Request failed');
    }

    return response.json();
}

function refreshCheckoutSummary(cart, subtotal, serverSummary = null) {
    const shipping = serverSummary?.shipping ?? 0;
    const shippingLabel = serverSummary?.shipping_label ?? (shipping ? formatCurrency(shipping) : 'Free');
    document.querySelectorAll('[data-checkout-summary], [data-cart-summary]').forEach((summary) => {
        summary.querySelector('[data-checkout-count]')?.replaceChildren(`${cart.length} item${cart.length === 1 ? '' : 's'}`);
        summary.querySelector('[data-checkout-subtotal]')?.replaceChildren(formatCurrency(subtotal));
        summary.querySelector('[data-checkout-shipping]')?.replaceChildren(shippingLabel);
        summary.querySelector('[data-checkout-shipping-note]')?.replaceChildren(serverSummary?.shipping_policy_text ?? '');
        summary.querySelector('[data-checkout-tax]')?.replaceChildren(formatCurrency(serverSummary?.tax ?? 0));
        summary.querySelector('[data-checkout-total]')?.replaceChildren(formatCurrency(serverSummary?.total ?? (subtotal + shipping)));

        cart.forEach((item) => {
            const key = `${item.slug}-${item.colour}-${item.size}`;
            const line = summary.querySelector(`[data-cart-key="${CSS.escape(key)}"]`);
            if (!line) return;
            line.querySelector('[data-line-total]')?.replaceChildren(formatCurrency(item.price * item.quantity));
            const quantityInput = line.querySelector('[data-cart-quantity]');
            if (quantityInput) quantityInput.value = item.quantity;
        });
    });
}

function syncBuyNowVariant() {
    const form = document.querySelector('.buy-now-form');
    if (!form) return;

    const colour = document.querySelector('[name="colour"]:checked')?.value;
    const size = document.querySelector('[name="size"]:checked')?.value;
    const quantity = document.querySelector('#quantity')?.value;

    if (colour) form.querySelector('[data-buy-now-colour]')?.setAttribute('value', colour);
    if (size) form.querySelector('[data-buy-now-size]')?.setAttribute('value', size);
    if (quantity) form.querySelector('[data-buy-now-quantity]')?.setAttribute('value', quantity);
}

function syncProductOptionPrice() {
    const selectedOption = document.querySelector('[data-option-price]:checked');
    const priceTarget = document.querySelector('[data-product-price]');

    if (!selectedOption || !priceTarget) return;

    priceTarget.replaceChildren(formatCurrency(Number(selectedOption.dataset.optionPrice)));
}

function fillCheckoutAddress(addressInput) {
    const fields = {
        customer_name: addressInput.dataset.customerName,
        customer_phone: addressInput.dataset.customerPhone,
        address_line_1: addressInput.dataset.addressLine1,
        address_line_2: addressInput.dataset.addressLine2,
        city: addressInput.dataset.city,
        pincode: addressInput.dataset.pincode,
        landmark: addressInput.dataset.landmark,
        delivery_location_url: addressInput.dataset.locationUrl,
    };

    Object.entries(fields).forEach(([name, value]) => {
        const field = document.querySelector(`[name="${name}"]`);
        if (field && value !== undefined) {
            field.value = value;
        }
    });
}

document.querySelector('[data-checkout-address]:checked') && fillCheckoutAddress(document.querySelector('[data-checkout-address]:checked'));

document.addEventListener('change', (event) => {
    const checkoutAddress = event.target.closest('[data-checkout-address]');

    if (checkoutAddress) {
        fillCheckoutAddress(checkoutAddress);
    }
});

document.addEventListener('click', async (event) => {
    const addButton = event.target.closest('[data-add-to-cart]');
    const wishlistButton = event.target.closest('[data-wishlist]');
    const closeButton = event.target.closest('[data-cart-close]');
    const shareButton = event.target.closest('[data-share-url]');
    const searchFocus = event.target.closest('[data-search-focus]');
    const filterToggle = event.target.closest('[data-filter-toggle]');
    const gstToggle = event.target.closest('[data-gst-toggle]');
    const businessType = event.target.closest('[data-business-type]');
    const cartUpdate = event.target.closest('[data-cart-update]');
    const cartRemove = event.target.closest('[data-cart-remove]');
    const currentLocation = event.target.closest('[data-use-current-location]');
    const navigationLink = event.target.closest('a[href]');

    if (navigationLink && !event.defaultPrevented) {
        const url = new URL(navigationLink.href, window.location.href);
        const isSamePageHash = url.pathname === window.location.pathname && url.search === window.location.search && url.hash;
        const isModifiedClick = event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0;
        const opensElsewhere = navigationLink.target && navigationLink.target !== '_self';
        const ignoredProtocol = !['http:', 'https:'].includes(url.protocol);

        if (!isSamePageHash && !isModifiedClick && !opensElsewhere && !ignoredProtocol) {
            showPageTransition();
        }
    }

    if (gstToggle) {
        const gstField = document.querySelector('[data-gst-number-field]');
        if (gstField) gstField.hidden = gstToggle.value !== 'yes';
    }

    if (businessType) {
        const otherField = document.querySelector('[data-other-business-field]');
        if (otherField) otherField.hidden = businessType.value !== 'Others';
    }

    if (currentLocation) {
        const input = document.querySelector('[data-location-url]');
        if (!navigator.geolocation || !input) {
            showToast('Location sharing is unavailable on this device');
            return;
        }

        currentLocation.disabled = true;
        currentLocation.textContent = 'Locating...';
        navigator.geolocation.getCurrentPosition((position) => {
            const { latitude, longitude } = position.coords;
            input.value = `https://www.google.com/maps?q=${latitude},${longitude}`;
            currentLocation.disabled = false;
            currentLocation.innerHTML = '<i class="fa-solid fa-location-crosshairs" aria-hidden="true"></i> Use Current Location';
            showToast('Delivery location added');
        }, () => {
            currentLocation.disabled = false;
            currentLocation.innerHTML = '<i class="fa-solid fa-location-crosshairs" aria-hidden="true"></i> Use Current Location';
            showToast('Unable to access location');
        }, { enableHighAccuracy: true, timeout: 10000 });
    }

    if (cartUpdate) {
        const line = cartUpdate.closest('[data-checkout-item]');
        const key = line?.dataset.cartKey;
        const quantity = Number(line?.querySelector('[data-cart-quantity]')?.value ?? 1);
        if (!key) return;

        try {
            const data = await sendJson(`/cart/${encodeURIComponent(key)}`, 'PATCH', { quantity });
            updateCartBadges(data.cart_count);
            refreshCheckoutSummary(data.cart, data.subtotal, data.summary);
            showToast(data.message ?? 'Cart updated');
        } catch {
            showToast('Unable to update cart');
        }
    }

    if (cartRemove) {
        const line = cartRemove.closest('[data-checkout-item]');
        const key = line?.dataset.cartKey;
        if (!key) return;

        try {
            const data = await sendJson(`/cart/${encodeURIComponent(key)}`, 'DELETE');
            line.remove();
            updateCartBadges(data.cart_count);
            refreshCheckoutSummary(data.cart, data.subtotal, data.summary);
            showToast(data.message ?? 'Removed from cart');
            if (data.cart_count === 0) window.location.href = '/cart';
        } catch {
            showToast('Unable to remove item');
        }
    }

    if (closeButton && miniCart) {
        miniCart.classList.remove('mini-cart--open');
        miniCart.setAttribute('aria-hidden', 'true');
    }

    if (searchFocus) {
        document.querySelector('#site-search')?.focus();
    }

    if (filterToggle) {
        document.querySelector('[data-mobile-filter-panel]')?.classList.toggle('is-open');
    }

    if (shareButton) {
        const url = shareButton.dataset.shareUrl;
        const title = shareButton.dataset.shareTitle ?? document.title;

        if (navigator.share) {
            try {
                await navigator.share({ title, url });
                return;
            } catch {
                return;
            }
        }

        await navigator.clipboard?.writeText(url);
        showToast('Product link copied');
    }

    if (addButton) {
        const quantityTarget = addButton.dataset.quantityTarget;
        const quantityInput = quantityTarget ? document.querySelector(quantityTarget) : null;
        const colour = document.querySelector('[name="colour"]:checked')?.value ?? addButton.dataset.colour;
        const size = document.querySelector('[name="size"]:checked')?.value ?? addButton.dataset.size;
        const quantity = quantityInput?.value ?? addButton.dataset.quantity ?? 1;
        const originalText = addButton.textContent;

        addButton.disabled = true;
        addButton.classList.add('button--loading');
        addButton.textContent = 'Adding...';

        try {
            const data = await postJson('/cart', {
                slug: addButton.dataset.slug,
                colour,
                size,
                quantity: Number(quantity),
            });

            addButton.classList.remove('button--loading');
            addButton.classList.add('button--added');
            addButton.textContent = '✓ Added';
            updateCartBadges(data.cart_count);
            renderMiniCart(data.cart, data.subtotal);
            showToast(data.message ?? 'Added to Cart');
            setTimeout(() => {
                addButton.disabled = false;
                addButton.classList.remove('button--added');
                addButton.textContent = originalText;
            }, 1600);
        } catch {
            addButton.disabled = false;
            addButton.classList.remove('button--loading');
            addButton.textContent = originalText;
            showToast('Unable to add item');
        }
    }

    if (wishlistButton) {
        try {
            const data = await postJson('/wishlist', { slug: wishlistButton.dataset.slug });
            document.querySelectorAll('[data-wishlist-count]').forEach((badge) => {
                badge.textContent = data.wishlist_count;
            });
            wishlistButton.classList.add('button--added');
            showToast(data.message ?? 'Wishlist updated');
            setTimeout(() => wishlistButton.classList.remove('button--added'), 900);
        } catch {
            showToast('Unable to update wishlist');
        }
    }
});

document.addEventListener('change', (event) => {
    if (event.target.matches('[name="colour"], [name="size"], #quantity')) {
        syncBuyNowVariant();
        syncProductOptionPrice();
    }
});

document.addEventListener('input', (event) => {
    if (event.target.matches('#quantity')) {
        syncBuyNowVariant();
    }
});

document.addEventListener('submit', (event) => {
    if (event.target instanceof HTMLFormElement && event.target.matches('.buy-now-form')) {
        syncBuyNowVariant();
    }

    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (form.dataset.noPageLoader !== undefined) return;
    if ((form.target && form.target !== '_self') || event.defaultPrevented) return;

    showPageTransition();
});

function initHeroCarousel() {
    const carousel = document.querySelector('[data-hero-carousel]');
    if (!carousel) return;

    const slides = [...carousel.querySelectorAll('[data-hero-slide]')];
    const dots = [...carousel.querySelectorAll('[data-hero-dot]')];
    const prev = carousel.querySelector('[data-hero-prev]');
    const next = carousel.querySelector('[data-hero-next]');
    let active = 0;
    let timer;
    let touchStartX = 0;

    const show = (index) => {
        active = (index + slides.length) % slides.length;
        slides.forEach((slide, slideIndex) => slide.classList.toggle('is-active', slideIndex === active));
        dots.forEach((dot, dotIndex) => dot.classList.toggle('is-active', dotIndex === active));
    };

    const restart = () => {
        clearInterval(timer);
        timer = setInterval(() => show(active + 1), 5000);
    };

    prev?.addEventListener('click', () => {
        show(active - 1);
        restart();
    });

    next?.addEventListener('click', () => {
        show(active + 1);
        restart();
    });

    dots.forEach((dot, index) => dot.addEventListener('click', () => {
        show(index);
        restart();
    }));

    carousel.addEventListener('touchstart', (event) => {
        touchStartX = event.touches[0].clientX;
    }, { passive: true });

    carousel.addEventListener('touchend', (event) => {
        const distance = event.changedTouches[0].clientX - touchStartX;
        if (Math.abs(distance) < 42) return;
        show(distance > 0 ? active - 1 : active + 1);
        restart();
    }, { passive: true });

    restart();
}

function initProductGallery() {
    const gallery = document.querySelector('[data-product-gallery]');
    if (!gallery) return;

    const slides = [...gallery.querySelectorAll('[data-product-slide]')];
    const thumbs = [...gallery.querySelectorAll('[data-product-gallery-thumb]')];
    const prev = gallery.querySelector('[data-product-gallery-prev]');
    const next = gallery.querySelector('[data-product-gallery-next]');
    let active = 0;
    let timer;
    let touchStartX = 0;

    const show = (index) => {
        active = (index + slides.length) % slides.length;
        slides.forEach((slide, slideIndex) => slide.classList.toggle('is-active', slideIndex === active));
        thumbs.forEach((thumb, thumbIndex) => thumb.classList.toggle('is-active', thumbIndex === active));
    };

    const restart = () => {
        clearInterval(timer);
        if (slides.length > 1) timer = setInterval(() => show(active + 1), 4500);
    };

    prev?.addEventListener('click', () => {
        show(active - 1);
        restart();
    });

    next?.addEventListener('click', () => {
        show(active + 1);
        restart();
    });

    thumbs.forEach((thumb, index) => thumb.addEventListener('click', () => {
        show(index);
        restart();
    }));

    gallery.addEventListener('mouseenter', () => clearInterval(timer));
    gallery.addEventListener('mouseleave', restart);
    gallery.addEventListener('touchstart', (event) => {
        touchStartX = event.touches[0].clientX;
    }, { passive: true });
    gallery.addEventListener('touchend', (event) => {
        const distance = event.changedTouches[0].clientX - touchStartX;
        if (Math.abs(distance) < 42) return;
        show(distance > 0 ? active - 1 : active + 1);
        restart();
    }, { passive: true });

    show(0);
    restart();
}

function initWhatsappPulse() {
    const float = document.querySelector('[data-whatsapp-float]');
    if (!float) return;

    const pulse = () => {
        float.classList.add('is-attention');
        setTimeout(() => float.classList.remove('is-attention'), 2000);
    };

    setTimeout(pulse, 1800);
    setInterval(pulse, 60000);
}

function initShopFilters() {
    const shop = document.querySelector('[data-shop-experience]');
    if (!shop) return;

    const cards = [...shop.querySelectorAll('[data-product-card]')];
    const search = shop.querySelector('[data-filter-search]');
    const price = shop.querySelector('[data-price-filter]');
    const priceOutput = shop.querySelector('[data-price-output]');
    const count = shop.querySelector('[data-product-count]');
    const sort = shop.querySelector('[data-sort-filter]');
    const grid = shop.querySelector('[data-product-grid]');
    const emptyState = shop.querySelector('[data-shop-empty]');
    const active = { category: '', options: {} };

    const selectedValues = (selector) => [...shop.querySelectorAll(`${selector}:checked`)].map((input) => input.value);

    const setCategoryToggle = (buttons, currentButton) => {
        const value = currentButton.dataset.categoryFilter;
        active.category = active.category === value ? '' : value;
        buttons.forEach((button) => button.classList.toggle('is-active', button.dataset.categoryFilter === active.category));
        apply();
    };

    const setOptionToggle = (buttons, currentButton) => {
        const key = currentButton.dataset.optionFilter;
        const value = currentButton.dataset.optionValue;
        active.options[key] = active.options[key] === value ? '' : value;
        buttons
            .filter((button) => button.dataset.optionFilter === key)
            .forEach((button) => button.classList.toggle('is-active', button.dataset.optionValue === active.options[key]));
        apply();
    };

    const matchesCategory = (card) => {
        if (!active.category) return true;
        const value = active.category.toLowerCase();
        if (['new arrivals', 'best sellers', 'sale', 'offers'].includes(value)) {
            if (value === 'new arrivals') return card.dataset.new === '1';
            if (value === 'best sellers') return card.dataset.bestSelling === '1';
            if (value === 'sale') return Number(card.dataset.discount) >= 30;
            return true;
        }

        return card.dataset.department.toLowerCase() === value
            || card.dataset.departmentSlug.toLowerCase() === value.replaceAll(' ', '-').replaceAll('&', '').replaceAll('--', '-')
            || card.dataset.category.toLowerCase() === value
            || card.dataset.subcategory.toLowerCase() === value;
    };

    const matchesDynamicOptions = (card) => {
        const filters = JSON.parse(card.dataset.filters || '{}');

        return Object.entries(active.options).every(([key, value]) => {
            if (!value) return true;
            const options = filters[key] || [];
            return options.map(String).includes(value);
        });
    };

    const apply = () => {
        const query = search?.value.trim().toLowerCase() ?? '';
        const maxPrice = Number(price?.value ?? 5000);
        const brands = selectedValues('[data-brand-filter]');
        const discounts = selectedValues('[data-discount-filter]').map(Number);
        const ratings = selectedValues('[data-rating-filter]').map(Number);
        const inStockOnly = shop.querySelector('[data-availability-filter]')?.checked ?? false;

        if (priceOutput) priceOutput.textContent = formatCurrency(maxPrice);

        let visibleCards = cards.filter((card) => {
            const namesMatch = !query || card.dataset.name.includes(query);
            const priceMatch = Number(card.dataset.price) <= maxPrice;
            const brandMatch = brands.length === 0 || brands.includes(card.dataset.brand);
            const stockMatch = !inStockOnly || card.dataset.available === '1';
            const discountMatch = discounts.length === 0 || Number(card.dataset.discount) >= Math.min(...discounts);
            const ratingMatch = ratings.length === 0 || Number(card.dataset.rating) >= Math.min(...ratings);

            return namesMatch && matchesCategory(card) && matchesDynamicOptions(card) && priceMatch && brandMatch && stockMatch && discountMatch && ratingMatch;
        });

        const sortValue = sort?.value ?? 'featured';
        visibleCards.sort((a, b) => {
            if (sortValue === 'price_asc') return Number(a.dataset.price) - Number(b.dataset.price);
            if (sortValue === 'price_desc') return Number(b.dataset.price) - Number(a.dataset.price);
            if (sortValue === 'rating_desc') return Number(b.dataset.rating) - Number(a.dataset.rating);
            if (sortValue === 'best_selling') return Number(b.dataset.reviews) - Number(a.dataset.reviews);
            if (sortValue === 'newest') return Number(b.dataset.new) - Number(a.dataset.new);
            return 0;
        });

        cards.forEach((card) => card.hidden = true);
        visibleCards.forEach((card) => {
            card.hidden = false;
            grid?.appendChild(card);
        });

        if (count) count.textContent = visibleCards.length;
        if (emptyState) emptyState.hidden = visibleCards.length > 0;
    };

    shop.querySelectorAll('[data-category-filter]').forEach((button) => {
        button.addEventListener('click', () => setCategoryToggle([...shop.querySelectorAll('[data-category-filter]')], button));
    });
    shop.querySelectorAll('[data-option-filter]').forEach((button) => {
        button.addEventListener('click', () => setOptionToggle([...shop.querySelectorAll('[data-option-filter]')], button));
    });

    [search, price, sort, ...shop.querySelectorAll('input[type="checkbox"]')].forEach((control) => {
        control?.addEventListener('input', apply);
        control?.addEventListener('change', apply);
    });

    apply();
}

initHeroCarousel();
initProductGallery();
initWhatsappPulse();
initShopFilters();
