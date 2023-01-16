// autosave pour l'édition d'un récit
// TODO: À reproduire pour tous les autres autosave, modifier : le "pubMature" (checkbox) et le lien (url)
export function axiosSave(value, name, file, url) {
  let data = new FormData();
  data.append("name", name);
  data.append("value", value);
  data.append("file", file);
  axios
    .post(url, data, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then(function (response) {
      //
    });
}
export function axiosEvent() {
  document.querySelectorAll(".axios").forEach(function (row) {
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
