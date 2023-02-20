import { NotyDisplay } from "../Noty";
import { ReadTimeFunction } from "./ChapterStats";
// !
// ! Fonction permettant de gérer la sauvegarde et la publication du chapitre
// !
export function AxiosSaveChapter() {
  if (document.getElementById("selectChapVersion")) {
    // ! Variables
    var toggleAS = document.getElementById("toggleAS");
    var title = document.getElementById("title");
    var editor = document.getElementById("editor");
    var selectChapVersion = document.getElementById("selectChapVersion");
    var saveChapter = document.getElementById("saveChapter");
    var axiosChapterAS = document.querySelectorAll(".axiosChapterAS");
    var togglePublish = document.getElementById("togglePublish");
    var spinAS = document.getElementById("spinAS");
    // !
    onload = () => {
      title.setSelectionRange(title.value.length, title.value.length);
      title.focus();
    };
    // ! EventListener sur l'auto save
    toggleAS.addEventListener("change", () => {
      toggleASfunc();
    });
    // ! Changement de version et stop de l'autosave (qui sera réactivé si la publication est dépubliée et que le bouton de sauvegarde est cliqué)
    selectChapVersion.addEventListener("change", () => {
      // * on toggle l'autosave sur "off"
      toggleAS.checked = false;
      // * On désactive l'autosave par l'appel de la fonction
      toggleASfunc();
      // * On récupère la version sélectionnée
      AxiosGetVersion(selectChapVersion.value);
    });
    // ! Sauvegarde du chapitre au bouton
    saveChapter.addEventListener("click", () => {
      AxiosChapter();
    });
    // ! Publication du chapitre
    togglePublish.addEventListener("change", () => {
      if (togglePublish.checked == true) {
        publishChapter(true);
      } else {
        publishChapter(false);
      }
    });
    // ! Autosave
    //  * Selection de tous les champs qui ont la classe axiosChapter et execution d'axios
    let timeout;
    axiosChapterAS.forEach(function (row) {
      row.addEventListener("keyup", () => {
        if (row.classList.contains("axiosChapterAS")) {
          spinAS.classList.remove("hidden");
          clearTimeout(timeout);
          timeout = setTimeout(() => {
            AxiosChapter();
            spinAS.classList.add("hidden");
          }, 30000);
        }
      });
    });
  }
}
// !
// ! Fonction de sauvegarde
// !
function AxiosChapter() {
  const url = "/story/chapter/autosave";
  // * récupération des éléments
  let quill = document.getElementById("editor").__quill;
  quill = quill.root.innerHTML;
  var title = document.getElementById("title").value;
  var hideIdPub = document.getElementById("hideIdPub").value;
  var hideIdChap = document.getElementById("hideIdChap").value;
  //
  // on prépare les données à envoyer
  // * on envoie le contenu
  let data = new FormData();
  data.append("title", title);
  data.append("quill", quill);
  data.append("idPub", hideIdPub);
  data.append("idChap", hideIdChap);
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
          "<span class='text-base font-medium'>Feuille enregistré</span><br />Votre feuille est à jour d'après cette version";
        var notyTimeout = 4500;
        var notyType = "success";
        NotyDisplay(notyText, notyType, notyTimeout);
      } else {
        var notyText =
          "<span class='text-base font-medium'>Erreur</span><br />Une erreur est survenue lors de la sauvegarde de votre feuille";
        var notyTimeout = 4500;
        var notyType = "error";
        NotyDisplay(notyText, notyType, notyTimeout);
      }
    });
}
// !
// ! Fonction de publication
// !
function publishChapter(publish) {
  var toggleAS = document.getElementById("toggleAS");
  var hidePubStatus = document.getElementById("hidePubStatus");
  var hideChapStatus = document.getElementById("hideChapStatus");
  var asTip = document.getElementById("asTip");
  //
  let url = "/story/chapter/publish";
  let data = new FormData();
  data.append("idChap", document.getElementById("hideIdChap").value);
  data.append("publish", publish);
  axios
    .post(url, data, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then(function (response) {
      if (response.data.code == "true") {
        var notyText =
          "<span class='text-base font-medium'>Feuille publié</span><br />Votre feuille est désormais visible par vos lecteurs";
        hideChapStatus.value = 2;
        AxiosChapter();
      } else {
        var notyText =
          "<span class='text-base font-medium'>Feuille dépublié</span><br />Votre feuille n'est plus visible par vos lecteurs";
        hideChapStatus.value = 1;
      }
      var notyTimeout = 4500;
      var notyType = "success";
      NotyDisplay(notyText, notyType, notyTimeout);
    })
    .then(function () {
      if (hidePubStatus.value > 1 && hideChapStatus.value > 1) {
        // * on désactive l'autosave et on disable le toggle autosave
        toggleAS.checked = false;
        toggleAS.disabled = true;
        saveChapter.innerHTML =
          '<i class="fa-solid fa-cloud-arrow-up"></i>&nbsp;Publier les modifications';
        asTip.setAttribute("data-tooltip-target", "tooltip-default");
        toggleASfunc();
      } else {
        // * on active l'autosave et on rétabli le toggle autosave
        toggleAS.disabled = false;
        saveChapter.innerHTML =
          '<i class="fa-regular fa-floppy-disk"></i>&nbsp;Enregistrer';
        asTip.removeAttribute("data-tooltip-target", "tooltip-default");
      }
    });
}
// !
// ! Fonction qui récupère la version sélectionnée
// !
function AxiosGetVersion(version) {
  let url = "/story/chapter/getversion";
  let data = new FormData();
  var quill = document.getElementById("editor").__quill;
  data.append("idPub", document.getElementById("hideIdPub").value);
  data.append("idChap", document.getElementById("hideIdChap").value);
  data.append("version", version);
  axios
    .post(url, data, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then(function (response) {
      quill.root.innerHTML = response.data.content;
      var notyText =
        "<span class='text-base font-medium'>Version chargée !</span><br />Elle ne vous convient pas ? Vous pouvez revenir à tout moment sur une version précédente.";
      var notyTimeout = 4500;
      var notyType = "info";
      NotyDisplay(notyText, notyType, notyTimeout);
      setTimeout(() => {
        document
          .getElementById("selectChapVersionContainer")
          .classList.add("animate__animated", "animate__flash");
      }, 1000);
      ReadTimeFunction(document.getElementById("editor"));
    });
}

// !
// ! Fonction permettant de gérer l'autosave via le toggle
// !
function toggleASfunc() {
  let hidePubStatus = document.getElementById("hidePubStatus");
  let hideChapStatus = document.getElementById("hideChapStatus");
  let toggleAS = document.getElementById("toggleAS");
  let axiosSpy = document.querySelectorAll(".axiosSpy");
  if (
    toggleAS.checked == true &&
    (hidePubStatus.value < 2 || hideChapStatus.value < 2)
  ) {
    axiosSpy.forEach(function (row) {
      if (!row.classList.contains("axioChapter")) {
        row.classList.add("axiosChapterAS");
      }
    });
  } else {
    axiosSpy.forEach(function (row) {
      row.classList.remove("axiosChapterAS");
    });
  }
}
if (document.getElementById("editorHTML")) {
  AxiosSaveChapter();
}
