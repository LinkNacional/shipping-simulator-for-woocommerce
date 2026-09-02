(function () {
    'use strict';

    function start(btn) {
        if (btn.getAttribute('data-installing') === '1') {
            return;
        }
        btn.setAttribute('data-installing', '1');
        btn.classList.add('is-loading');

        var bar = btn.querySelector('.wc-simulator-plugin-update-button__bar');
        var text = btn.querySelector('.wc-simulator-plugin-update-button__text');

        if (bar) {
            bar.style.transition = 'width 6s linear';
            bar.style.width = '90%';
        }

        var formData = new FormData();
        formData.append('action', WcShippingSimulatorPluginInstall.action);
        formData.append('nonce', WcShippingSimulatorPluginInstall.nonce);
        formData.append('install_action', btn.getAttribute('data-install-action'));

        fetch(WcShippingSimulatorPluginInstall.ajaxurl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        }).then(function (response) {
            return response.json();
        }).then(function (data) {
            if (!data || !data.success) {
                throw new Error(data && data.data && data.data.message ? data.data.message : 'Erro ao concluir a operação.');
            }

            if (bar) {
                bar.style.transition = 'width 0.4s ease';
                bar.style.width = '100%';
            }
            btn.classList.remove('is-loading');
            btn.classList.add('is-success');
            if (text) {
                text.textContent = 'Sucesso!';
            }
            var redirect = data.data && data.data.redirect_url
                ? data.data.redirect_url
                : WcShippingSimulatorPluginInstall.fallback_url;

            setTimeout(function () {
                window.location.href = redirect;
            }, 4000);
        }).catch(function (err) {
            btn.classList.remove('is-loading');
            btn.classList.add('is-error');
            btn.removeAttribute('data-installing');
            if (text) {
                text.textContent = (err && err.message) ? err.message : 'Erro. Tente novamente.';
            }
        });
    }

    document.addEventListener('click', function (event) {
        var target = event.target;
        if (!target || typeof target.closest !== 'function') {
            return;
        }
        var btn = target.closest('.wc-simulator-plugin-update-button');
        if (btn) {
            event.preventDefault();
            start(btn);
        }
    });
})();
