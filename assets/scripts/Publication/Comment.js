import axios from "axios";

import { NotyDisplay } from "../Noty";
export function Comment() {
  const commentDiv = document.getElementById("comment-section");
  if (!commentDiv) return;
  // ! Fonction de réponse à un commentaire
  const replyButton = document.querySelectorAll(".replyButton");
  replyButton.forEach((el) => {
    el.addEventListener("click", function () {
      const commentId = el.getAttribute("data-comment-id");
      let replyButtonIcon = document.getElementById(
        "reply-button-icon-" + commentId
      );
      if (replyButtonIcon.classList.contains("fa-comments")) {
        el.innerHTML = "Fermer";
        replyButtonIcon.classList.remove("fa-comments", "fa-flip-horizontal");
        replyButtonIcon.classList.add("fa-circle-xmark");
      } else {
        el.innerHTML = "Répondre";
        replyButtonIcon.classList.add("fa-comments", "fa-flip-horizontal");
        replyButtonIcon.classList.remove("fa-circle-xmark");
      }
      //
      let textareaReply = document.getElementById("reply-to-" + commentId);
      let replyTo = document.getElementById("display-reply-to-" + commentId);
      let replySend = document.getElementById("send-reply-to-" + commentId);
      // Affichage de la zone de texte
      replyTo.classList.toggle("hidden");
      // Aggrandissement de la zone de texte
      if (textareaReply) {
        textareaReply.addEventListener("input", () => {
          textareaReply.style.height = "";
          textareaReply.style.height = `${textareaReply.scrollHeight}px`;
        });
      }
      // Envoi du commentaire
      replySend.addEventListener("click", () => {
        console.log(commentId, textareaReply.value);
        var replyPath = replySend.getAttribute("data-reply-path");
        console.log(replyPath);
        let data = new FormData();
        let url = replyPath;
        data.append("id", commentId);
        data.append("replyContent", textareaReply.value);
        axios
          .post(url, data, {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          })
          .then((response) => {
            console.log(response.data);
            if (response.status === 200) {
              textareaReply.value = "";
              replyTo.classList.add("hidden");
              replyButtonIcon.classList.add("fa-reply", "fa-flip-horizontal");
              replyButtonIcon.classList.remove("fa-circle-xmark");
              el.innerHTML = "Répondre";
            }
          });
      });
    });
  });

  // ! Fonction de suppression d'un commentaire
  const deleteComment = document.querySelectorAll(".deleteCommentButton");
  deleteComment.forEach((el) => {
    el.addEventListener("click", function () {
      const commentDeletePath = el.getAttribute("data-delete-path");
      const commentId = el.getAttribute("data-comment-id");
      const url = commentDeletePath;
      const data = new FormData();
      data.append("id", commentId);
      axios
        .post(url, data, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        })
        .then((response) => {
          if (response.status === 200) {
            const comment = document.getElementById("comment-" + commentId);
            const hr = document.getElementById("hr-comment-" + commentId);
            const nbrCom = document.querySelectorAll(".nbr-com");
            nbrCom.forEach((nbr) => {
              nbr.innerHTML = Number(nbr.innerHTML) - 1;
            });
            comment.classList.add("animate__animated", "animate__zoomOut");
            //
            document.querySelectorAll(".one-comment").forEach((el) => {
              el.classList.add("commment-up");
            });
            // réduire la hauteur de la div à 0px
            setTimeout(() => {
              comment.remove();
              if (hr) {
                hr.remove();
              }
            }, 500);

            NotyDisplay(
              '<i class="fa-light fa-circle-check"></i>&nbsp;&nbsp;' +
                response.data.message,
              "info",
              2500
            );
          }
        })
        .catch((error) => {
          console.log(error);
        });
    });
  });
  // ! Fonction qui permet de liker un commentaire
  const likeButtons = document.querySelectorAll(".likeButton");
  likeButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const parts = button.id.split("-");
      const result = parts[1];
      const likePath = button.getAttribute("data-like-path");
      handleLikeButtonClick(button, result, likePath);
    });
  });
  const handleLikeButtonClick = (button, result, likePath) => {
    const url = likePath;
    const data = new FormData();
    data.append("id", result);
    axios
      .post(url, data, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      })
      .then((response) => {
        if (response.data.code === 201) {
          button.classList.remove("fa-regular", "animate__jello");
          button.classList.add(
            "fa-solid",
            "text-red-400",
            "dark:text-red-400",
            "animate__heartBeat"
          );
          const nbLikes = document.getElementById(`nbLikes-${result}`);
          nbLikes.innerHTML = Number(nbLikes.innerHTML) + 1;
        } else if (response.data.code === 200) {
          button.classList.remove(
            "fa-solid",
            "text-red-400",
            "animate__heartBeat"
          );
          button.classList.add("fa-regular", "animate__jello");
          const nbLikes = document.getElementById(`nbLikes-${result}`);
          nbLikes.innerHTML = Number(nbLikes.innerHTML) - 1;
        }
      });
  };
  // ! Fonction qui permet de modifier les commentaires
  const updateButton = document.querySelectorAll(".updateButton");

  updateButton.forEach((button) => {
    button.addEventListener("click", () => {
      const parts = button.id.split("-");
      const result = parts[1];

      const com = document.getElementById(`comShow-${result}`);
      const inner = document.getElementById(`updateCom-${result}`);

      inner.innerHTML = `
         <textarea id='comShow-${result}' class='chapterCommentEdit'>${com.textContent.trim()}</textarea>
         <button id='validCom-${result}' class='chapterCommentEditValidButton'><i class="fa-light fa-circle-check"></i> &nbsp;Valider</button>
         <button id='cancelCom-${result}' class="chapterCommentEditCancelButton" data-tippy-content="Annuler la modification"><i class="fa-duotone fa-xmark"></i></button>
       `;

      const buttonValid = document.getElementById(`validCom-${result}`);
      const updatePath = button.getAttribute("data-update-path");
      const newCom = document.getElementById(`comShow-${result}`);
      const cancelCom = document.getElementById(`cancelCom-${result}`);
      cancelCom.addEventListener("click", () => {
        inner.innerHTML = `<p class='chapterComment'  id='comShow-${result}'>${nl2br(
          com.textContent
        )}</p>`;
      });
      buttonValid.addEventListener("click", () => {
        const data = new FormData();
        const url = updatePath;
        data.append("id", result);
        data.append("newCom", newCom.value);
        axios
          .post(url, data, {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          })
          .then((response) => {
            newCom.textContent = response.data.comment;
            inner.innerHTML = `<p class='chapterComment' id='comShow-${result}'>${nl2br(
              newCom.textContent
            )}</p>`;
          });
      });
    });
  });
  // !

  const goToComment = document.querySelector(".goToComment");
  if (goToComment) {
    goToComment.addEventListener("click", () => {
      const commentSection = document.querySelector("#bottomChap");
      if (commentSection) {
        commentSection.scrollIntoView({ behavior: "smooth" });
      }
    });
  }
  // ! Fonction qui permet de mettre en exergue un nouveau commentaire
  const lastComment = document.getElementById("lastComment");
  if (lastComment) {
    lastComment.classList.add(
      "animate__animated",
      "animate__flipInX",
      "lastComment",
      "ease-in-out",
      "duration-500",
      "delay-500",
      "transform"
    );
    setTimeout(() => {
      lastComment.classList.remove("lastComment");
    }, 2000);
    setTimeout(() => {
      lastComment.classList.remove(
        "ease-in-out",
        "duration-500",
        "delay-500",
        "transform"
      );
    }, 4000);
  }
  // ! Fonction qui permet de modifeir la taille du textarea en fonction du contenu
  const textarea = document.getElementById("publication_comment_content");

  if (textarea) {
    textarea.addEventListener("input", () => {
      textarea.style.height = "";
      textarea.style.height = `${textarea.scrollHeight}px`;
    });
  }

  // ! Au clic sur le bouton d'envoi, on efface le formulaire textarea
  const sendComment = document.getElementById("sendComment");
  const commentContent = textarea;

  if (sendComment) {
    const nbrComLet = document.querySelector(".nbr-com-let");
    const nbrCom = nbrComLet.getAttribute("data-nbr-com");
    sendComment.addEventListener("click", () => {
      nbrComLet.innerHTML = Number(nbrCom) + 1;
      nbrComLet.setAttribute("data-nbr-com", Number(nbrCom) + 1);
      setTimeout(() => {
        commentContent.value = "";
        commentContent.rows = 1;
        commentContent.style.height = "";
      }, 100);
    });
  }
}
const nl2br = (str = "", isXHTML = true) => {
  const breakTag = isXHTML ? "<br />" : "<br>";
  return str.replace(/(\r\n|\n\r|\r|\n)/g, `$1${breakTag}`);
};
