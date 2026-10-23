// Small shared interactions that do not require a backend round trip.
document.addEventListener('DOMContentLoaded', function () {
	var themeButton = document.querySelector('#theme-toggle');
	if (themeButton) {
		var updateThemeButton = function () {
			var isDark = document.documentElement.dataset.theme === 'dark';
			themeButton.setAttribute('aria-pressed', isDark ? 'true' : 'false');
			themeButton.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
			themeButton.querySelector('.theme-label').textContent = isDark ? 'Light mode' : 'Dark mode';
			themeButton.querySelector('.theme-icon').textContent = isDark ? '☀' : '◐';
		};
		updateThemeButton();
		themeButton.addEventListener('click', function () {
			var isDark = document.documentElement.dataset.theme !== 'dark';
			if (isDark) document.documentElement.dataset.theme = 'dark';
			else delete document.documentElement.dataset.theme;
			try { localStorage.setItem('esf-theme', isDark ? 'dark' : 'light'); } catch (error) {}
			updateThemeButton();
		});
	}

	var toggle = document.querySelector('.password-toggle');
	var password = document.querySelector('#password');
	if (toggle && password) {
		toggle.addEventListener('click', function () {
			var showing = password.type === 'text';
			password.type = showing ? 'password' : 'text';
			toggle.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
		});
	}

	var role = document.querySelector('#role');
	var workerFields = document.querySelector('.worker-fields');
	if (role && workerFields) {
		var updateWorkerFields = function () {
			var isWorker = role.value === 'worker';
			workerFields.style.display = isWorker ? '' : 'none';
			document.querySelector('#position_id').required = isWorker;
			document.querySelector('#salary_grade_id').required = isWorker;
		};
		role.addEventListener('change', updateWorkerFields);
		updateWorkerFields();
	}
});
