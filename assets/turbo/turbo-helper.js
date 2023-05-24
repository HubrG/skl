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
import { addKeyword } from "../scripts/Publication/AddKeyword";
import { Comment } from "../scripts/Publication/Comment";
import { AxiosSavePublication } from "../scripts/Publication/Publication";
import { GlideModule } from "../scripts/Glide";
import { darkMode } from "../scripts/Darkmode";
import { Tabs } from "../scripts/Tabs";
import { ReadTime } from "../scripts/Publication/ChapterStats";
import { quillEditor } from "../scripts/Quill.js";
import { Sortables } from "../scripts/Publication/Sortable";
import { Search } from "../scripts/Publication/Search";
import { LazyLoad } from "../scripts/LazyLoad";
import { TippyC } from "../scripts/Tippy";
import { PublicationShow } from "../scripts/Publication/PublicationShow";
import { User } from "../scripts/User/User";
import { Dropdown } from "../scripts/Dropdown";
import { Assign } from "../scripts/Assign";
import { Dropdown as FlowbiteDropdown } from "flowbite";
import { Notification } from "../scripts/Notification";
import { axiosSaveChapter } from "../scripts/Publication/Chapter";
import { Charts } from "../scripts/Charts";
import { NotyDisplay } from "../scripts/Noty";
import { PublicationShowOne } from "../scripts/Publication/PublicationShowOne";
import { Inbox } from "../scripts/Inbox/Inbox";
import MicroModal from "micromodal"; // es6 module
import { Navbar } from "../scripts/Navbar";
import { Annotation } from "../scripts/Publication/Annotations";
import { searchPredictionKw } from "../scripts/Publication/SearchPredictionKw";
import { ForumTopicRead } from "../scripts/Forum/ForumTopicRead";

import { ShowChapter, DropdownMenu } from "../scripts/Publication/ChapterShow";

const TurboHelper = class {
  constructor() {
    // * Turbo before-cache sert à faire des actions avant le cache
    document.addEventListener("turbo:before-cache", () => {
      DropdownMenu();
    });
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
      User();
      ShowChapter();
      darkMode();
      Tabs();
      addKeyword();
      searchPredictionKw();
      Dropdown();
      Assign();
    });
    document.addEventListener("turbo:load", () => {
      AxiosSavePublication();
      Charts();
      Comment();
      Dropdown();
      DropdownMenu();
      GlideModule();
      LazyLoad();
      MicroModal.init();
      Navbar();
      PublicationShow();
      PublicationShowOne();
      quillEditor();
      ReadTime();
      Search();
      Sortables();
      TippyC();
      axiosSaveChapter();
      ForumTopicRead();
      Inbox();
      Notification();
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
    });

    document.addEventListener("turbo:frame-render", (event) => {
      Assign();
      Comment();
      DropdownMenu();
      Inbox();
      ForumTopicRead();
    });
    document.addEventListener("turbo:frame-load", (event) => {});
    // * Turbo Visit sert à faire des actions avant le chargement de la page
    document.addEventListener("turbo:visit", () => {
      document.body.classList.add("turbo-loading");
    });
    // * Turbo Before Render sert à faire des actions après le chargement de la page
    document.addEventListener("turbo:after-render", (event) => {});
    // * Turbo Before Render sert à faire des actions avant le chargement de la page
    document.addEventListener("turbo:submit-end", (event) => {});

    document.addEventListener("turbo:before-render", (event) => {
      // Récupérer tous les dropdowns de Flowbite
      const dropdowns = document.querySelectorAll(".navbar-dropdown");

      // Appliquer la classe 'hidden' pour masquer les dropdowns
      dropdowns.forEach((dropdown) => {
        dropdown.classList.add("hidden");
      });
    });
  }

  isPreviewRendered() {
    return document.documentElement.hasAttribute("data-turbo-preview");
  }
};
export default new TurboHelper();
