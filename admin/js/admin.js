(() => {
	const parseRows = (input) => {
		try {
			return JSON.parse(input.value || '[]');
		} catch (error) {
			return [];
		}
	};

	const syncRepeater = (repeater) => {
		const input = repeater.querySelector('.eptp-repeater-input');
		const rowsWrap = repeater.querySelector('.eptp-repeater-rows');
		const columns = JSON.parse(rowsWrap.dataset.columns || '{}');
		const rows = parseRows(input);

		rowsWrap.innerHTML = '';

		rows.forEach((row, index) => {
			const rowEl = document.createElement('div');
			rowEl.className = 'eptp-repeater-row';

			Object.keys(columns).forEach((key) => {
				const field = document.createElement(key === 'description' || key === 'answer' ? 'textarea' : 'input');
				field.value = row[key] || '';
				field.placeholder = columns[key];
				field.addEventListener('input', () => {
					rows[index][key] = field.value;
					input.value = JSON.stringify(rows);
				});
				rowEl.appendChild(field);
			});

			const actions = document.createElement('div');
			actions.className = 'eptp-repeater-actions';

			const remove = document.createElement('button');
			remove.type = 'button';
			remove.className = 'button-link-delete';
			remove.textContent = 'Remove';
			remove.addEventListener('click', () => {
				rows.splice(index, 1);
				input.value = JSON.stringify(rows);
				syncRepeater(repeater);
			});

			actions.appendChild(remove);
			rowEl.appendChild(actions);
			rowsWrap.appendChild(rowEl);
		});
	};

	document.querySelectorAll('.eptp-repeater').forEach((repeater) => {
		const addButton = repeater.querySelector('.eptp-add-row');
		const input = repeater.querySelector('.eptp-repeater-input');
		const rowsWrap = repeater.querySelector('.eptp-repeater-rows');
		const columns = JSON.parse(rowsWrap.dataset.columns || '{}');

		syncRepeater(repeater);

		addButton?.addEventListener('click', () => {
			const rows = parseRows(input);
			const row = {};
			Object.keys(columns).forEach((key) => {
				row[key] = '';
			});
			rows.push(row);
			input.value = JSON.stringify(rows);
			syncRepeater(repeater);
		});
	});

	const submitButton = document.getElementById('eptp-checkin-submit');
	const queryInput = document.getElementById('eptp-checkin-query');

	const escapeHtml = (value) => {
		const div = document.createElement('div');
		div.textContent = value || '';
		return div.innerHTML;
	};

	const performCheckin = async () => {
		const query = queryInput?.value?.trim();
		const override = document.getElementById('eptp-checkin-override')?.checked;
		const result = document.getElementById('eptp-checkin-result');

		if (!query || !result) {
			return;
		}

		submitButton?.setAttribute('aria-busy', 'true');
		if (submitButton) {
			submitButton.disabled = true;
		}
		result.className = 'eptp-checkin-result';
		result.textContent = 'Checking ticket…';

		try {
			const response = await window.fetch(eptpAdmin.restUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': eptpAdmin.nonce,
				},
				body: JSON.stringify({ query, override }),
			});
			const payload = await response.json();

			result.classList.add(payload.success ? 'is-success' : 'is-error');
			result.innerHTML = payload.success
				? `<strong>${escapeHtml(payload.message)}</strong><div class="eptp-checkin-meta"><span>${escapeHtml(payload.attendee.name)}</span><span>${escapeHtml(payload.attendee.email)}</span><span>${escapeHtml(payload.attendee.event_name)}</span><span>${escapeHtml(payload.attendee.code)}</span></div>`
				: escapeHtml(payload.message || 'Validation failed.');
		} catch (error) {
			result.classList.add('is-error');
			result.textContent = 'Unable to reach the check-in service.';
		} finally {
			submitButton?.removeAttribute('aria-busy');
			if (submitButton) {
				submitButton.disabled = false;
			}
		}
	};

	submitButton?.addEventListener('click', performCheckin);

	queryInput?.addEventListener('keydown', (event) => {
		if (event.key === 'Enter') {
			event.preventDefault();
			performCheckin();
		}
	});
})();
