let intervalId;

let nbrNotif = 0;
let nbrMessage = 0;
export function Notification() {
  if (!document.getElementById("username_login")) {
    return;
  }

  const titled = document.title;
  refreshInbox(titled);
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
  }, 1000);
}
function refreshInbox(titled) {
  let newTitle;
  if (document.getElementById("nbrInbox")) {
    if (document.getElementById("nbrInbox").getAttribute("data-nbr") > 0) {
      let nbrMessage =
        parseInt(document.getElementById("nbrInbox").getAttribute("data-nbr")) +
        nbrNotif;
      newTitle = "(" + nbrMessage + ") " + titled;
      document.title = newTitle;
      // Modification de l'icone smartphone (dot)
      if (nbrMessage > 0) {
        document.getElementById("notif-sm").classList.remove("hidden");
        document.getElementById("notif-sm").innerHTML = nbrMessage;
      } else {
        document.getElementById("notif-sm").classList.add("hidden");
      }
    }
  } else {
    document.getElementById("notif-sm").classList.add("hidden");
  }
}
