import { addKeyword } from "../scripts/addKeyword";
import axios from "axios";
import { axiosSave, axiosEvent } from "../scripts/axiosASPublication";
const TurboHelper = class {
  constructor() {
    document.addEventListener("turbo:before-cache", () => {});
    document.addEventListener("turbo:render", () => {
      addKeyword();
      axiosEvent();
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
