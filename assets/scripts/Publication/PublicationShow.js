import axios from "axios";
export function PublicationShow() {
  const pubContent = document.getElementById("PublicationShowContent");
  if (!pubContent) return;

  // Au clic que un élément de type Publication
  // on récupère l'id de la publication
  var info_button = document.querySelectorAll(".publication-button");
  info_button.forEach((element) => {
    // On récupère l'id de la publication qui commence par "info-" numéro de la publication
    var id = element.id.split("-")[1];
    // AU clic...
    element.addEventListener("click", (e) => {
      var pub = document.getElementById("publication-" + id);
      if (pub.classList.contains("col-span-2")) {
        pub.classList.remove("col-span-2", "row-span-2");
      } else {
        pub.classList.add("col-span-2", "row-span-2");
      }
    });
  });
}
PublicationShow();
