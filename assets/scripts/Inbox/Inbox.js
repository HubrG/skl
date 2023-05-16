let intervalId;

export function Inbox() {
  if (!document.getElementById("inbox")) {
    clearInterval(intervalId);
    return;
  }
  interval();

  //  ! Traitement de la recherche d'un utilisateur
  const searchUser = document.getElementById("chosenUser");
  if (searchUser) {
    searchUser.addEventListener("keyup", (event) => {
      axiosSearchUser(searchUser.value);
    });
  }
  // ! Traitement du ReadAt
  // Au clic sur une conversation
  const oneConversation = document.querySelectorAll(".one-conversation");
  oneConversation.forEach((element) => {
    element.addEventListener("click", (event) => {
      axiosReadAt(
        element.getAttribute("data-id"),
        document.getElementById("nbrMessage-" + element.getAttribute("data-id"))
      );
    });
  });
  // Au survol s'une conversation
  const messagesFrame = document.getElementById("messages-frame");
  if (messagesFrame) {
    messagesFrame.addEventListener("click", (event) => {
      axiosReadAt(
        messagesFrame.getAttribute("data-id"),
        document.getElementById(
          "nbrMessage-" + messagesFrame.getAttribute("data-id")
        )
      );
    });
  }
  // Au clic sur l'input
  const inboxContent = document.getElementById("inbox_content");
  if (inboxContent) {
    inboxContent.addEventListener("focus", (event) => {
      axiosReadAt(
        inboxContent.getAttribute("data-id"),
        document.getElementById(
          "nbrMessage-" + inboxContent.getAttribute("data-id")
        )
      );
    });
  }
}

function displayResults(users) {
  const searchUser = document.getElementById("chosenUser");

  const resultsDiv = document.getElementById("results");
  const hiddenInputElement = document.getElementById(
    "inbox_new_message_UserTo"
  );
  resultsDiv.classList.remove("hidden");
  // Effacez d'abord les résultats précédents
  resultsDiv.innerHTML = "";

  // Créez une liste non ordonnée pour afficher les utilisateurs
  const ul = document.createElement("ul");

  users.forEach((user) => {
    const li = document.createElement("li");
    li.textContent = user.nickname + " (@" + user.username + ")"; // Utilisez la propriété appropriée pour le nom d'utilisateur
    li.setAttribute("data-id", user.id); // Stockez l'ID utilisateur dans l'attribut data-id
    li.style.cursor = "pointer"; // Changez le curseur pour indiquer que l'élément est cliquable

    // Ajoutez un écouteur d'événement pour détecter les clics sur cet élément
    // Ajoutez un écouteur d'événement pour détecter les clics sur cet élément
    li.addEventListener("click", (event) => {
      const userId = event.target.getAttribute("data-id");
      const username = user.nickname;

      // Obtenez l'élément select
      const selectElement = document.getElementById("inbox_new_message_UserTo");

      // Parcourez les options du select
      for (let i = 0; i < selectElement.options.length; i++) {
        if (selectElement.options[i].value == userId) {
          selectElement.selectedIndex = i;
          break;
        }
      }

      searchUser.value = username; // Mettez à jour la valeur de l'élément de recherche
      resultsDiv.classList.add("hidden"); // Masquer les résultats
    });

    ul.appendChild(li);
  });

  resultsDiv.appendChild(ul);
}
function axiosSearchUser(user) {
  const url = "/inbox/search_user";
  const data = {
    searchUser: user,
  };
  axios
    .post(url, data, {
      headers: {
        "Content-Type": "application/json",
      },
    })
    .then((response) => {
      // console.log(response.data);
      var test = response.data.users;
      console.log(test);
      displayResults(test);

      // document.getElementById("inbox_new_message_UserTo").value =
    });
}
function axiosReadAt(userTo, clean) {
  const url = "/read_at";
  const data = {
    userTo: userTo,
  };
  axios
    .post(url, data, {
      headers: {
        "Content-Type": "application/json",
      },
    })
    .then((response) => {
      clean.classList.add("hidden");
    });
}
function scrollToBottom(element) {
  element.scrollTop = element.scrollHeight;
}
function interval() {
  if (intervalId) {
    clearInterval(intervalId);
  }
  if (stop === "stop") {
    return; // On arrête ici si stop est égal à "stop"
  }
  intervalId = setInterval(() => {
    if (document.getElementById("lastMessage")) {
      const lastMessage = document.getElementById("lastMessage").value;
      const userTo = document.getElementById("userTo").value;
      const inboxData = {
        lastMessage: lastMessage,
        userTo: userTo,
      };
      const url = "/reload-inbox";
      axios
        .post(url, inboxData, {
          headers: {
            "Content-Type": "application/json",
          },
        })
        .then((response) => {
          var nbUnreadMessages = response.data.nbUnreadMessages;
          // Itérer sur les entrées de nbUnreadMessages (clés et valeurs)
          for (const [key, value] of Object.entries(nbUnreadMessages)) {
            if (value > 0) {
              document.getElementById("nbrMessage-" + key).innerHTML = value;
              // const messagesFrame = document.getElementById("messages-frame");
              // if (messagesFrame.getAttribute("data-id") != key) {
              document
                .getElementById("nbrMessage-" + key)
                .classList.remove("hidden");
              // }
            }
          }
          if (response.data.message == true) {
            document.getElementById("reload").click();
          }
        });
    }
  }, 1000);
}
document.addEventListener("turbo:load", () => {
  if (!document.getElementById("inbox")) {
    clearInterval(intervalId);
    return;
  }
  const messagesFrame = document.getElementById("messages-frame");
  if (messagesFrame) {
    scrollToBottom(messagesFrame);
  }
});

document.addEventListener("turbo:frame-render", () => {
  if (!document.getElementById("inbox")) {
    clearInterval(intervalId);
    return;
  }
  const messagesFrame = document.getElementById("messages-frame");
  if (messagesFrame) {
    scrollToBottom(messagesFrame);
  }
});
