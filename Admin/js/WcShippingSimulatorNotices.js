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
		var email = modal.querySelector('input[name="rollback_email"]');
		var reason = modal.querySelector('textarea[name="rollback_reason"]');
		var confirm = modal.querySelector('.wc-simulator-rollback-confirm');
		if (!confirm) {
			return;
		}
		var filled = email && email.value.trim() !== '' && reason && reason.value.trim() !== '';
		confirm.disabled = !filled;
	}

	function submitRollback(confirm) {
		var modal = confirm.closest('.wc-simulator-rollback-modal');
		var email = modal.querySelector('input[name="rollback_email"]');
		var reason = modal.querySelector('textarea[name="rollback_reason"]');
		var action = modal.getAttribute('data-rollback-action');
		var nonce = modal.getAttribute('data-rollback-nonce');

		if (!email || !reason || email.value.trim() === '' || reason.value.trim() === '') {
			return;
		}

		confirm.disabled = true;

		var formData = new FormData();
		formData.append('action', action);
		formData.append('nonce', nonce);
		formData.append('email', email.value);
		formData.append('reason', reason.value);

		fetch(window.ajaxurl || '/wp-admin/admin-ajax.php', {
			method: 'POST',
			credentials: 'same-origin',
			body: formData
		}).then(function (response) {
			return response.json();
		}).then(function (data) {
			if (data && data.success) {
				window.location.reload();
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

		var open = target.closest('.wc-simulator-rollback-open');
		if (open) {
			var notice = open.closest('[data-dismissible]');
			openModal(notice ? notice.querySelector('.wc-simulator-rollback-modal') : null);
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
})();
