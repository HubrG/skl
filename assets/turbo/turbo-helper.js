import { addKeyword } from "../scripts/Publication/AddKeyword";
import {
  axiosSave,
  axiosEvent,
} from "../scripts/Publication/AxiosASPublication";
import { AxiosSaveChapter } from "../scripts/Publication/Chapter";
import { darkMode, ok } from "../scripts/Publication/Darkmode";
import { PublicationPublishButton } from "../scripts/Publication/PublicationPublish";
import { ReadTime } from "../scripts/Publication/ChapterStats";
import { quillEditor } from "../scripts/Quill.js";
import Sortable from "sortablejs";
// Default SortableJS
if (document.getElementById("itemsChap")) {
  // List with handle
  Sortable.create(document.getElementById("itemsChap"), {
    animation: 150, // ms, animation speed moving items when sorting, `0` — without animation
    easing: "cubic-bezier(1, 0, 0, 1)",
    onChange: function (/**Event*/ evt) {
      console.log(evt.newIndex); // most likely why this event is used is to get the dragging element's current index
      // same properties as onEnd
    },
  });
}
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
