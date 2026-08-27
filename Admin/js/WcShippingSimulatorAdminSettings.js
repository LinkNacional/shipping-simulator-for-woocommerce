document.addEventListener('DOMContentLoaded', function () {
    // ── Habilita/desabilita campos dependentes do "frete grátis por valor mínimo" ──
    const minimumFreeShippingRadios = document.querySelectorAll('input[name="wc_shipping_simulator_enable_min_free_shipping"]');

    if (minimumFreeShippingRadios.length > 0) {
        const minimumFreeShippingValue = document.getElementById('wc_shipping_simulator_min_free_shipping_value');
        const minimumFreeShippingCalcBase = document.getElementById('wc_shipping_simulator_free_shipping_calc_base');
        const minimumFreeShippingMessage = document.getElementById('wc_shipping_simulator_min_free_shipping_message');
        const minimumFreeShippingSuccessMessage = document.getElementById('wc_shipping_simulator_min_free_shipping_success_message');
        const onlyFreeShippingRadios = document.querySelectorAll('input[name="wc_shipping_simulator_only_free_shipping"]');
        const avoidDuplicationRadios = document.querySelectorAll('input[name="wc_shipping_simulator_avoid_free_shipping_duplication"]');
        const progressBarRadios = document.querySelectorAll('input[name="wc_shipping_simulator_enable_progress_bar_value"]');

        function updateMinimumFreeShippingValue() {
            const selectedOption = Array.from(minimumFreeShippingRadios).find(radio => radio.checked)?.value;

            if (selectedOption === 'yes') {
                if (minimumFreeShippingValue) {
                    minimumFreeShippingValue.readOnly = false;
                    minimumFreeShippingValue.style.backgroundColor = '';
                    minimumFreeShippingValue.style.cursor = '';
                }
                if (minimumFreeShippingCalcBase) {
                    minimumFreeShippingCalcBase.disabled = false;
                    minimumFreeShippingCalcBase.style.backgroundColor = '';
                    minimumFreeShippingCalcBase.style.cursor = '';
                }
                if (minimumFreeShippingMessage) {
                    minimumFreeShippingMessage.readOnly = false;
                    minimumFreeShippingMessage.style.backgroundColor = '';
                    minimumFreeShippingMessage.style.cursor = '';
                }
                if (minimumFreeShippingSuccessMessage) {
                    minimumFreeShippingSuccessMessage.readOnly = false;
                    minimumFreeShippingSuccessMessage.style.backgroundColor = '';
                    minimumFreeShippingSuccessMessage.style.cursor = '';
                }
                onlyFreeShippingRadios.forEach(radio => { radio.disabled = false; radio.style.cursor = ''; });
                avoidDuplicationRadios.forEach(radio => { radio.disabled = false; radio.style.cursor = ''; });
                progressBarRadios.forEach(radio => { radio.disabled = false; radio.style.cursor = ''; });
            } else if (selectedOption === 'no') {
                if (minimumFreeShippingValue) {
                    minimumFreeShippingValue.value = 0;
                    minimumFreeShippingValue.readOnly = true;
                    minimumFreeShippingValue.style.backgroundColor = '#f1f1f1';
                    minimumFreeShippingValue.style.cursor = 'not-allowed';
                }
                if (minimumFreeShippingCalcBase) {
                    minimumFreeShippingCalcBase.value = 'subtotal';
                    minimumFreeShippingCalcBase.disabled = true;
                    minimumFreeShippingCalcBase.style.backgroundColor = '#f1f1f1';
                    minimumFreeShippingCalcBase.style.cursor = 'not-allowed';
                }
                if (minimumFreeShippingMessage) {
                    minimumFreeShippingMessage.readOnly = true;
                    minimumFreeShippingMessage.style.backgroundColor = '#f1f1f1';
                    minimumFreeShippingMessage.style.cursor = 'not-allowed';
                }
                if (minimumFreeShippingSuccessMessage) {
                    minimumFreeShippingSuccessMessage.readOnly = true;
                    minimumFreeShippingSuccessMessage.style.backgroundColor = '#f1f1f1';
                    minimumFreeShippingSuccessMessage.style.cursor = 'not-allowed';
                }
                onlyFreeShippingRadios.forEach(radio => {
                    if (radio.value === 'no') { radio.click(); radio.checked = true; }
                    else if (radio.value === 'yes') { radio.checked = false; }
                    radio.disabled = true;
                    radio.style.cursor = 'not-allowed';
                });
                avoidDuplicationRadios.forEach(radio => {
                    if (radio.value === 'no') { radio.click(); radio.checked = true; }
                    else if (radio.value === 'yes') { radio.checked = false; }
                    radio.disabled = true;
                    radio.style.cursor = 'not-allowed';
                });
                progressBarRadios.forEach(radio => {
                    if (radio.value === 'no') { radio.click(); radio.checked = true; }
                    else if (radio.value === 'yes') { radio.checked = false; }
                    radio.disabled = true;
                    radio.style.cursor = 'not-allowed';
                });
            }
        }

        updateMinimumFreeShippingValue();
        minimumFreeShippingRadios.forEach(radio => {
            radio.addEventListener('change', updateMinimumFreeShippingValue);
        });
    }

    // ── Atualiza a descrição dinâmica do select "Opções de Frete e Entrega" ──
    const disableShipping = document.getElementById('wc_shipping_simulator_calc_disabled_shipping');
    if (disableShipping) {
        function initializeDescriptionUpdater() {
            const descBox = disableShipping.closest('.forminp')?.querySelector('p.description');
            if (descBox) {
                const descriptions = {
                    all: 'Todos os métodos de entrega e campos de endereço serão desabilitados.',
                    digital: 'Entrega será desabilitada apenas se o carrinho tiver somente produtos digitais.',
                    default: 'Entrega dinâmica será mantida conforme o padrão do Woocommerce.'
                };

                function updateDescription() {
                    const selected = disableShipping.value;
                    descBox.textContent = descriptions[selected] || '';
                }

                updateDescription();
                disableShipping.addEventListener('change', updateDescription);
                return true;
            }
            return false;
        }

        const observer = new MutationObserver(function () {
            if (initializeDescriptionUpdater()) {
                observer.disconnect();
            }
        });
        observer.observe(document.body, { childList: true, subtree: true });
        initializeDescriptionUpdater();
    }
});
