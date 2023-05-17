let intervalId;
let title = document.title;
let nbrNotif = 0;
let nbrMessage = 0;
export function Notification() {
  if (!document.getElementById("username_login")) {
    return;
  }

  interval();
}
function interval() {
  if (intervalId) {
    clearInterval(intervalId);
  }
  if (stop === "stop") {
    return; // On arrête ici si stop est égal à "stop"
  }
  intervalId = setInterval(() => {
    // const lastMessage = document.getElementById("lastMessage").value;
    // const userTo = document.getElementById("userTo").value;
    // console.log(lastMessage, userTo);
    const inboxData = {
      //   lastMessage: lastMessage,
      //   userTo: userTo,
    };
    const url = "/live/notification";
    axios
      .post(url, inboxData, {
        headers: {
          "Content-Type": "application/json",
        },
      })
      .then((response) => {
        if (response.data.newMessage > 0) {
          document
            .getElementById("icon-new-message")
            .classList.remove("hidden");
          document
            .getElementById("icon-no-new-message")
            .classList.add("hidden");
          nbrMessage = response.data.newMessage + nbrNotif;
          document.title = "(" + nbrMessage + ") " + title;
        } else {
          document.getElementById("icon-new-message").classList.add("hidden");
          document
            .getElementById("icon-no-new-message")
            .classList.remove("hidden");
          document.title = document.title;
        }
      });
  }, 5000);
}
