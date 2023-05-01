export function Tabs() {
  const tab = document.querySelectorAll(".tab-button");
  if (!tab) {
    return;
  }
  document.addEventListener("DOMContentLoaded", async function () {
    if (window.location.hash) {
      const hash = window.location.hash;
      const tab = document.querySelector(hash);
      if (tab) {
        // Délai de 300 ms (ou toute autre durée) avant de déclencher le clic
        setTimeout(() => {
          tab.dispatchEvent(new MouseEvent("click"));
        }, 300);
      }
    }
  });
  tab.forEach((tab) => {
    tab.addEventListener("click", (event) => {
      history.pushState(null, null, "#" + tab.id);
    });
  });
}
Tabs();
