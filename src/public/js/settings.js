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