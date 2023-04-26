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
import Glide from "@glidejs/glide";
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
      console.log("before cache");
      DropdownMenu();
      Dropdown();
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
      ShowChapter();
      toggleDrawer();
      targetQuote();
      darkMode();
    });
    document.addEventListener("turbo:load", () => {
      MicroModal.init();
      DropdownMenu();
      Dropdown();
      Charts();
      LazyLoad();
      TippyC();
      Comment();
      Navbar();
      PublicationShowOne();
      User();
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
      if (document.location.href.includes("#first")) {
        document.getElementById("itemsChap2").classList.add("animate__swing");
        setTimeout(() => {
          document
            .getElementById("itemsChap2")
            .classList.remove("animate__swing");
          location.hash = "";
        }, 2500);
      }
      if (document.querySelector(".glide-top")) {
        var glideMarket = new Glide(".glide-market", {
          type: "slider",
          autoplay: false,
          hoverpause: true,
          perView: 1,
          breakpoints: {
            1024: {
              perView: 1,
            },
            600: {
              perView: 1,
            },
          },
        });
        var glideTop = new Glide(".glide-top", {
          type: "slider",
          autoplay: 5000,
          hoverpause: true,
          perView: 1,
          keyboard: false,
          breakpoints: {
            1024: {
              perView: 1,
            },
            600: {
              perView: 1,
            },
          },
        });
        glideMarket.mount();
        glideTop.mount();
      }
    });
    document.addEventListener("turbo:frame-render", () => {
      console.log("frame render");
      Comment();
      DropdownMenu();
      Dropdown();
    });
    // * Turbo Visit sert à faire des actions avant le chargement de la page
    document.addEventListener("turbo:visit", () => {
      document.body.classList.add("turbo-loading");
      console.log("visit");
    });
    // * Turbo Before Render sert à faire des actions après le chargement de la page
    document.addEventListener("turbo:after-render", (event) => {
      console.log("after render");
    });
    // * Turbo Before Render sert à faire des actions avant le chargement de la page
    document.addEventListener("turbo:submit-end", (event) => {
      console.log("submit end");
    });

    document.addEventListener("turbo:before-render", (event) => {
      console.log("before render");
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
