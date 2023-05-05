import { NotyDisplay } from "../Noty";
import { ReadTimeFunction } from "./ChapterStats";
// !
// ! Fonction permettant de gérer la sauvegarde et la publication du chapitre
// !
const globalThis = window;
globalThis.scriptAlreadyExecuted = globalThis.scriptAlreadyExecuted || false;

export function axiosSaveChapter() {
  const selectChapVersion = document.getElementById("selectChapVersion");
  if (!selectChapVersion) return;

  // Variables
  const toggleAS = document.getElementById("toggleAS");
  const title = document.getElementById("title");
  const editor = document.getElementById("editor");
  const saveChapter = document.getElementById("saveChapter");
  const axiosChapterAS = document.querySelectorAll(".axiosChapterAS");
  const togglePublish = document.getElementById("togglePublish");
  const spinAS = document.getElementById("spinAS");

  // Initialisation
  onload = () => {
    title.setSelectionRange(title.value.length, title.value.length);
    title.focus();
  };

  // Event listeners
  toggleAS.addEventListener("change", toggleASfunc);
  selectChapVersion.addEventListener("change", handleVersionChange);
  saveChapter.addEventListener("click", handleChapterSave);
  togglePublish.addEventListener("change", handleChapterPublish);

  // Autosave
  const AUTOSAVE_DELAY = 30000;
  let autosaveTimeout;
  axiosChapterAS.forEach((row) => {
    if (row.classList.contains("axiosChapterAS")) {
      row.addEventListener("keyup", handleAutosave);
    }
  });

  // Functions
  function toggleASfunc() {
    if (toggleAS.checked) {
      startAutosave();
    } else {
      stopAutosave();
    }
  }

  function startAutosave() {
    autosaveTimeout = setTimeout(() => {
      axiosChapter();
      spinAS.classList.add("hidden");
    }, AUTOSAVE_DELAY);
    spinAS.classList.remove("hidden");
  }

  function stopAutosave() {
    clearTimeout(autosaveTimeout);
    spinAS.classList.add("hidden");
  }

  function handleVersionChange() {
    toggleAS.checked = false;
    stopAutosave();
    AxiosGetVersion(selectChapVersion.value);
  }

  function handleChapterSave() {
    axiosChapter();
    spinAS.classList.add("hidden");
  }
  globalThis.scriptAlreadyExecuted = true;
  function handleChapterPublish() {
    console.log("handleChapterPublish called");
    stopAutosave();
    const isPublished = togglePublish.checked;
    publishChapter(isPublished);
  }

  function handleAutosave() {
    stopAutosave();
    startAutosave();
  }
}
// !
// ! Fonction de sauvegarde
// !
function axiosChapter() {
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
          "<span class='text-base font-medium'>Feuille enregistrée</span><br />Votre feuille est à jour d'après cette version";
        var notyTimeout = 4500;
        var notyType = "success";
        if (!document.querySelector(".noty_type__success")) {
          NotyDisplay(notyText, notyType, notyTimeout);
        }
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
          "<span class='text-base font-medium'>Feuille publiée</span><br />Votre feuille est désormais visible par vos lecteurs";
        hideChapStatus.value = 2;
        axiosChapter();
      } else {
        var notyText =
          "<span class='text-base font-medium'>Feuille dépubliée</span><br />Votre feuille n'est plus visible par vos lecteurs";
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
          '<i class="fa-regular fa-cloud-arrow-up"></i>&nbsp;Publier les modifications';
        asTip.setAttribute("data-tooltip-target", "tooltip-default");
        asTip.classList.remove("cursor-pointer");
        asTip.classList.add("cursor-not-allowed", "opacity-60");
        toggleASfunc();
      } else {
        // * on active l'autosave et on rétabli le toggle autosave
        toggleAS.disabled = false;
        saveChapter.innerHTML =
          '<i class="fa-regular fa-floppy-disk"></i>&nbsp;Enregistrer';
        asTip.removeAttribute("data-tooltip-target", "tooltip-default");
        asTip.classList.remove("cursor-not-allowed", "opacity-60");
        asTip.classList.add("pointer");
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
export { axiosChapter };
