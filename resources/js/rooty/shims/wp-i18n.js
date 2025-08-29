const i18n = window?.wp?.i18n;

if (!i18n) {
  throw new Error('[rooty] wp.i18n is not available. Make sure you print wp-i18n + translations before @vite().');
}

export const { __, _x, _n, _nx, sprintf, setLocaleData } = i18n;
export default i18n;
