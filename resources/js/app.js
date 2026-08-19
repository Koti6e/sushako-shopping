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

function setAdminSidebarState(isOpen, trigger = null) {
    const sidebar = document.querySelector('[data-admin-sidebar]');
    const backdrop = document.querySelector('[data-admin-sidebar-close].admin-sidebar-backdrop');
    const toggles = document.querySelectorAll('[data-admin-sidebar-open]');

    if (!sidebar) return;

    sidebar.classList.toggle('is-open', isOpen);
    document.body?.classList.toggle('admin-sidebar-open', isOpen);

    if (backdrop) {
        backdrop.hidden = !isOpen;
    }

    toggles.forEach((toggle) => {
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    if (isOpen) {
        window.adminSidebarReturnFocus = trigger;
        sidebar.querySelector('a[href], button:not([disabled]), summary, [tabindex]:not([tabindex="-1"])')?.focus();
    } else if (window.adminSidebarReturnFocus instanceof HTMLElement) {
        window.adminSidebarReturnFocus.focus();
        window.adminSidebarReturnFocus = null;
    }
}

window.addEventListener('load', hidePageLoader);
window.addEventListener('pageshow', hidePageLoader);
requestAnimationFrame(() => requestAnimationFrame(hidePageLoader));
setTimeout(hidePageLoader, 800);

document.addEventListener('click', (event) => {
    const button = event.target.closest?.('[data-google-auth-button]');

    if (!button) return;

    if (button.classList.contains('is-loading') || button.getAttribute('aria-disabled') === 'true') {
        event.preventDefault();
        return;
    }

    button.classList.add('is-loading');
    button.setAttribute('aria-disabled', 'true');
    button.setAttribute('aria-busy', 'true');
});

function renderFileUpload(root) {
    const input = root.querySelector('[data-ui-file-input]');
    const list = root.querySelector('[data-ui-file-list]');

    if (!input || !list) return;

    list.innerHTML = '';
    list.hidden = input.files.length === 0;

    Array.from(input.files).forEach((file) => {
        const item = document.createElement('article');
        item.className = 'ui-file-upload__item';

        if (file.type.startsWith('image/')) {
            const image = document.createElement('img');
            image.alt = '';
            image.src = URL.createObjectURL(file);
            image.onload = () => URL.revokeObjectURL(image.src);
            item.append(image);
        } else {
            const icon = document.createElement('span');
            icon.className = 'ui-file-upload__file-icon';
            icon.innerHTML = '<i class="fa-regular fa-file" aria-hidden="true"></i>';
            item.append(icon);
        }

        const meta = document.createElement('span');
        const size = file.size > 1024 * 1024
            ? `${(file.size / (1024 * 1024)).toFixed(1)} MB`
            : `${Math.max(1, Math.round(file.size / 1024))} KB`;
        meta.innerHTML = `<strong></strong><small>${size}</small>`;
        meta.querySelector('strong').textContent = file.name;
        item.append(meta);
        list.append(item);
    });
}

document.querySelectorAll('[data-ui-file-upload]').forEach(renderFileUpload);
document.addEventListener('change', (event) => {
    const input = event.target.closest?.('[data-ui-file-input]');
    if (!input) return;

    const root = input.closest('[data-ui-file-upload]');
    if (root) renderFileUpload(root);
});

document.addEventListener('input', (event) => {
    const input = event.target.closest?.('[data-digits-only]');
    if (!input) return;

    input.value = input.value.replace(/\D+/g, '').slice(0, Number(input.maxLength) || 10);
});

document.querySelectorAll('[data-receipt-particles]').forEach((particles) => {
    setTimeout(() => particles.remove(), 1900);
});

function initShippingLabelConsole(scope = document) {
    const consoleRoot = scope.querySelector?.('[data-shipping-label-console]');
    if (!consoleRoot || consoleRoot.dataset.ready === 'true') return;

    consoleRoot.dataset.ready = 'true';

    const bulkRoots = Array.from(consoleRoot.querySelectorAll('[data-shipping-label-bulk]'));
    const stickyBulk = consoleRoot.querySelector('.shipping-label-bulk[hidden]');
    const selectAll = consoleRoot.querySelector('[data-select-all-labels]');
    const selectedCount = consoleRoot.querySelector('[data-selected-count]');
    const statusNodes = () => Array.from(consoleRoot.querySelectorAll('[data-bulk-status]'));
    const checkboxes = () => Array.from(consoleRoot.querySelectorAll('[data-label-order-checkbox]'));

    const selectedOrderIds = () => [...new Set(checkboxes()
        .filter((checkbox) => checkbox.checked)
        .map((checkbox) => Number(checkbox.value)))];

    const updateSelection = () => {
        const count = selectedOrderIds().length;
        if (selectedCount) selectedCount.textContent = count;
        if (stickyBulk) stickyBulk.hidden = count === 0;
        if (selectAll) {
            const visible = checkboxes().filter((checkbox) => checkbox.offsetParent !== null);
            selectAll.checked = visible.length > 0 && visible.every((checkbox) => checkbox.checked);
            selectAll.indeterminate = count > 0 && !selectAll.checked;
        }
    };

    selectAll?.addEventListener('change', () => {
        checkboxes().forEach((checkbox) => {
            checkbox.checked = selectAll.checked;
        });
        updateSelection();
    });

    checkboxes().forEach((checkbox) => checkbox.addEventListener('change', updateSelection));

    consoleRoot.querySelectorAll('[data-clear-selection]').forEach((button) => {
        button.addEventListener('click', () => {
            checkboxes().forEach((checkbox) => {
                checkbox.checked = false;
            });
            updateSelection();
        });
    });

    bulkRoots.forEach((root) => {
        root.querySelectorAll('[data-bulk-action]').forEach((button) => {
            button.addEventListener('click', async () => {
                const orderIds = selectedOrderIds();

                if (!orderIds.length) {
                    statusNodes().forEach((node) => {
                        node.textContent = 'Select at least one order first.';
                    });
                    return;
                }

                const action = button.dataset.bulkAction;
                statusNodes().forEach((node) => {
                    node.textContent = 'Working...';
                });

                try {
                    const response = await fetch(root.dataset.bulkUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': action === 'download_zip' || action === 'export_pdfs' ? 'application/zip' : 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ action, order_ids: orderIds }),
                    });

                    if (!response.ok) throw new Error('Bulk action failed');

                    if (action === 'download_zip' || action === 'export_pdfs') {
                        const blob = await response.blob();
                        const url = URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.href = url;
                        link.download = `shipping-labels-${Date.now()}.zip`;
                        link.click();
                        URL.revokeObjectURL(url);
                        statusNodes().forEach((node) => {
                            node.textContent = 'ZIP export downloaded.';
                        });
                        return;
                    }

                    const data = await response.json();
                    statusNodes().forEach((node) => {
                        node.textContent = data.message || 'Bulk action completed.';
                    });
                } catch {
                    statusNodes().forEach((node) => {
                        node.textContent = 'Unable to complete that bulk action.';
                    });
                }
            });
        });
    });

    const filterForm = consoleRoot.querySelector('[data-live-filters]');
    const loadFilteredPage = async (url, push = true) => {
        consoleRoot.classList.add('is-loading');
        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok) throw new Error('Filter request failed');
            const html = await response.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const nextConsole = doc.querySelector('[data-shipping-label-console]');
            if (!nextConsole) throw new Error('Dashboard missing');
            consoleRoot.replaceWith(nextConsole);
            if (push) window.history.pushState({}, '', url);
            initShippingLabelConsole(document);
        } catch {
            consoleRoot.classList.remove('is-loading');
        }
    };

    let filterTimer;
    filterForm?.addEventListener('input', () => {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(() => {
            const params = new URLSearchParams(new FormData(filterForm));
            loadFilteredPage(`${filterForm.action}?${params.toString()}`);
        }, 350);
    });
    filterForm?.addEventListener('change', () => {
        const params = new URLSearchParams(new FormData(filterForm));
        loadFilteredPage(`${filterForm.action}?${params.toString()}`);
    });
    filterForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        const params = new URLSearchParams(new FormData(filterForm));
        loadFilteredPage(`${filterForm.action}?${params.toString()}`);
    });

    consoleRoot.querySelectorAll('[data-filter-link], .pagination a').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            loadFilteredPage(link.href);
        });
    });

    const drawer = consoleRoot.querySelector('[data-label-preview-drawer]');
    const canvas = consoleRoot.querySelector('[data-label-preview-canvas]');
    const title = consoleRoot.querySelector('[data-label-preview-title]');
    const printForm = consoleRoot.querySelector('[data-label-print-form]');
    const downloadLink = consoleRoot.querySelector('[data-label-download]');
    const mapsLink = consoleRoot.querySelector('[data-label-maps]');
    let zoom = 1;

    consoleRoot.querySelectorAll('[data-preview-label]').forEach((button) => {
        button.addEventListener('click', () => {
            const template = consoleRoot.querySelector(`#${button.dataset.previewLabel}`);
            if (!template || !drawer || !canvas) return;
            zoom = 1;
            canvas.innerHTML = template.innerHTML;
            canvas.style.setProperty('--label-zoom', zoom);
            if (title) title.textContent = template.dataset.title || 'Preview';
            if (printForm) printForm.action = template.dataset.print || '#';
            if (downloadLink) downloadLink.href = template.dataset.download || '#';
            if (mapsLink) {
                mapsLink.href = template.dataset.maps || '#';
                mapsLink.toggleAttribute('hidden', !template.dataset.maps);
            }
            drawer.setAttribute('aria-hidden', 'false');
        });
    });

    consoleRoot.querySelector('[data-close-label-preview]')?.addEventListener('click', () => {
        drawer?.setAttribute('aria-hidden', 'true');
    });

    consoleRoot.querySelectorAll('[data-label-zoom]').forEach((button) => {
        button.addEventListener('click', () => {
            zoom = button.dataset.labelZoom === 'in' ? Math.min(1.35, zoom + 0.1) : Math.max(0.75, zoom - 0.1);
            canvas?.style.setProperty('--label-zoom', zoom);
        });
    });

    consoleRoot.querySelectorAll('[data-row-href]').forEach((row) => {
        row.addEventListener('click', (event) => {
            if (event.target.closest('a, button, input, form, details, summary')) return;
            window.location.href = row.dataset.rowHref;
        });
    });

    updateSelection();
}

