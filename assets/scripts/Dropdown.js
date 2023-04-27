export function Dropdown() {
  if (!document.querySelector(".dropdown-button")) return;
  const button = document.querySelectorAll(".dropdown-button");
  const dropdownmenu = document.querySelectorAll(".dropdown-content");
  let activeDropdown = null;
  //
  button.forEach((el) => {
    el.addEventListener("click", function () {
      // On ferme tous les menus déroulants
      dropdownmenu.forEach((ell) => {
        if (!ell.classList.contains("hidden")) {
          ell.classList.add("hidden");
        }
      });
      const elId = el.id.split("-")[1];
      const content = document.getElementById("ddm-" + elId);
      if (activeDropdown) {
        activeDropdown.classList.add("hidden");
      }
      if (activeDropdown !== content) {
        content.classList.remove("hidden");
        activeDropdown = content;
      } else {
        activeDropdown = null;
      }
    });
  });
  // On ajoute un événement "click" à chaque menu déroulant pour empêcher la propagation de l'événement
  dropdownmenu.forEach((el) => {
    el.addEventListener("click", function (event) {
      event.stopPropagation();
    });
  });
  // On ajoute l'événement "click" à la fenêtre
  window.addEventListener("click", function () {
    if (activeDropdown) {
      activeDropdown.classList.add("hidden");
      activeDropdown = null;
    }
  });
}
Dropdown();
