import { NotyDisplay } from "../Noty";

export function AxiosSavePublication() {
  if (document.getElementById("togglePubAS")) {
    // ! Variables autosave
    var togglePubAS = document.getElementById("togglePubAS");
    var axiosPubAS = document.querySelectorAll(".axiosPubAS");
    var pubMature = document.getElementById("publication_mature");
    let publish = document.getElementById("PublicationPublishButton");
    // ! Checkboxes change values
    pubMature.addEventListener("change", () => {
      if (pubMature.checked) {
        pubMature.value = 1;
      } else {
        pubMature.value = 0;
      }
    });
    // ! Publish
    publish.addEventListener("click", publishPublication);
    publishPublication("init"); //  On initialise la publication
    // ! EventListener sur l'auto-save
    togglePubASfunc();
    togglePubAS.addEventListener("change", () => {
      togglePubASfunc();
    });
    // ! Sauvegarde du chapitre au bouton
    savePublication.addEventListener("click", () => {
      AxiosPublication();
    });
    // ! Autosave
    //  * Selection de tous les champs qui ont la classe axiosPubAs et execution d'axios
    let timeout;
    axiosPubAS.forEach(function (row) {
      row.addEventListener("change", () => {
        if (row.classList.contains("axiosPubAS")) {
          clearTimeout(timeout);
          timeout = setTimeout(() => {
            AxiosPublication();
          }, 1000);
        }
      });
    });
  }
}
// !
// ! Fonction de sauvegarde
// !
function AxiosPublication() {
  const url = "/story/autosave";
  // * récupération des éléments
  var title = document.getElementById("publication_title").value;
  var summary = document.getElementById("publication_summary").value;
  var category = document.getElementById("publication_category").value;
  var mature = document.getElementById("publication_mature").value;
  var hideIdPub = document.getElementById("hideIdPub").value;
  // * Gestion de la photo (si changement de photo)
  var spinCover = document.getElementById("spinCover").classList;
  var hideNoCover = document.getElementById("hideNoCover");
  var showNewCover = document.getElementById("showNewCover");
  var coverShow = document.getElementById("cover");
  var cover = document.getElementById("publication_cover");
  var coverName = hideIdPub + Math.floor(Math.random() * 9999);
  var coverPath =
    "/images/uploads/story/" + hideIdPub + "/" + coverName + ".jpg";
  if (cover.files[0]) {
    var coverFile = cover.files[0];
    spinCover.toggle("hidden");
  } else {
    var coverFile = "";
  }
  // *
  // * on envoie le contenu
  let data = new FormData();
  data.append("title", title);
  data.append("summary", summary);
  data.append("category", category);
  data.append("mature", mature);
  data.append("idPub", hideIdPub);
  data.append("cover", coverFile);
  data.append("coverName", coverName);
  // * Envoi sur le serveur via Axios
  axios
    .post(url, data, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then(function (response) {
      console.log(response.data.code);
      if (response.data.code == 200) {
        var notyText =
          "<span class='text-base font-medium'>Récit enregistré</span><br />Votre récit est à jour.";
        var notyTimeout = 4500;
        var notyType = "success";
        NotyDisplay(notyText, notyType, notyTimeout);
        // * cover
        if (coverFile) {
          spinCover.toggle("hidden");
          coverShow.src = coverPath;
          cover.value = "";
          if (hideNoCover) {
            if (showNewCover.classList.contains("hidden")) {
              hideNoCover.classList.add("hidden");
              showNewCover.classList.remove("hidden");
            }
          }
        }
      } else {
        // * si le fichier n'était pas une image, on réinitialise le champ
        if (coverFile) {
          spinCover.toggle("hidden");
          cover.value = "";
        }
        // * on affiche l'erreur
        var notyText =
          "<span class='text-base font-medium'>Erreur</span><br />Une erreur est survenue lors de la sauvegarde de votre chapitre";
        var notyTimeout = 4500;
        var notyType = "error";
        NotyDisplay(notyText, notyType, notyTimeout);
      }
    });
}
// !
// ! Fonction permettant de gérer l'autosave via le toggle
// !
function togglePubASfunc() {
  console.log("ok");
  let hidePubStatus = document.getElementById("hidePubStatus");
  var togglePubAS = document.getElementById("togglePubAS");
  let axiosPubSpy = document.querySelectorAll(".axiosPubSpy");
  if (togglePubAS.checked == true && hidePubStatus.value < 2) {
    axiosPubSpy.forEach(function (row) {
      if (!row.classList.contains("axiosPubAS")) {
        row.classList.add("axiosPubAS");
      }
    });
  } else {
    axiosPubSpy.forEach(function (row) {
      if (row.id != "publication_cover") {
        row.classList.remove("axiosPubAS");
      }
    });
  }
}
// !
// ! Fonction de publication
// !
function publishPublication(ev) {
  // * Variables
  var hidePubStatus = document.getElementById("hidePubStatus");
  var publish = document.getElementById("PublicationPublishButton");
  var publishText = document.getElementById("publishText");
  var publishButton = document.getElementById("publishButton");
  var publishDiv = document.getElementById("publishDiv");
  var publishToggle = document.getElementById("publishToggle");
  var publishDateText = document.getElementById("publishDateText");
  var badgePubStatus = document.getElementById("badgePubStatus");
  var togglePubAS = document.getElementById("togglePubAS");
  var AStext = document.getElementById("AStext");
  var savePublication = document.getElementById("savePublication");
  var PublicationPublishModalText = document.getElementById(
    "PublicationPublishModalText"
  );
  // *
  // * Initialisation des variables et des états
  // *
  if (ev == "init") {
    if (publishButton.checked) {
      PublicationPublishModalText.innerHTML =
        "Êtes-vous certain(e) de vouloir annuler la publication de votre récit ?";
      publishDiv.classList.add("border-red-500", "bg-red-100");
      publishText.classList.add("text-red-900");
      publishToggle.classList.add("peer-checked:bg-red-600");
      publish.classList.add("bg-red-600", "hover:bg-red-800");
      badgePubStatus.classList.add(
        "bg-green-100",
        "text-green-800",
        "dark:text-green-300",
        "dark:bg-green-900"
      );
    } else {
      PublicationPublishModalText.innerHTML =
        "Êtes-vous certain(e) de vouloir publier votre récit ?";
      publishDiv.classList.add("bg-green-100", "border-green-500");
      publishText.classList.add("text-green-900");
      publishToggle.classList.add("peer-checked:bg-green-600", "bg-green-200");
      publish.classList.add("bg-green-600", "hover:bg-green-800");
      publishDateText.classList.add("hidden");
      AStext.classList.add("hidden");
      badgePubStatus.classList.add(
        "bg-red-100",
        "text-red-800",
        "dark:text-red-300",
        "dark:bg-red-900"
      );
    }
  } else {
    if (publishButton.checked == false) {
      publishButton.checked = true;
      publishDateTextSpan.innerHTML = "quelques instants";
      publishText.innerHTML = "Dépublier le récit";
      PublicationPublishModalText.innerHTML =
        "Êtes-vous certain(e) de vouloir annuler la publication de votre récit ?";
      publishDiv.classList.remove("bg-green-100", "border-green-500");
      publishDiv.classList.add("bg-red-100", "border-red-500");
      publishToggle.classList.remove("peer-checked:bg-green-600");
      publishToggle.classList.add("peer-checked:bg-red-600");
      publishText.classList.remove("text-green-900");
      publishText.classList.add("text-red-900");
      publish.classList.remove("bg-green-600", "hover:bg-green-800");
      publish.classList.add("bg-red-600", "hover:bg-red-800");
      AStext.classList.remove("hidden");
      badgePubStatus.classList.remove(
        "bg-red-100",
        "text-red-800",
        "dark:text-red-300",
        "dark:bg-red-900"
      );
      badgePubStatus.classList.add(
        "bg-green-100",
        "text-green-800",
        "dark:text-green-300",
        "dark:bg-green-900"
      );
      badgePubStatus.innerHTML = "Publié";
    } else {
      publishButton.checked = false;
      publishText.innerHTML = "Publier le récit";
      PublicationPublishModalText.innerHTML =
        "Êtes-vous certain(e) de vouloir publier votre récit ?";
      publishToggle.classList.remove("peer-checked:bg-red-600");
      publishToggle.classList.add("bg-green-200", "peer-checked:bg-green-600");
      publishDiv.classList.remove("bg-red-100", "border-red-500");
      publishDiv.classList.add("bg-green-100", "border-green-500");
      publishText.classList.remove("text-red-900");
      publishText.classList.add("text-green-900");
      publish.classList.remove("bg-red-600", "hover:bg-red-800");
      publish.classList.add("bg-green-600", "hover:bg-green-800");
      AStext.classList.add("hidden");
      badgePubStatus.classList.add(
        "bg-red-100",
        "text-red-800",
        "dark:text-red-300",
        "dark:bg-red-900"
      );
      badgePubStatus.classList.remove(
        "bg-green-100",
        "text-green-800",
        "dark:text-green-300",
        "dark:bg-green-900"
      );
      badgePubStatus.innerHTML = "Dépublié";
      publishDateText.classList.add("hidden");
    }
    // *
    // * Envoi en BDD
    // *
    let id = document.getElementById("hideId").value;
    let url = "/story/publish";
    let data = new FormData();
    let buttonStatus = publishButton.checked;
    data.append("pub", id);
    data.append("publish", buttonStatus);
    axios
      .post(url, data, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      })
      .then(function (response) {
        var notyTimeout = 4000;
        var notyType = "success";
        if (response.data["code"] == 200) {
          var notyText =
            "<span class='text-base font-medium'>Récit publié</span><br />Votre récit est désormais visible par vos lecteurs";
          hidePubStatus.value = 2;
          togglePubAS.checked = false;
          togglePubAS.disabled = true;
          savePublication.innerHTML =
            "<i class='fa-solid fa-cloud-arrow-up'></i> &nbsp;Publier les modifications";

          togglePubASfunc();
        } else {
          var notyText =
            "<span class='text-base font-medium'>Récit dépublié</span><br />Votre récit n'est plus visible par vos lecteurs";
          hidePubStatus.value = 1;
          togglePubAS.checked = true;
          togglePubAS.disabled = false;
          savePublication.innerHTML =
            "<i class='fa-solid fa-floppy-disk'></i>  &nbsp;Enregistrer";
          togglePubASfunc();
        }
        NotyDisplay(notyText, notyType, notyTimeout);
      })
      .catch(function (error) {
        if (error.response) {
          var notyText =
            "<span class='text-base font-medium'>Erreur</span><br />Une erreur est survenue lors de la sauvegarde de votre chapitre";
          var notyTimeout = 4500;
          var notyType = "error";
          NotyDisplay(notyText, notyType, notyTimeout);
        }
      });
  }
}
AxiosSavePublication();
