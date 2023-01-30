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
  }
  var html = document.getElementById("editorHTML").value;

  init(html, options);
}
function init(html, options) {
  var quill = new Quill("#editor", options);
  var delta = quill.clipboard.convert(html);
  quill.setContents(delta, "silent");
}
if (document.getElementById("editorHTML")) {
  quillEditor();
}
