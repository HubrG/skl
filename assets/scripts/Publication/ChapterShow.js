import axios from "axios";
export function ShowChapter() {
  if (document.getElementById("chapContentTurbo")) {
    let tooltiped = document.getElementById("highlighted-options");
    let highlighted = document.querySelectorAll("span.highlighted");
    activeClickTooltipHL(tooltiped, highlighted);
    window.addEventListener("click", function () {
      if (!tooltiped.classList.contains("hidden")) {
        tooltiped.classList.add("hidden");
      }
    });
    // ! commenter quote
    let commentB = document.querySelectorAll(".commentQuote");
    commentB.forEach((element) => {
      element.addEventListener("click", () => {
        commentQuote(
          selectedText,
          selectedTextP,
          selectedTextEl,
          selectedTextContext
        );
      });
    });
    // ! partage twitter
    let twitterB = document.querySelectorAll(".shareTwitter");
    twitterB.forEach((element) => {
      element.addEventListener("click", () => {
        shareNw("twitter");
      });
    });
    // ! partage facebook
    let fbB = document.querySelectorAll(".shareFb");
    fbB.forEach((element) => {
      element.addEventListener("click", () => {
        shareNw("facebook");
      });
    });
    // ! partage linkedin
    let lkB = document.querySelectorAll(".shareLk");
    lkB.forEach((element) => {
      element.addEventListener("click", () => {
        shareNw("linkedin");
      });
    });

    // ! Récupération des notes (type 0) du chapitre de l'utilisateur connecté (avec Tooltip des options de surlignage)
    // AxiosGetHighlight();
    // ! Bouton permettant la suppression de Highlights
    document.getElementById("delete-hl").addEventListener("click", () => {
      AxiosDeleteHighlight();
    });
    // ! Sélection de texte
    document
      .getElementById("chapArticle")
      .addEventListener("mouseup", function (e) {
        window.addEventListener("click", function () {
          if (window.getSelection().toString().length > 0) {
            tooltip.classList.remove("hidden");
          }
        });
        if (window.getSelection().toString()) {
          var selection = window.getSelection();
          var startNode = selection.anchorNode;
          var startOffset = selection.anchorOffset;
          var endNode = selection.focusNode;
          var endOffset = selection.focusOffset;

          if (startNode === endNode) {
            // La sélection se trouve dans un seul noeud de texte
            var start = Math.min(startOffset, endOffset);
            var preceedingText = startNode.textContent.substring(
              start - 5,
              start
            );
          }
          selectedTextContext = preceedingText;
          //
          //
          selectedText = window.getSelection().toString();
          // Reprise du texte avec les balises
          var selectedd = window.getSelection();
          if (selectedd.rangeCount) {
            var range = selectedd.getRangeAt(0);
            var div = document.createElement("div");
            div.appendChild(range.cloneContents());
            selectedTextEl = div.innerHTML;
          }
          //
          //
          selectedTextEnd = window.getSelection().getRangeAt(0).endOffset;
          let parts = window.getSelection().anchorNode.parentNode.closest("p");
          parts = parts.id.split("-");
          selectedTextP = parts[1];
          //
          if (selectedText) {
            let selection = window.getSelection();
            let range = selection.getRangeAt(0);
            let rect = range.getBoundingClientRect();
            tooltip.style.left = rect.left + window.scrollX - 130 + "px";
            tooltip.style.top = rect.top + window.scrollY - 30 + "px";
          } else {
            tooltip.style.display = "none";
            selectedText = "";
            selectedTextP = "";
            selectedTextEl = "";
          }
        }
      });
    let selectedText = "";
    let selectedTextEl = "";
    let selectedTextEnd = "";
    let selectedTextContext = "";
    let selectedTextP = "";
    const tooltip = document.getElementById("tools");
    let highlight = document.querySelectorAll("ul>li.hl");
    highlight.forEach((element) => {
      var color = element.getAttribute("data-color");
      element.addEventListener("click", function () {
        //!SECTION
        axiosHighlight(
          tooltip,
          selectedTextP,
          selectedText,
          selectedTextEl,
          selectedTextContext,
          selectedTextEnd,
          element.getAttribute("data-color")
        );
      });
    });
    // * Click sur la fenêtre (pour fermer les popups)
    window.addEventListener("click", function () {
      if (!tooltip.classList.contains("hidden")) {
        tooltip.classList.toggle("hidden");
      }
    });

    // !
    // ! Fonction qui cache les flèches de navigation si on est au début ou à la fin du chapitre
    const target = document.getElementById("commentFrame");
    if (document.getElementById("arrowNext")) {
      const elN = document.getElementById("arrowNext");
      const observer = new IntersectionObserver(function (entries) {
        if (entries[0].isIntersecting) {
          elN.style.display = "none";
        } else {
          elN.style.display = "flex";
        }
      });
      observer.observe(target);
      if (target.getBoundingClientRect().bottom < window.innerHeight) {
        elN.style.display = "none";
      }
    }
    if (document.getElementById("arrowPrevious")) {
      const elP = document.getElementById("arrowPrevious");
      const observer = new IntersectionObserver(function (entries) {
        if (entries[0].isIntersecting) {
          elP.style.display = "none";
        } else {
          elP.style.display = "flex";
        }
      });
      observer.observe(target);
      if (target.getBoundingClientRect().bottom < window.innerHeight) {
        elP.style.display = "none";
      }
    }
    // ! Fonction qui modifie le style du dernier commentaire posé
    if (document.getElementById("lastComment")) {
      var lastComment = document.getElementById("lastComment");
      lastComment.classList.add("animate__animated", "animate__flipInX");
      setTimeout(function () {
        lastComment.classList.remove("bg-slate-50");
        lastComment.classList.remove("text-slate-600");
      }, 2000);
    }
    // ! Fonction qui agrandit le textarea des commentaires
    if (document.getElementById("publication_chapter_comment_content")) {
      const textarea = document.getElementById(
        "publication_chapter_comment_content"
      );
      textarea.addEventListener("input", function () {
        this.style.height = "";
        this.style.height = this.scrollHeight + "px";
      });
    }
    var commentContent = document.getElementById(
      "publication_chapter_comment_content"
    );
    var sendComment = document.getElementById("sendComment");
    sendComment.addEventListener("click", function () {
      setTimeout(function () {
        commentContent.value = "";
        commentContent.style.height = "3.4rem";
      }, 100);
    });
    // ! Fonction qui permet de modifier les commentaires
    var updateButton = document.querySelectorAll(".updateButton");
    //
    updateButton.forEach((button) => {
      button.addEventListener("click", function () {
        let parts = button.id.split("-");
        let result = parts[1];
        //
        let com = document.getElementById("comShow-" + result);
        let inner = document.getElementById("updateCom-" + result);
        inner.innerHTML =
          "<textarea id='comShow-" +
          result +
          "'>" +
          com.textContent +
          "</textarea><button id='validCom-" +
          result +
          "'>Valider</button><button id='cancelCom-" +
          result +
          "'>Annuler</button>";
        // +
        // * On traite la modification du commentaire en appuyant sur le bouton
        var buttonValid = document.getElementById("validCom-" + result);
        var newCom = document.getElementById("comShow-" + result);
        var cancelCom = document.getElementById("cancelCom-" + result);
        cancelCom.addEventListener("click", function () {
          inner.innerHTML =
            "<p id='comShow-" + result + "'>" + nl2br(com.textContent) + "</p>";
        });
        buttonValid.addEventListener("click", function () {
          // On envoie la requête
          let data = new FormData();
          let url = "/recit/comment/up";
          data.append("idCom", result);
          data.append("newCom", newCom.value);
          axios
            .post(url, data, {
              headers: {
                "Content-Type": "multipart/form-data",
              },
            })
            .then(function (response) {
              newCom = response.data.comment;
              // On affiche le nouveau commentaire
              inner.innerHTML =
                "<p id='comShow-" + result + "'>" + nl2br(newCom) + "</p>";
            });
        });
      });
    });
    // ! Fonction qui permet de mettre en exergue un nouveau commentaire
    if (document.getElementById("lastComment")) {
      var lastComment = document.getElementById("lastComment");
      setTimeout(function () {
        lastComment.classList.remove("bg-slate-50");
        lastComment.classList.remove("text-slate-600");
      }, 2000);
    }
    // ! Fonction qui permet de liker un commentaire
    var likeButton = document.querySelectorAll(".likeButton");
    likeButton.forEach((button) => {
      button.addEventListener("click", function () {
        let parts = button.id.split("-");
        let result = parts[1];
        let url = "/recit/comment/like";
        let data = new FormData();
        data.append("idCom", result);
        axios
          .post(url, data, {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          })
          .then(function (response) {
            // * Si la réponse est en code 201, on change la couleur du bouton en "liked" et on met à jour le nombre de likes
            if (response.data.code == 201) {
              button.classList.remove(
                "fa-regular",
                "fa-heart",
                "animate__jello"
              );
              button.classList.add(
                "fa-solid",
                "fa-heart",
                "text-red-500",
                "animate__heartBeat"
              );
              var nbLikes = document.getElementById("nbLikes-" + result);
              nbLikes.innerHTML = Number(nbLikes.innerHTML) + 1;
            } else if (response.data.code == 200) {
              button.classList.remove(
                "fa-solid",
                "fa-heart",
                "text-red-500",
                "animate__heartBeat"
              );
              button.classList.add("fa-regular", "fa-heart", "animate__jello");
              var nbLikes = document.getElementById("nbLikes-" + result);
              nbLikes.innerHTML = Number(nbLikes.innerHTML) - 1;
            }
          });
      });
    });
  }
}
function nl2br(str, is_xhtml) {
  if (typeof str === "undefined" || str === null) {
    return "";
  }
  var breakTag =
    is_xhtml || typeof is_xhtml === "undefined" ? "<br />" : "<br>";
  return (str + "").replace(
    /([^>\r\n]?)(\r\n|\n\r|\r|\n)/g,
    "$1" + breakTag + "$2"
  );
}
// ! Fonction qui permet d'envoyer une highlight en base de données
function axiosHighlight(
  tooltip,
  selectedTextP,
  selectedText,
  selectedTextEl,
  selectedTextContext,
  selectedTextEnd,
  color
) {
  let url = "/recit/chapter/note";
  let data = new FormData();
  //
  data.append("p", selectedTextP);
  data.append("idChapter", document.getElementById("chapId").value);
  data.append("selection", selectedText.replace(/\n/g, "").trimEnd());
  data.append("color", color);
  data.append("contentEl", selectedTextEl.replace(/\n/g, "").trimEnd());
  data.append("context", selectedTextContext); // On enlève les sauts de ligne
  data.append("end", selectedTextEnd);
  data.append("type", "highlight");
  axios
    .post(url, data, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then(function (response) {
      tooltip.classList.add("hidden");
      //
      let pr = response.data.p;
      if (!pr) {
        pr = "0";
      }
      var chapArticle = document.getElementById("paragraphe-" + pr);
      // * On affiche le highlight
      // fonction d'affichage sur le DOM
      showHighlightDom(
        chapArticle,
        response.data.contextSel,
        color,
        response.data.selection,
        response.data.selectionEl,
        response.data.idNote
      );
      activeClickTooltipHL();
    });
}
// ! Fonction qui permet de récupérer les highlights en base de données
// function AxiosGetHighlight() {
//   let url = "/recit/chapter/getnote";
//   let data = new FormData();
//   //
//   data.append("idChapter", document.getElementById("chapId").value);
//   axios
//     .post(url, data, {
//       headers: {
//         "Content-Type": "multipart/form-data",
//       },
//     }) // * TODO : Remettre le texte en contexte (avec "context")
//     .then(function (response) {
//       response.data.message.forEach((notes) => {
//         let selectionEl = notes.selectionEl;
//         let selection = notes.selection;
//         let context = notes.contextSel;
//         let color = notes.color;
//         let id = notes.id;
//         let pr = notes.p;
//         if (!pr) {
//           pr = "0";
//         }
//         var chapArticle = document.getElementById("paragraphe-" + pr);
//         // fonction d'affichage sur le DOM
//         showHighlightDom(
//           chapArticle,
//           context,
//           color,
//           selection,
//           selectionEl,
//           id
//         );
//       });
//       // * On ajoute un tooltip sur les highlights au click
//       let tooltiped = document.getElementById("highlighted-options");
//       let highlighted = document.querySelectorAll("span.highlighted");

//       activeClickTooltipHL(tooltiped, highlighted);
//       window.addEventListener("click", function () {
//         if (!tooltiped.classList.contains("hidden")) {
//           tooltiped.classList.add("hidden");
//         }
//       });
//     });
// }
function activeClickTooltipHL() {
  let tooltiped = document.getElementById("highlighted-options");
  let highlighted = document.querySelectorAll("span.highlighted");
  highlighted.forEach((element) => {
    element.addEventListener("click", function (e) {
      e.stopPropagation();
      highlightedOptions(element, tooltiped);
      tooltiped.classList.remove("hidden");
    });
  });
}
function highlightedOptions(element, tooltiped) {
  let parts = element.id.split("-");
  let result = parts[1];
  document.getElementById("delete-hl").setAttribute("data-note-id", result);
  let selection = window.getSelection();
  let range = selection.getRangeAt(0);
  let rect = range.getBoundingClientRect();
  tooltiped.style.left = rect.left + window.scrollX - 80 + "px";
  tooltiped.style.top = rect.top + window.scrollY - 50 + "px";
  var tool = element.classList;
  var deleteLi = document.getElementById("delete-hl");

  const styles = {
    "hl-1": ["bg-emerald-200", "text-emerald-400"],
    "hl-2": ["bg-amber-200", "text-amber-400"],
    "hl-3": ["bg-red-200", "text-red-400"],
    default: ["bg-slate-200", "text-slate-400"],
  };

  deleteLi.classList = "";
  let classes = tool.contains("hl-1")
    ? styles["hl-1"]
    : tool.contains("hl-2")
    ? styles["hl-2"]
    : tool.contains("hl-3")
    ? styles["hl-3"]
    : styles.default;
  deleteLi.classList.add(...classes);
}
// ! fonction de partage sur les réseaux
function shareNw(nw) {
  // ! partage sur twitter
  let urlShare = "";
  let url = window.location.href;
  let title = document.getElementById("chapTitle").innerText;
  if (nw == "twitter") {
    urlShare = `https://twitter.com/share?url=${url}&text=${title}`;
  } else if (nw == "facebook") {
    urlShare = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
  } else if (nw == "linkedin") {
    urlShare = `https://www.linkedin.com/shareArticle/?mini=true&url=${url}`;
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
  idNote
) {
  if (chapArticle.innerHTML.includes(contextSel)) {
    var tests = contextSel + selection;
    chapArticle.innerHTML = chapArticle.innerHTML.replace(
      tests,
      contextSel +
        "<span id='hl-" +
        idNote +
        "' class='highlighted hl-" +
        color +
        "'>" +
        selection +
        "</span>"
    );
  } else if (!chapArticle.innerHTML.includes(selection)) {
    chapArticle.innerHTML = chapArticle.innerHTML.replace(
      selectionEl,
      "<span id='hl-" +
        idNote +
        "' class='highlighted hl-" +
        color +
        "'>" +
        selection +
        "</span>"
    );
  } else {
    chapArticle.innerHTML = chapArticle.innerHTML.replace(
      selection,
      "<span id='hl-" +
        idNote +
        "' class='highlighted hl-" +
        color +
        "'>" +
        selection +
        "</span>"
    );
  }
  if (selectionEl.includes("</p>")) {
    var bigArticle = document.getElementById("chapArticle");
    bigArticle.innerHTML = bigArticle.innerHTML.replaceAll(
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
// ! Fonction qui permet de supprimer les highlights en base de données
function AxiosDeleteHighlight() {
  let hlId = document.getElementById("delete-hl").getAttribute("data-note-id");
  let url = "/recit/chapter/delnote";
  let data = new FormData();
  data.append("idNote", hlId);
  axios
    .post(url, data, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then(function (response) {
      // * On supprime le highlight du DOM
      var el = document.getElementById("hl-" + hlId);
      var parent = el.parentNode;
      while (el.firstChild) parent.insertBefore(el.firstChild, el);
      parent.removeChild(el);
    });
}
function commentQuote(
  selectedText,
  selectedTextP,
  selectedTextEl,
  selectedTextContext
) {
  document.getElementById("insightQuote").innerHTML = selectedText;
  let drawerSelectedText = document.getElementById("drawerSelectedText");
  let drawerSelectedTextP = document.getElementById("drawerSelectedTextP");
  let drawerSelectedTextEl = document.getElementById("drawerSelectedTextEl");
  let drawerSelectedTextContext = document.getElementById(
    "drawerSelectedTextContext"
  );
  drawerSelectedTextP.value = selectedTextP;
  drawerSelectedText.value = selectedText;
  drawerSelectedTextEl.value = selectedTextEl;
  drawerSelectedTextContext.value = selectedTextContext;
}
ShowChapter();
