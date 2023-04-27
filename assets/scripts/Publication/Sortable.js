// ! CODE REFACTORISÉ LE 20/04/2023
//
import { Sortable } from "sortablejs";
import { NotyDisplay } from "../Noty";

export function Sortables() {
  if (!document.getElementById("itemsChap")) {
    return;
  }
  createMainSortable();
  createSecondSortable();
  createTrashSortable();

  addTrashClickListener();
  addTrashEverClickListener();

  document.addEventListener("DOMContentLoaded", function () {
    axiosGoSortable();
  });
}

function createMainSortable() {
  return Sortable.create(document.getElementById("itemsChap"), {
    easing: "cubic-bezier(0.65, 0, 0.35, 1)",
    group: "shared",
    handle: ".item",
    animation: 300,
    ghostClass: "ghost",
    onEnd: function (evt) {
      axiosGoSortable();
      //
      let status = evt.item;
      axiosGoStatus(status);
    },
    onChange: function (evt) {
      handleNoPublicationRemoval();
    },
  });
}

function createSecondSortable() {
  return Sortable.create(document.getElementById("itemsChap2"), {
    easing: "cubic-bezier(0.65, 0, 0.35, 1)",
    group: "shared",
    handle: ".item",
    animation: 300,
    swapClass: "dropdown-sort-swap",
    ghostClass: "ghost",
    onChange: function (evt) {
      handleNoChapUnpublishedRemoval();
    },
    onEnd: function (evt) {
      axiosGoSortable();
      //
      let status = evt.item;
      axiosGoStatus(status);
    },
  });
}

function createTrashSortable() {
  return Sortable.create(document.getElementById("itemsChapTrash"), {
    easing: "cubic-bezier(0.65, 0, 0.35, 1)",
    group: "shared",
    handle: ".item",
    animation: 300,
    swapClass: "dropdown-sort-swap",
    ghostClass: "ghost",
    onEnd: function (evt) {
      axiosGoSortable();
      //
      let status = evt.item;
      axiosGoStatus(status);
    },
    onChange: function (evt) {
      handleNoChapTrashRemoval();
    },
  });
}

function handleNoPublicationRemoval() {
  if (
    document.getElementById("noPublication") ||
    document.getElementById("noPublication2")
  ) {
    document.getElementById("noPublication").remove();
    document.getElementById("noPublication2").remove();
  }
}

function handleNoChapUnpublishedRemoval() {
  if (document.getElementById("noChapUnpublished")) {
    document.getElementById("noChapUnpublished").remove();
  }
  document.getElementById("itemsChap2").classList.remove("md:grid-cols-1");
  document.getElementById("itemsChap2").classList.add("md:grid-cols-2");
}

function handleNoChapTrashRemoval() {
  if (document.getElementById("noChapTrash")) {
    document.getElementById("noChapTrash").remove();
  }
  document.getElementById("itemsChapTrash").classList.remove("md:grid-cols-1");
  document.getElementById("itemsChapTrash").classList.add("md:grid-cols-2");
}
function addTrashClickListener() {
  const trashItems = document.querySelectorAll(".chap-trash");
  trashItems.forEach((item) => {
    item.addEventListener("click", () => {
      const listItem = item.closest(".list-group-item");
      listItem.classList.add(
        "animate__animated",
        "animate__backOutDown",
        "animate__faster"
      );
      setTimeout(() => {
        listItem.classList.remove("animate__animated", "animate__backOutDown");
      }, 600);
      setTimeout(() => {
        moveToTrash(listItem);
        // console.log(listItem);
        listItem.setAttribute("data-chapter-status", 0);
        axiosGoStatus(listItem);
      }, 500);
    });
  });
}
function addTrashEverClickListener() {
  const trashItems = document.querySelectorAll(".trash-forever");
  trashItems.forEach((item) => {
    item.addEventListener("click", () => {
      const listItem = item.closest(".list-group-item");
      axiosTrashForever(listItem);
    });
  });
}
function moveToTrash(item) {
  const itemsChapTrash = document.getElementById("itemsChapTrash");

  itemsChapTrash.appendChild(item);
  handleNoChapTrashRemoval();
  if (!document.querySelector(".noty_type__info")) {
    NotyDisplay(
      "<big>Feuille supprimée !</big><br>Vous pouvez encore la retrouver dans la section « Corbeille » durant 48h",
      "info",
      5000
    );
  }
}
function axiosGoSortable() {
  const url = "/story/chapter/sort";

  const parent1 = document.querySelector("#itemsChap");
  const parent2 = document.querySelector("#itemsChap2");
  const parent3 = document.querySelector("#itemsChapTrash");
  // On met à jour le statut de chaque chapitre sous itemsChapTrash
  const parent3Full = document.querySelectorAll(
    "#itemsChapTrash .list-group-item"
  );
  parent3Full.forEach((item) => {
    item.setAttribute("data-chapter-status", 0);
  });
  // On met à jour le statut de chaque chapitre sous itemsChapTrash
  const parent2Full = document.querySelectorAll("#itemsChap2 .list-group-item");
  parent2Full.forEach((item) => {
    item.setAttribute("data-chapter-status", 1);
  });
  // On met à jour le statut de chaque chapitre sous itemsChapTrash
  const parent1Full = document.querySelectorAll("#itemsChap .list-group-item");
  parent1Full.forEach((item) => {
    item.setAttribute("data-chapter-status", 2);
  });
  //

  document.querySelectorAll(".list-group-item").forEach((row, index) => {
    const data = createFormData(row, parent1, parent2, parent3, index);

    sendFormData(url, data, row).then((response) => {
      updateChapterNumber(row, response);
      updateNbrItems();
    });
  });
}
function updateNbrItems() {
  //
  const nbrPublished = document.querySelectorAll(
    "#itemsChap .list-group-item"
  ).length;
  const nbrUnpublished = document.querySelectorAll(
    "#itemsChap2 .list-group-item"
  ).length;
  const nbrTrashed = document.querySelectorAll(
    "#itemsChapTrash .list-group-item"
  ).length;

  // Mettez à jour les éléments HTML avec les nouveaux nombres
  document.getElementById("nbrPublished").innerHTML = nbrPublished;
  document.getElementById("nbrUnpublished").innerHTML = nbrUnpublished;
  document.getElementById("nbrTrashed").innerHTML = nbrTrashed;

  const trashInfo = document.getElementById("trash-info");
  if (nbrTrashed > 0) {
    trashInfo.classList.remove("hidden");
  } else {
    trashInfo.classList.add("hidden");
  }
}
function createFormData(row, parent1, parent2, parent3, nbr) {
  const data = new FormData();
  const indicator = document.getElementById(
    `indicator${row.getAttribute("chap")}`
  ).classList;
  getStatus(row, parent1, parent2, parent3, indicator);
  data.append("idChap", row.getAttribute("chap"));
  data.append("order", nbr);
  return data;
}

