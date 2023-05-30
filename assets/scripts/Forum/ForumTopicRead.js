import { NotyDisplay } from "../Noty";
import { Assign } from "../Assign";
export function ForumTopicRead() {
  const messagesFrame = document.getElementById("messages-frame");
  if (!messagesFrame) {
    return;
  }

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
