// Default SortableJS
import Sortable from "sortablejs";

export function Sortables() {
  if (document.getElementById("itemsChap")) {
    onload = function () {
      axiosGoSortable();
    };

    // List with handle
    Sortable.create(document.getElementById("itemsChap"), {
      animation: 250, // ms, animation speed moving items when sorting, `0` — without animation
      easing: "cubic-bezier(0.65, 0, 0.35, 1)",
      group: "shared", // set both lists to same group
      handle: ".item",
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
        }
        // same properties as onEnd
      },
    });
    // List with handle
    Sortable.create(document.getElementById("itemsChap2"), {
      animation: 250, // ms, animation speed moving items when sorting, `0` — without animation
      easing: "cubic-bezier(0.65, 0, 0.35, 1)",
      group: "shared", // set both lists to same group
      handle: ".item",
      ghostClass: "ghost",
      onEnd: function (/**Event*/ evt) {
        var itemEl = evt.item; // dragged HTMLElement
        axiosGoSortable();
      },
    });
  }
  (function (c, a, n) {
    var w = c.createElement(a),
      s = c.getElementsByTagName(a)[0];
    w.src = n;
    s.parentNode.insertBefore(w, s);
  })(document, "script", "https://sdk.canva.com/designbutton/v2/api.js");
}
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
Sortables();
