/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        'pos-bg':      '#0A0C10',
        'pos-surface': '#12151C',
        'pos-card':    '#1A1E28',
        'pos-border':  '#252B38',
        'pos-hover':   '#1E2432',
        'pos-accent':  '#F59E0B',
        'pos-green':   '#10B981',
        'pos-red':     '#EF4444',
        'pos-blue':    '#3B82F6',
        'pos-purple':  '#8B5CF6',
        'pos-text':    '#F1F5F9',
        'pos-muted':   '#64748B',
        'pos-subtle':  '#334155',
      },
    },
  },
  plugins: [],
}