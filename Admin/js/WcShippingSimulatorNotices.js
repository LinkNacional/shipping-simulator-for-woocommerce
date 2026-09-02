(function () {
	'use strict';

	// Dispensa qualquer aviso que exponha data-action/data-nonce via fetch,
	// sem depender de jQuery nem de script inline no PHP.
	document.addEventListener('click', function (event) {
		var target = event.target;
		if (!target || typeof target.closest !== 'function') {
			return;
		}

		var dismiss = target.closest('.notice-dismiss');
		if (!dismiss) {
			return;
		}

		var notice = dismiss.closest('[data-dismissible]');
		if (!notice) {
			return;
		}

		var action = notice.getAttribute('data-action');
		if (!action) {
			return;
		}

		event.preventDefault();

		var formData = new FormData();
		formData.append('action', action);
		formData.append('nonce', notice.getAttribute('data-nonce'));

		fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
			method: 'POST',
			credentials: 'same-origin',
			body: formData
		}).then(function () {
			notice.remove();
		}).catch(function () {
			notice.remove();
		});
	});

	// ── Cartão de sucesso / erro (rollback, migração, instalar, atualizar) ──
	function closeCard(card) {
		if (!card || card.classList.contains('is-closing')) {
			return;
		}
		card.classList.add('is-closing');
		card.style.opacity = '0';
		setTimeout(function () {
			card.remove();
		}, 300);
	}

	function mountCard(card) {
		var host = document.querySelector('.wp-header-end') || document.querySelector('#wpbody-content');
		if (!host) {
			return;
		}
		// O cartão permanece até o usuário clicar no "x" ou recarregar (F5).
		host.insertAdjacentElement('afterend', card);
	}

	function showSuccessCard(key) {
		var cfg = (window.WcShippingSimulatorNotices && WcShippingSimulatorNotices.success) || {};

		// Evita duplicar o cartão.
		var existing = document.querySelector('.wc-simulator-success-card, .wc-simulator-error-card');
		if (existing) {
			existing.remove();
		}

		var desc = cfg[key] || '';
		var card = document.createElement('div');
		card.className = 'notice notice-info is-dismissible wc-simulator-notice wc-simulator-notice--success wc-simulator-success-card';

		var icon = document.createElement('div');
		icon.className = 'wc-simulator-notice__icon';
		var img = document.createElement('img');
		img.src = (window.WcShippingSimulatorNotices && WcShippingSimulatorNotices.icon_url) || '';
		img.alt = cfg.title || '';
		icon.appendChild(img);
		card.appendChild(icon);

		var content = document.createElement('div');
		content.className = 'wc-simulator-notice__content';

		var title = document.createElement('p');
		title.className = 'wc-simulator-notice__title';
		var strong = document.createElement('strong');
		strong.textContent = cfg.title || '';
		var badge = document.createElement('span');
		badge.className = 'wc-simulator-notice__badge';
		badge.textContent = cfg.badge || '';
		title.appendChild(strong);
		title.appendChild(badge);

		var body = document.createElement('p');
		body.textContent = desc;

		content.appendChild(title);
		content.appendChild(body);
		card.appendChild(content);

		var close = document.createElement('button');
		close.type = 'button';
		close.className = 'notice-dismiss wc-simulator-success-card__close';
		var sr = document.createElement('span');
		sr.className = 'screen-reader-text';
		sr.textContent = cfg.close || 'Fechar';
		close.appendChild(sr);
		card.appendChild(close);

		mountCard(card);
	}

	function showErrorCard(message) {
		var cfg = (window.WcShippingSimulatorNotices && WcShippingSimulatorNotices.error) || {};

		var existing = document.querySelector('.wc-simulator-success-card, .wc-simulator-error-card');
		if (existing) {
			existing.remove();
		}

		var card = document.createElement('div');
		card.className = 'notice notice-error is-dismissible wc-simulator-notice wc-simulator-notice--error wc-simulator-error-card';

		var icon = document.createElement('div');
		icon.className = 'wc-simulator-notice__icon';
		var img = document.createElement('img');
		img.src = (window.WcShippingSimulatorNotices && WcShippingSimulatorNotices.icon_url) || '';
		img.alt = cfg.title || '';
		icon.appendChild(img);
		card.appendChild(icon);

		var content = document.createElement('div');
		content.className = 'wc-simulator-notice__content';

		var title = document.createElement('p');
		title.className = 'wc-simulator-notice__title';
		var strong = document.createElement('strong');
		strong.textContent = cfg.title || '';
		var badge = document.createElement('span');
		badge.className = 'wc-simulator-notice__badge';
		badge.textContent = cfg.badge || '';
		title.appendChild(strong);
		title.appendChild(badge);

		var body = document.createElement('p');
		body.textContent = message || 'Erro. Tente novamente.';

		content.appendChild(title);
		content.appendChild(body);
		card.appendChild(content);

		var close = document.createElement('button');
		close.type = 'button';
		close.className = 'notice-dismiss wc-simulator-error-card__close';
		var sr = document.createElement('span');
		sr.className = 'screen-reader-text';
		sr.textContent = cfg.close || 'Fechar';
		close.appendChild(sr);
		card.appendChild(close);

		mountCard(card);
	}

	// ── Modal de rollback (retorno à versão legada) ──
	function openModal(modal) {
		if (!modal) {
			return;
		}
		modal.classList.add('is-open');
		modal.setAttribute('aria-hidden', 'false');
		var first = modal.querySelector('input, textarea');
		if (first) {
			first.focus();
		}
	}

	function closeModal(modal) {
		if (!modal) {
			return;
		}
		modal.classList.remove('is-open');
		modal.setAttribute('aria-hidden', 'true');
	}

	function updateConfirmState(modal) {
		if (!modal) {
			return;
		}
		var reason = modal.querySelector('textarea[name="rollback_reason"]');
		var confirm = modal.querySelector('.wc-simulator-rollback-confirm');
		if (!confirm) {
			return;
		}
		confirm.disabled = !(reason && reason.value.trim() !== '');
	}

	function submitRollback(confirm) {
		var modal = confirm.closest('.wc-simulator-rollback-modal');
		var reason = modal.querySelector('textarea[name="rollback_reason"]');
		var action = modal.getAttribute('data-rollback-action');
		var nonce = modal.getAttribute('data-rollback-nonce');

		if (!reason || reason.value.trim() === '') {
			return;
		}

		confirm.disabled = true;

		var formData = new FormData();
		formData.append('action', action);
		formData.append('nonce', nonce);
		formData.append('reason', reason.value);

		fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
			method: 'POST',
			credentials: 'same-origin',
			body: formData
		}).then(function (response) {
			return response.json();
		}).then(function (data) {
			closeModal(modal);
			// Sucesso ou erro: recarrega e o PHP exibe o cartão via transient.
			window.location.reload();
		}).catch(function () {
			closeModal(modal);
			window.location.reload();
		});
	}

	document.addEventListener('click', function (event) {
		var target = event.target;
		if (!target || typeof target.closest !== 'function') {
			return;
		}

		var cardClose = target.closest('.wc-simulator-success-card__close, .wc-simulator-error-card__close');
		if (cardClose) {
			closeCard(cardClose.closest('.wc-simulator-success-card, .wc-simulator-error-card'));
			return;
		}

		var open = target.closest('.wc-simulator-rollback-open');
		if (open) {
			event.preventDefault();
			openModal(document.querySelector('.wc-simulator-rollback-modal'));
			return;
		}

		var close = target.closest('.wc-simulator-rollback-modal__close, .wc-simulator-rollback-cancel, .wc-simulator-rollback-modal__backdrop');
		if (close) {
			closeModal(close.closest('.wc-simulator-rollback-modal'));
			return;
		}

		var confirm = target.closest('.wc-simulator-rollback-confirm');
		if (confirm) {
			submitRollback(confirm);
		}
	});

	document.addEventListener('input', function (event) {
		var target = event.target;
		if (!target || typeof target.closest !== 'function') {
			return;
		}
		var modal = target.closest('.wc-simulator-rollback-modal');
		if (modal) {
			updateConfirmState(modal);
		}
	});

	// Exibe o cartão (sucesso ou erro) ao carregar a página.
	if (window.WcShippingSimulatorNotices && window.WcShippingSimulatorNotices.show_on_load) {
		if ('error' === window.WcShippingSimulatorNotices.show_on_load) {
			showErrorCard(window.WcShippingSimulatorNotices.error_message);
		} else {
			showSuccessCard(window.WcShippingSimulatorNotices.show_on_load);
		}
	}
})();
