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
