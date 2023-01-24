// autosave pour l'édition d'un récit
// TODO: À reproduire pour tous les autres autosave, modifier : le "pubMature" (checkbox) et le lien (url)
export function axiosSave(value, name, file, url) {
  let filename =
    document.getElementById("hideId").value + Math.floor(Math.random() * 9999);
  let path =
    "/images/uploads/story/" + document.getElementById("hideId").value + "/";
  let data = new FormData();
  data.append("name", name);
  data.append("value", value);
  data.append("file", file);
  data.append("filename", filename);
  axios
    .post(url, data, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then(function (response) {
      console.log(response.data.code);
      if (response.data.code == 200) {
        if (file) {
          document.getElementById("cover").src = path + filename + ".jpg";
          if (document.getElementById("hideNoCover")) {
            let hideNoCover = document.getElementById("hideNoCover");
            let showNewCover = document.getElementById("showNewCover");
            if (showNewCover.classList.contains("hidden")) {
              hideNoCover.classList.add("hidden");
              showNewCover.classList.remove("hidden");
            }
          }
        }
      } else {
        new Noty({
          text: "<i class='fa-solid fa-triangle-exclamation'></i> &nbsp;Une erreur est survenue, avez-vous bien essayé de charger une image ?",
          theme: "semanticui",
          progressBar: true,
          timeout: 5500,
          layout: "bottomCenter",
          type: "error",
          closeWith: ["click", "button"],
          animation: {
            open: "animate__animated animate__fadeInUp", // Animate.css class names
            close: "animate__animated animate__fadeOutDown", // Animate.css class names
          },
        }).show();
      }
      //
    });
}
export function axiosEvent() {
  document.querySelectorAll(".axiosPublication").forEach(function (row) {
    let timeout;
    const url = "/story/as/" + document.getElementById("hideId").value;
    row.addEventListener("change", () => {
      let file;
      if (row.files) {
        file = row.files[0];
      } else {
        file = "";
      }
      let pubMature = document.getElementById("publication_mature");
      if (pubMature.checked) {
        pubMature.value = 1;
      } else {
        pubMature.value = 0;
      }
      clearTimeout(timeout);
      axiosSave(row.value, row.name, file, url);
    });
    row.addEventListener("keyup", () => {
      clearTimeout(timeout);
      let file;
      timeout = setTimeout(() => {
        axiosSave(row.value, row.name, file, url);
      }, 5000);
    });
  });
}
axiosEvent();
