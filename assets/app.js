/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
// any CSS you import will output into a single css file (app.css in this case)
import "./styles/app.css";
window.axios = require("axios");
// start the Stimulus application
import "./bootstrap";
import "./turbo/turbo-helper";
import "/node_modules/flowbite/dist/flowbite.turbo.js";
import { addKeyword } from "./scripts/addKeyword";
import "form-data";
if (document.getElementById("keyValue")) {
  addKeyword();
}
