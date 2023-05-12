import axios from "axios";
import { NotyDisplay } from "../Noty";
export function ShowChapter() {
  const chapContentTurbo = document.getElementById("chapContentTurbo");
  const chapAvalaible = document.getElementById("chapId");
  if (!chapContentTurbo && !chapAvalaible) return;

  // ! SECTION - Traitement de la position du sticky
  window.addEventListener("scroll", function () {
    let nav = document.querySelector("nav");
    let stickyDiv = document.querySelector("#titleChapter");
    let sidebarScroll = document.getElementById("sidebar");
    // if (stickyDiv) {
    //   if (nav.getBoundingClientRect().top < 0) {
    //     stickyDiv.style.position = "sticky";
    //     stickyDiv.style.top = "0";
    //     if (sidebarScroll) {
    //       sidebarScroll.style.paddingTop = "1rem";
    //     }
    //   } else {
    //     stickyDiv.style.position = "sticky";
    //     stickyDiv.style.top = "3.5rem";
    //     if (sidebarScroll) {
    //       sidebarScroll.style.paddingTop = "5rem";
    //     }
    //   }
    // }
  });

  if (document.getElementById("insightQuote")) {
    // ! On vérifie que l'utilisateur est connecté
    const copyLink = document.getElementById("copyLink");
    copyLink.addEventListener("click", (e) => {
      var currentPageUrl = window.location.href;
      navigator.clipboard.writeText(currentPageUrl);
      NotyDisplay(
        '<i class="fa-duotone fa-copy"></i> Lien copié dans votre presse-papier',
        "info",
        2000
      );
    });
    //!SECTION — Click sur le bouton "like" du chapitre
    const likeChapter = document.getElementById("likeThisChapter");
    if (likeChapter) {
      likeChapter.addEventListener("click", () => {
        const likeChapterId = likeChapter.getAttribute("data-id");
        likeChapterData(likeChapterId);
      });
    }
    //!SECTION — Click sur le bouton "add bookmark" du chapitre
    const bmChapter = document.getElementById("bmThisChapter");
    if (bmChapter) {
      bmChapter.addEventListener("click", () => {
        const bmChapterId = bmChapter.getAttribute("data-id");
        bmChapterData(bmChapterId);
      });
    }
    //!SECTION — Traitement de tout ce qui concerne la sélection de texte
    //ANCHOR — Traitement de tout ce qui concerne la sélection de texte
    //!SECTION — Traitement de tout ce qui concerne la sélection de texte

    const chapArticle = document.getElementById("chapArticle");
    const tooltip = document.getElementById("tools");
    const highlight = document.querySelectorAll("ul > li.hl");

    const handleClick = (callback) => (element) =>
      element.addEventListener("click", callback);

    // ! modifier le contenu des inputs hidden du drawer lorsqu'on clique sur le bouton "commenter"
    const commentB = document.querySelectorAll(".commentQuote");
    commentB.forEach(
      handleClick(() =>
        updateDrawerContent(
          selectedText,
          selectedTextP,
          selectedTextEl,
          selectedTextContext,
          tooltip
        )
      )
    );

    // ! Traitement de la selection (affichage de la tooltip avec toutes les options et récupération de toutes les infos de la sélection)

    let selectedText = "";
    let selectedTextEl = "";
    let selectedTextEnd = "";
    let selectedTextContext = "";
    let selectedTextP = "";

    //*ANCHOR - partage sur les réseaux sociaux
    // const twitterB = document.querySelectorAll(".shareTwitter");
    // twitterB.forEach(handleClick(() => shareNw("twitter")));

    // const fbB = document.querySelectorAll(".shareFb");
    // fbB.forEach(handleClick(() => shareNw("facebook")));

    // const lkB = document.querySelectorAll(".shareLk");
    // lkB.forEach(handleClick(() => shareNw("linkedin")));
    // //*

    chapArticle.addEventListener("mouseup", (e) => {
      // if (window.getSelection().toString().length > 0) {
      //   tooltip.classList.remove("hidden");
      // } else {
      //   tooltip.classList.add("hidden");
      // }
      const selection = window.getSelection();
      let selectionShare = "";
      selectionShare = selection.toString();
      if (!selection.toString()) {
        return;
      }

      const startNode = selection.anchorNode;
      const startOffset = selection.anchorOffset;
      const endNode = selection.focusNode;
      const endOffset = selection.focusOffset;

      let preceedingText = "";
      if (startNode === endNode) {
        const start = Math.min(startOffset, endOffset);
        preceedingText = startNode.textContent.substring(start - 7, start);
      }
      selectedTextContext = preceedingText;

      selectedText = selection.toString();

      const selected = selection;
      if (selected.rangeCount) {
        const range = selected.getRangeAt(0);
        const div = document.createElement("div");
        div.appendChild(range.cloneContents());
        selectedTextEl = div.innerHTML;
      }

      selectedTextEnd = selection.getRangeAt(0).endOffset;
      const parts = selection.anchorNode.parentNode.closest("p");
      selectedTextP = parts.id.split("-")[1];

      if (selectedText) {
        const range = selection.getRangeAt(0);
        const rect = range.getBoundingClientRect();
        // tooltip.style.left = rect.left + window.scrollX - 130 + "px";
        // tooltip.style.top = rect.top + window.scrollY - 30 + "px";
      } else {
        tooltip.style.display = "none";
        selectedText = "";
        selectedTextP = "";
        selectedTextEl = "";
      }
    });

    highlight.forEach((element) => {
      const color = element.getAttribute("data-color");
      element.addEventListener("click", () => {
        highlightData(
          tooltip,
          selectedTextP,
          selectedText,
          selectedTextEl,
          selectedTextContext,
          selectedTextEnd,
          color
        );
      });
    });
  }
  const deleteQuote = document.getElementById("deleteQuote");
  if (deleteQuote) {
    deleteQuote.addEventListener("click", () => {
      document.getElementById("insightQuote").innerHTML = "";
      document.getElementById("drawerNoteQuote").value = "";
      deleteQuote.classList.add("hidden");
      const quoteSection = document.getElementById("quoteSection");
      quoteSection.classList.add("hidden");
    });
  }
  // ! Section qui permet de cacher la flèche "suivant" ou "précédent" si on est sur le dernier commentaire
  const target = document.getElementById("comment-section");

  const hideArrow = (id, el) => {
    if (el) {
      const observer = new IntersectionObserver((entries) => {
        entries[0].isIntersecting
          ? (el.style.display = "none")
          : (el.style.display = "flex");
      });
      observer.observe(target);
      if (target.getBoundingClientRect().bottom < window.innerHeight) {
        el.style.display = "none";
      }
    }
  };

  hideArrow("arrowNext", document.getElementById("arrowNext"));
  hideArrow("arrowPrevious", document.getElementById("arrowPrevious"));

  //!SECTION — Traitement de tout ce qui concerne les commentaires classiques (modification, suppression, style etc.)
  //ANCHOR — Traitement de tout ce qui concerne les commentaires classiques (modification, suppression, style etc.)
  //!SECTION — Traitement de tout ce qui concerne les commentaires classiques (modification, suppression, style etc.)
}
//*!SECTION FONCTIONS
//*!SECTION FONCTIONS
//*!SECTION FONCTIONS
const nl2br = (str = "", isXHTML = true) => {
  const breakTag = isXHTML ? "<br />" : "<br>";
  return str.replace(/(\r\n|\n\r|\r|\n)/g, `$1${breakTag}`);
};

