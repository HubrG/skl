import { NotyDisplay } from "../Noty";
export function Comment() {
  const commentDiv = document.getElementById("comment-section");
  if (!commentDiv) return;
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
            const nbrCom = document.querySelectorAll(".nbr-com");
            nbrCom.forEach((nbr) => {
              nbr.innerHTML = Number(nbr.innerHTML) - 1;
            });
            comment.classList.add("animate__animated", "animate__zoomOut");
            setTimeout(() => {
              comment.remove();
            }, 1000);
            NotyDisplay(response.data.message, "success", 2500);
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
          button.classList.remove(
            "material-symbols-outlined",
            "animate__jello"
          );
          button.classList.add(
            "material-icons",
            "text-red-500",
            "animate__heartBeat"
          );
          const nbLikes = document.getElementById(`nbLikes-${result}`);
          nbLikes.innerHTML = Number(nbLikes.innerHTML) + 1;
        } else if (response.data.code === 200) {
          button.classList.remove(
            "material-icons",
            "text-red-500",
            "animate__heartBeat"
          );
          button.classList.add("material-symbols-outlined", "animate__jello");
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
         <button id='validCom-${result}' class='chapterCommentEditValidButton'><span class="material-symbols-outlined">check_circle</span> &nbsp;Valider</button>
         <button id='cancelCom-${result}' class='chapterCommentEditCancelButton toggleSidebar' data-tippy-content="Annuler la modification"><span class="material-symbols-outlined toggleSidebar">
         cancel
         </span></button>
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
  // ! Fonction qui permet de mettre en exergue un nouveau commentaire
  const lastComment = document.getElementById("lastComment");
  if (lastComment) {
    lastComment.classList.add(
      "animate__animated",
      "animate__flipInX",
      "bg-slate-50",
      "text-slate-600"
    );
    setTimeout(() => {
      lastComment.classList.remove("bg-slate-50", "text-slate-600");
    }, 2000);
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
Comment();
