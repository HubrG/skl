import axios from "axios";
export function darkMode() {
  if (document.getElementById("darkButton")) {
    document
      .getElementById("darkButton")
      .addEventListener("click", function () {
        let html = document.getElementById("html");
        html.classList.toggle("dark");
        let dm = document.getElementById("darkMode");
        if (html.classList.contains("dark")) {
          dm.classList.remove("fa-moon-stars");
          dm.classList.add("fa-sun");
          setDarkmode(1);
        } else {
          dm.classList.remove("fa-sun");
          dm.classList.add("fa-moon-stars");
          setDarkmode(0);
        }
      });
  }
  if (document.getElementById("darkButtonSession")) {
    console.log("ok");
    document
      .getElementById("darkButtonSession")
      .addEventListener("click", function () {
        let html = document.getElementById("html");
        html.classList.toggle("dark");
        let dm = document.getElementById("darkModeSession");
        if (html.classList.contains("dark")) {
          dm.classList.remove("fa-moon-stars");
          dm.classList.add("fa-sun");
          setDarkmode(1);
        } else {
          dm.classList.remove("fa-sun");
          dm.classList.add("fa-moon-stars");
          setDarkmode(0);
        }
      });
  }
}
function setDarkmode(dark) {
  const data = new FormData();
  const url = "/param/user/set";
  data.append("param", "darkmode");
  data.append("value", dark);
  axios
    .post(url, data, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then((response) => {
      // console.log(response.data);
    });
}
darkMode();
