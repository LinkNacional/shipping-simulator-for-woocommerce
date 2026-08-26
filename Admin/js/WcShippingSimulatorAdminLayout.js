(function ($) {
  $(window).on('load', function () {
    const mainForm = document.querySelector('#mainform');
    if (!mainForm) return;

    const tables = Array.from(mainForm.querySelectorAll('table.form-table'));
    const subTitles = Array.from(mainForm.querySelectorAll('h2'));
    if (!tables.length || !subTitles.length) return;

    const ajax = (typeof wcShippingSimulatorAjax !== 'undefined') ? wcShippingSimulatorAjax : {};
    const icons = (typeof wcShippingSimulatorIcons !== 'undefined') ? wcShippingSimulatorIcons : {};

    const font_class = ajax.font_class || '';

    const mainContainer = document.createElement('div');
    mainContainer.style.display = 'flex';
    mainContainer.style.flexWrap = 'wrap';
    mainContainer.style.boxSizing = 'border-box';
    mainContainer.style.marginTop = '40px';
    mainContainer.style.gap = '20px';

    const contentContainer = document.createElement('div');
    contentContainer.className = 'lkn-settings-content';
    contentContainer.style.flex = '1';
    contentContainer.style.minWidth = '500px';
    contentContainer.style.boxSizing = 'border-box';

    // Coluna lateral (logo/empresa)
    const sideContainer = document.createElement('div');
    sideContainer.className = 'lkn-settings-side';
    sideContainer.style.display = 'flex';
    sideContainer.style.flexDirection = 'column';
    sideContainer.style.width = '400px';
    sideContainer.style.minWidth = '200px';
    sideContainer.style.alignItems = 'center';
    sideContainer.style.justifyContent = 'flex-start';
    sideContainer.style.padding = '32px 16px';
    sideContainer.style.boxSizing = 'border-box';

    const stickyContainer = document.createElement('div');
    stickyContainer.className = 'sticky-container';
    stickyContainer.style.position = 'sticky';
    stickyContainer.style.top = '120px';
    stickyContainer.style.maxWidth = '370px';

    function createFeatureMessage(iconText, messageLines) {
      const featureMessage = document.createElement('div');
      featureMessage.className = 'custom-feature-message';

      const infoIcon = document.createElement('span');
      infoIcon.textContent = iconText;
      infoIcon.style.marginRight = '10px';
      infoIcon.style.fontSize = '16px';

      const textContainer = document.createElement('div');
      textContainer.style.display = 'flex';
      textContainer.style.flexDirection = 'column';

      messageLines.forEach(line => {
        const messageLine = document.createElement('span');
        messageLine.innerHTML = line;
        messageLine.style.marginBottom = '5px';
        textContainer.appendChild(messageLine);
      });

      featureMessage.appendChild(infoIcon);
      featureMessage.appendChild(textContainer);

      return featureMessage;
    }

    const featureMessage1 = createFeatureMessage('✔️', [
      '<strong>NOVO:</strong> Formato para o CNPJ alfanumérico.'
    ]);

    const featureMessage2 = createFeatureMessage('✔️', [
      '<strong>AJUSTE:</strong> Novo sistema de frete por produto, prazos e comportamentos para frete grátis, além de ajustes na calculadora e no campo de número do Gutenberg.'
    ]);

    // Card promocional do plugin "Link de Pagamento de Faturas"
    const promotionalCard = document.createElement('div');
    promotionalCard.className = 'wc-shipping-simulator-promotional-card';
    promotionalCard.style.cssText = `
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 12px;
      padding: 20px;
      margin-top: 20px;
      color: white;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      position: relative;
      overflow: hidden;
    `;

    const backgroundDecor = document.createElement('div');
    backgroundDecor.style.cssText = `
      position: absolute;
      top: -50px;
      right: -50px;
      width: 100px;
      height: 100px;
      background: rgba(255,255,255,0.1);
      border-radius: 50%;
      pointer-events: none;
    `;
    promotionalCard.appendChild(backgroundDecor);

    const cardContent = document.createElement('div');
    cardContent.style.position = 'relative';
    cardContent.style.zIndex = '1';

    const cardTitle = document.createElement('h3');
    cardTitle.textContent = 'Plugin: Link de Pagamento de Faturas para WooCommerce';
    cardTitle.style.cssText = `
      margin: 0 0 12px 0;
      font-size: 16px;
      font-weight: 600;
      color: white;
      line-height: 1.3;
    `;

    const cardDescription = document.createElement('p');
    cardDescription.textContent = 'O Plugin Link de Pagamento é a solução completa para o seu negócio. Com ele, é possível gerar links de pagamento, parcelar compras em múltiplos cartões, configurar cobranças recorrentes, aplicar descontos e taxas, e criar orçamentos detalhados.';
    cardDescription.style.cssText = `
      margin: 0 0 16px 0;
      font-size: 14px;
      line-height: 1.5;
      color: rgba(255,255,255,0.9);
    `;

    const buttonsContainer = document.createElement('div');
    buttonsContainer.style.cssText = `
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    `;

    const learnMoreButton = document.createElement('button');
    learnMoreButton.textContent = 'Saiba mais';
    learnMoreButton.style.cssText = `
      background: rgba(255,255,255,0.2);
      border: 1px solid rgba(255,255,255,0.3);
      color: white;
      padding: 8px 16px;
      border-radius: 6px;
      font-size: 13px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
      backdrop-filter: blur(10px);
    `;

    learnMoreButton.addEventListener('mouseenter', function () {
      this.style.background = 'rgba(255,255,255,0.3)';
      this.style.transform = 'translateY(-1px)';
    });

    learnMoreButton.addEventListener('mouseleave', function () {
      this.style.background = 'rgba(255,255,255,0.2)';
      this.style.transform = 'translateY(0)';
    });

    learnMoreButton.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      window.open('https://br.wordpress.org/plugins/invoice-payment-for-woocommerce/', '_blank');
    });

    buttonsContainer.appendChild(learnMoreButton);

    if (!ajax.invoice_plugin_installed) {
      const installButton = document.createElement('button');
      installButton.textContent = 'Instalar';
      installButton.style.cssText = `
        background: rgba(255,255,255,0.9);
        border: none;
        color: #667eea;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
      `;

      installButton.addEventListener('mouseenter', function () {
        this.style.background = 'white';
        this.style.transform = 'translateY(-1px)';
        this.style.boxShadow = '0 2px 8px rgba(0,0,0,0.15)';
      });

      installButton.addEventListener('mouseleave', function () {
        this.style.background = 'rgba(255,255,255,0.9)';
        this.style.transform = 'translateY(0)';
        this.style.boxShadow = 'none';
      });

      installButton.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const installUrl = '/wp-admin/update.php?action=install-plugin&plugin=' + ajax.plugin_slug + '&_wpnonce=' + ajax.install_nonce;
        window.open(installUrl, '_blank');
      });

      buttonsContainer.appendChild(installButton);
    }

    cardContent.appendChild(cardTitle);
    cardContent.appendChild(cardDescription);
    cardContent.appendChild(buttonsContainer);
    promotionalCard.appendChild(cardContent);

    const settingsCard = document.querySelector('#WcShippingSimulatorLinkSettingsCard');
    if (settingsCard) {
      settingsCard.style.display = 'block';
      stickyContainer.appendChild(settingsCard);
      stickyContainer.appendChild(featureMessage1);
      stickyContainer.appendChild(featureMessage2);
      stickyContainer.appendChild(promotionalCard);
    }
    sideContainer.appendChild(stickyContainer);

    mainContainer.appendChild(contentContainer);
    mainContainer.appendChild(sideContainer);

    subTitles.forEach(h2 => contentContainer.appendChild(h2));
    tables.forEach(table => contentContainer.appendChild(table));

    const submitContent = mainForm.querySelector('.submit');
    if (submitContent) {
      submitContent.before(mainContainer);
    }

    // Cria o menu de tabs
    const tabMenu = document.createElement('div');
    tabMenu.className = 'lkn-settings-tabs';
    const tabLinks = [];

    subTitles.forEach((subTitle, idx) => {
      const tab = document.createElement('a');
      tab.textContent = subTitle.textContent;
      tab.href = '#' + subTitle.textContent.replace(/\s+/g, '-').toLowerCase();
      tab.className = 'nav-tab';
      tab.onclick = (e) => {
        e.preventDefault();

        const floatingComponent = document.querySelector('.wc-shipping-simulator-floating-icon-container');
        if (tab.textContent === 'Carrinho' || tab.textContent === 'Produto') {
          if (floatingComponent) {
            floatingComponent.style.display = 'flex';
          }
        } else {
          if (floatingComponent) {
            floatingComponent.style.display = 'none';
          }
        }

        tabLinks.forEach((el, i) => {
          el.className = i === idx ? 'nav-tab nav-tab-active' : 'nav-tab';
        });
        showTable(idx);
        window.location.hash = tab.hash;
      };
      tabMenu.appendChild(tab);
      tabLinks.push(tab);
      subTitle.remove();
    });

    // Insere o menu de tabs antes da primeira tabela
    tables[0].parentNode.insertBefore(tabMenu, tables[0]);

    // Aba "Shortcodes": o conteúdo vive no `desc` do título, não num input.
    // Movemos essa descrição para uma linha própria que o loop principal
    // transforma em card (gambiarra equivalente à do woo-better).
    tables.forEach((table, idx) => {
      const subtitleSlug = (tabLinks[idx] ? tabLinks[idx].textContent : '').replace(/\s+/g, '-').toLowerCase();
      const descId = 'wc_shipping_simulator_calc_title_' + subtitleSlug + '-description';
      const descDiv = document.getElementById(descId);
      if (descDiv && !table.querySelector('.wc-shipping-simulator-description-row')) {
        const tr = document.createElement('tr');
        tr.className = 'wc-shipping-simulator-description-row';

        const th = document.createElement('th');
        th.className = 'titledesc wc-shipping-simulator-custom-title';
        th.setAttribute('scope', 'row');
        const label = document.createElement('label');
        label.setAttribute('for', descId);
        const capitalize = (str) => str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
        label.textContent = capitalize(subtitleSlug);
        th.appendChild(label);

        const td = document.createElement('td');
        td.className = 'forminp';
        td.appendChild(descDiv);

        tr.appendChild(th);
        tr.appendChild(td);

        let tbody = table.querySelector('tbody');
        if (!tbody) {
          tbody = document.createElement('tbody');
          table.appendChild(tbody);
        }
        tbody.insertBefore(tr, tbody.firstChild);
      }
    });

    function showTable(activeIdx) {
      tables.forEach((table, idx) => {
        table.style.display = idx === activeIdx ? 'table' : 'none';
      });
    }

    // Reestrutura cada linha em card (header + body)
    tables.forEach((table) => {
      table.style.width = '100%';

      const ths = table.querySelectorAll('th');
      ths.forEach(th => { th.style.paddingTop = '68px'; });

      const rows = table.querySelectorAll('tr');
      rows.forEach(row => {
        const forminp = row.querySelector('.forminp');
        if (!forminp) return;

        forminp.style.display = 'flex';
        forminp.style.flexDirection = 'column';
        forminp.style.width = 'auto';
        forminp.style.padding = '15px 25px';
        forminp.style.backgroundColor = '#fff';
        forminp.style.border = '1px solid #dfdfdf';
        forminp.style.borderRadius = '8px';
        forminp.style.boxSizing = 'border-box';

        // Card especial da aba "Shortcodes" (sem input, conteúdo na descrição).
        const customTitleDesc = row.querySelector('.wc-shipping-simulator-custom-title');
        if (customTitleDesc) {
          const pElement = document.createElement('p');
          pElement.textContent = 'Utilize shortcodes para adicionar funcionalidades específicas do plugin a temas clássicos.';
          pElement.style.fontWeight = 'normal';
          pElement.style.color = '#343B45';

          customTitleDesc.style.paddingLeft = '.5em';
          customTitleDesc.style.fontSize = '20px';
          customTitleDesc.appendChild(pElement);

          const customLabel = customTitleDesc.querySelector('label');
          if (customLabel) {
            const headerComponent = document.createElement('div');
            headerComponent.className = 'woo-forminp-header';
            headerComponent.style.minHeight = '44px';

            customLabel.style.color = '#121519';

            const headerText = document.createElement('p');
            headerText.classList.add('woo-forminp-header-text');
            headerText.style.fontWeight = 'bold';
            headerText.style.color = '#121519';
            headerText.style.paddingLeft = '6px';
            headerText.textContent = customLabel.textContent.trim();

            const spanElement = document.createElement('span');
            spanElement.textContent = 'Shortcodes são especialmente úteis para temas clássicos que não utilizam o editor de blocos (Gutenberg).';
            spanElement.style.color = '#343B45';
            spanElement.style.fontSize = '13px';
            spanElement.style.paddingLeft = '6px';
            spanElement.style.display = 'block';

            const hrElement = document.createElement('hr');
            hrElement.style.border = 'none';
            hrElement.style.borderTop = '1px solid #ddd';
            hrElement.style.margin = '8px 0';

            headerComponent.appendChild(headerText);
            headerComponent.appendChild(spanElement);
            headerComponent.appendChild(hrElement);

            const bodyComponent = document.createElement('div');
            bodyComponent.className = 'woo-forminp-body';
            bodyComponent.style.display = 'flex';
            bodyComponent.style.flexDirection = 'column';
            bodyComponent.style.justifyContent = 'center';
            bodyComponent.style.padding = '20px 0px';
            bodyComponent.style.minHeight = '50px';
            bodyComponent.style.paddingLeft = '6px';

            while (forminp.firstChild) {
              bodyComponent.appendChild(forminp.firstChild);
            }

            forminp.innerHTML = '';
            forminp.appendChild(headerComponent);
            forminp.appendChild(bodyComponent);
          }
        }

        const inputField = forminp.querySelector('input, select, textarea');
        if (!inputField) return;

        const headerComponent = document.createElement('div');
        headerComponent.className = 'woo-forminp-header';
        headerComponent.style.minHeight = '44px';

        let labelElement = '';

        const titleDesc = row.querySelector('.titledesc');
        if (titleDesc) {
          const tipElement = titleDesc.querySelector('.woocommerce-help-tip');
          if (tipElement) tipElement.remove();

          const pElement = document.createElement('p');
          pElement.style.fontWeight = 'normal';
          pElement.style.color = '#343B45';
          if (inputField.getAttribute('data-desc-tip')) {
            pElement.textContent = inputField.getAttribute('data-desc-tip');
          }

          titleDesc.style.paddingLeft = '.5em';

          labelElement = titleDesc.querySelector('label');
          if (!labelElement) {
            if (titleDesc.textContent && titleDesc.textContent !== '') {
              labelElement = document.createElement('label');
              labelElement.setAttribute('for', inputField.id || '');
              labelElement.textContent = titleDesc.textContent;
              titleDesc.replaceChildren(labelElement);
            }
          }

          titleDesc.style.fontSize = '20px';
          titleDesc.appendChild(pElement);
        }

        if (labelElement) {
          labelElement.style.color = '#121519';

          const headerText = document.createElement('p');
          headerText.classList.add('woo-forminp-header-text');
          headerText.style.fontWeight = 'bold';
          headerText.style.color = '#121519';
          headerText.style.paddingLeft = '6px';

          if (inputField.getAttribute('data-subtitle')) {
            headerText.textContent = inputField.getAttribute('data-subtitle');
          } else {
            headerText.textContent = labelElement.textContent.trim();
          }

          const spanElement = document.createElement('span');
          if (inputField.getAttribute('data-title-description')) {
            spanElement.textContent = inputField.getAttribute('data-title-description');
          }
          spanElement.style.color = '#343B45';
          spanElement.style.fontSize = '13px';
          spanElement.style.paddingLeft = '6px';
          spanElement.style.display = 'block';

          const hrElement = document.createElement('hr');
          hrElement.style.border = 'none';
          hrElement.style.borderTop = '1px solid #ddd';
          hrElement.style.margin = '8px 0';

          headerComponent.appendChild(headerText);
          headerComponent.appendChild(spanElement);
          headerComponent.appendChild(hrElement);
        }

        const bodyComponent = document.createElement('div');
        bodyComponent.className = 'woo-forminp-body';
        bodyComponent.style.display = 'flex';
        bodyComponent.style.flexDirection = 'column';
        bodyComponent.style.justifyContent = 'center';
        bodyComponent.style.padding = '20px 0px';
        bodyComponent.style.minHeight = '50px';
        bodyComponent.style.paddingLeft = '6px';

        const descriptionField = inputField.closest('fieldset')?.querySelector('p.description');
        if (descriptionField) descriptionField.remove();

        const pDescriptionField = document.createElement('p');
        pDescriptionField.className = 'description';
        pDescriptionField.style.color = '#8F8F8F';
        if (inputField.getAttribute('data-description')) {
          pDescriptionField.innerHTML = inputField.getAttribute('data-description');
        }

        if (
          (inputField.tagName.toLowerCase() === 'input' && (inputField.type === 'text' || inputField.type === 'number')) ||
          inputField.tagName.toLowerCase() === 'select' ||
          inputField.tagName.toLowerCase() === 'textarea'
        ) {
          inputField.style.width = '100%';
          inputField.style.maxWidth = '400px';
          inputField.style.boxSizing = 'border-box';
          inputField.style.color = '#2C3338';
          bodyComponent.appendChild(inputField);
        } else if (inputField.tagName.toLowerCase() === 'input' && (inputField.type === 'checkbox' || inputField.type === 'radio')) {
          const fieldSetField = inputField.closest('fieldset');
          bodyComponent.appendChild(fieldSetField || inputField);
        } else {
          bodyComponent.appendChild(inputField);
        }

        bodyComponent.appendChild(pDescriptionField);

        // Preview ao vivo do campo CEP (input + botão + ícone)
        if (inputField.id.includes('postcode_current_style')) {
          const styleName = inputField.id.includes('cart') ? 'cart' : 'product';

          const containerDiv = document.createElement('div');
          containerDiv.classList.add('wc-shipping-simulator-container-current-style');

          const inputButtonGroup = document.createElement('div');
          inputButtonGroup.classList.add('wc-shipping-simulator-input-button-group-current-style');

          const inputWrapper = document.createElement('div');
          inputWrapper.classList.add('wc-shipping-simulator-input-wrapper-current-style');

          const styleComponents = {
            ['wc_shipping_simulator_calc_' + styleName + '_input_background_color_field']: 'backgroundColor',
            ['wc_shipping_simulator_calc_' + styleName + '_input_color_field']: 'color',
            ['wc_shipping_simulator_calc_' + styleName + '_input_border_width']: 'borderWidth',
            ['wc_shipping_simulator_calc_' + styleName + '_input_border_style']: 'borderStyle',
            ['wc_shipping_simulator_calc_' + styleName + '_input_border_color_field']: 'borderColor',
            ['wc_shipping_simulator_calc_' + styleName + '_input_border_radius']: 'borderRadius'
          };

          const placeholderInput = document.getElementById('wc_shipping_simulator_calc_' + styleName + '_input_placeholder');

          const textInput = document.createElement('input');
          textInput.type = 'text';
          textInput.id = 'wc_shipping_simulator_calc_' + styleName + '_input_current_style_postcode_fake_custom';
          textInput.placeholder = placeholderInput ? placeholderInput.value : 'Insira seu CEP';
          textInput.classList.add('wc-shipping-simulator-input-current-style');
          if (font_class) textInput.classList.add(font_class);
          textInput.style.cursor = 'pointer';
          textInput.readOnly = true;

          textInput.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const targetElement = document.getElementById('wc_shipping_simulator_calc_' + styleName + '_input_background_color_field_input');
            if (targetElement) {
              targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
          });

          Object.keys(styleComponents).forEach(componentId => {
            const styleProperty = styleComponents[componentId];
            const controlElement = document.getElementById(componentId);
            if (controlElement && controlElement.value) {
              textInput.style[styleProperty] = controlElement.value;
            }
          });

          const icon = document.createElement('img');
          icon.src = icons['transit'] || '';
          icon.alt = 'Ícone padrão';
          icon.classList.add('wc-shipping-simulator-icon-current-style');
          icon.classList.add('wc-shipping-simulator-input-icon');

          const colorSelect = document.getElementById('wc_shipping_simulator_calc_' + styleName + '_input_icon_color');
          if (colorSelect) {
            colorSelect.addEventListener('change', function () {
              const selectedColor = colorSelect.value;
              document.querySelectorAll('.wc-shipping-simulator-input-icon').forEach(ic => {
                ic.classList.remove('black-icon', 'gray-icon', 'red-icon', 'pink-icon', 'green-icon', 'blue-icon');
                ic.classList.add(selectedColor);
              });
            });
            icon.classList.add(colorSelect.value);
          }

          inputWrapper.appendChild(textInput);
          inputWrapper.appendChild(icon);

          const radioOptions = document.querySelectorAll('input[name="wc_shipping_simulator_calc_' + styleName + '_input_icon"]');
          radioOptions.forEach(option => {
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

          const buttonStyleComponents = {
            ['wc_shipping_simulator_calc_' + styleName + '_button_background_color_field']: 'backgroundColor',
            ['wc_shipping_simulator_calc_' + styleName + '_button_color_field']: 'color',
            ['wc_shipping_simulator_calc_' + styleName + '_button_border_width']: 'borderWidth',
            ['wc_shipping_simulator_calc_' + styleName + '_button_border_style']: 'borderStyle',
            ['wc_shipping_simulator_calc_' + styleName + '_button_border_color_field']: 'borderColor',
            ['wc_shipping_simulator_calc_' + styleName + '_button_border_radius']: 'borderRadius'
          };

          const button = document.createElement('button');
          button.textContent = 'CONSULTAR';
          button.id = 'wc_shipping_simulator_calc_' + styleName + '_button_current_style_postcode_fake_custom';
          button.classList.add('wc-shipping-simulator-button-current-style');
          if (font_class) button.classList.add(font_class);

          Object.keys(buttonStyleComponents).forEach(componentId => {
            const styleProperty = buttonStyleComponents[componentId];
            const controlElement = document.getElementById(componentId);
            if (controlElement && controlElement.value) {
              button.style[styleProperty] = controlElement.value;
            }
          });

          button.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const targetElement = document.getElementById('wc_shipping_simulator_calc_' + styleName + '_button_background_color_field_input');
            if (targetElement) {
              targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
          });

          inputButtonGroup.appendChild(inputWrapper);
          inputButtonGroup.appendChild(button);
          containerDiv.appendChild(inputButtonGroup);

          const linkText = document.createElement('p');
          linkText.textContent = 'Não sei meu CEP';
          linkText.classList.add('wc-shipping-simulator-link-current-style');
          if (font_class) linkText.classList.add(font_class);
          containerDiv.appendChild(linkText);

          inputField.parentNode.insertBefore(containerDiv, inputField);
          inputField.remove();
        }

        // Campo de cor: cria um color picker e esconde o campo original
        if (inputField.id.includes('color_field')) {
          const colorField = document.createElement('input');
          colorField.type = 'color';
          colorField.id = inputField.id + '_input';
          colorField.name = inputField.name + '_input';
          colorField.className = 'wc-shipping-simulator-color-field';
          colorField.value = inputField.value || '#000000';

          colorField.addEventListener('input', function () {
            inputField.value = colorField.value;
          });

          const parentDiv = document.createElement('div');
          parentDiv.className = 'wc-shipping-simulator-color-wrapper';
          parentDiv.style.display = 'flex';
          parentDiv.style.alignItems = 'center';
          parentDiv.style.flexDirection = 'row';
          parentDiv.style.flexWrap = 'wrap';
          parentDiv.style.gap = '10px';

          const descriptionElement = inputField.nextElementSibling;
          if (descriptionElement && descriptionElement.classList.contains('description')) {
            descriptionElement.style.fontSize = '16px';
            descriptionElement.style.margin = '0';
            parentDiv.appendChild(descriptionElement);
          }

          inputField.parentNode.insertBefore(parentDiv, inputField);
          parentDiv.appendChild(inputField);
          parentDiv.insertBefore(colorField, descriptionElement || inputField);
          inputField.style.display = 'none';
        }

        // Relações entre componentes (filho -> pai)
        const targetComponentNames = {
          'wc_shipping_simulator_min_free_shipping_value': 'wc_shipping_simulator_enable_min_free_shipping',
          'wc_shipping_simulator_only_free_shipping': 'wc_shipping_simulator_enable_min_free_shipping',
          'wc_shipping_simulator_avoid_free_shipping_duplication': 'wc_shipping_simulator_enable_min_free_shipping',
          'wc_shipping_simulator_min_free_shipping_success_message': 'wc_shipping_simulator_min_free_shipping_message',
          'wc_shipping_simulator_enable_progress_bar_value': 'wc_shipping_simulator_min_free_shipping_message',
          'wc_shipping_simulator_min_free_shipping_delivery_time': 'wc_shipping_simulator_enable_min_free_shipping',
          'wc_shipping_simulator_free_shipping_calc_base': 'wc_shipping_simulator_enable_min_free_shipping',
          'wc_shipping_simulator_free_shipping_by_product_delivery_time': 'wc_shipping_simulator_enable_free_shipping_by_product',

          'wc_shipping_simulator_calc_cart_input_border_width': 'wc_shipping_simulator_calc_cart_input_background_color_field',
          'wc_shipping_simulator_calc_cart_input_color_field': 'wc_shipping_simulator_calc_cart_input_background_color_field',
          'wc_shipping_simulator_calc_cart_input_border_style': 'wc_shipping_simulator_calc_cart_input_background_color_field',
          'wc_shipping_simulator_calc_cart_input_border_color_field': 'wc_shipping_simulator_calc_cart_input_background_color_field',
          'wc_shipping_simulator_calc_cart_input_border_radius': 'wc_shipping_simulator_calc_cart_input_background_color_field',
          'wc_shipping_simulator_calc_cart_button_color_field': 'wc_shipping_simulator_calc_cart_button_background_color_field',
          'wc_shipping_simulator_calc_cart_button_border_width': 'wc_shipping_simulator_calc_cart_button_background_color_field',
          'wc_shipping_simulator_calc_cart_button_border_style': 'wc_shipping_simulator_calc_cart_button_background_color_field',
          'wc_shipping_simulator_calc_cart_button_border_color_field': 'wc_shipping_simulator_calc_cart_button_background_color_field',
          'wc_shipping_simulator_calc_cart_button_border_radius': 'wc_shipping_simulator_calc_cart_button_background_color_field',
          'wc_shipping_simulator_calc_cart_input_icon': 'wc_shipping_simulator_calc_cart_input_placeholder',
          'wc_shipping_simulator_calc_cart_input_icon_color': 'wc_shipping_simulator_calc_cart_input_placeholder',
          'wc_shipping_simulator_calc_cart_custom_position': 'wc_shipping_simulator_calc_cart_input_position',

          'wc_shipping_simulator_calc_product_input_border_width': 'wc_shipping_simulator_calc_product_input_background_color_field',
          'wc_shipping_simulator_calc_product_input_color_field': 'wc_shipping_simulator_calc_product_input_background_color_field',
          'wc_shipping_simulator_calc_product_input_border_style': 'wc_shipping_simulator_calc_product_input_background_color_field',
          'wc_shipping_simulator_calc_product_input_border_color_field': 'wc_shipping_simulator_calc_product_input_background_color_field',
          'wc_shipping_simulator_calc_product_input_border_radius': 'wc_shipping_simulator_calc_product_input_background_color_field',
          'wc_shipping_simulator_calc_product_button_color_field': 'wc_shipping_simulator_calc_product_button_background_color_field',
          'wc_shipping_simulator_calc_product_button_border_width': 'wc_shipping_simulator_calc_product_button_background_color_field',
          'wc_shipping_simulator_calc_product_button_border_style': 'wc_shipping_simulator_calc_product_button_background_color_field',
          'wc_shipping_simulator_calc_product_button_border_color_field': 'wc_shipping_simulator_calc_product_button_background_color_field',
          'wc_shipping_simulator_calc_product_button_border_radius': 'wc_shipping_simulator_calc_product_button_background_color_field',
          'wc_shipping_simulator_calc_product_input_icon': 'wc_shipping_simulator_calc_product_input_placeholder',
          'wc_shipping_simulator_calc_product_input_icon_color': 'wc_shipping_simulator_calc_product_input_placeholder',
          'wc_shipping_simulator_calc_product_custom_position': 'wc_shipping_simulator_calc_product_input_position',

          'wc_shipping_simulator_calc_cache_expiration_time': 'wc_shipping_simulator_calc_enable_auto_postcode_search',
          'wc_shipping_simulator_calc_enable_auto_cache_reset': 'wc_shipping_simulator_calc_enable_auto_postcode_search'
        };

        forminp.innerHTML = '';
        forminp.appendChild(headerComponent);
        forminp.appendChild(bodyComponent);

        if (inputField.name && targetComponentNames[inputField.name]) {
          const receiveName = targetComponentNames[inputField.name];
          const receiveComponent = document.querySelector('[name="' + receiveName + '"]');
          if (receiveComponent) {
            const forminpReceiveBody = receiveComponent.closest('.woo-forminp-body');
            if (forminpReceiveBody) {
              bodyComponent.style.minHeight = 'auto';
              forminp.style.padding = '0px';
              forminp.style.margin = '0px';
              forminp.style.paddingTop = '15px';
              forminp.style.marginTop = '10px';
              forminp.style.border = 'none';
              forminp.style.marginLeft = '-6px';
              forminpReceiveBody.appendChild(forminp);
              row.remove();
            }
          }
        }
      });
    });

    // Atualização ao vivo dos estilos do preview
    function startEvents(styleName) {
      const validCssUnits = ['px', '%', 'em', 'rem', 'vh', 'vw', 'vmin', 'vmax', 'cm', 'mm', 'in', 'pt', 'pc', 'ex', 'ch'];

      const inputStyleComponents = {
        ['wc_shipping_simulator_calc_' + styleName + '_input_background_color_field_input']: { property: 'background-color', default: '#ffffff' },
        ['wc_shipping_simulator_calc_' + styleName + '_input_color_field_input']: { property: 'color', default: '#2C3338' },
        ['wc_shipping_simulator_calc_' + styleName + '_input_border_width']: { property: 'border-width', default: '1px' },
        ['wc_shipping_simulator_calc_' + styleName + '_input_border_style']: { property: 'border-style', default: 'solid' },
        ['wc_shipping_simulator_calc_' + styleName + '_input_border_color_field_input']: { property: 'border-color', default: '#ccc' },
        ['wc_shipping_simulator_calc_' + styleName + '_input_border_radius']: { property: 'border-radius', default: '4px' }
      };

      const targetComponent = document.getElementById('wc_shipping_simulator_calc_' + styleName + '_input_current_style_postcode_fake_custom');
      if (targetComponent) {
        Object.keys(inputStyleComponents).forEach(componentId => {
          const cfg = inputStyleComponents[componentId];
          const controlElement = document.getElementById(componentId);
          if (controlElement) {
            controlElement.addEventListener('change', function () {
              const value = controlElement.value;
              if (controlElement.type === 'text') {
                const regex = new RegExp('^\\d+(\\.\\d+)?(' + validCssUnits.join('|') + ')$');
                if (!regex.test(value)) {
                  controlElement.value = cfg.default;
                  targetComponent.style.setProperty(cfg.property, cfg.default, 'important');
                  return;
                }
              }
              targetComponent.style.setProperty(cfg.property, value, 'important');
            });
            targetComponent.style.setProperty(cfg.property, cfg.default, 'important');
          }
        });
      }

      const buttonStyleComponents = {
        ['wc_shipping_simulator_calc_' + styleName + '_button_background_color_field_input']: { property: 'background-color', default: '#0073aa' },
        ['wc_shipping_simulator_calc_' + styleName + '_button_color_field_input']: { property: 'color', default: '#ffffff' },
        ['wc_shipping_simulator_calc_' + styleName + '_button_border_width']: { property: 'border-width', default: '1px' },
        ['wc_shipping_simulator_calc_' + styleName + '_button_border_style']: { property: 'border-style', default: 'none' },
        ['wc_shipping_simulator_calc_' + styleName + '_button_border_color_field_input']: { property: 'border-color', default: 'transparent' },
        ['wc_shipping_simulator_calc_' + styleName + '_button_border_radius']: { property: 'border-radius', default: '4px' }
      };

      const targetButton = document.querySelector('#wc_shipping_simulator_calc_' + styleName + '_button_current_style_postcode_fake_custom');
      if (targetButton) {
        Object.keys(buttonStyleComponents).forEach(componentId => {
          const cfg = buttonStyleComponents[componentId];
          const controlElement = document.getElementById(componentId);
          if (controlElement) {
            controlElement.addEventListener('change', function () {
              const value = controlElement.value;
              if (controlElement.type === 'text') {
                const regex = new RegExp('^\\d+(\\.\\d+)?(' + validCssUnits.join('|') + ')$');
                if (!regex.test(value)) {
                  controlElement.value = cfg.default;
                  targetButton.style.setProperty(cfg.property, cfg.default, 'important');
                  return;
                }
              }
              targetButton.style.setProperty(cfg.property, value, 'important');
            });
          }
        });
      }

      const placeholderInput = document.getElementById('wc_shipping_simulator_calc_' + styleName + '_input_placeholder');
      const textInput = document.getElementById('wc_shipping_simulator_calc_' + styleName + '_input_current_style_postcode_fake_custom');
      if (placeholderInput && textInput) {
        placeholderInput.addEventListener('change', function () {
          textInput.placeholder = placeholderInput.value;
        });
      }

      // Adiciona imagens nos radios de ícone
      const iconMap = {
        'transit': icons['transit'],
        'bill': icons['bill'],
        'truck': icons['truck'],
        'postcode': icons['postcode'],
        'zipcode': icons['zipcode']
      };

      const radioOptions = document.querySelectorAll('input[name="wc_shipping_simulator_calc_' + styleName + '_input_icon"]');
      radioOptions.forEach(option => {
        const value = option.value;
        if (iconMap[value]) {
          const img = document.createElement('img');
          img.src = iconMap[value];
          img.alt = value;
          img.style.width = '40px';
          img.style.height = '40px';
          img.style.marginLeft = '10px';
          img.classList.add('wc-shipping-simulator-input-icon');
          const colorSelect = document.getElementById('wc_shipping_simulator_calc_' + styleName + '_input_icon_color');
          img.classList.add(colorSelect ? colorSelect.value : 'black-icon');

          const label = option.closest('label');
          if (label) {
            label.textContent = '';
            label.appendChild(option);
            label.appendChild(img);
            label.style.display = 'flex';
            label.style.alignItems = 'center';
            label.style.setProperty('margin', '14px 0', 'important');
          }
        }
      });
    }

    function handleCustomPosition(styleName) {
      const customPosition = document.getElementById('wc_shipping_simulator_calc_' + styleName + '_custom_position');
      const selectPosition = document.getElementById('wc_shipping_simulator_calc_' + styleName + '_input_position');

      function toggle() {
        if (customPosition && selectPosition) {
          const forminp = customPosition.closest('.forminp');
          if (forminp) {
            forminp.style.display = selectPosition.value === 'custom' ? 'flex' : 'none';
          }
        }
      }

      toggle();
      if (selectPosition) selectPosition.addEventListener('change', toggle);
    }

    function handleCacheSettings() {
      const cacheExpirationTime = document.getElementById('wc_shipping_simulator_calc_cache_expiration_time');
      const autoCacheReset = document.getElementById('wc_shipping_simulator_calc_enable_auto_cache_reset');
      if (!cacheExpirationTime || !autoCacheReset) return;

      const cacheExpirationForminp = cacheExpirationTime.closest('.forminp');
      const autoCacheResetForminp = autoCacheReset.closest('.forminp');

      function toggle() {
        const selectedValue = document.querySelector('input[name="wc_shipping_simulator_calc_enable_auto_postcode_search"]:checked')?.value;
        if (cacheExpirationForminp && autoCacheResetForminp) {
          cacheExpirationForminp.style.display = selectedValue === 'yes' ? 'flex' : 'none';
          autoCacheResetForminp.style.display = selectedValue === 'yes' ? 'flex' : 'none';
        }
      }

      toggle();
      document.querySelectorAll('input[name="wc_shipping_simulator_calc_enable_auto_postcode_search"]').forEach(radio => {
        radio.addEventListener('change', toggle);
      });
    }

    function handleClearCacheButton() {
      const cacheResetInput = document.getElementById('wc_shipping_simulator_calc_enable_auto_cache_reset');
      if (!cacheResetInput) return;

      cacheResetInput.style.display = 'none';

      const clearCacheButton = document.createElement('button');
      clearCacheButton.type = 'button';
      clearCacheButton.className = 'wc-shipping-simulator-cache-button components-button is-primary';
      clearCacheButton.textContent = 'Limpar o cache';

      function generateNewToken() {
        const prefix = 'WCBCB_';
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let randomPart = '';
        for (let i = 0; i < 19; i++) {
          randomPart += characters.charAt(Math.floor(Math.random() * characters.length));
        }
        return prefix + randomPart;
      }

      clearCacheButton.addEventListener('click', function () {
        if (confirm('Tem certeza de que deseja limpar o cache?')) {
          cacheResetInput.value = generateNewToken();
          clearCacheButton.textContent = 'Novo token gerado!';
          clearCacheButton.style.backgroundColor = '#00a32a';
          clearCacheButton.style.color = '#fff';
          setTimeout(() => {
            clearCacheButton.textContent = 'Limpar o cache';
            clearCacheButton.disabled = false;
            clearCacheButton.style.backgroundColor = '';
            clearCacheButton.style.color = '';
          }, 2000);
        }
      });

      cacheResetInput.parentNode.insertBefore(clearCacheButton, cacheResetInput.nextSibling);
    }

    function addProgressBarPreview() {
      const progressBarRadios = document.querySelectorAll('input[name="wc_shipping_simulator_enable_progress_bar_value"]');
      if (progressBarRadios.length === 0) return;

      const firstRadio = progressBarRadios[0];
      const formBody = firstRadio.closest('.woo-forminp-body');
      if (!formBody || formBody.querySelector('.wc-shipping-simulator-progress-preview')) return;

      const barImages = (typeof wcShippingSimulatorBarImages !== 'undefined') ? wcShippingSimulatorBarImages : {};

      const previewContainer = document.createElement('div');
      previewContainer.className = 'wc-shipping-simulator-progress-preview';
      previewContainer.style.marginTop = '15px';

      const previewTitle = document.createElement('h4');
      previewTitle.textContent = 'Preview da Barra de Progresso:';
      previewTitle.style.cssText = 'margin: 0 0 10px 0; font-size: 14px; color: #333;';

      const imagesContainer = document.createElement('div');
      imagesContainer.style.cssText = 'display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-start;';

      // Preview com label
      const withLabelContainer = document.createElement('div');
      withLabelContainer.className = 'preview-with-label';
      withLabelContainer.style.cssText = 'flex: 1; min-width: 200px; text-align: center;';

      const withLabelTitle = document.createElement('p');
      withLabelTitle.textContent = 'Com valores na barra (Sim)';
      withLabelTitle.style.cssText = 'margin: 0 0 8px 0; font-weight: bold; font-size: 13px; color: #0073aa;';

      const withLabelImg = document.createElement('img');
      withLabelImg.src = barImages.with_label || '';
      withLabelImg.alt = 'Barra com valores';
      withLabelImg.style.cssText = 'max-width: 100%; height: auto;';

      withLabelContainer.appendChild(withLabelTitle);
      withLabelContainer.appendChild(withLabelImg);

      // Preview sem label
      const withoutLabelContainer = document.createElement('div');
      withoutLabelContainer.className = 'preview-without-label';
      withoutLabelContainer.style.cssText = 'flex: 1; min-width: 200px; text-align: center;';

      const withoutLabelTitle = document.createElement('p');
      withoutLabelTitle.textContent = 'Sem valores na barra (Não)';
      withoutLabelTitle.style.cssText = 'margin: 0 0 8px 0; font-weight: bold; font-size: 13px; color: #0073aa;';

      const withoutLabelImg = document.createElement('img');
      withoutLabelImg.src = barImages.without_label || '';
      withoutLabelImg.alt = 'Barra sem valores';
      withoutLabelImg.style.cssText = 'max-width: 100%; height: auto;';

      withoutLabelContainer.appendChild(withoutLabelTitle);
      withoutLabelContainer.appendChild(withoutLabelImg);

      imagesContainer.appendChild(withLabelContainer);
      imagesContainer.appendChild(withoutLabelContainer);

      previewContainer.appendChild(previewTitle);
      previewContainer.appendChild(imagesContainer);

      function updatePreviewHighlight() {
        const selectedValue = Array.from(progressBarRadios).find(radio => radio.checked)?.value;
        if (selectedValue === 'yes') {
          withLabelContainer.style.opacity = '1';
          withoutLabelContainer.style.opacity = '0.5';
          withLabelTitle.style.color = '#0073aa';
          withoutLabelTitle.style.color = '#999';
        } else {
          withLabelContainer.style.opacity = '0.5';
          withoutLabelContainer.style.opacity = '1';
          withLabelTitle.style.color = '#999';
          withoutLabelTitle.style.color = '#0073aa';
        }
      }

      updatePreviewHighlight();
      progressBarRadios.forEach(radio => radio.addEventListener('change', updatePreviewHighlight));

      formBody.appendChild(previewContainer);
    }

    function handleDeliveryTimeReadOnly() {
      const minFreeShippingRadios = document.querySelectorAll('input[name="wc_shipping_simulator_enable_min_free_shipping"]');
      const minDeliveryTimeInput = document.querySelector('input[name="wc_shipping_simulator_min_free_shipping_delivery_time"]');
      const freeShippingByProductRadios = document.querySelectorAll('input[name="wc_shipping_simulator_enable_free_shipping_by_product"]');
      const productDeliveryTimeInput = document.querySelector('input[name="wc_shipping_simulator_free_shipping_by_product_delivery_time"]');

      function setState(radios, input) {
        if (!input) return;
        const checkedValue = document.querySelector('input[name="' + radios + '"]:checked')?.value;
        if (checkedValue === 'no') {
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

      setState('wc_shipping_simulator_enable_min_free_shipping', minDeliveryTimeInput);
      setState('wc_shipping_simulator_enable_free_shipping_by_product', productDeliveryTimeInput);

      minFreeShippingRadios.forEach(radio => radio.addEventListener('change', () => setState('wc_shipping_simulator_enable_min_free_shipping', minDeliveryTimeInput)));
      freeShippingByProductRadios.forEach(radio => radio.addEventListener('change', () => setState('wc_shipping_simulator_enable_free_shipping_by_product', productDeliveryTimeInput)));
    }

    startEvents('cart');
    startEvents('product');
    handleCustomPosition('cart');
    handleCustomPosition('product');
    handleCacheSettings();
    handleClearCacheButton();
    addProgressBarPreview();
    handleDeliveryTimeReadOnly();

    // Botão de copiar shortcode
    document.querySelectorAll('.wc-shipping-simulator-shortcode').forEach(function (codeEl) {
      const copyBtn = document.createElement('button');
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

      copyBtn.addEventListener('click', function () {
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

    // ── Ícone flutuante (navega até o preview nas abas Carrinho/Produto) ──
    const floatingIconContainer = document.createElement('div');
    floatingIconContainer.className = 'wc-shipping-simulator-floating-icon-container';

    const floatingIcon = document.createElement('img');
    floatingIcon.src = icons.consult || '';
    floatingIcon.alt = 'Ir para o componente';
    floatingIcon.title = 'Ir para o componente';
    floatingIcon.className = 'wc-shipping-simulator-floating-icon';

    floatingIconContainer.addEventListener('click', function () {
      const hash = window.location.hash;
      let styleName = '';

      if (hash.includes('carrinho')) {
        styleName = 'cart';
      } else if (hash.includes('produto')) {
        styleName = 'product';
      }

      if (styleName) {
        const targetElement = document.getElementById('wc_shipping_simulator_calc_' + styleName + '_input_current_style_postcode_fake_custom');
        if (targetElement) {
          targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });

          targetElement.style.transition = 'box-shadow 0.3s ease';
          targetElement.style.boxShadow = '0 0 10px 2px #0073aa';

          setTimeout(() => {
            targetElement.style.boxShadow = 'none';
          }, 1000);
        }
      } else {
        alert('Componente disponível apenas nas abas de Carrinho ou Produto.');
      }
    });

    floatingIconContainer.appendChild(floatingIcon);
    document.body.appendChild(floatingIconContainer);

    // Estado inicial: mostra apenas se a tab ativa for Carrinho ou Produto.
    let initialActiveTab = tabLinks[0];

    const currentHash = window.location.hash;
    if (currentHash) {
      const hashIndex = tabLinks.findIndex(tab => tab.href.endsWith(currentHash));
      if (hashIndex >= 0) {
        initialActiveTab = tabLinks[hashIndex];
      }
    }

    if (initialActiveTab && (initialActiveTab.textContent === 'Carrinho' || initialActiveTab.textContent === 'Produto')) {
      floatingIconContainer.style.display = 'flex';
    } else {
      floatingIconContainer.style.display = 'none';
    }

    // Ativa a primeira tab e suporta hash
    tabLinks[0].className = 'nav-tab nav-tab-active';
    showTable(0);

    const urlHash = window.location.hash;
    if (urlHash) {
      const idx = tabLinks.findIndex(a => a.href.endsWith(urlHash));
      if (idx >= 0) tabLinks[idx].click();
    }
  });
})(jQuery);
