document.addEventListener('DOMContentLoaded', function () {
    // ── "Desabilitado" apenas VISUAL (ver AGENTS.md § 5-C) ────────────────────
    //
    // REASON: input com o atributo `disabled` NÃO vai no POST. O
    // `WC_Admin_Settings::save_fields()` recebe `null` e mantém o valor ANTIGO
    // da option — a tela mostrava "Não"/cinza (o JS ainda forçava o valor) e a
    // calculadora continuava com o recurso ativo. O bloqueio é só de aparência:
    // o input segue habilitado e o valor exibido é o que fica salvo.
    function setFakeDisabled(elements, isDisabled) {
        if (!elements) return;

        const lista = elements.nodeType === 1 ? [elements] : elements;

        Array.prototype.forEach.call(lista, function (element) {
            if (!element) return;

            if (window.wcShippingSimulatorFakeDisabled) {
                window.wcShippingSimulatorFakeDisabled(element, isDisabled);
                return;
            }

            // Fallback (script auxiliar ausente): só aparência — o atributo
            // `disabled` NUNCA é aplicado.
            element.removeAttribute('disabled');
            element.disabled = false;
            element.style.opacity = isDisabled ? '0.6' : '';
            element.style.cursor = isDisabled ? 'not-allowed' : '';
        });
    }

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

        // Campos de TEXTO/ÁREA travados usam `readOnly` (e não `disabled`): o
        // valor continua indo no POST. Só o <select> e os radios — que não têm
        // como ser travados sem sair do POST — passam pelo helper visual.
        function setTextState(element, isReadOnly) {
            if (!element) return;

            element.readOnly = isReadOnly;
            element.style.backgroundColor = isReadOnly ? '#f1f1f1' : '';
            element.style.cursor = isReadOnly ? 'not-allowed' : '';
        }

        // Marca "Não" e AVISA quem depende do campo (ex.: o preview da barra de
        // progresso em AdminLayoutBehaviors) — equivale ao `radio.click()` antigo,
        // mas sem depender do clique (que o bloqueio visual intercepta).
        function checkNo(radios) {
            radios.forEach(function (radio) {
                const shouldCheck = (radio.value === 'no');

                if (radio.checked === shouldCheck) {
                    return;
                }

                radio.checked = shouldCheck;
                radio.dispatchEvent(new Event('change', { bubbles: true }));
            });
        }

        function updateMinimumFreeShippingValue() {
            const selectedOption = Array.from(minimumFreeShippingRadios).find(radio => radio.checked)?.value;

            if (selectedOption === 'yes') {
                setTextState(minimumFreeShippingValue, false);
                setTextState(minimumFreeShippingMessage, false);
                setTextState(minimumFreeShippingSuccessMessage, false);

                setFakeDisabled(minimumFreeShippingCalcBase, false);
                setFakeDisabled(onlyFreeShippingRadios, false);
                setFakeDisabled(avoidDuplicationRadios, false);
                setFakeDisabled(progressBarRadios, false);
            } else if (selectedOption === 'no') {
                if (minimumFreeShippingValue) {
                    minimumFreeShippingValue.value = 0;
                }
                if (minimumFreeShippingCalcBase) {
                    minimumFreeShippingCalcBase.value = 'subtotal';
                }

                setTextState(minimumFreeShippingValue, true);
                setTextState(minimumFreeShippingMessage, true);
                setTextState(minimumFreeShippingSuccessMessage, true);

                // O bloqueio é só VISUAL e o valor coerente ("Não") fica marcado,
                // então ele VAI no POST — tela e banco não divergem mais.
                setFakeDisabled(minimumFreeShippingCalcBase, true);
                setFakeDisabled(onlyFreeShippingRadios, true);
                setFakeDisabled(avoidDuplicationRadios, true);
                setFakeDisabled(progressBarRadios, true);

                checkNo(onlyFreeShippingRadios);
                checkNo(avoidDuplicationRadios);
                checkNo(progressBarRadios);
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
            const fieldRow = disableShipping.closest('.admin-layout-field-parent-flex') || disableShipping.closest('.forminp');
            const descBox = fieldRow
                ? (fieldRow.querySelector('.admin-layout-field-body-description') || fieldRow.querySelector('p.description'))
                : null;
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
