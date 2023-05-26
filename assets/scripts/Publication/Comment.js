import axios from "axios";
import { Assign } from "../Assign";
import { NotyDisplay } from "../Noty";
let isProcessingReply = false;
let isProcessingComment = false;
export function CommentReply() {
  const commentDiv = document.getElementById("comment-section");
  if (!commentDiv) return;
  // ! Fonction de réponse à une réponse de commentaire
  let replyToButton = document.querySelectorAll(".replyToButton");
  replyToButton.forEach((el) => {
    el.addEventListener("click", function () {
      if (isProcessingReply) return;
      isProcessingReply = true;
      const commentId = el.getAttribute("data-ref-id");
      const replyToButtonIcon = document.getElementById(
        "reply-to-button-icon-" + commentId
      );
      if (replyToButtonIcon.classList.contains("fa-comments")) {
        el.innerHTML = "Fermer";
        replyToButtonIcon.classList.remove("fa-comments", "fa-flip-horizontal");
        replyToButtonIcon.classList.add("fa-circle-xmark");
      } else {
        el.innerHTML = "Répondre";
        replyToButtonIcon.classList.add("fa-comments", "fa-flip-horizontal");
        replyToButtonIcon.classList.remove("fa-circle-xmark");
      }
      //
      const textareaReplyTo = document.getElementById(
        "reply-to-reply-" + commentId
      );
      const replyToReply = document.getElementById(
        "display-reply-to-reply-" + commentId
      );
      const replyToSend = document.getElementById(
        "send-reply-to-reply-" + commentId
      );
      // Affichage de la zone de texte
      replyToReply.classList.toggle("hidden");
      if (!replyToButtonIcon.classList.contains("fa-comments")) {
        el.innerHTML = "Répondre";
        replyToButtonIcon.classList.add("fa-comments", "fa-flip-horizontal");
        replyToButtonIcon.classList.remove("fa-circle-xmark");
        isProcessingReply = false;
      } else {
        el.innerHTML = "Fermer";
        replyToButtonIcon.classList.remove("fa-comments", "fa-flip-horizontal");
        replyToButtonIcon.classList.add("fa-circle-xmark");
        isProcessingReply = true;
      }
      isProcessingReply = false;
      // On focus le textarea et on se place à la fin du texte
      textareaReplyTo.focus();
      textareaReplyTo.setSelectionRange(
        textareaReplyTo.value.length,
        textareaReplyTo.value.length
      );
      // Aggrandissement de la zone de texte
      if (textareaReplyTo) {
        textareaReplyTo.addEventListener("input", () => {
          textareaReplyTo.style.height = "";
          textareaReplyTo.style.height = `${textareaReplyTo.scrollHeight}px`;
        });
      }
      // Envoi du commentaire
      if (!replyToSend.hasClickListener) {
        replyToSend.addEventListener("click", () => {
          if (isProcessingReply) return;
          isProcessingReply = true;
          var replyPath = replyToSend.getAttribute("data-reply-path");
          const data = new FormData();
          const url = replyPath;
          data.append("id", el.getAttribute("data-comment-id"));
          data.append("replyContent", textareaReplyTo.value);
          axios
            .post(url, data, {
              headers: {
                "Content-Type": "multipart/form-data",
              },
            })
            .then((response) => {
              console.log(response.data);
              if (response.status === 200) {
                textareaReplyTo.value = "";
                replyToReply.classList.add("hidden");
                replyToButtonIcon.classList.add(
                  "fa-reply",
                  "fa-flip-horizontal"
                );
                replyToButtonIcon.classList.remove("fa-circle-xmark");
                el.innerHTML = "Répondre";
                setTimeout(() => {
                  const replyId = "hr-comment-" + response.data.commentId;
                  const element = document.getElementById(replyId);
                  if (element) {
                    element.scrollIntoView({
                      block: "center",
                      behavior: "smooth",
                    });
                  }
                }, 1000);
              }
              // Réinitialiser l'indicateur après avoir reçu la réponse
              isProcessingReply = false;
            });
          replyToSend.hasClickListener = true;
        });
      }
    });
  });
}
export function Comment() {
  const commentDiv = document.getElementById("comment-section");
  if (!commentDiv) return;
  // ! Fonction de réponse à un commentaire
  let replyButton;
  replyButton = document.querySelectorAll(".replyButton");
  replyButton.forEach((el) => {
    el.addEventListener("click", function () {
      if (isProcessingComment) return;
      isProcessingComment = true;
      const commentId = el.getAttribute("data-comment-id");
      const replyButtonIcon = document.getElementById(
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
      const textareaReply = document.getElementById("reply-to-" + commentId);
      const replyTo = document.getElementById("display-reply-to-" + commentId);
      const replySend = document.getElementById("send-reply-to-" + commentId);
      // Affichage de la zone de texte
      replyTo.classList.toggle("hidden");
      if (!replyButtonIcon.classList.contains("fa-comments")) {
        el.innerHTML = "Répondre";
        replyButtonIcon.classList.add("fa-comments", "fa-flip-horizontal");
        replyButtonIcon.classList.remove("fa-circle-xmark");
        isProcessingComment = false;
      } else {
        el.innerHTML = "Fermer";
        replyButtonIcon.classList.remove("fa-comments", "fa-flip-horizontal");
        replyButtonIcon.classList.add("fa-circle-xmark");
        isProcessingComment = true;
      }
      isProcessingComment = false;

      // Aggrandissement de la zone de texte
      if (textareaReply) {
        textareaReply.addEventListener("input", () => {
          textareaReply.style.height = "";
          textareaReply.style.height = `${textareaReply.scrollHeight}px`;
        });
      }
      // Envoi du commentaire
      // Envoi du commentaire
      if (!replySend.hasClickListener) {
        replySend.addEventListener("click", () => {
          if (isProcessingComment) return;
          isProcessingComment = true;
          var replyPath = replySend.getAttribute("data-reply-path");
          console.log(replyPath);
          const data = new FormData();
          const url = replyPath;
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
              // Réinitialiser l'indicateur après avoir reçu la réponse
              isProcessingComment = false;
            });
          replySend.hasClickListener = true;
        });
      }
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
      console.log(result);
      const com = document.getElementById(`comShow-${result}`); // Sans assignation
      const com2 = document.getElementById(`comShow2-${result}`); // Avec assignation
      const inner = document.getElementById(`updateCom-${result}`);
      inner.innerHTML = `
         <textarea id='comShow-${result}' class='assign-user chapterCommentEdit textarea' style='margin-top:2.25rem!important'>${com.textContent.trim()}</textarea>
         <div class="assign-user-dropdown" style="display: none;margin-top:-1.5rem "></div>
         <div class='flex flex-row gap-x-2 items-center justify-between'>
         <button id='validCom-${result}' class='chapterCommentEditValidButton'><i class="fa-light fa-circle-check"></i> &nbsp;Valider</button>
         <button id='cancelCom-${result}' class='chapterCommentEditCancelButton' data-tippy-content="Annuler la modification"><i class="fa-duotone fa-xmark"></i> Annuler</button>
         </div>
       
       `;
      Assign();
      const buttonValid = document.getElementById(`validCom-${result}`);
      const updatePath = button.getAttribute("data-update-path");
      //
      const newCom = document.getElementById(`comShow-${result}`); // Sans assignation
      const newCom2 = document.getElementById(`comShow2-${result}`); // Avec assignation
      const cancelCom = document.getElementById(`cancelCom-${result}`);
      cancelCom.addEventListener("click", () => {
        inner.innerHTML = `<p class='chapterComment'>${
          document.getElementById(`comShow2-${result}`).innerHTML
        }</p>`;
        com.innerHTML = `${com.textContent}`;
        com2.innerHTML = `${com2.innerHTML}`;
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
            newCom.textContent = response.data.comment; // Sans assignation
            newCom2.textContent = response.data.comment2; // Avec assignation
            inner.innerHTML = `<p class="chapterComment" >${newCom2.textContent}</p>`;
            com.innerHTML = `${newCom.textContent}`;
            com2.innerHTML = `${newCom2.textContent}`;
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
Comment();
CommentReply();