function updateDrawerContent(
  selectedText,
  selectedTextP,
  selectedTextEl,
  selectedTextContext,
  tooltip
) {
  let insightQuote = document.getElementById("insightQuote");
  insightQuote.innerHTML = "« " + selectedText + " »";
  document.getElementById("drawerNoteQuote").value = selectedText.trim();
  insightQuote.classList.remove("hidden");
  const deleteQuote = document.getElementById("deleteQuote");
  deleteQuote.classList.remove("hidden");

  // Destructuring assignment to extract the elements from the DOM
  let {
    drawerSelectedText,
    drawerSelectedTextP,
    drawerSelectedTextEl,
    drawerSelectedTextContext,
  } = {
    drawerSelectedText: document.getElementById("drawerSelectedText"),
    drawerSelectedTextP: document.getElementById("drawerSelectedTextP"),
    drawerSelectedTextEl: document.getElementById("drawerSelectedTextEl"),
    drawerSelectedTextContext: document.getElementById(
      "drawerSelectedTextContext"
    ),
  };
  // Updating the value of the elements with the passed parameters
  drawerSelectedTextP.value = selectedTextP;
  drawerSelectedText.value = selectedText;
  drawerSelectedTextEl.value = selectedTextEl;
  drawerSelectedTextContext.value = selectedTextContext;
  // !
}

export function DropdownMenu() {
  if (!document.querySelector(".dropdown-button")) return;
  const button = document.querySelectorAll(".dropdown-button");
  const dropdownmenu = document.querySelectorAll(".dropdown-content");
  //
  button.forEach((el) => {
    el.addEventListener("click", function () {
      // On trouve l'id de el et on le découpé via le "-"
      let elId = el.id.split("-")[1];
      let content = document.getElementById("ddm-" + elId);
      if (content) {
        content.classList.toggle("show");
      }
    });
  });
  window.onclick = function () {
    if (!event.target.classList.contains("dropdown-button")) {
      // On ferme toutes content
      dropdownmenu.forEach((el) => {
        el.classList.remove("show");
      });
    }
  };
}
function likeChapterData(likeChapterId) {
  const nbrLike = document.getElementById("nbrLike");
  const likeChapter = document.getElementById("likeChapterThumb");
  const url = "/recit/chapter/like";
  const data = new FormData();
  //
  //
  data.append("idChapter", likeChapterId);
  axios.post(url, data).then((response) => {
    if (response.data.resp) {
      likeChapter.classList.remove("fa-regular");
      likeChapter.classList.add("fa-duotone", "text-rose-400");
      nbrLike.innerHTML = response.data.nbrLike;
    } else {
      likeChapter.classList.add("fa-regular");
      likeChapter.classList.remove("fa-duotone", "text-rose-400");
      nbrLike.innerHTML = response.data.nbrLike;
    }
  });
}
function bmChapterData(bmChapterId) {
  const nbrBm = document.getElementById("nbrBm");
  const bmChapter = document.getElementById("bmChapter");
  const url = "/recit/chapter/bm";
  const data = new FormData();
  //
  //
  data.append("idChapter", bmChapterId);
  axios.post(url, data).then((response) => {
    if (response.data.resp) {
      bmChapter.classList.remove("fa-regular");
      bmChapter.classList.add("fa-duotone", "text-purple-400");
      nbrBm.innerHTML = response.data.nbrBm;
      NotyDisplay(
        '<i class="fa-sharp fa-regular fa-circle-check"></i> &nbsp;&nbsp;Feuille ajoutée à votre collection',
        "success",
        2500
      );
    } else {
      bmChapter.classList.add("fa-regular");
      bmChapter.classList.remove("fa-duotone", "text-purple-400");
      nbrBm.innerHTML = response.data.nbrBm;
      if (response.data.message != "Non autorisé.") {
        NotyDisplay(
          '<i class="fa-sharp fa-regular fa-circle-check"></i> &nbsp;&nbsp;Feuille retirée de votre collection',
          "info",
          2500
        );
      }
    }
  });
}
ShowChapter();
