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
          dm.innerHTML = "wb_sunny";
          setDarkmode(1);
        } else {
          dm.innerHTML = "nightlight_badge";
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
      console.log(response.data);
    });
}
darkMode();
