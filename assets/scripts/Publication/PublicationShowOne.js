import axios from "axios";
import { NotyDisplay } from "../Noty";
export function PublicationShowOne() {
  const pubContent = document.getElementById("PublicationShowOneContent");
  if (!pubContent) return;
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
  //!SECTION - SUIVRE UN RÉCIT
  const followBtn = document.getElementById("followBtn");
  if (followBtn) {
    followBtn.addEventListener("click", (e) => {
      e.preventDefault();
      const url = followBtn.getAttribute("data-href");
      axios
        .post(url)
        .then((response) => {
          if (response.data.code == 200) {
            let followIcon = document.getElementById("followIcon");
            let followInfo = document.getElementById("followInfo");
            let followTitle = document.getElementById("followTitle");
            //
            NotyDisplay(response.data.message, "success", 2000);
            if (followBtn.classList.contains("follow-collection-buttons-ok")) {
              followBtn.classList.remove("follow-collection-buttons-ok");
              followBtn.classList.add("follow-collection-buttons");
              //
              followIcon.classList.add("fa-bell");
              followIcon.classList.remove("fa-bell-slash");
              followTitle.innerHTML = "Suivre ce récit";
              followInfo.innerHTML =
                "Vous recevrez une notification à chaque nouveau chapitre publié.";
            } else {
              followBtn.classList.remove("follow-collection-buttons");
              followBtn.classList.add("follow-collection-buttons-ok");
              //
              followIcon.classList.remove("fa-bell");
              followIcon.classList.add("fa-bell-slash");
              followTitle.innerHTML = "Ne plus suivre ce récit";
              followInfo.innerHTML =
                "Vous ne recevrez plus de notification à chaque nouveau chapitre publié.";
            }
          }
        })
        .catch((error) => {
          console.log(error);
        });
    });
  }
  //!SECTION - AJOUTER À MA COLLECTION
  const addBtn = document.getElementById("btnCollection");
  if (addBtn) {
    addBtn.addEventListener("click", (e) => {
      e.preventDefault();
      const url = addBtn.getAttribute("data-href");
      axios
        .post(url)
        .then((response) => {
          if (response.data.code == 200) {
            let addIcon = document.getElementById("iconCollection");
            let addInfo = document.getElementById("infoCollection");
            let addTitle = document.getElementById("titleCollection");
            //
            NotyDisplay(response.data.message, "success", 2000);
            if (addBtn.classList.contains("follow-collection-buttons-ok")) {
              addBtn.classList.remove("follow-collection-buttons-ok");
              addBtn.classList.add("follow-collection-buttons");
              //

              addTitle.innerHTML = "Ajouter à ma collection";
              addInfo.innerHTML =
                "Vous pourrez retrouver ce récit dans votre collection.";
            } else {
              addBtn.classList.remove("follow-collection-buttons");
              addBtn.classList.add("follow-collection-buttons-ok");
              //

              addTitle.innerHTML = "Retirer de ma collection";
              addInfo.innerHTML =
                "Vous ne pourrez plus retrouver ce récit dans votre collection.";
            }
          }
        })
        .catch((error) => {
          console.log(error);
        });
    });
  }
}
