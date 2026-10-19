/**
 * Comportamentos das configurações da Calculadora de frete.
 *
 * Portado do antigo `WcShippingSimulatorAdminLayout.js`: o layout agora vem do
 * template PHP (`templates/WcShippingSimulatorAdminSettingsLayout.php`), e este
 * arquivo mantém apenas o que é comportamento:
 *
 * 1. Preview ao vivo do campo de CEP (produto e carrinho).
 * 2. Reflexo dos campos de estilo (cores, borda, raio) no preview.
 * 3. Ícones das opções de ícone e cor do ícone.
 * 4. Exibição condicional da "posição personalizada".
 * 5. Exibição condicional das opções de cache + botão "Limpar o cache".
 *
 * @package Shipping_Simulator
 */

(function () {
	'use strict';

	var PREFIX = 'wc_shipping_simulator_';
	var ajax = typeof wcShippingSimulatorAjax !== 'undefined' ? wcShippingSimulatorAjax : {};
	var icons = typeof wcShippingSimulatorIcons !== 'undefined' ? wcShippingSimulatorIcons : {};
	var fontClass = ajax.font_class || '';

	/** Linha (card) do campo no template. */
	function getFieldRow(fieldId) {
		var field = document.getElementById(fieldId) || document.querySelector('[name="' + fieldId + '"]');
		if (!field) return null;
		return field.closest('.admin-layout-field-parent-flex');
	}

	/** Container onde os campos são renderizados dentro do card. */
	function getInputWrapper(fieldId) {
		var row = getFieldRow(fieldId);
		if (!row) return null;
		return row.querySelector('.admin-layout-field-input-wrapper');
	}

	// ── Preview + eventos de estilo ────────────────────────────────────────
	function buildPreview(styleName) {
		var anchorId = PREFIX + 'calc_' + styleName + '_postcode_current_style';
		var anchor = document.getElementById(anchorId);
		if (!anchor) return;

		var wrapper = getInputWrapper(anchorId);
		if (!wrapper) return;

		var containerDiv = document.createElement('div');
		containerDiv.classList.add('wc-shipping-simulator-container-current-style');

		var inputButtonGroup = document.createElement('div');
		inputButtonGroup.classList.add('wc-shipping-simulator-input-button-group-current-style');

		var inputWrapper = document.createElement('div');
		inputWrapper.classList.add('wc-shipping-simulator-input-wrapper-current-style');

		var inputStyleComponents = {
			[PREFIX + 'calc_' + styleName + '_input_background_color_field_input']: 'backgroundColor',
			[PREFIX + 'calc_' + styleName + '_input_color_field_input']: 'color',
			[PREFIX + 'calc_' + styleName + '_input_border_width']: 'borderWidth',
			[PREFIX + 'calc_' + styleName + '_input_border_style']: 'borderStyle',
			[PREFIX + 'calc_' + styleName + '_input_border_color_field_input']: 'borderColor',
			[PREFIX + 'calc_' + styleName + '_input_border_radius']: 'borderRadius'
		};

		var placeholderInput = document.getElementById(PREFIX + 'calc_' + styleName + '_input_placeholder');

		var textInput = document.createElement('input');
		textInput.type = 'text';
		textInput.id = PREFIX + 'calc_' + styleName + '_input_current_style_postcode_fake_custom';
		textInput.placeholder = placeholderInput ? placeholderInput.value : 'Insira seu CEP';
		textInput.classList.add('wc-shipping-simulator-input-current-style');
		if (fontClass) textInput.classList.add(fontClass);
		textInput.style.cursor = 'pointer';
		textInput.readOnly = true;

		textInput.addEventListener('click', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var targetElement = document.getElementById(PREFIX + 'calc_' + styleName + '_input_background_color_field_input');
			if (targetElement) {
				targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
			}
		});

		Object.keys(inputStyleComponents).forEach(function (componentId) {
			var styleProperty = inputStyleComponents[componentId];
			var controlElement = document.getElementById(componentId);
			if (controlElement && controlElement.value) {
				textInput.style[styleProperty] = controlElement.value;
			}
		});

		var icon = document.createElement('img');
		icon.src = icons.transit || '';
		icon.alt = 'Ícone padrão';
		icon.classList.add('wc-shipping-simulator-icon-current-style');
		icon.classList.add('wc-shipping-simulator-input-icon');

		var colorSelect = document.getElementById(PREFIX + 'calc_' + styleName + '_input_icon_color');
		if (colorSelect) {
			colorSelect.addEventListener('change', function () {
				var selectedColor = colorSelect.value;
				document.querySelectorAll('.wc-shipping-simulator-input-icon').forEach(function (ic) {
					ic.classList.remove('black-icon', 'gray-icon', 'red-icon', 'pink-icon', 'green-icon', 'blue-icon');
					ic.classList.add(selectedColor);
				});
			});
			icon.classList.add(colorSelect.value);
		}

		inputWrapper.appendChild(textInput);
		inputWrapper.appendChild(icon);

		// Imagens + reflexo nas opções de ícone.
		document.querySelectorAll('input[name="' + PREFIX + 'calc_' + styleName + '_input_icon"]').forEach(function (option) {
			option.addEventListener('change', function () {
				if (icons[option.value]) {
					icon.src = icons[option.value];
					icon.alt = option.value;
				}
			});
			if (option.checked && icons[option.value]) {
				icon.src = icons[option.value];
				icon.alt = option.value;
			}
		});

		var buttonStyleComponents = {
			[PREFIX + 'calc_' + styleName + '_button_background_color_field_input']: 'backgroundColor',
			[PREFIX + 'calc_' + styleName + '_button_color_field_input']: 'color',
			[PREFIX + 'calc_' + styleName + '_button_border_width']: 'borderWidth',
			[PREFIX + 'calc_' + styleName + '_button_border_style']: 'borderStyle',
			[PREFIX + 'calc_' + styleName + '_button_border_color_field_input']: 'borderColor',
			[PREFIX + 'calc_' + styleName + '_button_border_radius']: 'borderRadius'
		};

		var button = document.createElement('button');
		button.textContent = 'CONSULTAR';
		button.id = PREFIX + 'calc_' + styleName + '_button_current_style_postcode_fake_custom';
		button.classList.add('wc-shipping-simulator-button-current-style');
		if (fontClass) button.classList.add(fontClass);

		Object.keys(buttonStyleComponents).forEach(function (componentId) {
			var styleProperty = buttonStyleComponents[componentId];
			var controlElement = document.getElementById(componentId);
			if (controlElement && controlElement.value) {
				button.style[styleProperty] = controlElement.value;
			}
		});

		button.addEventListener('click', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var targetElement = document.getElementById(PREFIX + 'calc_' + styleName + '_button_background_color_field_input');
			if (targetElement) {
				targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
			}
		});

		inputButtonGroup.appendChild(inputWrapper);
		inputButtonGroup.appendChild(button);
		containerDiv.appendChild(inputButtonGroup);

		var linkText = document.createElement('p');
		linkText.textContent = 'Não sei meu CEP';
		linkText.classList.add('wc-shipping-simulator-link-current-style');
		if (fontClass) linkText.classList.add(fontClass);
		containerDiv.appendChild(linkText);

		wrapper.insertBefore(containerDiv, anchor);
		// O campo original é só um proxy do preview — não deve aparecer.
		anchor.remove();
		startEvents(styleName);
	}

	function startEvents(styleName) {
		var validCssUnits = ['px', '%', 'em', 'rem', 'vh', 'vw', 'vmin', 'vmax', 'cm', 'mm', 'in', 'pt', 'pc', 'ex', 'ch'];

		function bindStyleComponents(components, target) {
			if (!target) return;
			Object.keys(components).forEach(function (componentId) {
				var cfg = components[componentId];
				var controlElement = document.getElementById(componentId);
				if (!controlElement) return;

				controlElement.addEventListener('change', function () {
					var value = controlElement.value;
					if (controlElement.type === 'text') {
						var regex = new RegExp('^\\d+(\\.\\d+)?(' + validCssUnits.join('|') + ')$');
						if (!regex.test(value)) {
							controlElement.value = cfg.default;
							target.style.setProperty(cfg.property, cfg.default, 'important');
							return;
						}
					}
					target.style.setProperty(cfg.property, value, 'important');
				});

				target.style.setProperty(cfg.property, cfg.default, 'important');
			});
		}

		bindStyleComponents({
			[PREFIX + 'calc_' + styleName + '_input_background_color_field_input']: { property: 'background-color', default: '#ffffff' },
			[PREFIX + 'calc_' + styleName + '_input_color_field_input']: { property: 'color', default: '#2C3338' },
			[PREFIX + 'calc_' + styleName + '_input_border_width']: { property: 'border-width', default: '1px' },
			[PREFIX + 'calc_' + styleName + '_input_border_style']: { property: 'border-style', default: 'solid' },
			[PREFIX + 'calc_' + styleName + '_input_border_color_field_input']: { property: 'border-color', default: '#ccc' },
			[PREFIX + 'calc_' + styleName + '_input_border_radius']: { property: 'border-radius', default: '4px' }
		}, document.getElementById(PREFIX + 'calc_' + styleName + '_input_current_style_postcode_fake_custom'));

		bindStyleComponents({
			[PREFIX + 'calc_' + styleName + '_button_background_color_field_input']: { property: 'background-color', default: '#0073aa' },
			[PREFIX + 'calc_' + styleName + '_button_color_field_input']: { property: 'color', default: '#ffffff' },
			[PREFIX + 'calc_' + styleName + '_button_border_width']: { property: 'border-width', default: '1px' },
			[PREFIX + 'calc_' + styleName + '_button_border_style']: { property: 'border-style', default: 'none' },
			[PREFIX + 'calc_' + styleName + '_button_border_color_field_input']: { property: 'border-color', default: 'transparent' },
			[PREFIX + 'calc_' + styleName + '_button_border_radius']: { property: 'border-radius', default: '4px' }
		}, document.getElementById(PREFIX + 'calc_' + styleName + '_button_current_style_postcode_fake_custom'));

		var placeholderInput = document.getElementById(PREFIX + 'calc_' + styleName + '_input_placeholder');
		var textInput = document.getElementById(PREFIX + 'calc_' + styleName + '_input_current_style_postcode_fake_custom');
		if (placeholderInput && textInput) {
			placeholderInput.addEventListener('change', function () {
				textInput.placeholder = placeholderInput.value;
			});
		}

		// Imagens nos radios de ícone.
		var iconMap = {
			transit: icons.transit,
			bill: icons.bill,
			truck: icons.truck,
			postcode: icons.postcode,
			zipcode: icons.zipcode
		};

		document.querySelectorAll('input[name="' + PREFIX + 'calc_' + styleName + '_input_icon"]').forEach(function (option) {
			var value = option.value;
			if (!iconMap[value]) return;

			var label = option.closest('label');
			if (!label || label.querySelector('.wc-shipping-simulator-input-icon')) return;

			var img = document.createElement('img');
			img.src = iconMap[value];
			img.alt = value;
			img.style.width = '40px';
			img.style.height = '40px';
			img.style.marginLeft = '10px';
			img.classList.add('wc-shipping-simulator-input-icon');

			var colorSelect = document.getElementById(PREFIX + 'calc_' + styleName + '_input_icon_color');
			img.classList.add(colorSelect ? colorSelect.value : 'black-icon');

			// O layout original mostra SÓ o ícone (o texto do radio é descartado).
			Array.prototype.slice.call(label.childNodes).forEach(function (node) {
				if (node !== option) {
					label.removeChild(node);
				}
			});

			label.appendChild(option);
			label.appendChild(img);
			label.style.display = 'flex';
			label.style.alignItems = 'center';
			label.style.setProperty('margin', '14px 0', 'important');
		});
	}

	// ── Posição personalizada ──────────────────────────────────────────────
	function handleCustomPosition(styleName) {
		var customPosition = document.getElementById(PREFIX + 'calc_' + styleName + '_custom_position');
		var selectPosition = document.getElementById(PREFIX + 'calc_' + styleName + '_input_position');
		if (!customPosition || !selectPosition) return;

		var row = customPosition.closest('.admin-layout-field-parent-flex');

		function toggle() {
			if (row) {
				row.style.display = selectPosition.value === 'custom' ? '' : 'none';
			}
		}

		toggle();
		selectPosition.addEventListener('change', toggle);
	}

	// ── Cache ──────────────────────────────────────────────────────────────
	function handleCacheSettings() {
		var cacheExpirationTime = document.getElementById(PREFIX + 'calc_cache_expiration_time');
		var autoCacheReset = document.getElementById(PREFIX + 'calc_enable_auto_cache_reset');
		if (!cacheExpirationTime || !autoCacheReset) return;

		var cacheExpirationRow = cacheExpirationTime.closest('.admin-layout-field-parent-flex');
		var autoCacheResetRow = autoCacheReset.closest('.admin-layout-field-parent-flex');

		function toggle() {
			var checked = document.querySelector('input[name="' + PREFIX + 'calc_enable_auto_postcode_search"]:checked');
			var selectedValue = checked ? checked.value : null;
			if (cacheExpirationRow) cacheExpirationRow.style.display = selectedValue === 'yes' ? '' : 'none';
			if (autoCacheResetRow) autoCacheResetRow.style.display = selectedValue === 'yes' ? '' : 'none';
		}

		toggle();
		document.querySelectorAll('input[name="' + PREFIX + 'calc_enable_auto_postcode_search"]').forEach(function (radio) {
			radio.addEventListener('change', toggle);
		});
	}

	function handleClearCacheButton() {
		var cacheResetInput = document.getElementById(PREFIX + 'calc_enable_auto_cache_reset');
		if (!cacheResetInput) return;

		cacheResetInput.style.display = 'none';

		var clearCacheButton = document.createElement('button');
		clearCacheButton.type = 'button';
		clearCacheButton.className = 'wc-shipping-simulator-cache-button components-button is-primary';
		clearCacheButton.textContent = 'Limpar o cache';

		function generateNewToken() {
			var prefix = 'WCBCB_';
			var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			var randomPart = '';
			for (var i = 0; i < 19; i++) {
				randomPart += characters.charAt(Math.floor(Math.random() * characters.length));
			}
			cacheResetInput.value = prefix + randomPart;
		}

		clearCacheButton.addEventListener('click', function (e) {
			e.preventDefault();
			if (!window.confirm('Tem certeza de que deseja limpar o cache?')) {
				return;
			}
			generateNewToken();
			clearCacheButton.textContent = 'Novo token gerado!';
			clearCacheButton.style.backgroundColor = '#00a32a';
			clearCacheButton.style.color = '#fff';
			setTimeout(function () {
				clearCacheButton.textContent = 'Limpar o cache';
				clearCacheButton.disabled = false;
				clearCacheButton.style.backgroundColor = '';
				clearCacheButton.style.color = '';
			}, 2000);
		});

		var anchor = getInputWrapper(PREFIX + 'calc_enable_auto_cache_reset');
		if (anchor) {
			anchor.appendChild(clearCacheButton);
		}
	}

	// ── Sincroniza os campos de cor (swatch ↔ valor salvo) ─────────────────
	function handleColorFields() {
		document.querySelectorAll('.admin-layout-color-swatch').forEach(function (swatch) {
			var textId = swatch.getAttribute('data-target');
			var textInput = textId ? document.getElementById(textId) : null;
			if (!textInput) return;

			swatch.addEventListener('input', function () {
				textInput.value = swatch.value;
			});

			textInput.addEventListener('input', function () {
				if (/^#[0-9a-fA-F]{6}$/.test(textInput.value)) {
					swatch.value = textInput.value;
				}
			});
		});
	}

	// ── Preview da barra de progresso ──────────────────────────────────────
	function addProgressBarPreview() {
		var progressBarRadios = document.querySelectorAll('input[name="' + PREFIX + 'enable_progress_bar_value"]');
		if (!progressBarRadios.length) return;

		var wrapper = getInputWrapper(PREFIX + 'enable_progress_bar_value');
		if (!wrapper || wrapper.querySelector('.wc-shipping-simulator-progress-preview')) return;

		var barImages = typeof wcShippingSimulatorBarImages !== 'undefined' ? wcShippingSimulatorBarImages : {};

		var previewContainer = document.createElement('div');
		previewContainer.className = 'wc-shipping-simulator-progress-preview';
		previewContainer.style.marginTop = '15px';

		var previewTitle = document.createElement('h4');
		previewTitle.textContent = 'Preview da Barra de Progresso:';
		previewTitle.style.cssText = 'margin: 0 0 10px 0; font-size: 14px; color: #333;';

		var imagesContainer = document.createElement('div');
		imagesContainer.style.cssText = 'display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-start;';

		function buildPreviewBlock(img, alt, title) {
			var block = document.createElement('div');
			block.style.cssText = 'flex: 1; min-width: 200px; text-align: center;';
			var titleEl = document.createElement('p');
			titleEl.textContent = title;
			titleEl.style.cssText = 'margin: 0 0 8px 0; font-weight: bold; font-size: 13px; color: #0073aa;';
			var imgEl = document.createElement('img');
			imgEl.src = img || '';
			imgEl.alt = alt;
			imgEl.style.cssText = 'max-width: 100%; height: auto;';
			block.appendChild(titleEl);
			block.appendChild(imgEl);
			return { block: block, title: titleEl };
		}

		var withLabel = buildPreviewBlock(barImages.with_label, 'Barra com valores', 'Com valores na barra (Sim)');
		var withoutLabel = buildPreviewBlock(barImages.without_label, 'Barra sem valores', 'Sem valores na barra (Não)');

		imagesContainer.appendChild(withLabel.block);
		imagesContainer.appendChild(withoutLabel.block);

		previewContainer.appendChild(previewTitle);
		previewContainer.appendChild(imagesContainer);

		function updatePreviewHighlight() {
			var checked = Array.prototype.find.call(progressBarRadios, function (radio) {
				return radio.checked;
			});
			var selectedValue = checked ? checked.value : null;
			withLabel.block.style.opacity = selectedValue === 'yes' ? '1' : '0.5';
			withoutLabel.block.style.opacity = selectedValue === 'yes' ? '0.5' : '1';
			withLabel.title.style.color = selectedValue === 'yes' ? '#0073aa' : '#999';
			withoutLabel.title.style.color = selectedValue === 'yes' ? '#999' : '#0073aa';
		}

		updatePreviewHighlight();
		progressBarRadios.forEach(function (radio) {
			radio.addEventListener('change', updatePreviewHighlight);
		});

		wrapper.appendChild(previewContainer);
	}

	// ── Tempo de entrega somente leitura quando o recurso está desligado ────
	function handleDeliveryTimeReadOnly() {
		var minFreeShippingRadios = document.querySelectorAll('input[name="' + PREFIX + 'enable_min_free_shipping"]');
		var minDeliveryTimeInput = document.querySelector('input[name="' + PREFIX + 'min_free_shipping_delivery_time"]');
		var freeShippingByProductRadios = document.querySelectorAll('input[name="' + PREFIX + 'enable_free_shipping_by_product"]');
		var productDeliveryTimeInput = document.querySelector('input[name="' + PREFIX + 'free_shipping_by_product_delivery_time"]');

		function setState(radioName, input) {
			if (!input) return;
			var checked = document.querySelector('input[name="' + radioName + '"]:checked');
			if (checked && checked.value === 'no') {
				input.value = '';
				input.readOnly = true;
				input.style.opacity = '0.5';
				input.style.cursor = 'not-allowed';
			} else {
				input.readOnly = false;
				input.style.opacity = '1';
				input.style.cursor = '';
			}
		}

		setState(PREFIX + 'enable_min_free_shipping', minDeliveryTimeInput);
		setState(PREFIX + 'enable_free_shipping_by_product', productDeliveryTimeInput);

		minFreeShippingRadios.forEach(function (radio) {
			radio.addEventListener('change', function () {
				setState(PREFIX + 'enable_min_free_shipping', minDeliveryTimeInput);
			});
		});
		freeShippingByProductRadios.forEach(function (radio) {
			radio.addEventListener('change', function () {
				setState(PREFIX + 'enable_free_shipping_by_product', productDeliveryTimeInput);
			});
		});
	}

	// ── Botão de copiar shortcode ───────────────────────────────────────────
	function handleShortcodeCopy() {
		document.querySelectorAll('.wc-shipping-simulator-shortcode').forEach(function (codeEl) {
			if (codeEl.nextSibling && codeEl.nextSibling.classList && codeEl.nextSibling.classList.contains('wc-shipping-simulator-copy-shortcode-btn')) {
				return;
			}

			var copyBtn = document.createElement('button');
			copyBtn.type = 'button';
			copyBtn.className = 'wc-shipping-simulator-copy-shortcode-btn';
			copyBtn.title = 'Copiar shortcode';
			copyBtn.innerHTML = '📋';
			copyBtn.style.marginLeft = '8px';
			copyBtn.style.cursor = 'pointer';
			copyBtn.style.border = 'none';
			copyBtn.style.background = 'transparent';
			copyBtn.style.fontSize = '16px';
			copyBtn.style.transition = 'transform 0.2s';

			copyBtn.addEventListener('click', function (e) {
				e.preventDefault();
				if (!navigator.clipboard) return;
				navigator.clipboard.writeText(codeEl.textContent.trim()).then(function () {
					copyBtn.innerHTML = '✅';
					copyBtn.style.transform = 'scale(1.2)';
					setTimeout(function () {
						copyBtn.innerHTML = '📋';
						copyBtn.style.transform = 'scale(1)';
					}, 1200);
				});
			});

			codeEl.parentNode.insertBefore(copyBtn, codeEl.nextSibling);
		});
	}

	// ── Ícone flutuante (leva ao preview nas abas Produto/Carrinho) ─────────
	function addFloatingIcon() {
		if (!document.querySelector('.admin-layout-page-wrapper')) return;

		var container = document.createElement('div');
		container.className = 'wc-shipping-simulator-floating-icon-container';

		var img = document.createElement('img');
		img.src = icons.consult || '';
		img.alt = 'Ir para o componente';
		img.title = 'Ir para o componente';
		img.className = 'wc-shipping-simulator-floating-icon';

		container.appendChild(img);
		document.body.appendChild(container);

		function currentStyleName() {
			var activeLink = document.querySelector('.admin-layout-title-link.active');
			var target = activeLink ? (activeLink.getAttribute('data-target') || '') : '';
			if (target.indexOf('carrinho') !== -1) return 'cart';
			if (target.indexOf('produto') !== -1) return 'product';
			return '';
		}

		function toggleVisibility() {
			container.style.display = currentStyleName() ? 'flex' : 'none';
		}

		container.addEventListener('click', function () {
			var styleName = currentStyleName();
			if (!styleName) {
				window.alert('Componente disponível apenas nas abas de Carrinho ou Produto.');
				return;
			}

			var targetElement = document.getElementById(PREFIX + 'calc_' + styleName + '_input_current_style_postcode_fake_custom');
			if (!targetElement) return;

			targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
			targetElement.style.transition = 'box-shadow 0.3s ease';
			targetElement.style.boxShadow = '0 0 10px 2px #0073aa';
			setTimeout(function () {
				targetElement.style.boxShadow = 'none';
			}, 1000);
		});

		// Acompanha a troca de abas do menu superior.
		document.querySelectorAll('.admin-layout-title-link').forEach(function (link) {
			link.addEventListener('click', function () {
				setTimeout(toggleVisibility, 0);
			});
		});

		toggleVisibility();
	}

	function init() {
		if (!document.querySelector('.admin-layout-page-wrapper')) return;

		handleColorFields();
		buildPreview('product');
		buildPreview('cart');
		handleCustomPosition('product');
		handleCustomPosition('cart');
		handleCacheSettings();
		handleClearCacheButton();
		addProgressBarPreview();
		handleDeliveryTimeReadOnly();
		handleShortcodeCopy();
		addFloatingIcon();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
}());
