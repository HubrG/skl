import { NotyDisplay } from "../Noty";
import axios from "axios";
export function AxiosSavePublication() {
  if (!document.getElementById("hideIdPub")) {
    return;
  }
  if (document.getElementById("togglePubAS")) {
    axiosGoSortable();
    // ! FIRST PUB
    if (document.location.href.includes("#first")) {
      if (document.getElementById("itemsChap2")) {
        document.getElementById("itemsChap2").classList.add("animate__swing");
        setTimeout(() => {
          document
            .getElementById("itemsChap2")
            .classList.remove("animate__swing");
          location.hash = "";
        }, 2500);
      }
    }
    // ! Suppression de la couverture
    var deleteCover = document.getElementById("deleteCover");
    if (deleteCover) {
      deleteCover.addEventListener("click", () => {
        axiosDeleteCover();
      });
    }
    // ! Publication Sale - Afficher la div de vente si case cochée
    if (document.getElementById("publication_sale")) {
      var saleLinks = document.getElementById("saleLinks");
      var sale = document.getElementById("publication_sale");
      sale.addEventListener("change", () => {
        if (sale.checked) {
          saleLinks.classList.remove("hidden");
        } else {
          saleLinks.classList.add("hidden");
        }
      });
    }
    // ! Publication Access - Afficher la div d'accès si case cochée
    if (document.getElementById("publication_access")) {
      var accessDiv = document.getElementById("user-access-div");
      var access = document.getElementById("publication_access");
      access.addEventListener("change", () => {
        if (access.checked) {
          accessDiv.classList.remove("hidden");
        } else {
          accessDiv.classList.add("hidden");
        }
      });
    }
    // ! Publication Access - Afficher la div d'accès si case cochée
    if (document.getElementById("publication_support")) {
      var supportDiv = document.getElementById("support-div");
      var support = document.getElementById("publication_support");
      support.addEventListener("change", () => {
        if (support.checked) {
          supportDiv.classList.remove("hidden");
        } else {
          supportDiv.classList.add("hidden");
        }
      });
    }
    // ! Édition rapide du titre (à la volée)
    // On récupère tous les éléments avec la classe "fastChangeTitle"
    var fastChangeTitles = document.querySelectorAll(".fastChangeTitle");

    fastChangeTitles.forEach(function (element) {
      element.addEventListener("click", function (event) {
        var clickedElement = event.target;
        clickedElement.classList.add("active");
        var titleDataId = event.target.getAttribute("data-title-id");
        var titleId = event.target.getAttribute("id");
        var titleInput = document.getElementById("titleInput" + titleDataId);
        var oldTitle = this.innerHTML.trim();
        titleInput.classList.remove("hidden");
        this.classList.add("hidden");
        titleInput.value = this.innerHTML.trim();
        titleInput.focus();
        var blurOccurred = false;

        titleInput.addEventListener("blur", function (e) {
          blurOccurred = true;

          var updatedTitle = titleInput.value.trim();
          clickedElement.innerHTML = updatedTitle;
          clickedElement.classList.remove("hidden");
          titleInput.classList.add("hidden");

          if (updatedTitle.trim() != oldTitle) {
            axiosChangeTitle(titleDataId, updatedTitle);
          }
        });

        titleInput.addEventListener("keydown", function (e) {
          if (e.keyCode === 13) {
            blurOccurred = true;
            e.preventDefault();

            var updatedTitle = titleInput.value.trim();
            clickedElement.innerHTML = updatedTitle;
            clickedElement.classList.remove("hidden");
            titleInput.classList.add("hidden");

            if (updatedTitle.trim() != oldTitle) {
              axiosChangeTitle(titleDataId, updatedTitle);
            }
          }
        });

        event.stopPropagation();
      });
    });

    // ! Variables autosave
    var togglePubAS = document.getElementById("togglePubAS");
    var axiosPubAS = document.querySelectorAll(".axiosPubAS");
    var pubMature = document.getElementById("publication_mature");
    let publish = document.getElementById("PublicationPublishButton");
    var coverShow = document.getElementById("cover");
    var spinCover = document.getElementById("spinCover");
    var cover = document.getElementById("publication_cover");
    var publishDiv = document.getElementById("publishDiv");
    // ! Checkboxes change values
    pubMature.addEventListener("change", () => {
      if (pubMature.checked) {
        pubMature.value = 1;
      } else {
        pubMature.value = 0;
      }
    });
    cover.addEventListener("change", () => {
      spinCover.classList.toggle("hidden");
      coverShow.classList.add("opacity-50");
    });
    // ! Si on est sur la page de publication
    if (document.getElementById("hidePubStatus")) {
      // ! Gestion des "tasks" de publication
      const task = document.getElementById("task");
      const taskCategory = document.getElementById("taskCategory");
      const taskPublish = document.getElementById("taskPublish");
      const publishPaidAttention = document.getElementById(
        "publish-paid-attention"
      );
      const publishPaidAttentionPub = document.getElementById(
        "publish-paid-attention-pub"
      );
      const publishPaidAttentionCat = document.getElementById(
        "publish-paid-attention-cat"
      );
      const publicationCategory = document.getElementById(
        "publication_category"
      );
      publicationCategory.addEventListener("change", () => {
        if (publicationCategory.value) {
          taskCategory.classList.add("animate__fadeOut");
          setTimeout(() => {
            publishPaidAttentionCat.classList.add("hidden");
            taskCategory.classList.add("hidden");
            taskCategory.classList.remove("animate__fadeOut");
            if (taskPublish.classList.contains("hidden")) {
              task.classList.add("hidden");
              publishPaidAttention.classList.add("hidden");
            }
          }, 1500);
        } else {
          taskCategory.classList.add("animate__fadeIn");
          if (taskPublish.classList.contains("hidden")) {
            task.classList.remove("hidden");
            publishPaidAttention.classList.remove("hidden");
          }
          setTimeout(() => {
            taskCategory.classList.remove("hidden");
            publishPaidAttentionCat.classList.remove("hidden");
          }, 1500);
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
    }
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
  if (document.getElementById("publication_finished")) {
    var finished = document.getElementById("publication_finished").checked;
  } else {
    var finished = 0;
  }
  if (document.getElementById("publication_sale")) {
    var sale = document.getElementById("publication_sale").checked;
  } else {
    var sale = 0;
  }
  if (document.getElementById("publication_sale_web")) {
    var saleWeb = document.getElementById("publication_sale_web").value;
    var salePaper = document.getElementById("publication_sale_paper").value;
  }
  //!
  var mature = document.getElementById("publication_mature").checked;
  var access = document.getElementById("publication_access").checked;
  var support = document.getElementById("publication_support").checked;
  var showOldVersions = document.getElementById(
    "publication_show_old_versions"
  ).checked;
  var hideSearch = document.getElementById("publication_hide_search").checked;
  var allowRevision = document.getElementById(
    "publication_allow_revision"
  ).checked;
  var hideIdPub = document.getElementById("hideIdPub").value;
  // * Gestion de la photo (si changement de photo)
  var spinCover = document.getElementById("spinCover").classList;
  const hideNoCover = document.getElementById("hideNoCover");
  const noCover = document.getElementById("no-cover");
  const coverOk = document.getElementById("cover-ok");
  var showNewCover = document.getElementById("showNewCover");
  var coverShow = document.getElementById("cover");
  var cover = document.getElementById("publication_cover");

  if (cover.files[0]) {
    if (cover.files[0].size > 10000000) {
      var coverFile = "";
      var notyText =
        "<span class='text-base font-medium'>Fichier trop volumineux</span><br />Le fichier ne doit pas dépasser 10Mo.";
      var notyTimeout = 4500;
      var notyType = "error";
      NotyDisplay(notyText, notyType, notyTimeout);
      spinCover.toggle("hidden");
      cover.value = "";
      coverShow.classList.remove("opacity-50");
      return false;
    } else {
      var coverFile = cover.files[0];
    }
  } else {
    var coverFile = "";
  }
  // *
  // * on envoie le contenu
  let data = new FormData();
  data.append("title", title);
  data.append("summary", summary);
  data.append("finished", finished);
  data.append("access", access);
  data.append("support", support);
  data.append("allowRevision", allowRevision);
  data.append("showOldVersions", showOldVersions);
  data.append("hideSearch", hideSearch);
  data.append("category", category);
  data.append("mature", mature);
  data.append("sale", sale);
  data.append("sale_paper", salePaper);
  data.append("sale_web", saleWeb);
  data.append("idPub", hideIdPub);
  data.append("cover", coverFile);
  // * Envoi sur le serveur via Axios
  axios
    .post(url, data, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then(function (response) {
      if (response.data.code == 200) {
        var notyText =
          "<span class='text-base font-medium'>Récit enregistré</span><br />Votre récit est à jour.";
        var notyTimeout = 4500;
        var notyType = "success";
        if (!document.querySelector(".noty_type__success")) {
          NotyDisplay(notyText, notyType, notyTimeout);
        }
        // * cover
        if (response.data.cloudinary) {
          spinCover.toggle("hidden");
          coverShow.classList.remove("opacity-50");
          coverShow.src = response.data.cloudinary;
          cover.value = "";
          if (coverOk.classList.contains("hidden")) {
            noCover.classList.add("hidden");
            coverOk.classList.remove("hidden");
          }
        }
      } else {
        // * si le fichier n'était pas une image, on réinitialise le champ
        if (coverFile) {
          spinCover.toggle("hidden");
          coverShow.classList.remove("opacity-50");
          cover.value = "";
        }
        // * on affiche l'erreur
        var notyText =
          "<span class='text-base font-medium'>Erreur</span><br />" +
          response.data.value;
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
  //
  var publish = document.getElementById("PublicationPublishButton");
  //
  var publishText = document.getElementById("publishText");
  var publishButton = document.getElementById("publishButton");
  var publishDiv = document.getElementById("publishDiv");
  var publishToggle = document.getElementById("publishToggle");
  var publishDateText = document.getElementById("publishDateText");
  var badgePubStatus = document.getElementById("badgePubStatus");
  var togglePubAS = document.getElementById("togglePubAS");
  var ASTooltip = document.getElementById("ASTooltip");
  var ASText = document.getElementById("ASText");
  var ASDisabled = document.getElementById("ASDisabled");
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
        "Êtes-vous certain(e) de vouloir <span class='text-red-500 dark:text-red-500'>dépublier</span> de votre récit ?";

      publishToggle.classList.add("peer-checked:bg-red-600");
      publish.classList.add("button-red");
      badgePubStatus.classList.toggle("badge-published");
      ASDisabled.classList.add("cursor-not-allowed");
    } else {
      PublicationPublishModalText.innerHTML =
        "Êtes-vous certain(e) de vouloir <span class='text-emerald-500 dark:text-emerald-500'>publier</span> votre récit ?";
      publishToggle.classList.add("peer-checked:bg-slate-600", "bg-slate-200");
      publishDateText.classList.add("hidden");
      ASTooltip.classList.add("hidden");
      publish.classList.add("button-emerald");
      badgePubStatus.classList.toggle("badge-unpublished");
    }
  } else {
    if (publishButton.checked == false) {
      publishButton.checked = true;
      publishDateTextSpan.innerHTML = "quelques instants";
      if (document.getElementById("challenge")) {
        publishText.innerHTML = "Dépublier l'exercice";
      } else {
        publishText.innerHTML = "Dépublier de Scrilab";
      }
      PublicationPublishModalText.innerHTML =
        "Êtes-vous certain(e) de vouloir <span class='text-red-500 dark:text-red-500'>dépublier</span> de votre récit ?";
      publishToggle.classList.remove("peer-checked:bg-slate-600");
      publish.classList.add("button-red");
      publish.classList.remove("button-emerald");
      publishToggle.classList.add("peer-checked:bg-red-600");
      ASTooltip.classList.remove("hidden");
      ASText.classList.add(
        "cursor-default",
        "text-slate-400",
        "dark:text-slate-600"
      );
      ASDisabled.classList.add("cursor-not-allowed");
      ASText.classList.remove("cursor-pointer");
      badgePubStatus.classList.toggle("badge-unpublished");
      badgePubStatus.classList.toggle("badge-published");
      badgePubStatus.innerHTML = "Publié";
      publishDateText.classList.remove("hidden");
    } else {
      publishButton.checked = false;
      if (document.getElementById("challenge")) {
        publishText.innerHTML = "Publier l'exercice";
      } else {
        publishText.innerHTML = "Publier sur Scrilab";
      }
      PublicationPublishModalText.innerHTML =
        "Êtes-vous certain(e) de vouloir <span class='text-emerald-500 dark:text-emerald-500'>publier</span> votre récit ?";
      publishToggle.classList.remove("peer-checked:bg-red-600");
      publishToggle.classList.add("bg-slate-200", "peer-checked:bg-slate-600");
      ASTooltip.classList.add("hidden");
      ASText.classList.remove(
        "cursor-default",
        "text-slate-400",
        "dark:text-slate-600"
      );
      ASDisabled.classList.remove("cursor-not-allowed");
      ASText.classList.add("cursor-pointer");
      badgePubStatus.classList.toggle("badge-unpublished");
      badgePubStatus.classList.toggle("badge-published");
      badgePubStatus.innerHTML = "Dépublié";
      publishDateText.classList.add("hidden");
      publish.classList.remove("button-red");
      publish.classList.add("button-emerald");
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
          savePublication.innerHTML = `<i class="fa-regular fa-cloud-arrow-up"></i> &nbsp;Valider les modifications`;
          togglePubASfunc();
        } else if (response.data["code"] == 600) {
          var notyText =
            "<span class='text-base font-medium'>Erreur</span><br />Impossible de publier votre réponse à l'exercice, car l'exercice est terminé";
          var notyType = "error";
          publishButton.checked = false;
          publishButton.disabled = true;
        } else {
          var notyText =
            "<span class='text-base font-medium'>Récit dépublié</span><br />Votre récit n'est plus visible par vos lecteurs";
          hidePubStatus.value = 1;
          togglePubAS.checked = true;
          togglePubAS.disabled = false;
          savePublication.innerHTML = `<i class="fa-regular fa-floppy-disk"></i>  &nbsp;Sauvegarder`;
          togglePubASfunc();
        }
        NotyDisplay(notyText, notyType, notyTimeout);
      })
      .catch(function (error) {
        if (error.response) {
          var notyText =
            "<span class='text-base font-medium'>Erreur</span><br />Une erreur est survenue lors de la sauvegarde de votre feuille";
          var notyTimeout = 4500;
          var notyType = "error";
          NotyDisplay(notyText, notyType, notyTimeout);
        }
      });
  }
}
// !
// ! Fonction permettant de passer le chapitre en publié/dépublié selon qu'on le place dans lap artie "publiée" ou "non publiée"
// !
function axiosGoSortable() {
  let nbr = 0;
  let url = "/story/chapter/sort";
  //
  // ! FIXME: Attention, pas du tout optimal, ça envoi une requête pour chaque chapitre... à voir comment refactoriser
  var parent1 = document.querySelector("#itemsChap");
  var parent2 = document.querySelector("#itemsChap2");
  document.querySelectorAll(".list-group-item").forEach(function (row) {
    row.id = nbr;
    let data = new FormData();
    // ! On envoi le nouveau status du chapitre en bdd
    var t = "indicator" + row.getAttribute("chap");

    var indicator = document.getElementById(t).classList;

    if (parent1.contains(row)) {
      data.append("status", 2);
      indicator.remove("bg-gray-500");
      indicator.add("bg-green-500");
    } else {
      data.append("status", 1);
      indicator.remove("bg-green-500");
      indicator.add("bg-gray-500");
    }
    data.append("idChap", row.getAttribute("chap"));
    data.append("order", nbr);
    axios
      .post(url, data, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      })
      .then(function (response) {});
    nbr++;
  });
}
async function axiosChangeTitle(id, title) {
  const url = "/story/fastedititle";
  const formData = new FormData();
  formData.append("title", title);
  formData.append("id", id);

  try {
    const response = await axios.post(url, formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
  } catch (error) {
    let notyText =
      "<span class='text-base font-medium'>Erreur</span><br />Une erreur est survenue lors de la modification du titre de votre récit";
    let notyType = "error";
    let notyTimeout = 4500;
    if (error.response) {
      if (!document.querySelector(".noty_type__error")) {
        NotyDisplay(notyText, notyType, notyTimeout);
      }
    }
  }
}
function axiosDeleteCover() {
  let id = document.getElementById("hideId").value;
  let url = "/story/deletecover";
  let data = new FormData();
  data.append("id", id);
  axios
    .post(url, data, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then(function (response) {
      var coverOk = document.getElementById("cover-ok");
      var noCover = document.getElementById("no-cover");
      if (response.data["code"] == 200) {
        var notyTimeout = 4000;
        var notyType = "info";
        var notyText =
          "<span class='text-base font-medium'>Couverture supprimée</span><br />La couverture de votre récit a été supprimée";
        NotyDisplay(notyText, notyType, notyTimeout);
        coverOk.classList.add("hidden");
        noCover.classList.remove("hidden");
      }
    })
    .catch(function (error) {
      if (error.response) {
        var notyText =
          "<span class='text-base font-medium'>Erreur</span><br />Une erreur est survenue lors de la suppression de la couverture de votre récit";
        var notyTimeout = 4500;
        var notyType = "error";
        NotyDisplay(notyText, notyType, notyTimeout);
      }
    });
}
AxiosSavePublication();
