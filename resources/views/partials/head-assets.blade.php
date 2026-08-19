<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Arabic:wght@400;600;700&amp;family=Noto+Serif:wght@400;600;700&amp;family=Be+Vietnam+Pro:wght@400;500;600;700&amp;display=swap" rel="stylesheet" />
<script id="tailwind-config">
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                "colors": {
                    "on-primary": "#ffffff",
                    "on-secondary-fixed": "#1b1c1c",
                    "inverse-primary": "#a5c8ff",
                    "on-surface": "#181c21",
                    "surface-container-low": "#f2f3fc",
                    "on-tertiary-fixed": "#311300",
                    "primary-fixed-dim": "#a5c8ff",
                    "primary-container": "#1976d2",
                    "surface-container": "#F5F5F5",
                    "on-error-container": "#93000a",
                    "secondary-container": "#e4e2e1",
                    "surface": "#f9f9ff",
                    "on-error": "#ffffff",
                    "on-secondary-fixed-variant": "#474747",
                    "surface-container-high": "#e6e8f0",
                    "secondary-fixed-dim": "#c8c6c6",
                    "on-surface-variant": "#414752",
                    "on-secondary": "#ffffff",
                    "on-tertiary-fixed-variant": "#733600",
                    "inverse-surface": "#2d3037",
                    "surface-container-lowest": "#ffffff",
                    "success": "#2E7D32",
                    "tertiary-fixed-dim": "#ffb688",
                    "error": "#ba1a1a",
                    "primary-fixed": "#d4e3ff",
                    "surface-variant": "#e0e2ea",
                    "on-background": "#181c21",
                    "primary": "#005dac",
                    "error-container": "#ffdad6",
                    "on-primary-fixed": "#001c3a",
                    "surface-container-highest": "#e0e2ea",
                    "tertiary-fixed": "#ffdbc7",
                    "secondary": "#5f5e5e",
                    "on-tertiary-container": "#fffeff",
                    "surface-tint": "#005faf",
                    "on-tertiary": "#ffffff",
                    "on-primary-container": "#fffdff",
                    "secondary-fixed": "#e4e2e1",
                    "inverse-on-surface": "#eff0f9",
                    "on-secondary-container": "#656464",
                    "surface-bright": "#f9f9ff",
                    "tertiary": "#944700",
                    "background": "#f9f9ff",
                    "tertiary-container": "#ba5b00",
                    "outline-variant": "#c1c6d4",
                    "on-primary-fixed-variant": "#004786",
                    "outline": "#E0E0E0",
                    "surface-dim": "#d8dae2"
                },
                "borderRadius": {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
                },
                "spacing": {
                    "unit": "8px",
                    "card-padding": "32px",
                    "gutter": "16px",
                    "stack-gap": "16px",
                    "container-margin": "24px"
                },
                "fontFamily": {
                    "title-md": ["Noto Serif Arabic", "Noto Serif", "serif"],
                    "headline-lg": ["Noto Serif Arabic", "Noto Serif", "serif"],
                    "display-lg": ["Noto Serif Arabic", "Noto Serif", "serif"],
                    "headline-lg-mobile": ["Noto Serif Arabic", "Noto Serif", "serif"],
                    "label-sm": ["Be Vietnam Pro", "sans-serif"],
                    "body-lg": ["Be Vietnam Pro", "sans-serif"],
                    "body-md": ["Be Vietnam Pro", "sans-serif"]
                },
                "fontSize": {
                    "title-md": ["20px", {"lineHeight": "28px", "fontWeight": "600"}],
                    "headline-lg": ["32px", {"lineHeight": "40px", "fontWeight": "600"}],
                    "display-lg": ["40px", {"lineHeight": "52px", "fontWeight": "700"}],
                    "headline-lg-mobile": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                    "label-sm": ["12px", {"lineHeight": "16px", "letterSpacing": "0.5px", "fontWeight": "500"}],
                    "body-lg": ["18px", {"lineHeight": "28px", "fontWeight": "400"}],
                    "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}]
                }
            }
        }
    }
</script>
<style>
    body {
        background-color: theme('colors.background');
        color: theme('colors.on-background');
        min-height: max(884px, 100dvh);
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
    }
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        font-size: 24px;
    }
    .material-symbols-outlined.filled {
        font-variation-settings: 'FILL' 1;
    }
    .bg-pattern {
        background-image: radial-gradient(var(--tw-colors-outline-variant) 1px, transparent 1px);
        background-size: 24px 24px;
        opacity: 0.3;
    }
    .card-shadow {
        box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.05);
    }
    .chip-checkbox:checked + label {
        background-color: theme('colors.primary-container');
        color: theme('colors.on-primary-container');
        border-color: theme('colors.primary-container');
    }
    .chip-checkbox:checked + label .material-symbols-outlined {
        font-variation-settings: 'FILL' 1;
    }
    .section-content {
        transition: max-height 0.3s ease-out, opacity 0.3s ease-out;
        max-height: 2000px;
        opacity: 1;
        overflow: hidden;
    }
    .section-content.collapsed {
        max-height: 0;
        opacity: 0;
    }
    .chevron {
        transition: transform 0.3s ease;
    }
    .collapsed .chevron {
        transform: rotate(-180deg);
    }
    .field-error {
        color: theme('colors.error');
        font-size: 12px;
        margin-top: 4px;
        display: none;
    }
    .field-error.show {
        display: block;
    }
    [x-cloak] { display: none !important; }
</style>
