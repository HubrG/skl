/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
// any CSS you import will output into a single css file (app.css in this case)
import "@glidejs/glide/dist/css/glide.core.min.css";
import "./bootstrap";
import "./styles/app.css";
import "./turbo/turbo-helper";
window.axios = require("axios");
// TODO: refaire cette fonction
import Glide from "@glidejs/glide";
import "./scripts/Noty";
if (document.querySelector(".glide-top")) {
  var glideMarket = new Glide(".glide-market", {
    type: "slider",
    autoplay: false,
    hoverpause: true,
    perView: 1,
    breakpoints: {
      1024: {
        perView: 1,
      },
      600: {
        perView: 1,
      },
    },
  });
  var glideTop = new Glide(".glide-top", {
    type: "slider",
    autoplay: 5000,
    hoverpause: true,
    perView: 1,
    keyboard: false,
    breakpoints: {
      1024: {
        perView: 1,
      },
      600: {
        perView: 1,
      },
    },
  });
  glideMarket.mount();
  glideTop.mount();
}
// window.addEventListener("scroll", () => {
//   const nav = document.getElementById("nav");
//   const progressAt = window.innerHeight * 2; // 50vh
//   const scrollY = window.scrollY;

//   // Calculez la progression du défilement entre 0 et 1
//   let progress = scrollY / progressAt;
//   progress = Math.min(Math.max(progress, 0), 1);

//   // Ajustez la position et l'opacité de la barre de navigation en fonction de la progression
//   nav.style.transform = `translateY(${-progress * 150}%)`;
// });
