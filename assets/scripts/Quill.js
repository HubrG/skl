import Quill from "quill";
export function quillEditor() {
  var toolbarOptions = [
    ["bold", "italic", "underline", "strike"], // toggled buttons
    ["blockquote"],
    [{ header: 2 }], // custom button values
    [{ list: "ordered" }, { list: "bullet" }],
    ["link", "image"],
    [{ align: [] }],
    ["clean"], // remove formatting button
  ];
  var options = {
    placeholder: "Contenu de votre chapitre...",
    theme: "bubble",
    modules: {
      toolbar: toolbarOptions,
      clipboard: {
        matchVisual: false,
        formats: [
          "bold",
          "italic",
          "underline",
          "align",
          "link",
          "header",
          "list",
        ],
      },
    },
    formats: ["bold", "italic", "underline", "link", "header", "list", "align"],
  };
  if (document.getElementById("editor")) {
    const quill = new Quill("#editor", options);
    quill.root.innerHTML = document.getElementById("editorHTML").value;
  }
}
if (document.getElementById("editorHTML")) {
  quillEditor();
}
