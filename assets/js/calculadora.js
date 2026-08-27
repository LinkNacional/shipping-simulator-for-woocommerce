// Aplica os recursos migrados da "Calculadora de frete" (woo-better) no
// componente do shipping-simulator de forma aditiva, sem alterar o template
// nem o CSS original.
window.addEventListener('DOMContentLoaded', function () {
    var data = window.wcShippingSimulatorCalcData || {};
    if (!data.context) return;

    var iconFilters = {
        'black-icon': 'brightness(0) saturate(100%) invert(0) sepia(0%) saturate(0%) hue-rotate(0deg) brightness(0%) contrast(100%)',
        'gray-icon': 'brightness(0) saturate(100%) invert(90%) sepia(0%) saturate(0%) hue-rotate(0deg) brightness(95%) contrast(90%)',
        'red-icon': 'brightness(0) saturate(100%) invert(14%) sepia(100%) saturate(7458%) hue-rotate(1deg) brightness(96%) contrast(105%)',
        'pink-icon': 'brightness(0) saturate(100%) invert(50%) sepia(100%) saturate(3000%) hue-rotate(300deg) brightness(95%) contrast(105%)',
        'green-icon': 'brightness(0) saturate(100%) invert(39%) sepia(100%) saturate(2000%) hue-rotate(90deg) brightness(90%) contrast(100%)',
        'blue-icon': 'brightness(0) saturate(100%) invert(30%) sepia(100%) saturate(2000%) hue-rotate(200deg) brightness(90%) contrast(100%)'
    };

    function applyStyles(el, styles) {
        if (!el || !styles) return;
        Object.keys(styles).forEach(function (prop) {
            var value = styles[prop];
            if (value === '' || value === null || value === undefined) return;
            el.style.setProperty(prop, value, 'important');
        });
    }

    function injectIcon(input) {
        if (!data.icon) return;

        var wrap = input.parentElement;
        if (!wrap || !wrap.classList.contains('wc-shipping-sim-input-wrap')) {
            wrap = document.createElement('div');
            wrap.className = 'wc-shipping-sim-input-wrap';
            input.parentNode.insertBefore(wrap, input);
            wrap.appendChild(input);
        }

        wrap.style.position = 'relative';
        wrap.style.flex = '1';
        wrap.style.maxWidth = '12em';
        wrap.style.marginRight = '0.5em';

        input.style.width = '100%';
        input.style.maxWidth = 'none';
        input.style.marginRight = '0';
        input.style.paddingRight = '2.2em';
        input.style.boxSizing = 'border-box';

        wrap.querySelectorAll('.wc-shipping-sim-input-icon').forEach(function (el) {
            el.remove();
        });

        var icon = document.createElement('img');
        icon.src = data.icon;
        icon.alt = '';
        icon.setAttribute('aria-hidden', 'true');
        icon.className = 'wc-shipping-sim-input-icon';
        icon.style.position = 'absolute';
        icon.style.right = '0.6em';
        icon.style.top = '50%';
        icon.style.transform = 'translateY(-50%)';
        icon.style.width = '20px';
        icon.style.height = '20px';
        icon.style.pointerEvents = 'none';

        var filter = iconFilters[data.iconColor];
        if (filter) icon.style.filter = filter;

        wrap.appendChild(icon);
    }

    function moveSimulator(el) {
        var position = data.position || 'top';
        var selector = data.customSelector || '';

        if (position === 'custom' && selector) {
            var target = document.querySelector(selector);
            if (target) {
                target.appendChild(el);
                return;
            }
        }

        var summary = el.closest('.summary');
        if (!summary) return;

        if (position === 'top') {
            summary.insertBefore(el, summary.firstChild);
        } else if (position === 'middle') {
            var cartForm = summary.querySelector('form.cart');
            if (cartForm) {
                summary.insertBefore(el, cartForm);
            }
        } else if (position === 'bottom') {
            summary.appendChild(el);
        }
    }

    document.querySelectorAll('#wc-shipping-sim').forEach(function (root) {
        var input = root.querySelector('.input-postcode');
        var button = root.querySelector('.button.submit');

        if (input) {
            applyStyles(input, data.inputStyles);
            if (data.fontFamily) input.style.fontFamily = data.fontFamily;
            injectIcon(input);
        }

        if (button) {
            applyStyles(button, data.buttonStyles);
            if (data.fontFamily) button.style.fontFamily = data.fontFamily;
        }

        if (data.context === 'product') {
            moveSimulator(root);
        }
    });
});
