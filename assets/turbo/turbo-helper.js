import { addKeyword } from "../scripts/Publication/AddKeyword";
import {
  axiosSave,
  axiosEvent,
} from "../scripts/Publication/AxiosASPublication";
import { AxiosSaveChapter } from "../scripts/Publication/AxiosASChapter";
import { darkMode, ok } from "../scripts/Publication/Darkmode";
import { PublicationPublishButton } from "../scripts/Publication/PublicationPublish";
import { ReadTime } from "../scripts/Publication/ChapterStats";
import { quillEditor } from "../scripts/Quill.js";

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
      AxiosSaveChapter();
      PublicationPublishButton();
      quillEditor();
      ReadTime();
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
