/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
// any CSS you import will output into a single css file (app.css in this case)
import "./styles/app.css";
import "/node_modules/flowbite/dist/flowbite.turbo.js";
window.axios = require("axios");
import "./turbo/turbo-helper";
import "./bootstrap";
import { addKeyword } from "./scripts/Publication/AddKeyword";
import Quill from "quill";
// TODO: refaire cette fonction
if (document.getElementById("keyValue")) {
  addKeyword();
}
