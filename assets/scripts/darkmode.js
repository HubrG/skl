export function darkMode() {
  if (document.getElementById("darkButton")) {
    document.getElementById("darkButton").addEventListener("click", ok);
    let html2 = document.getElementById("html");
    let dm = document.getElementById("darkMode");
    if (html2.classList.contains("dark")) {
      dm.innerHTML = "clair";
    } else {
      dm.innerHTML = "sombre";
    }
  }
}
export function ok() {
  let html = document.getElementById("html");
  html.classList.toggle("dark");
  let dm = document.getElementById("darkMode");
  if (html.classList.contains("dark")) {
    dm.innerHTML = "clair";
  } else {
    dm.innerHTML = "sombre";
  }
}
darkMode();
