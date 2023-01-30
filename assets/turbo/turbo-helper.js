import "../../node_modules/flowbite/dist/flowbite.turbo.js";
import { addKeyword } from "../scripts/Publication/AddKeyword";
import { AxiosSaveChapter } from "../scripts/Publication/Chapter";
import { AxiosSavePublication } from "../scripts/Publication/Publication";
import { darkMode } from "../scripts/Darkmode";
import { ReadTime } from "../scripts/Publication/ChapterStats";
import { quillEditor } from "../scripts/Quill.js";
import { Sortables } from "../scripts/Publication/Sortable";
import { LazyLoad } from "../scripts/LazyLoad";

const TurboHelper = class {
  constructor() {
    document.addEventListener("turbo:before-cache", () => {});
    document.addEventListener("turbo:render", () => {
      addKeyword();
      darkMode();
      AxiosSavePublication();
      if (document.getElementById("editorHTML")) {
        ReadTime();
        AxiosSaveChapter();
        quillEditor();
      }
      if (document.querySelector(".list-group-item")) {
        Sortables();
      }
      LazyLoad();
    });
    document.addEventListener("turbo:visit", () => {
      // fade out the old body
      document.body.classList.add("turbo-loading");
    });
    document.addEventListener("turbo:before-render", (event) => {
      if (this.isPreviewRendered()) {
        // this is a preview that has been instantly swapped
        // remove .turbo-loading so the preview starts fully opaque
        event.detail.newBody.classList.remove("turbo-loading");
        // start fading out 10ms later after opacity starts full
        setTimeout(() => {
          document.body.classList.add("turbo-loading");
        }, 10);
      } else {
        // when we are *about* to render a fresh page
        // we should already be faded out, so start us faded out
        event.detail.newBody.classList.add("turbo-loading");
      }
    });
    document.addEventListener("turbo:render", () => {
      if (!this.isPreviewRendered()) {
        // if this is a preview, then we do nothing: stay faded out
        // after rendering the REAL page, we first allow the .turbo-loading to
        // instantly start the page at lower opacity. THEN remove the class,
        // which allows the fade in
        setTimeout(() => {
          document.body.classList.remove("turbo-loading");
        }, 10);
      }
    });
  }
  isPreviewRendered() {
    return document.documentElement.hasAttribute("data-turbo-preview");
  }
};
export default new TurboHelper();
