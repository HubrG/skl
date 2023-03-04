// Default SortableJS
import { Sortable } from "sortablejs";

export function Sortables() {
  if (document.getElementById("itemsChap")) {
    onload = function () {
      axiosGoSortable();
    };

    // List with handle
    Sortable.create(document.getElementById("itemsChap"), {
      easing: "cubic-bezier(0.65, 0, 0.35, 1)",
      group: "shared", // set both lists to same group
      handle: ".item",
      animation: 300,
      ghostClass: "ghost",
      onEnd: function (/**Event*/ evt) {
        var itemEl = evt.item; // dragged HTMLElement
        var origEl = evt.item;
        var cloneEl = evt.clone;
        axiosGoSortable();
      },
      onChange: function (/**Event*/ evt) {
        evt.newIndex; // most likely why this event is used is to get the dragging element's current index
        if (document.getElementById("noPublication")) {
          document.getElementById("noPublication").remove();
          document.getElementById("noPublication2").remove();
        }
        // same properties as onEnd
      },
    });
    // List with handle
    Sortable.create(document.getElementById("itemsChap2"), {
      easing: "cubic-bezier(0.65, 0, 0.35, 1)",
      group: "shared", // set both lists to same group
      handle: ".item",
      animation: 300,
      swapClass: "dropdown-sort-swap", // The class applied to the hovered swap item
      ghostClass: "ghost",
      onChange: function (/**Event*/ evt) {
        document.getElementById("noChapUnpublished").remove();
        document
          .getElementById("itemsChap2")
          .classList.remove("md:grid-cols-1");
        document.getElementById("itemsChap2").classList.add("md:grid-cols-2");
      },
      onEnd: function (/**Event*/ evt) {
        var itemEl = evt.item; // dragged HTMLElement
        axiosGoSortable();
      },
    });
  }
}
function axiosGoSortable() {
  let nbr = 0;
  let url = "/story/chapter/sort";
  var parent1 = document.querySelector("#itemsChap");
  var parent2 = document.querySelector("#itemsChap2");
  document.querySelectorAll(".list-group-item").forEach(function (row) {
    row.id = nbr;
    let data = new FormData();
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
      .then(function (response) {
        var chapterNumber = document.getElementById(
          "chapter-sort-" + row.getAttribute("chap")
        );
        if (response.data.status == 2) {
          if (chapterNumber.classList.contains("hidden")) {
            chapterNumber.classList.remove("hidden");
          }
          chapterNumber.innerHTML = response.data.order;
        } else {
          chapterNumber.classList.add("hidden");
        }
      });
    nbr++;
  });
}
Sortables();
