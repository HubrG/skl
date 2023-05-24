import { NotyDisplay } from "../Noty";
import { Assign } from "../Assign";
export function ForumTopicRead() {
  const messagesFrame = document.getElementById("messages-frame");
  if (!messagesFrame) {
    return;
  }
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
            // const nbrCom = document.querySelectorAll(".nbr-com");
            // nbrCom.forEach((nbr) => {
            //   nbr.innerHTML = Number(nbr.innerHTML) - 1;
            // });
            comment.classList.add("animate__animated", "animate__zoomOut");
            //
            // document.querySelectorAll(".one-comment").forEach((el) => {
            //   el.classList.add("commment-up");
            // });
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
         <textarea id='comShow-${result}' class='assign-user topicCommentEdit textarea'>${com.textContent.trim()}</textarea>
         <div class="assign-user-dropdown" style="display: none;margin-top:-1.5rem "></div>
         <div class='flex flex-row gap-x-2 items-center justify-between'>
        
         <button id='validCom-${result}' class='topicCommentEditValidButton'><i class="fa-light fa-circle-check"></i> &nbsp;Valider</button>
         <button id='cancelCom-${result}' class='topicCommentEditCancelButton' data-tippy-content="Annuler la modification"><i class="fa-duotone fa-xmark"></i> Annuler</button>
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
        inner.innerHTML = `<p class='topicComment'>${
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
            inner.innerHTML = `<p class="topicComment" >${newCom2.textContent}</p>`;
            com.innerHTML = `${newCom.textContent}`;
            com2.innerHTML = `${newCom2.textContent}`;
          });
      });
    });
  });
  // ! Suppression d'un sujet
  // * Popup de confirmation de suppression
  const popupConfirmPub = document.getElementById("popup-confirm-delete-topic");
  if (popupConfirmPub) {
    const confirmDelete = document.getElementById("confirm-delete-button"); // Boutton de confirmation de suppression

    confirmDelete.addEventListener("click", (event) => {
      document.querySelector("main").classList.add("opacity-50");
      window.top.location.href = confirmDelete.getAttribute("data-delete-path");
    });
  }
}
// const nl2br = (str = "", isXHTML = true) => {
//   const breakTag = isXHTML ? "<br />" : "";
//   return str.replace(/(\r\n|\n\r|\r|\n)/g, `$1${breakTag}`);
// };