function getStatus(row, parent1, parent2, parent3, indicator) {
  let status;

  if (parent1.contains(row)) {
    status = 2;
    indicator.replace("bg-gray-500", "bg-green-500");
  }
  if (parent2.contains(row)) {
    status = 1;
    indicator.replace("bg-green-500", "bg-gray-500");
  }
  if (parent3.contains(row)) {
    status = 0;
    indicator.replace("bg-green-500", "bg-gray-500");
  }
}

function sendFormData(url, data, row) {
  return axios.post(url, data, {
    headers: {
      "Content-Type": "multipart/form-data",
    },
  });
}

function updateChapterNumber(row, response) {
  const chapterNumber = document.getElementById(
    `chapter-sort-${row.getAttribute("chap")}`
  );
  if (response.data.status === 2) {
    chapterNumber.classList.remove("hidden");
    chapterNumber.innerHTML = response.data.order;
  } else {
    chapterNumber.classList.add("hidden");
  }
}

function updateTaskDisplay() {
  const taskPublish = document.getElementById("taskPublish");
  const taskCategory = document.getElementById("taskCategory");
  const task = document.getElementById("task");
  let nbrPublished = document.getElementById("nbrPublished").innerHTML;
  //

  if (nbrPublished > 0) {
    taskPublish.classList.add("hidden");
    if (taskCategory.classList.contains("hidden")) {
      task.classList.add("hidden");
    } else {
      task.classList.remove("hidden");
    }
  } else {
    taskPublish.classList.remove("hidden");
    if (taskCategory.classList.contains("hidden")) {
      task.classList.remove("hidden");
    } else {
      task.classList.add("hidden");
    }
  }
}
function axiosGoStatus(status) {
  let data = new FormData();
  data.append("idChap", status.getAttribute("chap"));
  data.append("status", status.getAttribute("data-chapter-status"));
  let urls = "/story/chapter/status";
  axios
    .post(urls, data, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then((response) => {
      let trashTemp = document.getElementById(
        "trashTemp" + status.getAttribute("chap")
      );
      let trashEver = document.getElementById(
        "trashEver" + status.getAttribute("chap")
      );

      if (response.status === 200) {
        axiosGoSortable();
        updateNbrItems();
        updateTaskDisplay();
        if (response.data.status == 0) {
          trashTemp.classList.add("hidden");
          trashEver.classList.remove("hidden");
        } else {
          trashTemp.classList.remove("hidden");
          trashEver.classList.add("hidden");
        }
      } else {
        NotyDisplay(
          "Une erreur est survenue. Veuillez actualiser la page !",
          "error",
          3000
        );
        setTimeout(() => {
          window.location.reload();
        }, 3000);
      }
    });
}
function axiosTrashForever(listItem) {
  let data = new FormData();
  let urls = "/story/chapter/" + listItem.getAttribute("chap") + "/delete";
  axios
    .post(urls, data, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then((response) => {
      if (response.status === 200) {
        listItem.classList.add("animate__animated", "animate__bounceOut");
        setTimeout(() => {
          listItem.remove();
          updateNbrItems();
        }, 1000);
      } else {
        NotyDisplay(
          "Une erreur est survenue. Veuillez actualiser la page !",
          "error",
          3000
        );
        setTimeout(() => {
          window.location.reload();
        }, 3000);
      }
    });
}
Sortables();
