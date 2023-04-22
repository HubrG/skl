import { addKeyword } from "../scripts/Publication/AddKeyword";
import { Comment } from "../scripts/Publication/Comment";
import { axiosSaveChapter } from "../scripts/Publication/Chapter";
import { AxiosSavePublication } from "../scripts/Publication/Publication";
import { darkMode } from "../scripts/Darkmode";
import { ReadTime } from "../scripts/Publication/ChapterStats";
import { quillEditor } from "../scripts/Quill.js";
import { Sortables } from "../scripts/Publication/Sortable";
import { LazyLoad } from "../scripts/LazyLoad";
import { TippyC } from "../scripts/Tippy";
import { PublicationShow } from "../scripts/Publication/PublicationShow";
import { User } from "../scripts/User/User";
import { Dropdown } from "../scripts/Dropdown";
import { Charts } from "../scripts/Charts";
import { NotyDisplay } from "../scripts/Noty";
import { PublicationShowOne } from "../scripts/Publication/PublicationShowOne";

import MicroModal from "micromodal"; // es6 module
MicroModal.init();
// ! Flashes
if (document.getElementById("flashbag-success")) {
  if (
    document.getElementById("flashbag-success").getAttribute("data-count") == 1
  ) {
    NotyDisplay(
      document.getElementById("flashbag-success").innerHTML,
      document.getElementById("flashbag-success").getAttribute("data-status"),
      2500
    );
    document.getElementById("flashbag-success").setAttribute("data-count", 2);
  }
}
//!
import { Navbar } from "../scripts/Navbar";
import {
  ShowChapter,
  toggleDrawer,
  targetQuote,
  DropdownMenu,
} from "../scripts/Publication/ChapterShow";
import axios from "axios";

const TurboHelper = class {
  constructor() {
    // * Turbo before-cache sert à faire des actions avant le cache
    document.addEventListener("turbo:before-cache", () => {
      darkMode();
    });
    if (document.querySelector(".list-group-item")) {
      Sortables();
    }
    // * Turbo Frame Missing sert à recharger la page si le frame est manquant
    document.addEventListener("turbo:frame-missing", (event) => {
      document.querySelectorAll(".turbo-frame-error").forEach((element) => {
        element.innerHTML = "Redirection en cours...";
      });
      window.top.location.reload();
    });
    // * Turbo Render sert à faire des actions après le chargement de la page
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
      darkMode();
      PublicationShowOne();
      // ! Flashes
      if (document.getElementById("flashbag-success")) {
        if (
          document
            .getElementById("flashbag-success")
            .getAttribute("data-count") == 1
        ) {
          NotyDisplay(
            document.getElementById("flashbag-success").innerHTML,
            document
              .getElementById("flashbag-success")
              .getAttribute("data-status"),
            2500
          );
          document
            .getElementById("flashbag-success")
            .setAttribute("data-count", 2);
        }
      }
      //!
      if (document.getElementById("hideIdPub")) {
        addKeyword();
        AxiosSavePublication();
      }
      if (document.getElementById("editorHTML")) {
        ReadTime();
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
      Charts();
      axiosSaveChapter();
      Navbar();
      Comment();
      targetQuote();
      TippyC();
      Dropdown();
      DropdownMenu();
      MicroModal.init();
      User();
      if (document.location.href.includes("#first")) {
        document.getElementById("itemsChap2").classList.add("animate__swing");
        setTimeout(() => {
          document
            .getElementById("itemsChap2")
            .classList.remove("animate__swing");
          location.hash = "";
        }, 2500);
      }
    });
    // * Turbo Frame Render sert à faire des actions après le chargement d'un frame
    document.addEventListener("turbo:frame-render", () => {
      MicroModal.init();
      if (document.getElementById("editorHTML")) {
        ReadTime();
      }
      quillEditor();
      // ! Flashes
      if (document.getElementById("flashbag-success")) {
        if (
          document
            .getElementById("flashbag-success")
            .getAttribute("data-count") == 1
        ) {
          NotyDisplay(
            document.getElementById("flashbag-success").innerHTML,
            document
              .getElementById("flashbag-success")
              .getAttribute("data-status"),
            2500
          );
          document
            .getElementById("flashbag-success")
            .setAttribute("data-count", 2);
        }
      }
      //!
      ShowChapter();
      targetQuote();
      LazyLoad();
      TippyC();
      Navbar();
      Comment();
      DropdownMenu();
      darkMode();
      PublicationShowOne();
      Dropdown();
      User();
      if (document.querySelector(".list-group-item")) {
        Sortables();
      }
      document
        .getElementById("mega-menu-icons-dropdown")
        .classList.add("hidden");
      if (document.getElementById("dropdownInformation")) {
        document.getElementById("dropdownInformation").classList.add("hidden");
      }
    });
    // * Turbo Visit sert à faire des actions avant le chargement de la page
    document.addEventListener("turbo:visit", () => {
      // fade out the old body
      // document.body.classList.add("turbo-loading");
      quillEditor();
      darkMode();
    });
    // * Turbo Before Render sert à faire des actions après le chargement de la page
    document.addEventListener("turbo:after-render", (event) => {
      darkMode();
      quillEditor();
    });
    // * Turbo Before Render sert à faire des actions avant le chargement de la page
    document.addEventListener("turbo:submit-end", (event) => {
      // On recharge la page
      console.log("submit-start");
      console.log(event.detail.fetchResponse);
    });

    document.addEventListener("turbo:before-render", (event) => {
      darkMode();
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
  }

  isPreviewRendered() {
    return document.documentElement.hasAttribute("data-turbo-preview");
  }
};
export default new TurboHelper();
