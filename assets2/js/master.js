/**
 * Laces – Master JavaScript
 * Combines smooth scrolling, quantity controls, and payment alert.
 * Include this file AFTER Bootstrap JS and SweetAlert2 JS.
 */

document.addEventListener("DOMContentLoaded", function () {
    "use strict";

    const ajaxOnlyMode = !!document.body && document.body.hasAttribute('data-ajax-only');

    const THEME_STORAGE_KEY = 'laces-theme';
    const LANGUAGE_STORAGE_KEY = 'laces-language';
    const AJAX_NAV_ENABLED = true;
    let ajaxNavigationInProgress = false;
    const ajaxPageCache = new Map();
    const ajaxScrollPositions = new Map();
    const MAX_AJAX_CACHE_ENTRIES = 12;

    const TRANSLATIONS = {
        en: {
            home: 'Home',
            trending: 'Trending',
            categories: 'Categories',
            productList: 'Product List',
            searchPlaceholder: 'Search...',
            myAccount: 'My Account',
            darkMode: 'Dark mode',
            viewProfile: 'View Profile',
            myOrders: 'My Orders',
            signOut: 'Sign Out',
            welcomeBack: 'Welcome back!',
            manageOrders: 'Manage your orders and preferences',
            loginTitle: 'Login',
            loginSubtitle: 'Back for more shopping?',
            signIn: 'Sign In',
            forgotPassword: 'Forgot password?',
            signUp: 'Sign up',
            registerTitle: 'Register',
            registerSubtitle: 'Start your shopping journey.',
            login: 'Login',
            updatePasswordTitle: 'Forgot Password',
            updatePasswordSubtitle: "We'll help you reset your password.",
            updatePassword: 'Update Password',
            rememberPassword: 'Remember your password?',
            language: 'Language',
            aboutUs: 'About Us',
            profile: 'Profile',
            orderHistory: 'Order History',
            quickLinks: 'Quick Links',
            stayConnected: 'Stay Connected',
            subscribeNewsletter: 'Subscribe to our newsletter',
            emailAddress: 'Email address',
            subscribe: 'Subscribe',
            similarItems: 'Similar Items',
            addToBasket: 'Add to Basket',
            buyNow: 'Buy Now',
            size: 'Size',
            quantity: 'Quantity',
            details: 'Details',
            color: 'Color',
            stock: 'Stock',
            sizeAndFit: 'Size and Fit',
            paymentSuccessful: 'Payment Successful!',
            paymentProcessed: 'Your payment has been processed successfully.',
            orderDetailsHere: 'Order details here!',
            signOutQuestion: 'Sign out?',
            signOutBody: 'You will need to log in again to continue.',
            confirmSignOut: 'Yes, sign out',
            cancel: 'Cancel',
            addedTitle: 'Added!',
            addedToBasketText: 'Item added to your basket.',
            oopsTitle: 'Oops',
            couldNotAddItem: 'Could not add item.',
            errorTitle: 'Error',
            failedToAddItem: 'Failed to add item to cart'
        },
        fil: {
            home: 'Home',
            trending: 'Sikat na Produkto',
            categories: 'Mga Kategorya',
            productList: 'Listahan ng Produkto',
            searchPlaceholder: 'Maghanap...',
            myAccount: 'Aking Account',
            darkMode: 'Madilim na mode',
            viewProfile: 'Tingnan ang Profile',
            myOrders: 'Aking Orders',
            signOut: 'Mag-sign out',
            welcomeBack: 'Maligayang pagbabalik!',
            manageOrders: 'Pamahalaan ang iyong orders at preferences',
            loginTitle: 'Mag-login',
            loginSubtitle: 'Handa ka na ulit mamili?',
            signIn: 'Mag-sign in',
            forgotPassword: 'Nakalimutan ang password?',
            signUp: 'Mag-sign up',
            registerTitle: 'Mag-register',
            registerSubtitle: 'Simulan ang iyong shopping journey.',
            login: 'Mag-login',
            updatePasswordTitle: 'Nakalimutang Password',
            updatePasswordSubtitle: 'Tutulungan ka naming i-reset ang iyong password.',
            updatePassword: 'I-update ang Password',
            rememberPassword: 'Naalala mo na ang password mo?',
            language: 'Wika',
            aboutUs: 'Tungkol Sa Amin',
            profile: 'Profile',
            orderHistory: 'Kasaysayan ng Order',
            quickLinks: 'Mabilis na Links',
            stayConnected: 'Manatiling Konektado',
            subscribeNewsletter: 'Mag-subscribe sa aming newsletter',
            emailAddress: 'Email address',
            subscribe: 'Mag-subscribe',
            similarItems: 'Kahawig na Items',
            addToBasket: 'Idagdag sa Basket',
            buyNow: 'Bilhin Ngayon',
            size: 'Sukat',
            quantity: 'Dami',
            details: 'Detalye',
            color: 'Kulay',
            stock: 'Stock',
            sizeAndFit: 'Sukat at Fit',
            paymentSuccessful: 'Tagumpay ang Bayad!',
            paymentProcessed: 'Matagumpay na naproseso ang iyong bayad.',
            orderDetailsHere: 'Tingnan dito ang detalye ng order!',
            signOutQuestion: 'Mag-sign out?',
            signOutBody: 'Kailangan mong mag-login muli para magpatuloy.',
            confirmSignOut: 'Oo, mag-sign out',
            cancel: 'Kanselahin',
            addedTitle: 'Nagdagdag!',
            addedToBasketText: 'Nagdagdag ang item sa basket mo.',
            oopsTitle: 'Oops',
            couldNotAddItem: 'Hindi maidagdag ang item.',
            errorTitle: 'Error',
            failedToAddItem: 'Nabigo ang pagdagdag sa cart'
        }
    };

    const HARDCODED_TEXT_KEYS = [
        'home', 'trending', 'categories', 'productList', 'myAccount', 'darkMode',
        'viewProfile', 'myOrders', 'signOut', 'welcomeBack', 'manageOrders',
        'loginTitle', 'loginSubtitle', 'signIn', 'forgotPassword', 'signUp',
        'registerTitle', 'registerSubtitle', 'login', 'updatePasswordTitle',
        'updatePasswordSubtitle', 'updatePassword', 'rememberPassword', 'language',
        'aboutUs', 'profile', 'orderHistory', 'quickLinks', 'stayConnected',
        'subscribeNewsletter', 'emailAddress', 'subscribe', 'similarItems',
        'addToBasket', 'buyNow', 'size', 'quantity', 'details', 'color', 'stock',
        'sizeAndFit'
    ];

    function getStoredLanguage() {
        const saved = localStorage.getItem(LANGUAGE_STORAGE_KEY);
        return Object.prototype.hasOwnProperty.call(TRANSLATIONS, saved) ? saved : 'en';
    }

    function getLanguagePack(langValue) {
        return TRANSLATIONS[langValue] || TRANSLATIONS.en;
    }

    function getTranslatedValue(langValue, key, fallback) {
        const pack = getLanguagePack(langValue);
        if (Object.prototype.hasOwnProperty.call(pack, key)) {
            return pack[key];
        }
        return fallback;
    }

    function normalizeTextValue(text) {
        return (text || '').replace(/\s+/g, ' ').trim();
    }

    function buildTextLookupMap() {
        const map = {};
        Object.keys(TRANSLATIONS).forEach((langCode) => {
            const pack = TRANSLATIONS[langCode];
            HARDCODED_TEXT_KEYS.forEach((key) => {
                const value = normalizeTextValue(pack[key]);
                if (!value) {
                    return;
                }
                map[value] = key;
            });
        });
        return map;
    }

    const TEXT_LOOKUP_MAP = buildTextLookupMap();

    function applyHardcodedTextOverrides(langValue) {
        const pack = getLanguagePack(langValue);
        const walker = document.createTreeWalker(
            document.body,
            NodeFilter.SHOW_TEXT,
            {
                acceptNode: (node) => {
                    if (!node || !node.parentElement) {
                        return NodeFilter.FILTER_REJECT;
                    }

                    const parentTag = node.parentElement.tagName;
                    if (parentTag === 'SCRIPT' || parentTag === 'STYLE' || parentTag === 'NOSCRIPT') {
                        return NodeFilter.FILTER_REJECT;
                    }

                    const normalized = normalizeTextValue(node.nodeValue);
                    if (!normalized) {
                        return NodeFilter.FILTER_REJECT;
                    }

                    return Object.prototype.hasOwnProperty.call(TEXT_LOOKUP_MAP, normalized)
                        ? NodeFilter.FILTER_ACCEPT
                        : NodeFilter.FILTER_REJECT;
                }
            }
        );

        const textNodes = [];
        let currentNode = walker.nextNode();
        while (currentNode) {
            textNodes.push(currentNode);
            currentNode = walker.nextNode();
        }

        textNodes.forEach((node) => {
            const normalized = normalizeTextValue(node.nodeValue);
            const key = TEXT_LOOKUP_MAP[normalized];
            if (!key || !pack[key]) {
                return;
            }

            node.nodeValue = node.nodeValue.replace(normalized, pack[key]);
        });
    }

    function normalizeAvatarUrl(avatarUrl) {
        if (!avatarUrl) return null;
        if (/^https?:\/\//i.test(avatarUrl)) return avatarUrl;
        if (avatarUrl.startsWith('/')) return avatarUrl;

        const inCartPath = window.location.pathname.replace(/\\/g, '/').includes('/cart/');
        if (inCartPath && avatarUrl.startsWith('assets2/')) {
            return '../' + avatarUrl;
        }
        return avatarUrl;
    }

    function applyUserProfile(profile) {
        const normalized = normalizeAvatarUrl(profile.avatar_url);
        if (!normalized) return;

        const avatarImages = document.querySelectorAll('img[src*="gg--profile.png"], img[data-user-avatar]');
        avatarImages.forEach((img) => {
            img.src = normalized;
            img.setAttribute('data-user-avatar', 'true');
            img.style.objectFit = 'cover';
            img.style.borderRadius = '50%';
            img.style.filter = 'none';

            if (!img.getAttribute('height') && img.getAttribute('width')) {
                img.setAttribute('height', img.getAttribute('width'));
            }

            img.classList.remove('opacity-75');
        });

        const firstName = (profile.first_name || '').trim();
        const username = (profile.username || '').trim();
        const fullName = `${(profile.first_name || '').trim()} ${(profile.last_name || '').trim()}`.trim();
        const greetingName = firstName || username || 'there';
        const lang = getStoredLanguage();
        const t = TRANSLATIONS[lang] || TRANSLATIONS.en;

        const welcomeTitles = document.querySelectorAll('.offcanvas .offcanvas-body h6.fw-bold');
        welcomeTitles.forEach((el) => {
            el.textContent = lang === 'fil' ? `Maligayang pagbalik, ${greetingName}!` : `Welcome, ${greetingName}!`;
        });

        const welcomeSubtexts = document.querySelectorAll('.offcanvas .offcanvas-body p.small.text-muted');
        welcomeSubtexts.forEach((el) => {
            if (profile.email) {
                el.textContent = profile.email;
            } else if (fullName) {
                el.textContent = fullName;
            } else {
                el.textContent = t.manageOrders;
            }
        });
    }

    function syncThemeToggleState(theme) {
        const toggleInputs = document.querySelectorAll('.theme-toggle-input');
        toggleInputs.forEach((input) => {
            input.checked = theme === 'dark';
        });
    }

    function applyTheme(theme) {
        const normalizedTheme = theme === 'dark' ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', normalizedTheme);
        localStorage.setItem(THEME_STORAGE_KEY, normalizedTheme);
        syncThemeToggleState(normalizedTheme);
    }

    function getUrlKey(urlLike) {
        try {
            const parsed = new URL(urlLike, window.location.origin);
            return `${parsed.pathname}${parsed.search}`;
        } catch (error) {
            return String(urlLike || '');
        }
    }

    function saveCurrentScrollPosition() {
        const currentKey = getUrlKey(window.location.href);
        ajaxScrollPositions.set(currentKey, window.scrollY || window.pageYOffset || 0);
    }

    function restoreScrollPosition(urlLike) {
        const key = getUrlKey(urlLike);
        const savedY = ajaxScrollPositions.get(key);
        if (typeof savedY !== 'number') {
            window.scrollTo(0, 0);
            return;
        }

        window.requestAnimationFrame(() => {
            window.scrollTo(0, savedY);
        });
    }

    function setCachedPage(urlKey, payload) {
        if (!urlKey || !payload || !payload.html) {
            return;
        }

        if (ajaxPageCache.has(urlKey)) {
            ajaxPageCache.delete(urlKey);
        }

        ajaxPageCache.set(urlKey, payload);

        if (ajaxPageCache.size > MAX_AJAX_CACHE_ENTRIES) {
            const firstKey = ajaxPageCache.keys().next().value;
            ajaxPageCache.delete(firstKey);
        }
    }

    async function fetchPageHtml(targetUrl) {
        const response = await fetch(targetUrl, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error('AJAX navigation failed.');
        }

        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('text/html')) {
            throw new Error('Non-HTML response received.');
        }

        const html = await response.text();
        const resolvedUrl = response.url || targetUrl;
        const cacheKey = getUrlKey(resolvedUrl);
        setCachedPage(cacheKey, { html, resolvedUrl });

        return { html, resolvedUrl };
    }

    async function prefetchPage(urlLike) {
        if (!AJAX_NAV_ENABLED || ajaxOnlyMode || ajaxNavigationInProgress) {
            return;
        }

        let parsed;
        try {
            parsed = new URL(urlLike, window.location.origin);
        } catch (error) {
            return;
        }

        if (parsed.origin !== window.location.origin) {
            return;
        }

        const targetKey = getUrlKey(parsed.href);
        const currentKey = getUrlKey(window.location.href);
        if (!targetKey || targetKey === currentKey || ajaxPageCache.has(targetKey)) {
            return;
        }

        try {
            await fetchPageHtml(parsed.href);
        } catch (error) {
            // Silent failure for prefetch attempts.
        }
    }

    function shouldHandleWithAjax(anchor, event) {
        if (!AJAX_NAV_ENABLED || !anchor) {
            return false;
        }

        if (event && (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0)) {
            return false;
        }

        if (anchor.hasAttribute('download') || anchor.getAttribute('target') === '_blank' || anchor.hasAttribute('data-no-ajax')) {
            return false;
        }

        const href = anchor.getAttribute('href') || '';
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) {
            return false;
        }

        if (href.includes('db/action/logout.php')) {
            return false;
        }

        let targetUrl;
        try {
            targetUrl = new URL(anchor.href, window.location.origin);
        } catch (error) {
            return false;
        }

        if (targetUrl.origin !== window.location.origin) {
            return false;
        }

        const currentPath = window.location.pathname + window.location.search;
        const targetPath = targetUrl.pathname + targetUrl.search;
        if (currentPath === targetPath && targetUrl.hash) {
            return false;
        }

        return true;
    }

    async function navigateWithAjax(url, options = {}) {
        const replaceHistory = !!options.replaceHistory;
        const shouldRestoreScroll = !!options.restoreScroll;
        const targetUrl = typeof url === 'string' ? url : String(url || '');

        if (!AJAX_NAV_ENABLED || !targetUrl || ajaxNavigationInProgress) {
            if (targetUrl) {
                window.location.href = targetUrl;
            }
            return;
        }

        saveCurrentScrollPosition();
        ajaxNavigationInProgress = true;
        document.documentElement.classList.add('ajax-nav-loading');

        try {
            const cached = ajaxPageCache.get(getUrlKey(targetUrl));
            const page = cached || await fetchPageHtml(targetUrl);
            const html = page.html;
            const resolvedUrl = page.resolvedUrl;

            if (replaceHistory) {
                window.history.replaceState({ ajax: true }, '', resolvedUrl);
            } else {
                window.history.pushState({ ajax: true }, '', resolvedUrl);
            }

            document.open();
            document.write(html);
            document.close();

            if (shouldRestoreScroll) {
                restoreScrollPosition(resolvedUrl);
            }
        } catch (error) {
            window.location.href = targetUrl;
        } finally {
            ajaxNavigationInProgress = false;
            document.documentElement.classList.remove('ajax-nav-loading');
        }
    }

    function initializeAjaxNavigation() {
        if (!AJAX_NAV_ENABLED) {
            return;
        }

        document.addEventListener('click', function (event) {
            const anchor = event.target.closest('a[href]');
            if (!shouldHandleWithAjax(anchor, event)) {
                return;
            }

            event.preventDefault();
            navigateWithAjax(anchor.href);
        });

        document.addEventListener('mouseover', function (event) {
            const anchor = event.target.closest('a[href]');
            if (!shouldHandleWithAjax(anchor)) {
                return;
            }

            prefetchPage(anchor.href);
        });

        document.addEventListener('focusin', function (event) {
            const anchor = event.target.closest('a[href]');
            if (!shouldHandleWithAjax(anchor)) {
                return;
            }

            prefetchPage(anchor.href);
        });

        window.addEventListener('popstate', function () {
            navigateWithAjax(window.location.href, { replaceHistory: true, restoreScroll: true });
        });

        if ('scrollRestoration' in window.history) {
            window.history.scrollRestoration = 'manual';
        }
    }

    function initializeAjaxFormNavigation() {
        if (!AJAX_NAV_ENABLED) {
            return;
        }

        document.addEventListener('submit', function (event) {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            if (form.getAttribute('role') === 'search') {
                return;
            }

            if (form.hasAttribute('data-no-ajax')) {
                return;
            }

            const method = (form.getAttribute('method') || 'GET').toUpperCase();
            if (method !== 'GET') {
                return;
            }

            const action = form.getAttribute('action') || window.location.href;
            let actionUrl;
            try {
                actionUrl = new URL(action, window.location.href);
            } catch (error) {
                return;
            }

            if (actionUrl.origin !== window.location.origin) {
                return;
            }

            const hasFileInput = !!form.querySelector('input[type="file"]');
            if (hasFileInput) {
                return;
            }

            event.preventDefault();

            const formData = new FormData(form);
            const params = new URLSearchParams();

            formData.forEach((value, key) => {
                if (typeof value !== 'string') {
                    return;
                }
                params.append(key, value);
            });

            const query = params.toString();
            const destination = query ? `${actionUrl.pathname}?${query}` : actionUrl.pathname;
            navigateWithAjax(destination);
        });
    }

    function getInitialTheme() {
        const savedTheme = localStorage.getItem(THEME_STORAGE_KEY);
        if (savedTheme === 'dark' || savedTheme === 'light') {
            return savedTheme;
        }

        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        return prefersDark ? 'dark' : 'light';
    }

    function createThemeToggleMarkup() {
        return `
            <div class="list-group-item border-0 py-3 theme-toggle-item" data-theme-toggle-item>
                <label class="theme-toggle-label mb-0 form-check form-switch">
                    <span class="theme-toggle-copy">
                        <i class="bi bi-moon-stars me-3"></i> Dark mode
                    </span>
                    <input class="form-check-input theme-toggle-input" type="checkbox" role="switch" aria-label="Toggle dark mode">
                </label>
            </div>
        `;
    }

    function createFallbackThemeOffcanvasMarkup() {
        return `
            <button
                type="button"
                class="btn theme-fab"
                data-bs-toggle="offcanvas"
                data-bs-target="#themeMenu"
                aria-controls="themeMenu"
                aria-label="Open theme menu"
            >
                Theme
            </button>

            <div class="offcanvas offcanvas-end" tabindex="-1" id="themeMenu" aria-labelledby="themeMenuLabel">
                <div class="offcanvas-header border-bottom">
                    <h5 class="offcanvas-title fw-bold" id="themeMenuLabel">Appearance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body"></div>
            </div>
        `;
    }

    function ensureOffcanvasForThemeToggle() {
        if (document.querySelector('.offcanvas')) {
            return;
        }

        document.body.insertAdjacentHTML('beforeend', createFallbackThemeOffcanvasMarkup());
    }

    function injectThemeToggleToMenus() {
        const offcanvasBodies = document.querySelectorAll('.offcanvas .offcanvas-body');
        if (!offcanvasBodies.length) {
            return;
        }

        offcanvasBodies.forEach((menuBody) => {
            if (menuBody.querySelector('[data-theme-toggle-item]')) {
                return;
            }

            const menuList = menuBody.querySelector('.list-group, .list-group-flush');
            if (menuList) {
                menuList.insertAdjacentHTML('afterbegin', createThemeToggleMarkup());
            } else {
                menuBody.insertAdjacentHTML('afterbegin', createThemeToggleMarkup());
            }
        });

        syncThemeToggleState(document.documentElement.getAttribute('data-theme') || 'light');
    }

    function loadCurrentUserProfile() {
        const inCartPath = window.location.pathname.replace(/\\/g, '/').includes('/cart/');
        const avatarEndpoint = inCartPath ? '../db/action/current_user_avatar.php' : 'db/action/current_user_avatar.php';

        fetch(avatarEndpoint, { credentials: 'same-origin' })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Avatar request failed');
                }
                return response.json();
            })
            .then((data) => {
                if (!data || !data.avatar_url) {
                    return;
                }
                applyUserProfile(data);
            })
            .catch(() => {
                // Keep default avatar when request fails.
            });
    }

    function initializeLanguageSelector() {
        let isApplyingLanguage = false;

        function ensureLanguageTrigger() {
            const existingTrigger = document.querySelector('button[data-language-trigger]') || document.querySelector('.navbar .btn img[src*="world.png"]');
            if (existingTrigger) {
                return;
            }

            const fallbackButton = document.createElement('button');
            fallbackButton.type = 'button';
            fallbackButton.className = 'btn language-fab';
            fallbackButton.setAttribute('data-language-trigger', 'true');
            fallbackButton.setAttribute('aria-label', 'Select language');
            fallbackButton.innerHTML = '<i class="bi bi-translate me-1"></i> Language';
            document.body.appendChild(fallbackButton);
        }

        const supportedLanguages = [
            { value: 'en', label: 'English' },
            { value: 'fil', label: 'Filipino' }
        ];

        function getCurrentLanguage() {
            return getStoredLanguage();
        }

        function translateText(selector, text) {
            document.querySelectorAll(selector).forEach((el) => {
                if (!el) {
                    return;
                }

                const icon = el.querySelector('i');
                if (icon) {
                    const iconMarkup = icon.outerHTML;
                    el.innerHTML = `${iconMarkup} ${text}`;
                } else {
                    el.textContent = text;
                }
            });
        }

        function applyLanguageToPage(langValue) {
            const t = getLanguagePack(langValue);

            translateText('.text-center.mt-3 a[href="index.php"]', t.home);
            translateText('.text-center.mt-3 a[href*="sort=sales"]', t.trending);
            translateText('.text-center.mt-3 a[href="product-list.php"]:nth-of-type(1)', t.categories);
            translateText('.text-center.mt-3 a[href="product-list.php"]:nth-of-type(2)', t.productList);

            document.querySelectorAll('input[type="search"]').forEach((input) => {
                input.setAttribute('placeholder', t.searchPlaceholder);
            });

            translateText('.offcanvas-title#profileMenuLabel', t.myAccount);
            translateText('.theme-toggle-copy', t.darkMode);
            translateText('.list-group-item[href*="profilepage.php"]', t.viewProfile);
            translateText('.list-group-item[href*="orderHistory.php"]', t.myOrders);
            translateText('.list-group-item[href*="logout.php"]', t.signOut);
            translateText('.offcanvas .text-center h6.fw-bold', t.welcomeBack);
            translateText('.offcanvas .text-center p.small.text-muted', t.manageOrders);

            translateText('.form-title', window.location.pathname.includes('registerpage.php') ? t.registerTitle : (window.location.pathname.includes('updatepassword.php') ? t.updatePasswordTitle : t.loginTitle));
            translateText('.form-subtitle', window.location.pathname.includes('registerpage.php') ? t.registerSubtitle : (window.location.pathname.includes('updatepassword.php') ? t.updatePasswordSubtitle : t.loginSubtitle));
            translateText('button.btn-aces[type="submit"]', window.location.pathname.includes('registerpage.php') ? t.registerTitle : (window.location.pathname.includes('updatepassword.php') ? t.updatePassword : t.signIn));
            translateText('a[href*="updatepassword.php"]', t.forgotPassword);
            translateText('a[href*="registerpage.php"]', t.signUp);
            translateText('a[href*="loginpage.php"]', t.login);

            const rememberText = document.querySelector('footer p.small.text-muted');
            if (rememberText && window.location.pathname.includes('updatepassword.php')) {
                rememberText.childNodes.forEach((node) => {
                    if (node.nodeType === Node.TEXT_NODE) {
                        node.textContent = `${t.rememberPassword} `;
                    }
                });
            }

            const fallbackTrigger = document.querySelector('button[data-language-trigger]');
            if (fallbackTrigger && fallbackTrigger.closest('.navbar') === null) {
                fallbackTrigger.setAttribute('aria-label', t.language);
                fallbackTrigger.innerHTML = `<i class="bi bi-translate me-1"></i> ${t.language}`;
            }

            applyHardcodedTextOverrides(langValue);
        }

        function scheduleLanguageApply(langValue) {
            if (isApplyingLanguage) {
                return;
            }

            isApplyingLanguage = true;
            window.requestAnimationFrame(() => {
                applyLanguageToPage(langValue);
                isApplyingLanguage = false;
            });
        }

        function setCurrentLanguage(langValue) {
            localStorage.setItem(LANGUAGE_STORAGE_KEY, langValue);
            document.documentElement.setAttribute('lang', langValue === 'fil' ? 'fil' : 'en');
            scheduleLanguageApply(langValue);
        }

        function buildDropdownMarkup(selectedLang) {
            const optionsMarkup = supportedLanguages.map((lang) => {
                const selectedClass = lang.value === selectedLang ? ' is-selected' : '';
                const selectedIcon = lang.value === selectedLang ? '<i class="bi bi-check2 ms-auto"></i>' : '';
                return `<button type="button" class="lang-option${selectedClass}" data-lang-value="${lang.value}">${lang.label}${selectedIcon}</button>`;
            }).join('');

            return `<div class="lang-dropdown" role="menu" aria-label="Language selection">${optionsMarkup}</div>`;
        }

        function closeExistingDropdown() {
            const existing = document.querySelector('.lang-dropdown');
            if (existing) {
                existing.remove();
            }
        }

        function openDropdown(triggerButton) {
            closeExistingDropdown();

            const selectedLang = getCurrentLanguage();
            const wrapper = document.createElement('div');
            wrapper.innerHTML = buildDropdownMarkup(selectedLang);
            const dropdown = wrapper.firstElementChild;
            if (!dropdown) {
                return;
            }

            document.body.appendChild(dropdown);

            const triggerRect = triggerButton.getBoundingClientRect();
            const top = triggerRect.bottom + window.scrollY + 10;
            const left = triggerRect.left + window.scrollX - 32;
            dropdown.style.top = `${top}px`;
            dropdown.style.left = `${Math.max(12, left)}px`;
        }

        ensureLanguageTrigger();

        const triggerButtons = new Set();
        document.querySelectorAll('button[data-language-trigger]').forEach((button) => {
            triggerButtons.add(button);
        });

        document.querySelectorAll('.navbar .btn img[src*="world.png"]').forEach((triggerImage) => {
            const triggerButton = triggerImage.closest('button');
            if (!triggerButton) {
                return;
            }
            triggerButtons.add(triggerButton);
        });

        triggerButtons.forEach((triggerButton) => {

            triggerButton.setAttribute('aria-label', 'Select language');
            triggerButton.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                const hasOpenDropdown = !!document.querySelector('.lang-dropdown');
                if (hasOpenDropdown) {
                    closeExistingDropdown();
                    return;
                }

                openDropdown(triggerButton);
            });
        });

        document.addEventListener('click', function (event) {
            const option = event.target.closest('.lang-option');
            if (option) {
                const selectedLang = option.getAttribute('data-lang-value') || 'en';
                setCurrentLanguage(selectedLang);
                closeExistingDropdown();
                return;
            }

            const clickedInsideDropdown = event.target.closest('.lang-dropdown');
            const clickedTrigger = event.target.closest('.navbar .btn');
            if (!clickedInsideDropdown && !clickedTrigger) {
                closeExistingDropdown();
            }
        });

        setCurrentLanguage(getCurrentLanguage());

        const languageMutationObserver = new MutationObserver(() => {
            scheduleLanguageApply(getCurrentLanguage());
        });

        languageMutationObserver.observe(document.body, {
            childList: true,
            characterData: true,
            subtree: true
        });
    }

    function initializeGlobalProductSearch() {
        const searchForms = document.querySelectorAll('form[role="search"]');
        if (!searchForms.length) {
            return;
        }

        searchForms.forEach((form) => {
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                const searchInput = form.querySelector('input[type="search"]');
                const query = searchInput ? searchInput.value.trim() : '';

                const inCartPath = window.location.pathname.replace(/\\/g, '/').includes('/cart/');
                const targetPath = inCartPath ? '../product-list.php' : 'product-list.php';

                const params = new URLSearchParams();
                if (query !== '') {
                    params.set('q', query);
                }

                const destination = params.toString() ? `${targetPath}?${params.toString()}` : targetPath;
                navigateWithAjax(destination);
            });
        });
    }

    applyTheme(getInitialTheme());
    initializeAjaxNavigation();
    initializeAjaxFormNavigation();

    if (!ajaxOnlyMode) {
        ensureOffcanvasForThemeToggle();
        injectThemeToggleToMenus();
        initializeGlobalProductSearch();
        initializeLanguageSelector();

        const themeToggleObserver = new MutationObserver(() => {
            injectThemeToggleToMenus();
        });

        themeToggleObserver.observe(document.body, { childList: true, subtree: true });

        document.addEventListener('input', function (event) {
            const target = event.target;
            if (!(target instanceof HTMLInputElement) || !target.classList.contains('theme-toggle-input')) {
                return;
            }

            applyTheme(target.checked ? 'dark' : 'light');
        });
    }

    document.addEventListener('click', function (event) {
        const logoutLink = event.target.closest('a[href*="db/action/logout.php"]');
        if (!logoutLink) {
            return;
        }

        event.preventDefault();

        const logoutUrl = logoutLink.getAttribute('href') || 'db/action/logout.php';

        if (typeof Swal === 'undefined') {
            if (window.confirm('Are you sure you want to sign out?')) {
                window.location.href = logoutUrl;
            }
            return;
        }

        Swal.fire({
            icon: 'question',
            title: getTranslatedValue(getStoredLanguage(), 'signOutQuestion', 'Sign out?'),
            text: getTranslatedValue(getStoredLanguage(), 'signOutBody', 'You will need to log in again to continue.'),
            showCancelButton: true,
            confirmButtonText: getTranslatedValue(getStoredLanguage(), 'confirmSignOut', 'Yes, sign out'),
            cancelButtonText: getTranslatedValue(getStoredLanguage(), 'cancel', 'Cancel')
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = logoutUrl;
            }
        });
    });

    if (!ajaxOnlyMode) {
        loadCurrentUserProfile();
    }

    // ========== 1. SMOOTH SCROLLING FOR NAV LINKS ==========
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            // Only smooth scroll if it's an internal anchor link (#something)
            if (href && href.startsWith('#') && href !== '#') {
                e.preventDefault();
                const targetElement = document.querySelector(href);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    const isCartPage = window.location.pathname.replace(/\\/g, '/').includes('/cart/');
    if (!isCartPage) {
        const quantityPills = document.querySelectorAll('.quantity-pill');
        quantityPills.forEach(pill => {
            const increaseBtn = pill.querySelector('.increase-btn');
            const decreaseBtn = pill.querySelector('.decrease-btn');
            const quantitySpan = pill.querySelector('.quantity');

            if (increaseBtn && decreaseBtn && quantitySpan) {
                increaseBtn.addEventListener('click', function () {
                    let quantity = parseInt(quantitySpan.textContent) || 0;
                    quantity++;
                    quantitySpan.textContent = quantity;
                });

                decreaseBtn.addEventListener('click', function () {
                    let quantity = parseInt(quantitySpan.textContent) || 0;
                    if (quantity > 0) {
                        quantity--;
                        quantitySpan.textContent = quantity;
                    }
                });
            }
        });
    }

    // ========== 3. PAYMENT SUCCESS ALERT (SWEETALERT2) ==========
    const payBtn = document.getElementById('pay-now-btn');
    if (payBtn) {
        payBtn.addEventListener('click', function () {
            // SweetAlert2 must be loaded separately (CDN)
            Swal.fire({
                icon: "success",
                iconColor: "#eab543",
                title: getTranslatedValue(getStoredLanguage(), 'paymentSuccessful', 'Payment Successful!'),
                text: getTranslatedValue(getStoredLanguage(), 'paymentProcessed', 'Your payment has been processed successfully.'),
                confirmButtonText: "OK",
                footer: `<a href="orderHistory.php">${getTranslatedValue(getStoredLanguage(), 'orderDetailsHere', 'Order details here!')}</a>`,
                customClass: {
                    popup: 'swal-popup',
                    icon: 'swal-icon',
                    title: 'swal-title',
                    text: 'swal-text',
                    footer: 'swal-footer',
                    confirmButton: 'swal-confirm-button'
                },
                buttonsStyling: false
            });
        });
    }

    // ========== 4. ADD TO CART BUTTON ==========
    document.addEventListener('click', function(e) {
        if (!e.target.classList.contains('add-to-cart-btn')) return;
        e.preventDefault();
        const pid = e.target.dataset.productId;
        if (!pid) return;
        const qtyEl = document.getElementById('qtyValue');
        const quantity = qtyEl ? parseInt(qtyEl.textContent.trim()) || 1 : 1;        
        fetch('db/action/cart_action.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'action=add&product_id=' + pid + '&quantity=' + quantity
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ 
                    icon:'success', 
                    title: getTranslatedValue(getStoredLanguage(), 'addedTitle', 'Added!'), 
                    text: getTranslatedValue(getStoredLanguage(), 'addedToBasketText', 'Item added to your basket.'), 
                    timer:1500, 
                    showConfirmButton:false 
                });
            } else {
                Swal.fire({ 
                    icon:'error', 
                    title: getTranslatedValue(getStoredLanguage(), 'oopsTitle', 'Oops'), 
                    text: data.message || getTranslatedValue(getStoredLanguage(), 'couldNotAddItem', 'Could not add item.') 
                });
            }
        })
        .catch(err => {
            console.error('Error:', err);
            Swal.fire({ 
                icon:'error', 
                title: getTranslatedValue(getStoredLanguage(), 'errorTitle', 'Error'), 
                text: getTranslatedValue(getStoredLanguage(), 'failedToAddItem', 'Failed to add item to cart') 
            });
        });
    });

    // ========== 5. BUY NOW BUTTON ==========
document.addEventListener('click', function(e) {
    if (!e.target.classList.contains('buy-now-btn')) return;
    e.preventDefault();

    const pid = e.target.dataset.productId;
    if (!pid) return;

    const inCartPath = window.location.pathname.replace(/\\/g, '/').includes('/cart/');
    const cartActionUrl = inCartPath ? '../db/action/cart_action.php' : 'db/action/cart_action.php';
    const cartPageUrl   = inCartPath ? 'cart.php' : 'cart/cart.php';

    fetch(cartActionUrl, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=add&product_id=' + pid + '&quantity=1'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = cartPageUrl + '?buynow=' + pid;
        } else {
            Swal.fire({ 
                icon: 'error', 
                title: 'Oops', 
                text: data.message || 'Could not add item.' 
            });
        }
    })
    .catch(() => {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to process Buy Now.' });
    });
});
});