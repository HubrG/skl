import { NotyDisplay } from "../Noty";

export function User() {
  // ! AJout d'un utilisateur en ami
  const followUser = document.getElementById("follow-user");
  if (followUser) {
    followUser.addEventListener("click", (event) => {
      const url = "/follow/user";
      const data = new FormData();
      data.append("user", followUser.getAttribute("data-user-id"));
      axios
        .post(url, data, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        })
        .then((response) => {
          if (response.data.code === 200) {
            followUser.classList.remove("button-emerald");
            followUser.classList.add("button-sky");
            followUser.innerHTML = `	<i class="fa-regular fa-user-plus"></i>
						Suivre`;
            var notyText = response.data.message;
            var notyTimeout = 3500;
            var notyType = "info";
            NotyDisplay(notyText, notyType, notyTimeout);
          } else {
            followUser.classList.add("button-emerald");
            followUser.classList.remove("button-sky");
            followUser.innerHTML = `<i class="fa-regular fa-user-group"></i>
						Suivi(e) !`;
            var notyText = response.data.message;
            var notyTimeout = 3500;
            var notyType = "success";
            NotyDisplay(notyText, notyType, notyTimeout);
          }
        })
        .catch((error) => {
          console.log(error);
        });
    });
  }
  // ! Modification des paramètres de notifications
  document.addEventListener("DOMContentLoaded", function () {
    const cityInput = document.querySelector(
      ".autocomplete[data-autocomplete='ville']"
    );
    if (cityInput) {
      new Awesomplete(cityInput, { list: [], minChars: 2 });
    }
  });

  const notif = document.getElementById("notif-account");
  if (notif) {
    const notifCheckboxes = document.querySelectorAll(".notif-checkbox");
    notifCheckboxes.forEach((checkbox) => {
      checkbox.addEventListener("change", (event) => {
        const data = new FormData();
        const url = "/param/user/set/notification";
        data.append("type", checkbox.getAttribute("data-notif-type"));
        data.append("nb", checkbox.getAttribute("data-notif-nb"));
        data.append("value", event.target.checked);
        axios
          .post(url, data, {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          })
          .then((response) => {
            if (response.status === 200) {
              console.log("Paramètre de notification bien modifié");
            }
          })
          .catch((error) => {
            console.log(error);
          });
      });
    });
  }
  // ! Suppression d'un utlisateur
  // * Popup de confirmation de suppression
  const popupConfirmUser = document.getElementById(
    "popup-confirm-delete-account"
  );
  if (popupConfirmUser) {
    var confirmDelete = document.getElementById(
      "confirm-delete-account-button"
    ); // Boutton de confirmation de suppression
    confirmDelete.addEventListener("click", (event) => {
      window.location.href = confirmDelete.getAttribute("data-url-delete");
    });
  }
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
  // ! Modification du background de la section profil et de la photo de profil
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
