import { NotyDisplay } from "../Noty";

export function User() {
  // ! Suppression d'un récit
  // * Popup de confirmation de suppression

  const popupConfirmPub = document.getElementById("popup-confirm-delete");
  if (popupConfirmPub) {
    let deleteDropdown = document.querySelectorAll(".delete-pub"); // Dropdown de suppression
    var confirmDelete = document.getElementById("confirm-delete-button"); // Boutton de confirmation de suppression
    deleteDropdown.forEach((item) => {
      item.addEventListener("click", (event) => {
        confirmDelete.removeAttribute("data-url-delete");
        let pubId = item.getAttribute("data-pub-id");
        confirmDelete.setAttribute("data-url-delete", pubId);
      });
    });
    confirmDelete.addEventListener("click", (event) => {
      document.querySelector("main").classList.add("opacity-50");
      window.location.href = confirmDelete.getAttribute("data-url-delete");
    });
  }
  // * Modification du background de la section profil et de la photo de profil
  // Si "pp" existe, on ajoute un event listener sur le bouton "pp" sinon on retourne rien
  const pp = document.getElementById("pp");
  const pbg = document.getElementById("pbg");
  if (!pp) return;
  pp.addEventListener("change", (event) => {
    UpdateProfilPicture(pp.files[0], "pp");
  });
  pbg.addEventListener("change", (event) => {
    UpdateProfilPicture(pbg.files[0], "pbg");
  });
}
function UpdateProfilPicture(file, type) {
  if (type === "pp") {
    var image = document.getElementById("profil_picture");
  } else if (type === "pbg") {
    var image = document.getElementById("section_profil");
  }
  if (!file) {
    return;
  }
  image.classList.add("opacity-50");
  if (file.size > 10000000) {
    var notyText =
      "<span class='text-base font-medium'>Fichier trop volumineux</span><br />Le fichier ne doit pas dépasser 10mo.";
    var notyTimeout = 4500;
    var notyType = "error";
    NotyDisplay(notyText, notyType, notyTimeout);
    image.classList.remove("opacity-50");
    return;
  }
  // On crée un objet FormData
  const url = "/update/user/update_picture";
  const formData = new FormData();
  // On ajoute le fichier à l'objet FormData
  formData.append(type, file);
  // On envoie la requête
  axios
    .post(url, formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then((response) => {
      if (response.data.code === 200) {
        if (type === "pp") {
          image.src = response.data.cloudinary;
          var notyText =
            "<span class='text-base font-medium'>Photo de profil mise à jour</span>";
          if (
            document.getElementById("profil_picture_navbar") &&
            type === "pp"
          ) {
            document.getElementById("profil_picture_navbar").src =
              response.data.cloudinary;
          }
        } else if (type === "pbg") {
          let bgPic = response.data.cloudinary;
          bgPic = bgPic.replace("/upload/", "/upload/e_vectorize:colors:30,");
          var notyText =
            "<span class='text-base font-medium'>Photo de couverture mise à jour</span>";
          const newStyle = document.createElement("style");
          newStyle.innerHTML =
            '#section_profil::before { background-image:url("' + bgPic + '") }';
          document.body.appendChild(newStyle);
        }
        var notyTimeout = 3500;
        var notyType = "success";
        NotyDisplay(notyText, notyType, notyTimeout);
      } else {
        var notyText =
          "<span class='text-base font-medium'>Erreur</span><br/>" +
          response.data.value;
        var notyTimeout = 3500;
        var notyType = "error";
        NotyDisplay(notyText, notyType, notyTimeout);
      }
    })
    .then(() => {
      image.classList.remove("opacity-50");
    });
}
User();
