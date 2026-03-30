document.querySelectorAll(".ajax-form").forEach(form => {
	form.addEventListener("submit", async function(e) {
		e.preventDefault();

		const confirmMsg = form.dataset.confirm;
		if (confirmMsg && !confirm(confirmMsg))
			return;

		const action = form.dataset.action;
		const msg = form.querySelector(".settings-msg");
		const btn = form.querySelector("button[type='submit']");
		const data	= new URLSearchParams(new FormData(form));

		btn.disabled = true;
		msg.textContent = '';
		msg.className = 'settings-msg';

		const response = await fetch(action, {
			method: "POST",
			headers: { "Content-Type": "application/x-www-form-urlencoded" },
			body: data
		});

		const result = await response.json();
		btn.disabled = false;

		if (result.success) {
			msg.textContent = result.message ?? 'Saved';
			msg.classList.add('settings-msg--success');

			// Account deleted — redirect to home
			if (action === '/settings/delete')
				window.location.href = '/';

			form.querySelectorAll("input[type='password']").forEach(i => i.value = '');
		} else {
			msg.textContent = result.error ?? 'Something went wrong';
			msg.classList.add('settings-msg--error');
		}
	});
});

// ── Avatar upload ──────────────────────────────────────────────
(function () {
	const input  = document.getElementById('avatarInput');
	const preview = document.getElementById('avatarPreview');
	const msg = document.getElementById('avatarMsg');
	if (!input || !preview)
		return;

	let originalSrc = preview.src;

	input.addEventListener('change', async () => {
		const file = input.files[0];
		if (!file)
			return;

		// instant local preview
		const objectUrl = URL.createObjectURL(file);
		preview.src = objectUrl;
		msg.textContent = 'Uploading…';
		msg.className = 'settings-msg';
		const formData = new FormData();
		formData.append('avatar', file);

		try {
			const res	= await fetch('/settings/avatar', { method: 'POST', body: formData });
			const result = await res.json();

			if (result.success) {
				originalSrc = result.avatar + '?t=' + Date.now();
				preview.src = originalSrc;
				msg.textContent = result.message ?? '✓ Avatar updated!';
				msg.classList.add('settings-msg--success');

				// update every other avatar on the page
				document.querySelectorAll('img.avatar').forEach(img => {
					if (img !== preview) img.src = originalSrc;
				});
			} else {
				preview.src = originalSrc;
				msg.textContent = result.error ?? 'Upload failed.';
				msg.classList.add('settings-msg--error');
			}
		} catch {
			preview.src = originalSrc;
			msg.textContent = 'Network error. Please try again.';
			msg.classList.add('settings-msg--error');
		} finally {
			input.value = '';
			URL.revokeObjectURL(objectUrl);
		}
	});
}());
