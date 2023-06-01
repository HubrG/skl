let intervalId;
import { NotyDisplay } from "./Noty";
let firstLoad = true;
let nbrNotif = 0;
let nbrMessage = 0;
let audio = new Audio("/sons/notif.mp3");
let soundEnabled = false;

export function Notification() {
  if (!document.getElementById("username_login")) {
    return;
  }
  const titled = document.title;
  refreshInbox(titled);
  refreshNotif(titled);
  interval(titled);
  // ! Activation du son
  document
    .getElementById("enable-sound")
    .addEventListener("click", function () {
      console.log("ok");
      // Tenter de jouer le son pour obtenir l'autorisation de l'utilisateur
      let playPromise = audio.play();

      if (playPromise !== undefined) {
        playPromise
          .then(function () {
            // L'autorisation a été accordée
            soundEnabled = true;
          })
          .catch(function (error) {
            // L'autorisation a été refusée
            console.log("Lecture automatique du son refusée");
          });
      }
    });
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
      NotyDisplay(
        '<i class="fa-duotone fa-bells"></i><br>' +
          document.getElementById("last-notif").textContent,
        "warning",
        3000
      );
      audio.play(); // play the sound
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
