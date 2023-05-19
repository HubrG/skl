let intervalId;
let lastMessage;
export function Inbox() {
  if (!document.getElementById("inbox")) {
    clearInterval(intervalId);
    return;
  }

  scrollToBottom();

  interval();

  // ! Traitement du ReadAt
  // Au clic sur une conversation
  const oneConversation = document.querySelectorAll(".one-conversation");
  oneConversation.forEach((element) => {
    element.addEventListener("click", (event) => {
      axiosReadAt(element.getAttribute("data-id"));
    });
  });
  // Au clic sur la fenêtre de conversation
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

function axiosReadAt(group, clean) {
  const url = "/read_at";
  const data = {
    group: group,
  };
  axios
    .post(url, data, {
      headers: {
        "Content-Type": "application/json",
      },
    })
    .then((response) => {
      if (clean) {
        clean.classList.add("hidden");
      }
    });
}
function scrollToBottom(element) {
  var div = document.getElementById("messages-scroll");
  div.scrollTop = div.scrollHeight;
  if (document.getElementById("inbox_content")) {
    document.getElementById("inbox_content").focus();
  }
}
function interval() {
  if (intervalId) {
    clearInterval(intervalId);
  }
  intervalId = setInterval(() => {
    if (document.querySelector(".last-message")) {
      var elements = document.querySelectorAll(".last-message");
      var lastElement = elements[elements.length - 1];
      var lastElementTime = lastElement.getAttribute("data-time");
      if (lastElementTime != lastMessage) {
        lastMessage = lastElementTime;
        // document.getElementById("reload-message-frame").click();
        scrollToBottom();
      }
    }
  }, 100);
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
