export default {
    content: [
      './resources/**/*.blade.php',
      './resources/**/*.js',
      './storage/framework/views/*.php',
      './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],
    theme: {
      extend: {
        fontFamily: {
          sans: [
            'Instrument Sans',
            'ui-sans-serif',
            'system-ui',
            'sans-serif',
            'Apple Color Emoji',
            'Segoe UI Emoji',
            'Segoe UI Symbol',
            'Noto Color Emoji',
          ],
        },
      },
    },
    plugins: [],
  };
  