initShippingLabelConsole(document);
window.addEventListener('popstate', () => window.location.reload());

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
    const total = serverSummary?.total ?? (subtotal + shipping);
    const tax = serverSummary?.tax ?? 0;
    document.querySelectorAll('[data-checkout-summary], [data-cart-summary]').forEach((summary) => {
        summary.querySelector('[data-checkout-count]')?.replaceChildren(`${cart.length} item${cart.length === 1 ? '' : 's'}`);
        summary.querySelector('[data-checkout-subtotal]')?.replaceChildren(formatCurrency(subtotal));
        summary.querySelector('[data-checkout-shipping]')?.replaceChildren(serverSummary?.shipping_status === 'free' ? 'Complimentary' : shippingLabel);
        summary.querySelector('[data-checkout-shipping-note]')?.replaceChildren(serverSummary?.shipping_policy_text ?? '');
        summary.querySelector('[data-checkout-tax]')?.replaceChildren(formatCurrency(tax));
        summary.querySelector('[data-checkout-total]')?.replaceChildren(formatCurrency(total));

        cart.forEach((item) => {
            const key = `${item.slug}-${item.colour}-${item.size}`;
            const line = summary.querySelector(`[data-cart-key="${CSS.escape(key)}"]`);
            if (!line) return;
            line.querySelector('[data-line-total]')?.replaceChildren(formatCurrency(item.price * item.quantity));
            const quantityInput = line.querySelector('[data-cart-quantity]');
            if (quantityInput) quantityInput.value = item.quantity;
            line.querySelector('[data-cart-step="-1"]')?.toggleAttribute('disabled', item.quantity <= 1);
        });
    });

    document.querySelector('[data-mobile-checkout-total]')?.replaceChildren(formatCurrency(total));
    document.querySelectorAll('[data-delivery-progress]').forEach((panel) => {
        const threshold = Number(panel.dataset.threshold || 999);
        const remaining = Math.max(0, threshold - subtotal);
        const unlocked = serverSummary?.shipping_status === 'free';
        panel.classList.toggle('is-unlocked', unlocked);
        panel.querySelector('[data-delivery-title]')?.replaceChildren(unlocked ? 'Complimentary delivery unlocked' : `You're ${formatCurrency(remaining)} away from complimentary delivery`);
        panel.querySelector('[data-delivery-copy]')?.replaceChildren(unlocked ? 'Delivery is on us for this order.' : "Add a little more to your bag and we'll take care of the delivery.");
        panel.querySelector('[data-delivery-progress-bar]')?.style.setProperty('width', `${Math.min(100, Math.round((subtotal / threshold) * 100))}%`);
        const icon = panel.querySelector('[data-delivery-icon]');
        if (icon) {
            icon.classList.toggle('fa-circle-check', unlocked);
            icon.classList.toggle('fa-truck-fast', !unlocked);
        }
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

function setCheckoutPaymentChoice(value) {
    const root = document.querySelector('[data-checkout-payment-method]');
    if (!root) return;

    root.querySelectorAll('[data-checkout-payment-card]').forEach((card) => {
        const input = card.querySelector('input[type="radio"]');
        const selected = input?.value === value;
        card.classList.toggle('is-selected', selected);
        if (input) input.checked = selected;
    });

    const submit = document.querySelector('[data-checkout-payment-submit]');
    const submitLabel = submit?.querySelector('[data-checkout-payment-submit-label]');
    const submitIcon = submit?.querySelector('[data-checkout-payment-submit-icon]');
    const reassurance = document.querySelector('[data-payment-reassurance]');

    if (submitLabel && submit) {
        submitLabel.textContent = value === 'cod' ? submit.dataset.codLabel : submit.dataset.onlineLabel;
    }

    if (submitIcon) {
        submitIcon.classList.toggle('fa-box', value === 'cod');
        submitIcon.classList.toggle('fa-lock', value !== 'cod');
    }

    if (reassurance) {
        reassurance.textContent = value === 'cod'
            ? 'Pay directly when your order is delivered.'
            : 'Your payment details are processed securely. Sushako does not store your card or UPI credentials.';
    }
}

async function updateCartLine(line, quantity) {
    const key = line?.dataset.cartKey;
    if (!key) return;

    line.classList.add('is-updating');

    try {
        const data = await sendJson(`/cart/${encodeURIComponent(key)}`, 'PATCH', { quantity });
        updateCartBadges(data.cart_count);
        refreshCheckoutSummary(data.cart, data.subtotal, data.summary);
        line.classList.add('is-updated');
        showToast(data.message ?? 'Shopping bag updated');
        setTimeout(() => line.classList.remove('is-updated'), 520);
    } catch {
        showToast('Unable to update shopping bag');
    } finally {
        line.classList.remove('is-updating');
    }
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
document.querySelector('[name="payment_preference"]:checked') && setCheckoutPaymentChoice(document.querySelector('[name="payment_preference"]:checked').value);

document.addEventListener('change', (event) => {
    const checkoutAddress = event.target.closest('[data-checkout-address]');
    const paymentChoice = event.target.closest('[name="payment_preference"]');

    if (checkoutAddress) {
        fillCheckoutAddress(checkoutAddress);
    }

    if (paymentChoice) {
        setCheckoutPaymentChoice(paymentChoice.value);
    }
});

document.addEventListener('submit', (event) => {
    const checkoutForm = event.target.closest('.luxury-checkout__form');
    if (!checkoutForm) return;

    const submit = checkoutForm.querySelector('[data-checkout-payment-submit]');
    const submitLabel = submit?.querySelector('[data-checkout-payment-submit-label]');
    const selected = checkoutForm.querySelector('[name="payment_preference"]:checked')?.value ?? 'online';

    if (submit) {
        submit.disabled = true;
        submit.classList.add('button--loading');
    }

    if (submitLabel) {
        submitLabel.textContent = selected === 'cod' ? 'Placing Your Order...' : 'Opening Secure Payment...';
    }
});

document.addEventListener('submit', (event) => {
    const buyNowForm = event.target.closest('[data-buy-now-form], .buy-now-form');
    if (!buyNowForm) return;

    const submit = buyNowForm.querySelector('[data-buy-now-submit]');
    const label = submit?.querySelector('[data-buy-now-label], span');
    const actionRow = buyNowForm.closest('.product-card__actions, .buy-actions');

    if (submit) {
        submit.disabled = true;
        submit.classList.add('button--loading');
    }

    actionRow?.querySelectorAll('[data-add-to-cart], [data-buy-now-submit]').forEach((button) => {
        button.disabled = true;
    });

    if (label) {
        label.textContent = 'Opening Checkout...';
    }
});

document.addEventListener('submit', (event) => {
    const guardedForm = event.target.closest('[data-confirm-submit], .shipping-label-workspace-form');
    if (!guardedForm) return;

    const message = guardedForm.dataset.confirmSubmit;
    if (message && !window.confirm(message)) {
        event.preventDefault();
        return;
    }

    guardedForm.querySelectorAll('button[type="submit"]').forEach((button) => {
        const label = button.querySelector('span') ?? button;
        button.disabled = true;
        button.classList.add('button--loading');
        if (button.dataset.submitText) {
            label.textContent = button.dataset.submitText;
        }
    });
});

document.addEventListener('click', async (event) => {
    const addButton = event.target.closest('[data-add-to-cart]');
    const wishlistButton = event.target.closest('[data-wishlist]');
    const closeButton = event.target.closest('[data-cart-close]');
    const shareButton = event.target.closest('[data-share-url]');
    const searchFocus = event.target.closest('[data-search-focus]');
    const filterToggle = event.target.closest('[data-filter-toggle]');
    const filterClose = event.target.closest('[data-filter-close]');
    const sortToggle = event.target.closest('[data-sort-toggle]');
    const sortClose = event.target.closest('[data-sort-close]');
    const productsOverlay = event.target.closest('[data-products-overlay]');
    const gstToggle = event.target.closest('[data-gst-toggle]');
    const businessType = event.target.closest('[data-business-type]');
    const cartUpdate = event.target.closest('[data-cart-update]');
    const cartStep = event.target.closest('[data-cart-step]');
    const cartRemove = event.target.closest('[data-cart-remove]');
    const currentLocation = event.target.closest('[data-use-current-location]');
    const orderCopy = event.target.closest('[data-order-copy]');
    const adminSidebarOpen = event.target.closest('[data-admin-sidebar-open]');
    const adminSidebarClose = event.target.closest('[data-admin-sidebar-close]');
    const adminActionMenu = event.target.closest('[data-admin-action-menu]');
    const navigationLink = event.target.closest('a[href]');

    document.querySelectorAll('[data-admin-action-menu][open]').forEach((menu) => {
        if (menu !== adminActionMenu) {
            menu.removeAttribute('open');
        }
    });

    if (adminSidebarOpen) {
        setAdminSidebarState(true, adminSidebarOpen);
        return;
    }

    if (adminSidebarClose) {
        setAdminSidebarState(false);
        return;
    }

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

    if (orderCopy) {
        const label = orderCopy.querySelector('[data-order-copy-label]');
        const originalLabel = label?.textContent ?? 'Copy';

        try {
            await navigator.clipboard?.writeText(orderCopy.dataset.orderCopy);
            if (label) label.textContent = 'Copied';
            showToast('Copied');
            setTimeout(() => {
                if (label) label.textContent = originalLabel;
            }, 1400);
        } catch {
            showToast('Unable to copy order ID');
        }
    }

    if (cartStep) {
        const line = cartStep.closest('[data-checkout-item]');
        const quantityInput = line?.querySelector('[data-cart-quantity]');
        if (!quantityInput) return;
        const nextQuantity = Math.min(10, Math.max(1, Number(quantityInput.value || 1) + Number(cartStep.dataset.cartStep)));
        quantityInput.value = nextQuantity;
        cartStep.disabled = true;
        await updateCartLine(line, nextQuantity);
        cartStep.disabled = false;
    }

    if (cartUpdate) {
        const line = cartUpdate.closest('[data-checkout-item]');
        const key = line?.dataset.cartKey;
        const quantity = Number(line?.querySelector('[data-cart-quantity]')?.value ?? 1);
        if (!key) return;

        await updateCartLine(line, quantity);
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
        const panel = document.querySelector('[data-mobile-filter-panel]');
        const backdrop = document.querySelector('.products-filter-backdrop');
        panel?.classList.add('is-open');
        backdrop?.removeAttribute('hidden');
        filterToggle.setAttribute('aria-expanded', 'true');
        document.body.classList.add('products-panel-open');
        panel?.querySelector('a, button, input, select')?.focus();
    }

    if (filterClose) {
        const panel = document.querySelector('[data-mobile-filter-panel]');
        const backdrop = document.querySelector('.products-filter-backdrop');
        const toggle = document.querySelector('[data-filter-toggle]');
        panel?.classList.remove('is-open');
        if (!document.querySelector('[data-sort-sheet].is-open')) backdrop?.setAttribute('hidden', '');
        toggle?.setAttribute('aria-expanded', 'false');
        document.body.classList.toggle('products-panel-open', Boolean(document.querySelector('[data-sort-sheet].is-open')));
        toggle?.focus();
    }

    if (sortToggle) {
        const sheet = document.querySelector('[data-sort-sheet]');
        const backdrop = document.querySelector('.products-filter-backdrop');
        sheet?.removeAttribute('hidden');
        requestAnimationFrame(() => sheet?.classList.add('is-open'));
        backdrop?.removeAttribute('hidden');
        sortToggle.setAttribute('aria-expanded', 'true');
        document.body.classList.add('products-panel-open');
        sheet?.querySelector('a, button')?.focus();
    }

    if (sortClose) {
        const sheet = document.querySelector('[data-sort-sheet]');
        const backdrop = document.querySelector('.products-filter-backdrop');
        const toggle = document.querySelector('[data-sort-toggle]');
        sheet?.classList.remove('is-open');
        setTimeout(() => sheet?.setAttribute('hidden', ''), 220);
        if (!document.querySelector('[data-mobile-filter-panel].is-open')) backdrop?.setAttribute('hidden', '');
        toggle?.setAttribute('aria-expanded', 'false');
        document.body.classList.toggle('products-panel-open', Boolean(document.querySelector('[data-mobile-filter-panel].is-open')));
        toggle?.focus();
    }

    if (productsOverlay) {
        document.querySelector('[data-mobile-filter-panel].is-open [data-filter-close]')?.click();
        document.querySelector('[data-sort-sheet].is-open [data-sort-close]')?.click();
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
        const label = addButton.querySelector('[data-add-to-cart-label]');
        const originalText = label?.textContent ?? addButton.textContent;
        const actionRow = addButton.closest('.product-card__actions, .buy-actions');
        const purchaseButtons = actionRow?.querySelectorAll('[data-add-to-cart], [data-buy-now-submit]') ?? [addButton];

        purchaseButtons.forEach((button) => {
            button.disabled = true;
        });
        addButton.classList.add('button--loading');
        addButton.setAttribute('aria-live', 'polite');
        if (label) {
            label.textContent = 'Adding...';
        } else {
            addButton.textContent = 'Adding...';
        }

        try {
            const data = await postJson('/cart', {
                slug: addButton.dataset.slug,
                colour,
                size,
                quantity: Number(quantity),
            });

            addButton.classList.remove('button--loading');
            addButton.classList.add('button--added');
            if (label) {
                label.textContent = 'Added';
            } else {
                addButton.textContent = 'Added';
            }
            updateCartBadges(data.cart_count);
            renderMiniCart(data.cart, data.subtotal);
            showToast(data.message ?? 'Added to Cart');
            setTimeout(() => {
                purchaseButtons.forEach((button) => {
                    button.disabled = false;
                });
                addButton.classList.remove('button--added');
                if (label) {
                    label.textContent = originalText;
                } else {
                    addButton.textContent = originalText;
                }
            }, 1600);
        } catch {
            purchaseButtons.forEach((button) => {
                button.disabled = false;
            });
            addButton.classList.remove('button--loading');
            if (label) {
                label.textContent = originalText;
            } else {
                addButton.textContent = originalText;
            }
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
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
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
        if (prefersReducedMotion || slides.length < 2) return;
        timer = setInterval(() => show(active + 1), 5000);
    };

    const pause = () => clearInterval(timer);

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

    carousel.addEventListener('mouseenter', pause);
    carousel.addEventListener('mouseleave', restart);
    carousel.addEventListener('focusin', pause);
    carousel.addEventListener('focusout', restart);
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
    if (shop.dataset.serverFiltered !== undefined) return;

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

document.addEventListener('keydown', (event) => {
    const adminSidebar = document.querySelector('[data-admin-sidebar].is-open');

    if (adminSidebar && event.key === 'Escape') {
        setAdminSidebarState(false);
        return;
    }

    const openAdminActionMenu = document.querySelector('[data-admin-action-menu][open]');
    if (openAdminActionMenu && event.key === 'Escape') {
        openAdminActionMenu.removeAttribute('open');
        openAdminActionMenu.querySelector('summary')?.focus();
        return;
    }

    if (event.key === 'ArrowDown') {
        const summary = event.target.closest?.('[data-admin-action-menu] > summary');
        if (summary) {
            const menu = summary.closest('[data-admin-action-menu]');
            menu?.setAttribute('open', '');
            event.preventDefault();
            menu?.querySelector('.admin-action-menu__panel a, .admin-action-menu__panel button')?.focus();
            return;
        }
    }

    const panel = document.querySelector('[data-mobile-filter-panel].is-open, [data-sort-sheet].is-open');
    if (!panel) return;

    if (event.key === 'Escape') {
        panel.querySelector('[data-filter-close], [data-sort-close]')?.click();
        return;
    }

    if (event.key !== 'Tab') return;

    const focusable = [...panel.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])')];
    if (focusable.length === 0) return;
    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
});

document.addEventListener('change', (event) => {
    const sort = event.target.closest('[data-server-sort]');
    if (sort) sort.form?.submit();
});

function initRotatingSearchPlaceholder() {
    const input = document.querySelector('[data-search-placeholder]');
    if (!input) return;

    const placeholders = [
        'Search health mixes...',
        'Search masala powders...',
        'Search electronics...',
        'Search everyday essentials...',
        'Search new arrivals...',
    ];
    let index = 0;

    setInterval(() => {
        if (document.activeElement === input || input.value) return;
        input.classList.add('is-placeholder-changing');
        setTimeout(() => {
            index = (index + 1) % placeholders.length;
            input.placeholder = placeholders[index];
            input.classList.remove('is-placeholder-changing');
        }, 220);
    }, 3600);
}

function initStorefrontReveal() {
    const sections = [...document.querySelectorAll('[data-reveal]')];
    if (sections.length === 0) return;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) {
        sections.forEach((section) => section.classList.add('is-visible'));
        return;
    }

    sections.forEach((section) => section.classList.add('is-reveal-pending'));

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            entry.target.classList.remove('is-reveal-pending');
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.12 });

    sections.forEach((section, index) => {
        section.style.transitionDelay = `${Math.min(index * 35, 180)}ms`;
        observer.observe(section);
    });
}

function initCustomerManagement() {
    const toggle = document.querySelector('[data-customer-view-toggle]');
    if (toggle) {
        const savedView = window.localStorage.getItem('sushakoCustomerView');
        const currentUrl = new URL(window.location.href);

        if (savedView && !currentUrl.searchParams.has('view')) {
            currentUrl.searchParams.set('view', savedView);
            window.location.replace(currentUrl.toString());
            return;
        }

        toggle.querySelectorAll('[data-view-mode]').forEach((link) => {
            link.addEventListener('click', () => {
                window.localStorage.setItem('sushakoCustomerView', link.dataset.viewMode);
            });
        });
    }

    document.querySelectorAll('[data-whatsapp-composer]').forEach((form) => {
        const textarea = form.querySelector('[data-message-counter]');
        const counter = form.parentElement?.querySelector('[data-message-count]');
        if (!textarea || !counter) return;

        const update = () => {
            counter.textContent = textarea.value.length.toString();
        };

        textarea.addEventListener('input', update);
        update();
    });
}

function initSellerPromoCarousel() {
    document.querySelectorAll('[data-seller-carousel]').forEach((carousel) => {
        const track = carousel.querySelector('[data-seller-carousel-track]');
        const slides = [...carousel.querySelectorAll('[data-seller-slide]')];
        const prev = carousel.querySelector('[data-seller-prev]');
        const next = carousel.querySelector('[data-seller-next]');
        if (!track || slides.length <= 1) return;

        let index = 0;
        let paused = false;
        let startX = null;

        const render = () => {
            track.style.transform = `translateX(${-index * 100}%)`;
        };
        const go = (delta) => {
            index = (index + delta + slides.length) % slides.length;
            render();
        };

        prev?.addEventListener('click', () => go(-1));
        next?.addEventListener('click', () => go(1));
        carousel.addEventListener('mouseenter', () => { paused = true; });
        carousel.addEventListener('mouseleave', () => { paused = false; });
        carousel.addEventListener('touchstart', (event) => { startX = event.touches[0]?.clientX ?? null; }, { passive: true });
        carousel.addEventListener('touchend', (event) => {
            if (startX === null) return;
            const endX = event.changedTouches[0]?.clientX ?? startX;
            if (Math.abs(startX - endX) > 42) go(startX > endX ? 1 : -1);
            startX = null;
        }, { passive: true });
        carousel.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft') go(-1);
            if (event.key === 'ArrowRight') go(1);
        });

        setInterval(() => {
            if (!paused && !document.hidden) go(1);
        }, 5200);
    });
}

