/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx}',
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        primary: {
          50:  '#E6EEFF',
          100: '#CCE0FF',
          200: '#99BFFF',
          300: '#669FFF',
          400: '#3380FF',
          500: '#0065FF',
          600: '#0052CC',
          700: '#0043A4',
          800: '#003380',
          900: '#002460',
        },
        brand: {
          sidebar: '#1D2125',
          'sidebar-hover': '#282E33',
          'sidebar-active': '#1868DB',
          'sidebar-text': '#B8C4CE',
          body: '#F4F5F7',
          surface: '#FFFFFF',
          border: '#DFE1E6',
          'text-dark': '#172B4D',
          'text-muted': '#6B778C',
        },
      },
      fontFamily: {
        sans: ['-apple-system', 'BlinkMacSystemFont', "'Segoe UI'", 'Roboto', "'Noto Sans'", 'Ubuntu', 'Droid Sans', "'Helvetica Neue'", 'sans-serif'],
      },
      boxShadow: {
        card: '0 1px 2px 0 rgba(9,30,66,.25), 0 0 0 1px rgba(9,30,66,.08)',
        'card-hover': '0 4px 8px -2px rgba(9,30,66,.25), 0 0 0 1px rgba(9,30,66,.08)',
        panel: '0 8px 16px -4px rgba(9,30,66,.25), 0 0 0 1px rgba(9,30,66,.08)',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
