window.addEventListener('DOMContentLoaded', () => {
    const $ = (s, root = document) => root.querySelector(s);
    const on = (el, evt, cb) => el.addEventListener(evt, cb);

    window.wc_shipping_simulators = [];

    document.querySelectorAll('#wc-shipping-sim').forEach((root) => {
        const form = $('#wc-shipping-sim-form', root);
        const params = JSON.parse(form.dataset.params);

        const input = $('.input-postcode', form);
        const button = $('.button.submit', form);
        const results = $('#wc-shipping-sim-results', root);

        const I = (val) => val;
        const hooks = {
            filterFormData: I,
            filterXHR: I,
            filterResults: I,
            filterProduct: I,
            filterQuantity: I,
            filterPostcodeMaxLength: () =>
                config.postcode_mask ? config.postcode_mask.length : 15,
            beforeSubmit: (xhr) => {
                config.hooks.resultsHandler('');
                input.disabled = true;
                button.classList.add('loading');
            },
            afterSubmit: (xhr) => {
                input.disabled = false;
                button.classList.remove('loading');
            },
            resultsHandler: (html) => {
                results.ariaBusy = !html ? 'true' : 'false';
                results.innerHTML = html;
            },
            errorHandler: (message, data) => {
                alert(message || config.errors.unexpected);
                console.error('wc-shipping-simulator request error:', data);
            },
            submitHandler: (evt) => {
                evt.preventDefault();
                if (config.requesting) return;

                const product = getProduct();
                if (0 === product.id) return;

                config.requesting = true;

                const variation = product.variation_id
                    ? '&variation_id=' + product.variation_id
                    : '';
                const qty = getQuantity();

                const formData = config.hooks.filterFormData(
                    `action=${params.ajax_action}&postcode=${
                        input.value
                    }&product_id=${product.id}&quantity=${
                        qty >= 1 ? qty : 1
                    }${variation}`
                );

                let xhr = new XMLHttpRequest();

                xhr.open('POST', params.ajax_url, true);

                xhr.setRequestHeader(
                    'Content-Type',
                    'application/x-www-form-urlencoded'
                );

                xhr.timeout = +config.timeout || 0;

                xhr.onload = () => {
                    config.requesting = false;
                    config.hooks.afterSubmit(xhr);

                    try {
                        const res = JSON.parse(xhr.responseText);
                        if (res.success) {
                            const results = config.hooks.filterResults(
                                res.results_html ? res.results_html : ''
                            );
                            config.hooks.resultsHandler(results);
                        } else {
                            config.hooks.errorHandler(res.error, res);
                        }
                    } catch (e) {
                        config.hooks.errorHandler(null, e);
                    }
                };

                xhr.ontimeout = () => {
                    config.requesting = false;
                    config.hooks.afterSubmit(xhr);
                    config.hooks.errorHandler(config.errors.timeout, 'timeout');
                };

                xhr = config.hooks.filterXHR(xhr);

                config.hooks.beforeSubmit(xhr);

                xhr.send(formData);
            },
            inputMaskHandler: () => {
                input.maxLength = config.hooks.filterPostcodeMaxLength();
                if (config.postcode_mask) {
                    // apply input mask
                    on(input, 'input', (evt) => {
                        const mask = config.postcode_mask;
                        input.value = applyMask(input.value || '', mask);
                        input.maxLength = config.postcode_mask.length;
                    });

                    input.dispatchEvent(new Event('input'));

                    // usage: applyMask('01153000', 'XX XXX-XXX') // returns "01 153-000"
                    function applyMask(text, mask, symbol = 'X') {
                        if (!mask) return text;
                        let result = '';
                        // remove all non allphanumerics
                        const _text = (text + '').replace(/[^0-9]/g, '');
                        for (
                            let i = 0, j = 0, len = mask.length;
                            i < len;
                            i++
                        ) {
                            if (!_text[j]) break;
                            if (symbol === mask[i]) {
                                result += _text[j];
                                j++;
                            } else {
                                result += mask[i] || '';
                                j = j > 0 ? j-- : 0;
                            }
                        }
                        return result;
                    }
                }
            },
        };
        const config = {
            ...params,
            requesting: false,
            hooks,
            root,
        };

        // Use this global object to manipulate the simulator
        wc_shipping_simulators.push(config);
        document.dispatchEvent(
            new CustomEvent('wc_shipping_simulator:init', {
                detail: config,
            })
        );

        root.style.display = config.root_display;

        config.hooks.inputMaskHandler && config.hooks.inputMaskHandler();

        on(form, 'submit', config.hooks.submitHandler);

        if (config.auto_submit && config.postcode_mask) {
            on(input, 'input', (evt) => {
                const maxLength = config.hooks.filterPostcodeMaxLength();
                if (maxLength > 0 && input.value.length === maxLength) {
                    input.blur();
                    button.click();
                }
            });
        }

        function getProduct() {
            const product = {
                type: params.product_type,
                id: params.product_id,
            };
            if ('variable' === product.type) {
                const variation_id_input = $(
                    '.variations_form .variation_id'
                );
                product.variation_id = variation_id_input
                    ? variation_id_input.value
                    : 0;
                if (config.requires_variation && !product.variation_id) {
                    const error = wc_add_to_cart_variation_params
                        ? wc_add_to_cart_variation_params
                              .i18n_make_a_selection_text
                        : '';
                    config.hooks.errorHandler(error, 'no_variation_selected');
                    product.id = 0; // abort the submit
                }
            }
            return config.hooks.filterProduct(product);
        }

        function getQuantity() {
            let value = 1;
            const input = $('.cart [name="quantity"]');
            if (input) {
                value = input.value;
            }
            return config.hooks.filterQuantity(+value || 1);
        }
    });
});
