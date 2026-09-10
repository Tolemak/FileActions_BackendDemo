const THEME_KEY = 'theme';

export function initThemeToggle(): void {
    const button = document.getElementById('theme-toggle-btn');
    const icon = button?.querySelector('i');
    if (!button || !icon) return;

    const applyIcon = (theme: string) => {
        icon.classList.toggle('fa-moon', theme === 'light');
        icon.classList.toggle('fa-sun', theme === 'dark');
    };

    applyIcon(document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light');

    button.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-bs-theme', next);
        localStorage.setItem(THEME_KEY, next);
        applyIcon(next);
    });
}
