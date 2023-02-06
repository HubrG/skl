import axios from "axios";
export function ShowChapter() {
  if (document.getElementById("chapContentTurbo")) {
    // ! Récupération des notes (type 0) du chapitre de l'utilisateur connecté
    let url = "/recit/chapter/getnote";
    let data = new FormData();
    //
    data.append("idChapter", document.getElementById("chapId").value);
    axios
      .post(url, data, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      }) // * TODO : Remettre le texte en contexte (avec "context")
      .then(function (response) {
        response.data.message.forEach((notes) => {
          let selectionEl = notes.selectionEl;
          let selection = notes.selection;
          let context = notes.context.trim();
          let id = notes.id;
          let pr = notes.p;
          if (!pr) {
            pr = "0";
          }
          var chapArticle = document.getElementById("paragraphe-" + pr);
          if (!chapArticle.innerHTML.includes(selection)) {
            chapArticle.innerHTML = chapArticle.innerHTML.replace(
              selectionEl,
              "<span class='highlighted' id='hl-" +
                id +
                "'>" +
                selection +
                "</span>"
            );
          } else {
            chapArticle.innerHTML = chapArticle.innerHTML.replace(
              selection,
              "<span class='highlighted' id='hl-" +
                id +
                "'>" +
                selection +
                "</span>"
            );
          }
        });
      });
    // ! Fonction qui affiche une petite tooltip avec une icone de suppression au survol de la souris sur un élément de classe "highlighted"
    var highlighted = document.querySelectorAll("span.highlighted");
    highlighted.forEach((element) => {
      element.addEventListener("click", function () {
        let parts = element.id.split("-");
        let result = parts[1];
        let tooltiped = document.getElementById("highlited-options");
        tooltiped.classList.toggle("hidden");
      });
    });
    // ! Fonction de surlignage
    // * Sélection de texte
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
          var start = selection.anchorOffset - 2;
          var end = selection.focusOffset;
          var startNode = selection.anchorNode;
          var textContent = startNode.textContent;
          var contextStart = start >= 0 ? start : 0;
          var contextEnd = end <= textContent.length ? end : textContent.length;
          selectedTextContext = textContent.substring(contextStart, contextEnd);
          console.log(selectedTextContext);
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
            tooltip.style.left = rect.left + window.scrollX + "px";
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
    let highlight = document.getElementById("highlight");
    // * Click sur la popup
    highlight.addEventListener("click", function () {
      let url = "/recit/chapter/note";
      let data = new FormData();
      //
      data.append("p", selectedTextP);
      data.append("idChapter", document.getElementById("chapId").value);
      data.append("selection", selectedText);
      data.append("contentEl", selectedTextEl);
      data.append("context", selectedTextContext.trim().replace(/\n.*/g, "")); // On enlève les sauts de ligne
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
          document.getElementById("chapArticle").innerHTML = document
            .getElementById("chapArticle")
            .innerHTML.replace(
              selectedText,
              "<span style='bg-amber-200 w-10 h-10'>|</span>" +
                selectedText +
                "<span style='bg-amber-200 w-10 h-10'>|</span>"
            );
        });
    });
    // * Click sur la fenêtre (pour fermer la popup)
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
ShowChapter();
