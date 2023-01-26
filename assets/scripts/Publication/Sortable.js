// Default SortableJS
import Sortable from "sortablejs";

export function Sortables() {
  if (document.getElementById("itemsChap")) {
    // List with handle
    Sortable.create(document.getElementById("itemsChap"), {
      animation: 250, // ms, animation speed moving items when sorting, `0` — without animation
      easing: "cubic-bezier(1, 0, 0, 1)",

      onEnd: function (/**Event*/ evt) {
        var itemEl = evt.item; // dragged HTMLElement
        // evt.to; // target list
        // evt.from; // previous list
        // evt.oldIndex; // element's old index within old parent
        // evt.newIndex; // element's new index within new parent
        // evt.oldDraggableIndex; // element's old index within old parent, only counting draggable elements
        // evt.newDraggableIndex; // element's new index within new parent, only counting draggable elements
        // evt.clone; // the clone element
        // evt.pullMode;
        // evt.clone;
        let nbr = 0;
        let url = "/story/chapter/sort";
        //
        // ! FIXME: Attention, pas du tout optimal, ça envoi une requête pour chaque chapitre... à voir comment refactoriser
        document.querySelectorAll(".list-group-item").forEach(function (row) {
          row.id = nbr;
          let data = new FormData();
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

        // when item is in another sortable: `"clone"` if cloning, `true` if moving
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
function UpAxios() {}
Sortables();
