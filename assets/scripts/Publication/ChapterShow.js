import axios from "axios";
export function ShowChapter() {
  const chapContentTurbo = document.getElementById("chapContentTurbo");
  if (!chapContentTurbo) return;

  //!SECTION — Traitement de tout ce qui concerne la sélection de texte
  //ANCHOR — Traitement de tout ce qui concerne la sélection de texte
  //!SECTION — Traitement de tout ce qui concerne la sélection de texte

  const chapArticle = document.getElementById("chapArticle");
  const tooltip = document.getElementById("tools");
  const highlight = document.querySelectorAll("ul > li.hl");

  const highlightedOptions = document.getElementById("highlighted-options");
  const highlightedSpans = document.querySelectorAll("span.highlighted");
  activeClickTooltipHL(highlightedOptions, highlightedSpans);

  window.addEventListener("click", () => {
    if (!highlightedOptions.classList.contains("hidden")) {
      highlightedOptions.classList.add("hidden");
    }
  });
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
  const commentBAlready = document.querySelectorAll(".commentAlreadyQuote");
  // commentBAlready.forEach(handleClick(() => updateDrawerContent()));
  //*ANCHOR - partage sur les réseaux sociaux
  const twitterB = document.querySelectorAll(".shareTwitter");
  twitterB.forEach(handleClick(() => shareNw("twitter")));

  const fbB = document.querySelectorAll(".shareFb");
  fbB.forEach(handleClick(() => shareNw("facebook")));

  const lkB = document.querySelectorAll(".shareLk");
  lkB.forEach(handleClick(() => shareNw("linkedin")));
  // ! Bouton permettant la suppression de Highlights
  document.getElementById("delete-hl").addEventListener("click", () => {
    deleteHighlight();
  });
  // ! Traitement de la selection (affichage de la tooltip avec toutes les options et récupération de toutes les infos de la sélection)

  let selectedText = "";
  let selectedTextEl = "";
  let selectedTextEnd = "";
  let selectedTextContext = "";
  let selectedTextP = "";

  chapArticle.addEventListener("mouseup", (e) => {
    if (window.getSelection().toString().length > 0) {
      tooltip.classList.remove("hidden");
    } else {
      tooltip.classList.add("hidden");
    }
    const selection = window.getSelection();
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
      tooltip.style.left = rect.left + window.scrollX - 130 + "px";
      tooltip.style.top = rect.top + window.scrollY - 30 + "px";
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

  //!SECTION — Traitement de tout ce qui concerne les commentaires classiques (modification, suppression, style etc.)
  //ANCHOR — Traitement de tout ce qui concerne les commentaires classiques (modification, suppression, style etc.)
  //!SECTION — Traitement de tout ce qui concerne les commentaires classiques (modification, suppression, style etc.)
  // ! Sidebar
  const toggleButton = document.querySelectorAll(".toggleSidebar");
  var sidebar = document.getElementById("sidebar");

  toggleButton.forEach((element) => {
    element.addEventListener("click", () => {
      toggleSidebar(sidebar);
    });
  });
  window.addEventListener("click", () => {
    // On désactive le clic si le click est sur un élément possedant la classe ".toggleSidebar"
    if (
      event.target.classList.contains("toggleSidebar") ||
      event.target.id == "sidebar" ||
      event.target.closest("#sidebar")
    ) {
      return;
    }
    if (!sidebar.classList.contains("hidden")) {
      document.getElementById("insightQuote").classList.add("hidden");
      toggleSidebar(sidebar);
    }
  });

  // ! Section qui permet de cacher la flèche "suivant" ou "précédent" si on est sur le dernier commentaire
  const target = document.getElementById("footer");

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

  // ! Fonction qui modifie le style du dernier commentaire posé
  const textarea = document.getElementById(
    "publication_chapter_comment_content"
  );
  const lastComment = document.getElementById("lastComment");

  if (lastComment) {
    lastComment.classList.add("animate__animated", "animate__flipInX");
    setTimeout(() => {
      lastComment.classList.remove("bg-slate-50", "text-slate-600");
    }, 2000);
  }
  // ! Agrandir le textarea des commentaires à mesure que l'on écrit
  const commentContent = document.getElementById(
    "publication_chapter_comment_content"
  );
  const sendComment = document.getElementById("sendComment");

  if (textarea) {
    textarea.addEventListener("input", () => {
      textarea.style.height = "";
      textarea.style.height = `${textarea.scrollHeight}px`;
    });
  }
  // ! Au clic sur le bouton d'envoi, on efface le formulaire textarea

  sendComment.addEventListener("click", () => {
    setTimeout(() => {
      commentContent.value = "";
      commentContent.style.height = "3.4rem";
    }, 100);
  });

  // ! Fonction qui permet de modifier les commentaires
  const updateButton = document.querySelectorAll(".updateButton");

  updateButton.forEach((button) => {
    button.addEventListener("click", () => {
      const parts = button.id.split("-");
      const result = parts[1];

      const com = document.getElementById(`comShow-${result}`);
      const inner = document.getElementById(`updateCom-${result}`);

      inner.innerHTML = `
      <textarea id='comShow-${result}'>${com.textContent}</textarea>
      <button id='validCom-${result}'>Valider</button>
      <button id='cancelCom-${result}'>Annuler</button>
    `;

      const buttonValid = document.getElementById(`validCom-${result}`);
      const newCom = document.getElementById(`comShow-${result}`);
      const cancelCom = document.getElementById(`cancelCom-${result}`);

      cancelCom.addEventListener("click", () => {
        inner.innerHTML = `<p id='comShow-${result}'>${nl2br(
          com.textContent
        )}</p>`;
      });

      buttonValid.addEventListener("click", () => {
        const data = new FormData();
        const url = "/recit/comment/up";

        data.append("idCom", result);
        data.append("newCom", newCom.value);

        axios
          .post(url, data, {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          })
          .then((response) => {
            newCom.textContent = response.data.comment;
            inner.innerHTML = `<p id='comShow-${result}'>${nl2br(
              newCom.textContent
            )}</p>`;
          });
      });
    });
  });

  // ! Fonction qui permet de mettre en exergue un nouveau commentaire
  if (lastComment) {
    setTimeout(() => {
      lastComment.classList.remove("bg-slate-50", "text-slate-600");
    }, 2000);
  }
  // ! Fonction qui permet de liker un commentaire
  const handleLikeButtonClick = (button, result) => {
    const url = "/recit/comment/like";
    const data = new FormData();
    data.append("idCom", result);

    axios
      .post(url, data, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      })
      .then((response) => {
        if (response.data.code === 201) {
          button.classList.remove("fa-regular", "fa-heart", "animate__jello");
          button.classList.add(
            "fa-solid",
            "fa-heart",
            "text-red-500",
            "animate__heartBeat"
          );
          const nbLikes = document.getElementById(`nbLikes-${result}`);
          nbLikes.innerHTML = Number(nbLikes.innerHTML) + 1;
        } else if (response.data.code === 200) {
          button.classList.remove(
            "fa-solid",
            "fa-heart",
            "text-red-500",
            "animate__heartBeat"
          );
          button.classList.add("fa-regular", "fa-heart", "animate__jello");
          const nbLikes = document.getElementById(`nbLikes-${result}`);
          nbLikes.innerHTML = Number(nbLikes.innerHTML) - 1;
        }
      });
  };

  const likeButtons = document.querySelectorAll(".likeButton");
  likeButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const parts = button.id.split("-");
      const result = parts[1];
      handleLikeButtonClick(button, result);
    });
  });
}
//*!SECTION FONCTIONS
//*!SECTION FONCTIONS
//*!SECTION FONCTIONS
const nl2br = (str = "", isXHTML = true) => {
  const breakTag = isXHTML ? "<br />" : "<br>";
  return str.replace(/(\r\n|\n\r|\r|\n)/g, `$1${breakTag}`);
};
// ! Fonction qui permet d'envoyer une highlight en base de données
const highlightData = (
  tooltip,
  selectedTextP,
  selectedText,
  selectedTextEl,
  selectedTextContext,
  selectedTextEnd,
  color
) => {
  const url = "/recit/chapter/note";
  const data = new FormData();
  data.append("p", selectedTextP);
  data.append("idChapter", document.getElementById("chapId").value);
  data.append("selection", selectedText.replace(/\n/g, "").trimEnd());
  data.append("color", color);
  data.append("contentEl", selectedTextEl.replace(/\n/g, "").trimEnd());
  data.append("context", selectedTextContext);
  data.append("end", selectedTextEnd);
  data.append("type", "highlight");
  axios
    .post(url, data, { headers: { "Content-Type": "multipart/form-data" } })
    .then((response) => {
      tooltip.classList.add("hidden");
      let pr = response.data.p || "0";
      const chapArticle = document.getElementById(`paragraphe-${pr}`);
      showHighlightDom(
        chapArticle,
        response.data.contextSel,
        color,
        response.data.selection,
        response.data.selectionEl,
        response.data.idNote,
        response.data.p
      );
      activeClickTooltipHL();
      // * On stocke l'id de la note dans un input pour pouvoir la récupérer dans le formulaire de commentaire
      document.getElementById("drawerNoteQuote").value =
        response.data.selection;
    });
};
const activeClickTooltipHL = () => {
  const tooltiped = document.getElementById("highlighted-options");
  const highlighted = document.querySelectorAll("span.highlighted");
  highlighted.forEach((element) => {
    element.addEventListener("click", (e) => {
      e.stopPropagation();
      highlightedOptions(element, tooltiped);
      tooltiped.classList.remove("hidden");
    });
  });
};
function highlightedOptions(element, tooltiped) {
  const parts = element.id.split("-");
  const result = parts[1];
  const deleteBtn = document.getElementById("delete-hl");
  deleteBtn.setAttribute("data-note-id", result);
  const selection = window.getSelection();
  const range = selection.getRangeAt(0);
  const rect = range.getBoundingClientRect();
  tooltiped.style.left = `${rect.left + window.scrollX - 80}px`;
  tooltiped.style.top = `${rect.top + window.scrollY - 50}px`;
  const styles = {
    "hl-1": ["bg-emerald-200", "text-emerald-400"],
    "hl-2": ["bg-amber-200", "text-amber-400"],
    "hl-3": ["bg-red-200", "text-red-400"],
    default: ["bg-blue-200", "text-blue-400"],
  };
  deleteBtn.className = "";
  const classes =
    styles[
      element.classList.contains("hl-1")
        ? "hl-1"
        : element.classList.contains("hl-2")
        ? "hl-2"
        : element.classList.contains("hl-3")
        ? "hl-3"
        : "default"
    ];
  deleteBtn.classList.add(...classes);
}
// ! fonction de partage sur les réseaux
function shareNw(nw) {
  const url = window.location.href;
  const title = document.getElementById("chapTitle").innerText;
  let urlShare;
  switch (nw) {
    case "twitter":
      urlShare = `https://twitter.com/share?url=${url}&text=${title}`;
      break;
    case "facebook":
      urlShare = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
      break;
    case "linkedin":
      urlShare = `https://www.linkedin.com/shareArticle/?mini=true&url=${url}`;
      break;
    default:
      return;
  }
  window.open(urlShare, "_blank");
}
// ! Fonction d'affichage sur le dom des highlights
function showHighlightDom(
  chapArticle,
  contextSel,
  color,
  selection,
  selectionEl,
  idNote,
  p
) {
  if (selectionEl.includes("</p>")) {
    //!SECTION
    const bigArticle = document.getElementById("chapArticle");
    var regex = '<p id="paragraphe-' + p + '"(.*?)>(.*?)</p>';
    var match = bigArticle.innerHTML.match(regex);
    if (selectionEl.indexOf("</p>") !== -1) {
      if (selectionEl.indexOf("</p>")) {
        var selectionEl2 = selectionEl.substr(0, selectionEl.indexOf("</p>"));
        var matches = selectionEl2.match(/<[^>]*>|[^<]+/g);
        var n = 0;
        for (var key in matches) {
          if (matches[key].indexOf("<") === -1) {
            matches[key] =
              "<span id='hl-" +
              idNote +
              "' class='hlId-" +
              idNote +
              " highlighted hl-" +
              color +
              " hlMultiple'>" +
              matches[key] +
              "</span>";
          }
          n++;
        }
        // Join
        var selectionEl3 = matches.join("");
        bigArticle.innerHTML = bigArticle.innerHTML.replace(
          selectionEl2,
          "<span id='hl-" +
            idNote +
            "' class='highlighted hl-" +
            color +
            "'>" +
            selectionEl3 +
            "</span>"
        );
      } else {
        bigArticle.innerHTML = bigArticle.innerHTML.replace(
          selectionEl,
          "<span id='hl-" +
            idNote +
            "' class='highlighted hl-" +
            color +
            "'>" +
            selectionEl +
            "</span>"
        );
      }
    }
    bigArticle.innerHTML = bigArticle.innerHTML.replace(
      selectionEl,
      `<span id='hl-${idNote}' class='highlighted hl-${color}'>${selectionEl}</span>`
    );
  } else {
    let contextAndSel = contextSel + selection;
    let newHTML = chapArticle.innerHTML.replace(
      contextAndSel,
      `${contextSel}<span id='hl-${idNote}' class='highlighted hl-${color}'>${selection}</span>`
    );
    chapArticle.innerHTML = newHTML;
    if (chapArticle.innerHTML === newHTML) {
      newHTML = chapArticle.innerHTML.replace(
        selectionEl,
        `<span id='hl-${idNote}' class='highlighted hl-${color}'>${selection}</span>`
      );
    }
    if (chapArticle.innerHTML === newHTML) {
      newHTML = chapArticle.innerHTML.replace(
        selectionEl,
        `<span id='hl-${idNote}' class='highlighted hl-${color}'>${selection}</span>`
      );
    }
  }
}
// ! Fonction qui permet de supprimer les highlights en base de données
async function deleteHighlight() {
  const hlId = document
    .getElementById("delete-hl")
    .getAttribute("data-note-id");
  const url = "/recit/chapter/delnote";
  const data = new FormData();
  data.append("idNote", hlId);

  try {
    await axios.post(url, data, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
  } catch (error) {
    console.error(error);
  }

  // * Remove the highlight from the DOM
  const el = document.getElementById("hl-" + hlId);
  const parent = el.parentNode;
  while (el.firstChild) parent.insertBefore(el.firstChild, el);
  parent.removeChild(el);
  if (document.getElementById("hl-" + hlId)) {
    const el2 = document.querySelectorAll(".hlId-" + hlId);
    el2.forEach((element) => {
      let parent = element.parentNode;
      while (element.firstChild)
        parent.insertBefore(element.firstChild, element);
      parent.removeChild(element);
    });
  }
}
function updateDrawerContent(
  selectedText,
  selectedTextP,
  selectedTextEl,
  selectedTextContext,
  tooltip
) {
  let insightQuote = document.getElementById("insightQuote");
  insightQuote.innerHTML = "« " + selectedText + " »";
  insightQuote.classList.remove("hidden");
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
  highlightData(
    tooltip,
    selectedTextP,
    selectedText,
    selectedTextEl,
    selectedTextContext,
    "",
    "4"
  );
}
function toggleSidebar(sidebar) {
  sidebar.classList.toggle("hidden");
  sidebar.classList.add("animate__animated", "animate__slideOutRight");
  if (sidebar.classList.contains("animate__slideOutRight")) {
    sidebar.classList.remove("animate__slideOutRight");
    sidebar.classList.add("animate__slideInRight");
  } else {
    sidebar.classList.add("animate__slideOutRight");
    sidebar.classList.remove("animate__slideInRight");
  }
  sidebar.classList.toggle("-translate-x-full");
}
ShowChapter();
