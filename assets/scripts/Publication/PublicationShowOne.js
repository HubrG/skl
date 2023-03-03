import axios from "axios";
import { NotyDisplay } from "../Noty";
export function PublicationShowOne() {
  const pubContent = document.getElementById("PublicationShowOneContent");
  if (!pubContent) return;
  //!SECTION
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
              followIcon.innerHTML = "loyalty";
              followTitle.innerHTML = "Suivre ce récit";
              followInfo.innerHTML =
                "Vous recevrez une notification à chaque nouvelle feuille publiée.";
            } else {
              followBtn.classList.remove("follow-collection-buttons");
              followBtn.classList.add("follow-collection-buttons-ok");
              //
              followIcon.innerHTML = "label_off";
              followTitle.innerHTML = "Ne plus suivre ce récit";
              followInfo.innerHTML =
                "Vous ne recevrez plus de notification à chaque nouvelle feuille publiée.";
            }
          }
        })
        .catch((error) => {
          console.log(error);
        });
    });
  }
}
PublicationShowOne();
