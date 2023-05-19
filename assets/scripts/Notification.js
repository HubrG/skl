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
    }
  }
}
