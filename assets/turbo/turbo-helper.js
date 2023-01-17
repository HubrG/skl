import { addKeyword } from "../scripts/addKeyword";
import axios from "axios";
import { axiosSave, axiosEvent } from "../scripts/axiosASPublication";
import { darkMode, ok } from "../scripts/darkmode";
(function (c, a, n) {
  var w = c.createElement(a),
    s = c.getElementsByTagName(a)[0];
  w.src = n;
  s.parentNode.insertBefore(w, s);
})(document, "script", "https://sdk.canva.com/designbutton/v2/api.js");
const TurboHelper = class {
  constructor() {
    document.addEventListener("turbo:before-cache", () => {});
    document.addEventListener("turbo:render", () => {
      addKeyword();
      axiosEvent();
      darkMode();
      // canva
      (function (c, a, n) {
        var w = c.createElement(a),
          s = c.getElementsByTagName(a)[0];
        w.src = n;
        s.parentNode.insertBefore(w, s);
      })(document, "script", "https://sdk.canva.com/designbutton/v2/api.js");
    });
    document.addEventListener("turbo:visit", () => {
      // fade out the old body
      document.body.classList.add("turbo-loading");
    });
    document.addEventListener("turbo:before-render", (event) => {
      // when we are *about* to render, start us faded out
      event.detail.newBody.classList.add("turbo-loading");
    });
    document.addEventListener("turbo:render", () => {
      // after rendering, we first allow the turbo-loading class to set the low opacity
      // THEN, one frame later, we remove the turbo-loading class, which allows the fade in
      requestAnimationFrame(() => {
        document.body.classList.remove("turbo-loading");
      });
    });
  }
};
export default new TurboHelper();
