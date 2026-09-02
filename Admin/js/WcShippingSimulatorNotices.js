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

	// ── Cartão de sucesso (rollback / migração de configuração) ──
	function closeSuccessCard(card) {
		if (!card || card.classList.contains('is-closing')) {
			return;
		}
		card.classList.add('is-closing');
		card.style.opacity = '0';
		setTimeout(function () {
			card.remove();
		}, 300);
	}

	function showSuccessCard(key) {
		var cfg = (window.WcShippingSimulatorNotices && WcShippingSimulatorNotices.success) || {};

		// Evita duplicar o cartão.
		var existing = document.querySelector('.wc-simulator-success-card');
		if (existing) {
			existing.remove();
		}

		var desc = cfg[key] || '';
		var html =
			'<div class="notice wc-simulator-notice wc-simulator-notice--success wc-simulator-success-card">' +
				'<div class="wc-simulator-notice__content">' +
					'<p class="wc-simulator-notice__title">' +
						'<strong>' + (cfg.title || '') + '</strong>' +
						'<span class="wc-simulator-notice__badge">' + (cfg.badge || '') + '</span>' +
					'</p>' +
					'<p>' + desc + '</p>' +
				'</div>' +
				'<button type="button" class="notice-dismiss wc-simulator-success-card__close"><span class="screen-reader-text">' + (cfg.close || 'Fechar') + '</span></button>' +
			'</div>';

		var host = document.querySelector('.wp-header-end') || document.querySelector('#wpbody-content');
		if (!host) {
			return;
		}

		var temp = document.createElement('div');
		temp.innerHTML = html;
		var card = temp.firstElementChild;

		host.insertAdjacentElement('afterend', card);

		// Fecha sozinho após alguns segundos.
		setTimeout(function () {
			closeSuccessCard(card);
		}, 3600);
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
			if (data && data.success) {
				closeModal(modal);
				showSuccessCard('rollback');
				setTimeout(function () {
					window.location.reload();
				}, 4000);
				return;
			}
			confirm.disabled = false;
			window.alert(data && data.data && data.data.message ? data.data.message : 'Não foi possível aplicar o rollback.');
		}).catch(function () {
			confirm.disabled = false;
			window.alert('Não foi possível aplicar o rollback.');
		});
	}

	document.addEventListener('click', function (event) {
		var target = event.target;
		if (!target || typeof target.closest !== 'function') {
			return;
		}

		var successClose = target.closest('.wc-simulator-success-card__close');
		if (successClose) {
			closeSuccessCard(successClose.closest('.wc-simulator-success-card'));
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

	// Exibe o cartão de sucesso ao chegar da migração de configuração.
	if (window.WcShippingSimulatorNotices && WcShippingSimulatorNotices.show_on_load) {
		showSuccessCard(WcShippingSimulatorNotices.show_on_load);
	}
})();
