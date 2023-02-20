import { NotyDisplay } from "../Noty";

export function User() {
  // Si "pp" existe, on ajoute un event listener sur le bouton "pp" sinon on retourne rien

  const pp = document.getElementById("pp");
  if (!pp) return;

  pp.addEventListener("change", (event) => {
    UpdateProfilPicture(pp.files[0]);
  });
}
function UpdateProfilPicture(file) {
  if (file.size > 10000000) {
    var notyText =
      "<span class='text-base font-medium'>Fichier trop volumineux</span><br />Le fichier ne doit pas dépasser 10mo.";
    var notyTimeout = 4500;
    var notyType = "error";
    NotyDisplay(notyText, notyType, notyTimeout);
    return;
  }
  // On crée un objet FormData
  const url = "/update/user/update_pp";
  const formData = new FormData();
  // On ajoute le fichier à l'objet FormData
  formData.append("pp", file);
  // On envoie la requête
  axios
    .post(url, formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then((response) => {
      if (response.data.code === 200) {
        document.getElementById("profil_picture").src =
          response.data.cloudinary;
        var notyText =
          "<span class='text-base font-medium'>Photo de profil mise à jour</span>";
        var notyTimeout = 3500;
        var notyType = "success";
        NotyDisplay(notyText, notyType, notyTimeout);
        if (document.getElementById("profil_picture_navbar")) {
          document.getElementById("profil_picture_navbar").src =
            response.data.cloudinary;
        }
      } else {
        var notyText =
          "<span class='text-base font-medium'>Erreur</span><br/>" +
          response.data.value;
        var notyTimeout = 3500;
        var notyType = "error";
        NotyDisplay(notyText, notyType, notyTimeout);
      }
    });
}
function isImage(file) {
  const reader = new FileReader();
  reader.readAsArrayBuffer(file);

  reader.onloadend = function () {
    const arr = new Uint8Array(reader.result).subarray(0, 4);
    let header = "";
    for (let i = 0; i < arr.length; i++) {
      header += arr[i].toString(16);
    }
    switch (header) {
      case "89504e47":
        return true; // PNG
      case "47494638":
        return true; // GIF
      case "ffd8ffe0":
      case "ffd8ffe1":
      case "ffd8ffe2":
        return true; // JPEG
      default:
        return false; // not an image
    }
  };
}
User();
