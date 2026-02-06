async function checkAuth() {
	try {
		const res = await fetch('/', { method: 'POST', body: new URLSearchParams({ action: 'check_auth' }) });
		const json = await res.json();
		if (json.logged_in) {
			document.getElementById('login-link').style.display = 'none';
			document.getElementById('logout-btn').style.display = 'inline';
		}
	} catch (err) {
		console.error(err);
	}
}

document.getElementById('logout-btn')?.addEventListener('click', async () => {
	await fetch('/', { method: 'POST', body: new URLSearchParams({ action: 'logout' }) });
	window.location.reload();
});

document.addEventListener('DOMContentLoaded', checkAuth);
