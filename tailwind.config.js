/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        'google-blue': '#4285f4',
        'google-red': '#ea4335',
        'google-yellow': '#fbbc04',
        'google-green': '#34a853',
      },
    },
  },
  plugins: [],
}
