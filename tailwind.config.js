module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
    './src/**/*.html',
    './src/**/*.vue',
    './src/**/*.jsx',
    
  ],
  theme: {
    extend: {
      keyframes: {
        "fade-in": {
          '0%': { opacity: '0%' },
          '100%': { opacity: '100%' },
        }
      },
      animation: {
        "fade-in": 'fade-in 0.2s ease-in-out',
      } 
    },
  },
  plugins: [
    require('flowbite/plugin')
]
}