function initEnterpriseSearch() {
    document.querySelectorAll('[data-enterprise-search]').forEach((form) => {
        const input = form.querySelector('[data-search-input]');
        const panel = form.querySelector('[data-search-panel]');
        const clear = form.querySelector('[data-search-clear]');
        const state = form.querySelector('[data-search-state]');
        const resultsRoot = form.querySelector('[data-search-results]');
        const viewAll = form.querySelector('[data-search-view-all]');
        if (!input || form.dataset.enterpriseReady === 'true') return;

        form.dataset.enterpriseReady = 'true';
        const key = 'sushakoRecentSearches';
        let timer = null;
        let activeIndex = -1;

        const searches = () => {
            try {
                return JSON.parse(window.localStorage.getItem(key) || '[]').slice(0, 5);
            } catch {
                return [];
            }
        };

        const saveSearch = () => {
            const term = input.value.trim();
            if (!term) return;
            const next = [term, ...searches().filter((item) => item.toLowerCase() !== term.toLowerCase())].slice(0, 5);
            window.localStorage.setItem(key, JSON.stringify(next));
        };

        const close = () => {
            if (panel) panel.hidden = true;
            activeIndex = -1;
        };

        const open = () => {
            if (panel) panel.hidden = false;
        };

        const setState = (message) => {
            if (!state) return;
            state.textContent = message;
            state.hidden = !message;
        };

        const items = () => [...form.querySelectorAll('[data-search-result]')];

        const focusItem = (index) => {
            const nodes = items();
            nodes.forEach((node) => node.classList.remove('is-active'));
            if (!nodes.length) return;
            activeIndex = (index + nodes.length) % nodes.length;
            nodes[activeIndex].classList.add('is-active');
            nodes[activeIndex].focus();
        };

        const renderGroup = (title, rows) => {
            if (!rows.length) return '';
            return `<section><strong>${title}</strong>${rows.map((row) => `
                <a href="${row.url}" data-search-result>
                    <span>${row.highlight}</span>
                    <small>${row.meta || ''}</small>
                </a>
            `).join('')}</section>`;
        };

        const renderRecent = () => {
            const term = input.value.trim();
            if (term.length > 0) return;
            const recent = searches();
            if (!resultsRoot) return;
            if (!recent.length) {
                resultsRoot.innerHTML = '';
                setState('Type at least 2 characters to search products, categories and stores.');
                if (viewAll) viewAll.hidden = true;
                return;
            }

            resultsRoot.innerHTML = `<section><strong>Recent Searches</strong>${recent.map((item) => `
                <button type="button" data-search-result data-search-term="${item}">
                    <span>${item}</span>
                    <small>Recent search</small>
                </button>
            `).join('')}</section>`;
            setState('');
            if (viewAll) viewAll.hidden = true;
        };

        const loadSuggestions = async () => {
            const term = input.value.trim();
            clear?.toggleAttribute('hidden', term.length === 0);
            activeIndex = -1;

            if (term.length === 0) {
                renderRecent();
                return;
            }

            if (term.length < 2) {
                if (resultsRoot) resultsRoot.innerHTML = '';
                setState('Type at least 2 characters to search.');
                if (viewAll) viewAll.hidden = true;
                open();
                return;
            }

            setState('Searching...');
            open();

            try {
                const url = new URL(form.dataset.suggestionsUrl, window.location.origin);
                url.searchParams.set('q', term);
                const response = await fetch(url, { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('Search failed');
                const data = await response.json();
                const html = [
                    renderGroup('Products', data.products || []),
                    renderGroup('Categories', data.categories || []),
                    renderGroup('Stores', data.stores || []),
                ].join('');

                if (resultsRoot) resultsRoot.innerHTML = html;
                setState(html ? '' : 'No results found.');
                if (viewAll) {
                    viewAll.hidden = false;
                    viewAll.href = data.view_all_url || `${form.action}?q=${encodeURIComponent(term)}`;
                }
            } catch {
                if (resultsRoot) resultsRoot.innerHTML = '';
                setState('Unable to load suggestions. Press Enter to search.');
                if (viewAll) viewAll.hidden = false;
            }
        };

        form.addEventListener('submit', saveSearch);
        input.addEventListener('focus', () => {
            open();
            renderRecent();
        });
        input.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(loadSuggestions, 220);
            open();
        });
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                close();
                input.focus();
            } else if (event.key === 'ArrowDown') {
                event.preventDefault();
                focusItem(activeIndex + 1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                focusItem(activeIndex - 1);
            }
        });
        clear?.addEventListener('click', () => {
            input.value = '';
            input.focus();
            clear.hidden = true;
            renderRecent();
        });
        form.addEventListener('click', (event) => {
            const recent = event.target.closest('[data-search-term]');
            if (recent) {
                input.value = recent.dataset.searchTerm;
                form.requestSubmit();
            }
        });
        document.addEventListener('click', (event) => {
            if (!form.contains(event.target)) close();
        });
        clear?.toggleAttribute('hidden', input.value.length === 0);
        close();
    });
}

