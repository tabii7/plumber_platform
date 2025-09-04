<div x-data="darkMode()" class="dark-mode-toggle">
    <button @click="toggle()" 
            class="toggle-switch"
            :class="{ 'active': isDark }"
            role="switch" 
            :aria-checked="isDark"
            title="Toggle dark mode">
        <span class="toggle-slider"></span>
        <span class="toggle-icon sun-icon" x-show="!isDark">☀️</span>
        <span class="toggle-icon moon-icon" x-show="isDark">🌙</span>
    </button>
    <span class="toggle-label">
        <span x-show="!isDark">Light</span>
        <span x-show="isDark">Dark</span>
    </span>
</div>

<style>
.dark-mode-toggle {
    display: flex;
    align-items: center;
    gap: 8px;
}

.toggle-switch {
    position: relative;
    width: 60px;
    height: 30px;
    background: #e5e7eb;
    border: none;
    border-radius: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 4px;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
}

.toggle-switch:hover {
    background: #d1d5db;
    transform: scale(1.05);
}

.toggle-switch.active {
    background: #10b981;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
}

.toggle-switch.active:hover {
    background: #059669;
}

.toggle-slider {
    position: absolute;
    top: 2px;
    left: 2px;
    width: 26px;
    height: 26px;
    background: white;
    border-radius: 50%;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    z-index: 2;
}

.toggle-switch.active .toggle-slider {
    transform: translateX(30px);
}

.toggle-icon {
    font-size: 14px;
    z-index: 1;
    transition: all 0.3s ease;
}

.sun-icon {
    opacity: 1;
}

.moon-icon {
    opacity: 1;
}

.toggle-label {
    font-size: 14px;
    font-weight: 500;
    color: #6b7280;
    user-select: none;
    min-width: 40px;
}

/* Dark mode styles for the toggle itself */
body.dark .toggle-switch {
    background: #374151;
}

body.dark .toggle-switch:hover {
    background: #4b5563;
}

body.dark .toggle-switch.active {
    background: #10b981;
}

body.dark .toggle-label {
    color: #d1d5db;
}
</style>

<script>
function darkMode() {
    return {
        isDark: false,
        
        init() {
            // Check for saved theme preference or default to light mode
            this.isDark = localStorage.getItem('darkMode') === 'true' || 
                         (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches);
            
            this.applyTheme();
        },
        
        toggle() {
            this.isDark = !this.isDark;
            this.applyTheme();
            localStorage.setItem('darkMode', this.isDark);
        },
        
        applyTheme() {
            if (this.isDark) {
                document.documentElement.classList.add('dark');
                document.body.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.body.classList.remove('dark');
            }
        }
    }
}
</script>
