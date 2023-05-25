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
// ON active jquery
// TODO: refaire cette fonction
import "./scripts/Noty";
import Granim from "granim";
//
var canvas = document.getElementById("canvas-image-blending");
// Set canvas size
canvas.width = window.innerWidth;
canvas.height = window.innerHeight;
var granimInstance = new Granim({
  element: "#canvas-image-blending",
  direction: "top-bottom",
  isPausedWhenNotInView: true,

  states: {
    "default-state": {
      gradients: [
        ["#a8a8a8", "#dbdbdb"],
        ["#FF6B6B", "#556270"],
        ["#80d3fe", "#7ea0c4"],
        ["#f0ab51", "#eceba3"],
      ],
      transitionSpeed: 7000,
    },
  },
});
granimInstance;
