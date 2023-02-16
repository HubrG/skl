import { addKeyword } from "../scripts/Publication/AddKeyword";
import { AxiosSaveChapter } from "../scripts/Publication/Chapter";
import { AxiosSavePublication } from "../scripts/Publication/Publication";
import { darkMode } from "../scripts/Darkmode";
import { ReadTime } from "../scripts/Publication/ChapterStats";
import { quillEditor } from "../scripts/Quill.js";
import { Sortables } from "../scripts/Publication/Sortable";
import { LazyLoad } from "../scripts/LazyLoad";
import { PublicationShow } from "../scripts/Publication/PublicationShow";
import {
  ShowChapter,
  toggleDrawer,
  targetQuote,
  DropdownMenu,
} from "../scripts/Publication/ChapterShow";
import tippy from "tippy.js";
tippy("[data-tippy-content]");

const TurboHelper = class {
  constructor() {
    document.addEventListener("turbo:before-cache", () => {});
    document.addEventListener("turbo:render", () => {
      darkMode();
      if (document.getElementById("hideIdPub")) {
        addKeyword();
        AxiosSavePublication();
      }
      if (document.getElementById("editorHTML")) {
        ReadTime();
        AxiosSaveChapter();
        quillEditor();
      }
      if (document.querySelector(".list-group-item")) {
        Sortables();
      }
      if (document.getElementById("PublicationShowContent")) {
        PublicationShow();
      }
      LazyLoad();
      ShowChapter();
      toggleDrawer();
      targetQuote();
      tippy("[data-tippy-content]");
      DropdownMenu();
    });
    document.addEventListener("turbo:frame-render", () => {
      ShowChapter();
      targetQuote();
      LazyLoad();
      tippy("[data-tippy-content]");
      DropdownMenu();
      document
        .getElementById("mega-menu-icons-dropdown")
        .classList.add("hidden");
      document.getElementById("dropdownInformation").classList.add("hidden");
    });
    document.addEventListener("turbo:visit", () => {
      // fade out the old body
      document.body.classList.add("turbo-loading");
    });
    document.addEventListener("turbo:after-render", (event) => {});
    document.addEventListener("turbo:before-render", (event) => {
      document.getElementById("flowbite").remove();
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