function initEnterpriseMenus() {
    document.addEventListener('click', (event) => {
        document.querySelectorAll('details[data-account-dropdown], .site-nav-menu').forEach((details) => {
            if (!details.contains(event.target)) details.open = false;
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        document.querySelectorAll('details[open]').forEach((details) => {
            details.open = false;
        });
    });
}

function initEnterpriseButtons() {
    document.querySelectorAll('form').forEach((form) => {
        if (form.dataset.loadingReady === 'true') return;
        form.dataset.loadingReady = 'true';
        form.addEventListener('submit', () => {
            const submitter = form.querySelector('button[type="submit"]:not([data-no-loading])');
            if (!submitter || submitter.disabled) return;
            submitter.classList.add('button--loading');
            submitter.setAttribute('aria-busy', 'true');
        });
    });
}

function initEnterpriseModals() {
    const focusable = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

    const closeModal = (modal) => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('ui-modal-open');
        window.uiModalReturnFocus?.focus?.();
        window.uiModalReturnFocus = null;
    };

    document.querySelectorAll('[data-ui-modal-open]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const modal = document.querySelector(trigger.dataset.uiModalOpen);
            if (!modal) return;
            window.uiModalReturnFocus = trigger;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('ui-modal-open');
            modal.querySelector(focusable)?.focus();
        });
    });

    document.querySelectorAll('[data-ui-modal]').forEach((modal) => {
        modal.querySelectorAll('[data-ui-modal-close]').forEach((close) => close.addEventListener('click', () => closeModal(modal)));
        modal.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeModal(modal);
            if (event.key !== 'Tab') return;
            const items = [...modal.querySelectorAll(focusable)];
            if (!items.length) return;
            const first = items[0];
            const last = items[items.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });
    });
}

function initCopyTextActions() {
    document.addEventListener('click', async (event) => {
        const trigger = event.target.closest('[data-copy-text]');
        if (!trigger) return;

        const text = trigger.dataset.copyText || '';
        if (!text) return;

        try {
            await navigator.clipboard?.writeText(text);
            const original = trigger.textContent;
            trigger.textContent = 'Copied';
            setTimeout(() => {
                trigger.textContent = original;
            }, 1400);
        } catch (error) {
            trigger.textContent = 'Copy failed';
            setTimeout(() => {
                trigger.textContent = 'Copy Address';
            }, 1400);
        }
    });
}

initHeroCarousel();
initProductGallery();
initWhatsappPulse();
initShopFilters();
initRotatingSearchPlaceholder();
initStorefrontReveal();
initCustomerManagement();
initSellerPromoCarousel();
initEnterpriseSearch();
initEnterpriseMenus();
initEnterpriseButtons();
initEnterpriseModals();
initCopyTextActions();
