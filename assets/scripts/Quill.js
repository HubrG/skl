import Quill from "quill";
export function quillEditor() {
  var toolbarOptions = [
    ["bold", "italic", "underline", "strike"], // toggled buttons
    ["blockquote"],
    [{ header: 2 }], // custom button values
    [{ list: "ordered" }, { list: "bullet" }],
    ["link", "image"],
    ["clean"], // remove formatting button
  ];
  var options = {
    placeholder: "Contenu de votre chapitre...",
    modules: {
      toolbar: toolbarOptions,
    },
    theme: "bubble",
  };
  if (document.getElementById("editor")) {
    const quill = new Quill("#editor", options);
    // * exemple de bouton personnalisé TODO: à utiliser pour des images
    // document
    //   .getElementById("boldButton")
    //   .addEventListener("click", function () {
    //     quill.format("bold", true);
    //   });

    document.getElementById("editor").addEventListener("paste", function () {
      quill.clipboard.addMatcher(Node.ELEMENT_NODE, (node, delta) => {
        let ops = [];
        delta.ops.forEach((op) => {
          if (op.insert && typeof op.insert === "string") {
            ops.push({
              insert: op.insert,
            });
          }
        });
        delta.ops = ops;
        return delta;
      });
    });
  }
}
quillEditor();
