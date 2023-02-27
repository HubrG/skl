import axios from "axios";
export function Navbar() {
  //! Clear notification number
  const notificationButton = document.getElementById(
    "notification-menu-button"
  );
  notificationButton.addEventListener("click", () => {
    const notificationNbr = document.getElementById("nbr-notification");
    if (notificationNbr) {
      notificationNbr.remove();
      // On demande à axios d'appeler la route /clearnotification
      axios
        .post("/clearnotification")
        .then((response) => {
          console.log(response);
        })
        .catch((error) => {
          console.log(error);
        });
    }
  });
}
Navbar();
