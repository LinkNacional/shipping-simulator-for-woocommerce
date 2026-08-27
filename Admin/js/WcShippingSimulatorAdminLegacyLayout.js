(function ($) {
  $(window).on('load', function () {
    const mainForm = document.querySelector('#mainform');
    if (!mainForm) return;

    const tables = Array.from(mainForm.querySelectorAll('table.form-table'));
    const subTitles = Array.from(mainForm.querySelectorAll('h2'));
    if (!tables.length || !subTitles.length) return;

    const legacyData = (typeof wcShippingSimulatorLegacy !== 'undefined') ? wcShippingSimulatorLegacy : {};
    const descriptionDivs = Array.from(mainForm.querySelectorAll('div[id$="-description"]'));

    // ── Container principal (conteúdo + coluna lateral) ──
    const mainContainer = document.createElement('div');
    mainContainer.style.cssText = 'display:flex;flex-wrap:wrap;box-sizing:border-box;margin-top:40px;gap:20px;';

    const contentContainer = document.createElement('div');
    contentContainer.className = 'lkn-settings-content';
    contentContainer.style.cssText = 'flex:1;min-width:500px;box-sizing:border-box;';

    const sideContainer = document.createElement('div');
    sideContainer.className = 'lkn-settings-side';
    sideContainer.style.cssText = 'display:flex;flex-direction:column;width:400px;min-width:200px;align-items:center;justify-content:flex-start;padding:32px 16px;box-sizing:border-box;';

    const stickyContainer = document.createElement('div');
    stickyContainer.className = 'sticky-container';
    stickyContainer.style.cssText = 'position:sticky;top:120px;max-width:370px;';

    // ── Card lateral: Link Nacional (opcional) ──
    const settingsCard = document.querySelector('#WcShippingSimulatorLinkSettingsCard');
    if (settingsCard) {
      settingsCard.style.display = 'block';
      stickyContainer.appendChild(settingsCard);
    }

    // ── Card de aviso (amarelo) sobre os novos recursos ──
    const warningCard = document.createElement('div');
    warningCard.className = 'wc-shipping-simulator-legacy-warning-card';
    warningCard.style.cssText = 'background:#fcf9e8;border:1px solid #dba617;border-left-width:4px;border-left-color:#dba617;border-radius:5px;padding:14px 16px;margin-top:20px;color:#1d2327;';

    const warningTitle = document.createElement('strong');
    warningTitle.textContent = 'Novos recursos disponíveis';
    warningTitle.style.cssText = 'display:block;margin-bottom:6px;font-size:14px;';

    const warningText = document.createElement('p');
    warningText.textContent = 'Frete grátis, calculadora de frete no produto/carrinho e opções de endereço agora estão na aba Calculadora de Frete.';
    warningText.style.cssText = 'margin:0 0 10px;font-size:13px;line-height:1.5;';

    warningCard.appendChild(warningTitle);
    warningCard.appendChild(warningText);

    if (legacyData.calculator_url) {
      const warningLink = document.createElement('a');
      warningLink.href = legacyData.calculator_url;
      warningLink.textContent = 'Ir para Calculadora de Frete';
      warningLink.className = 'button button-primary';
      warningLink.style.cssText = 'display:inline-flex;align-items:center;justify-content:center;';
      warningCard.appendChild(warningLink);
    }

    stickyContainer.appendChild(warningCard);

    // ── Card promocional: woo-better (mesma lógica de instalação do de fatura) ──
    const promotionalCard = document.createElement('div');
    promotionalCard.className = 'wc-shipping-simulator-promotional-card';
    promotionalCard.style.cssText = 'background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);border-radius:12px;padding:20px;margin-top:20px;color:white;box-shadow:0 4px 15px rgba(0,0,0,0.1);position:relative;overflow:hidden;';

    const backgroundDecor = document.createElement('div');
    backgroundDecor.style.cssText = 'position:absolute;top:-50px;right:-50px;width:100px;height:100px;background:rgba(255,255,255,0.1);border-radius:50%;pointer-events:none;';
    promotionalCard.appendChild(backgroundDecor);

    const cardContent = document.createElement('div');
    cardContent.style.cssText = 'position:relative;z-index:1;';

    const cardTitle = document.createElement('h3');
    cardTitle.textContent = 'Plugin: Calculadora de Frete e Campos Checkout para o Brasil';
    cardTitle.style.cssText = 'margin:0 0 12px;font-size:16px;font-weight:600;color:white;line-height:1.3;';

    const cardDescription = document.createElement('p');
    cardDescription.textContent = 'Campos brasileiros no checkout, máscaras de CPF/CNPJ/CEP, validações e integrações com os principais métodos de entrega.';
    cardDescription.style.cssText = 'margin:0 0 16px;font-size:14px;line-height:1.5;color:rgba(255,255,255,0.9);';

    const buttonsContainer = document.createElement('div');
    buttonsContainer.style.cssText = 'display:flex;gap:10px;flex-wrap:wrap;';

    const learnMoreButton = document.createElement('button');
    learnMoreButton.textContent = 'Saiba mais';
    learnMoreButton.style.cssText = 'background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);color:white;padding:8px 16px;border-radius:6px;font-size:13px;font-weight:500;cursor:pointer;transition:all 0.3s ease;backdrop-filter:blur(10px);';

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
      window.open('https://br.wordpress.org/plugins/woo-better-shipping-calculator-for-brazil/', '_blank');
    });

    buttonsContainer.appendChild(learnMoreButton);

    if (!legacyData.woo_better_plugin_installed) {
      const installButton = document.createElement('button');
      installButton.textContent = 'Instalar';
      installButton.style.cssText = 'background:rgba(255,255,255,0.9);border:none;color:#667eea;padding:8px 16px;border-radius:6px;font-size:13px;font-weight:500;cursor:pointer;transition:all 0.3s ease;';

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

        const installUrl = '/wp-admin/update.php?action=install-plugin&plugin=' + legacyData.plugin_slug + '&_wpnonce=' + legacyData.install_nonce;
        window.open(installUrl, '_blank');
      });

      buttonsContainer.appendChild(installButton);
    }

    cardContent.appendChild(cardTitle);
    cardContent.appendChild(cardDescription);
    cardContent.appendChild(buttonsContainer);
    promotionalCard.appendChild(cardContent);

    stickyContainer.appendChild(promotionalCard);

    sideContainer.appendChild(stickyContainer);
    mainContainer.appendChild(contentContainer);
    mainContainer.appendChild(sideContainer);

    subTitles.forEach(function (h2) { contentContainer.appendChild(h2); });
    tables.forEach(function (table) { contentContainer.appendChild(table); });

    const submitContent = mainForm.querySelector('.submit');
    if (submitContent) {
      submitContent.before(mainContainer);
    }

    // ── Menu de tabs ──
    const tabMenu = document.createElement('div');
    tabMenu.className = 'lkn-settings-tabs';
    const tabLinks = [];

    subTitles.forEach(function (subTitle, idx) {
      const tab = document.createElement('a');
      tab.textContent = subTitle.textContent;
      tab.href = '#' + subTitle.textContent.replace(/\s+/g, '-').toLowerCase();
      tab.className = 'nav-tab';
      tab.onclick = function (e) {
        e.preventDefault();
        tabLinks.forEach(function (el, i) {
          el.className = i === idx ? 'nav-tab nav-tab-active' : 'nav-tab';
        });
        showTable(idx);
        window.location.hash = tab.hash;
      };
      tabMenu.appendChild(tab);
      tabLinks.push(tab);
      subTitle.remove();
    });

    tables[0].parentNode.insertBefore(tabMenu, tables[0]);

    // Descrição dos títulos (se houver) logo após o menu de tabs.
    descriptionDivs.forEach(function (div) {
      div.style.cssText = 'color:#50575e;font-size:14px;margin:0 0 16px;';
      tabMenu.insertAdjacentElement('afterend', div);
    });

    function showTable(activeIdx) {
      tables.forEach(function (table, idx) {
        table.style.display = idx === activeIdx ? 'table' : 'none';
      });
    }

    // ── Transforma cada linha em card (header + body) ──
    tables.forEach(function (table) {
      table.style.width = '100%';

      const ths = table.querySelectorAll('th');
      ths.forEach(function (th) { th.style.paddingTop = '68px'; });

      const rows = table.querySelectorAll('tr');
      rows.forEach(function (row) {
        const forminp = row.querySelector('.forminp');
        const titleDesc = row.querySelector('.titledesc');
        if (!forminp || !titleDesc) return;

        const inputField = forminp.querySelector('input, select, textarea');
        if (!inputField) return;

        forminp.style.cssText = 'display:flex;flex-direction:column;width:auto;padding:15px 25px;background-color:#fff;border:1px solid #dfdfdf;border-radius:8px;box-sizing:border-box;';

        // Tooltip (desc_tip) sai do th e vira descrição logo abaixo do título.
        const tipElement = titleDesc.querySelector('.woocommerce-help-tip');
        if (tipElement) tipElement.remove();

        const pElement = document.createElement('p');
        pElement.style.cssText = 'font-weight:normal;color:#343B45;';
        if (inputField.getAttribute('data-desc-tip')) {
          pElement.textContent = inputField.getAttribute('data-desc-tip');
        }

        titleDesc.style.paddingLeft = '.5em';
        titleDesc.style.fontSize = '20px';

        let labelElement = titleDesc.querySelector('label');
        if (labelElement) {
          labelElement.style.color = '#121519';
        }
        titleDesc.appendChild(pElement);

        // Header do card.
        const headerComponent = document.createElement('div');
        headerComponent.className = 'woo-forminp-header';
        headerComponent.style.minHeight = '44px';

        const headerText = document.createElement('p');
        headerText.className = 'woo-forminp-header-text';
        headerText.style.cssText = 'font-weight:bold;color:#121519;padding-left:6px;margin:0;';
        headerText.textContent = inputField.getAttribute('data-subtitle') || (labelElement ? labelElement.textContent.trim() : '');

        const spanElement = document.createElement('span');
        spanElement.style.cssText = 'color:#343B45;font-size:13px;padding-left:6px;display:block;margin-top:4px;';
        if (inputField.getAttribute('data-title-description')) {
          spanElement.textContent = inputField.getAttribute('data-title-description');
        }

        const hrElement = document.createElement('hr');
        hrElement.style.cssText = 'border:none;border-top:1px solid #ddd;margin:8px 0;';

        headerComponent.appendChild(headerText);
        headerComponent.appendChild(spanElement);
        headerComponent.appendChild(hrElement);

        // Body: move o conteúdo atual e adiciona a descrição final.
        const bodyComponent = document.createElement('div');
        bodyComponent.className = 'woo-forminp-body';
        bodyComponent.style.cssText = 'display:flex;flex-direction:column;justify-content:center;padding:20px 0;min-height:50px;padding-left:6px;';

        // Remove descrições nativas (desc + desc_tip) para evitar duplicar.
        forminp.querySelectorAll('.description').forEach(function (el) { el.remove(); });

        while (forminp.firstChild) {
          bodyComponent.appendChild(forminp.firstChild);
        }

        const pDescriptionField = document.createElement('p');
        pDescriptionField.className = 'description';
        pDescriptionField.style.cssText = 'color:#8F8F8F;';
        if (inputField.getAttribute('data-description')) {
          pDescriptionField.textContent = inputField.getAttribute('data-description');
        }
        bodyComponent.appendChild(pDescriptionField);

        // Campos de texto/select/textarea em largura total.
        const field = bodyComponent.querySelector('input[type="text"], input[type="number"], select, textarea');
        if (field) {
          field.style.width = '100%';
          field.style.maxWidth = '400px';
          field.style.boxSizing = 'border-box';
          field.style.color = '#2C3338';
        }

        forminp.appendChild(headerComponent);
        forminp.appendChild(bodyComponent);
      });
    });

    // Ativa a primeira tab (e respeita o hash).
    tabLinks[0].className = 'nav-tab nav-tab-active';
    showTable(0);

    const urlHash = window.location.hash;
    if (urlHash) {
      const idx = tabLinks.findIndex(function (a) { return a.href.endsWith(urlHash); });
      if (idx >= 0) tabLinks[idx].click();
    }
  });
})(jQuery);
