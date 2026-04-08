(() => {
	const form = document.querySelector('.eptp-waitlist');
	const search = document.getElementById('eptp-event-search');
	const pills = document.querySelectorAll('.eptp-filter-pill');
	const cards = document.querySelectorAll('#eptp-event-grid .eptp-event-card');
	const emptyState = document.getElementById('eptp-empty-state');
	let activeFilter = 'all';

	const updateArchiveFilters = () => {
		if (!cards.length) {
			return;
		}

		const term = (search?.value || '').trim().toLowerCase();
		let visible = 0;

		cards.forEach((card) => {
			const matchesSearch = !term || (card.dataset.search || '').includes(term);
			const categories = (card.dataset.category || '').split(' ').filter(Boolean);
			const matchesFilter = activeFilter === 'all' || categories.includes(activeFilter);
			const show = matchesSearch && matchesFilter;

			card.hidden = !show;
			if (show) {
				visible += 1;
			}
		});

		if (emptyState) {
			emptyState.hidden = visible !== 0;
		}
	};

	search?.addEventListener('input', updateArchiveFilters);

	pills.forEach((pill) => {
		pill.addEventListener('click', () => {
			activeFilter = pill.dataset.filter || 'all';
			pills.forEach((button) => {
				const isActive = button === pill;
				button.classList.toggle('is-active', isActive);
				button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
			});
			updateArchiveFilters();
		});
	});

	form?.addEventListener('submit', async (event) => {
		event.preventDefault();

		const submit = form.querySelector('button');
		const note = form.querySelector('.eptp-inline-note');
		const data = {
			event_id: form.querySelector('[name="event_id"]')?.value,
			name: form.querySelector('[name="name"]')?.value,
			email: form.querySelector('[name="email"]')?.value,
		};

		if (submit) {
			submit.disabled = true;
		}

		try {
			const response = await fetch(eptpPublic.restUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': eptpPublic.nonce,
				},
				body: JSON.stringify(data),
			});
			const payload = await response.json();
			if (note) {
				note.textContent = payload.message || 'You have been added to the waitlist.';
			}
			if (response.ok) {
				form.reset();
			}
		} catch (error) {
			if (note) {
				note.textContent = 'Unable to submit the waitlist form right now.';
			}
		}

		if (submit) {
			submit.disabled = false;
		}
	});
})();
