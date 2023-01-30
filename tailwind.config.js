module.exports = {
  darkMode: "class",
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
    "./src/**/*.html",
    "./src/**/*.vue",
    "./src/**/*.jsx",
    "./layouts/**/*.html",
    "./content/**/*.md",
    "./content/**/*.html",
    "./src/**/*.js",
  ],
  theme: {
    extend: {
      keyframes: {
        "fade-in": {
          "0%": { opacity: "0%" },
          "100%": { opacity: "100%" },
        },
      },
      animation: {
        "fade-in": "fade-in 0.2s ease-in-out",
      },
    },
  },
  safelist: [
    "w-64",
    "w-1/2",
    "rounded-l-lg",
    "rounded-r-lg",
    "bg-gray-200",
    "grid-cols-4",
    "grid-cols-7",
    "h-6",
    "leading-6",
    "h-9",
    "leading-9",
    "shadow-lg",
  ],
  plugins: [require("flowbite/plugin")],
};
