import Quill from "quill";
export function AxiosSaveChapter() {
  document.querySelectorAll(".axiosChapter").forEach(function (row) {
    let timeout;
    // ! Save
    document.getElementById("saveChapter").addEventListener("click", () => {
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        AxiosChapter(row);
      }, 0);
    });
    // ! Autosave
    row.addEventListener("keyup", () => {
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        AxiosChapter(row);
      }, 60000);
    });
    // ! récupération d'une version précédente
    document
      .getElementById("selectChapVersion")
      .addEventListener("change", () => {
        AxiosGetVersion(document.getElementById("selectChapVersion").value);
      });
  });
}
function AxiosChapter(row) {
  const url = "/story/chapter/autosave";
  // * récupération du contenu du quill
  let data = new FormData();
  var quill = document.getElementById("editor").__quill;
  var valueQuill = quill.root.innerHTML;
  var name = row.id;
  var value = row.value;
  // on prépare les données à envoyer
  // * on envoie le contenu
  data.append("name", name);
  data.append("value", value);
  data.append("valueQuill", valueQuill);
  if (document.getElementById("hideIdChap")) {
    data.append("idPub", document.getElementById("hideIdPub").value);
    data.append("idChap", document.getElementById("hideIdChap").value);
  } else {
    data.append("idChap", 0);
  }
  axios
    .post(url, data, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then(function (response) {
      console.log(response.data.code);
      new Noty({
        text: "Publication enregistrée !",
        theme: "semanticui",
        progressBar: true,
        timeout: 4500,
        layout: "bottomCenter",
        type: "success",
        closeWith: ["click", "button"],
        animation: {
          open: "animate__animated animate__fadeInUp", // Animate.css class names
          close: "animate__animated animate__fadeOutDown", // Animate.css class names
        },
      }).show();
    });
}
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
      new Noty({
        text: "Version chargée ! Vous pouvez toujours revenir à la version précédente en selectionnant la version la plus récente dans la liste déroulante.",
        theme: "semanticui",
        progressBar: true,
        timeout: 10000,
        layout: "bottomCenter",
        type: "success",
        closeWith: ["click", "button"],
        animation: {
          open: "animate__animated animate__fadeInUp", // Animate.css class names
          close: "animate__animated animate__fadeOutDown", // Animate.css class names
        },
      }).show();
    });
}
AxiosSaveChapter();
