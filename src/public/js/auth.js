async function checkAuth() {
	try {
		const res = await fetch('/auth/status', {credentials: 'include'});
		const json = await res.json();

		if (json.logged_in) {
			document.getElementById('login-link')?.remove();
			document.getElementById('signup-link')?.remove();

			document.getElementById('profile-link').style.display = 'inline';
			document.getElementById('logout-link').style.display = 'inline';
		}
	} catch (e) {
		console.error(e);
	}
}

document.getElementById('logout-link')?.addEventListener('click', async e => {
	e.preventDefault();
	await fetch('/logout', {credentials: 'include'});
	location.reload();
});

document.addEventListener('DOMContentLoaded', checkAuth);
