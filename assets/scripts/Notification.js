let intervalId;
import { NotyDisplay } from "./Noty";
let firstLoad = true;
let nbrNotif = 0;
let nbrMessage = 0;

export function Notification() {
  if (!document.getElementById("username_login")) {
    return;
  }
  const titled = document.title;
  refreshInbox(titled);
  refreshNotif(titled);
  interval(titled);
}
function interval(titled) {
  if (intervalId) {
    clearInterval(intervalId);
  }
  if (stop === "stop") {
    return; // On arrête ici si stop est égal à "stop"
  }
  intervalId = setInterval(() => {
    refreshInbox(titled);
    refreshNotif(titled);
  }, 1000);
}
function refreshInbox(titled) {
  let newTitle;
  if (document.getElementById("nbrInbox")) {
    nbrMessage = parseInt(
      document.getElementById("nbrInbox").getAttribute("data-nbr") || 0
    );
    let totalNbr = nbrMessage + nbrNotif;
    newTitle = totalNbr > 0 ? "(" + totalNbr + ") " + titled : titled;
    document.title = newTitle;
    // Modification de l'icone smartphone (dot)
    if (totalNbr > 0) {
      document.getElementById("notif-sm").classList.remove("hidden");
      document.getElementById("notif-sm").innerHTML = totalNbr;
    } else {
      document.getElementById("notif-sm").classList.add("hidden");
    }
  } else {
    document.getElementById("notif-sm").classList.add("hidden");
  }
}

function refreshNotif(titled) {
  let newTitle;
  if (document.getElementById("notification-menu-button")) {
    let nbrNotifold = nbrNotif;
    nbrNotif = parseInt(
      document
        .getElementById("notification-menu-button")
        .getAttribute("data-nbr") || 0
    );
    if (!firstLoad && nbrNotif > nbrNotifold) {
      let notyInstance = new Noty({
        text:
          '<i class="fa-duotone fa-bells"></i><br>' +
          document.querySelector("#last-notif>div>span>div").textContent +
          " — <strong>Voir</strong>",
        theme: "semanticui",
        progressBar: true,
        timeout: 3000,
        layout: "bottomCenter",
        type: "warning",
        closeWith: ["click", "button"],
        animation: {
          open: "animate__animated animate__fadeInUp", // Animate.css class names
          close: "animate__animated animate__fadeOutDown", // Animate.css class names
        },
        callbacks: {
          onClick: function () {
            // AU clic, on envoie sur la page des notifications
            let links = document.querySelectorAll(
              "#last-notif>div>span>div>a[data-notif-clic]"
            );
            links.forEach((link) => {
              var linked = link.getAttribute("href");
              window.top.location.href = linked;
            });
          },
        },
      });

      notyInstance.show();
    }
    firstLoad = false;

    let totalNbr = nbrMessage + nbrNotif;
    newTitle = totalNbr > 0 ? "(" + totalNbr + ") " + titled : titled;
    document.title = newTitle;
    // Modification de l'icone smartphone (dot)
    if (totalNbr > 0) {
      document.getElementById("notif-sm").classList.remove("hidden");
      document.getElementById("notif-sm").innerHTML = totalNbr;
    } else {
      document.getElementById("notif-sm").classList.add("hidden");
    }
  } else {
    document.getElementById("notif-sm").classList.add("hidden");
  }
}
