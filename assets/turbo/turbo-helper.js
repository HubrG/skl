import "../../node_modules/flowbite/dist/flowbite.turbo.js";
import { addKeyword } from "../scripts/Publication/AddKeyword";
import { AxiosSaveChapter } from "../scripts/Publication/Chapter";
import { AxiosSavePublication } from "../scripts/Publication/Publication";
import { darkMode } from "../scripts/Darkmode";
import { ReadTime } from "../scripts/Publication/ChapterStats";
import { quillEditor } from "../scripts/Quill.js";
import Quill from "quill";
window.Noty = require("noty");
window.axios = require("axios");
import { Sortables } from "../scripts/Publication/Sortable";

const TurboHelper = class {
  constructor() {
    document.addEventListener("turbo:before-cache", () => {});
    document.addEventListener("turbo:render", () => {
      addKeyword();
      darkMode();
      AxiosSaveChapter();
      AxiosSavePublication();
      ReadTime();
      quillEditor();
      Sortables();
    });
    document.addEventListener("turbo:visit", () => {
      // fade out the old body

      document.body.classList.add("turbo-loading");
    });
    document.addEventListener("turbo:before-render", (event) => {
      // when we are *about* to render, start us fadeddd out
      event.detail.newBody.classList.add("turbo-loading");
    });
    document.addEventListener("turbo:render", () => {
      // after rendering, we first allow the turbo-loading class to set the low opacity
      // THEN, one frame later, we remove the turbo-loading class, which adllows the fade in
      requestAnimationFrame(() => {
        document.body.classList.remove("turbo-loading");
      });
    });
  }
};
export default new TurboHelper();
