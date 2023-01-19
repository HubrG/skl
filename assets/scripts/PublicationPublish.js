export function PublicationPublishButton() {
  if (document.getElementById("PublicationPublishButton")) {
    let publish = document.getElementById("PublicationPublishButton");
    publish.addEventListener("click", action);
    let publishText = document.getElementById("publishText");
    let publishButton = document.getElementById("publishButton");
    let publishDiv = document.getElementById("publishDiv");
    let publishToggle = document.getElementById("publishToggle");
    let publishDateText = document.getElementById("publishDateText");
    let PublicationPublishModalText = document.getElementById(
      "PublicationPublishModalText"
    );
    if (publishButton.checked) {
      PublicationPublishModalText.innerHTML =
        "Êtes-vous certain(e) de vouloir annuler la publication de votre récit ?";
      publishDiv.classList.add("border-red-500", "bg-red-100");
      publishText.classList.add("text-red-900");
      publishToggle.classList.add("peer-checked:bg-red-600");
      publish.classList.add("bg-red-600", "hover:bg-red-800");
    } else {
      PublicationPublishModalText.innerHTML =
        "Êtes-vous certain(e) de vouloir publier votre récit ?";
      publishDiv.classList.add("bg-green-100", "border-green-500");
      publishText.classList.add("text-green-900");
      publishToggle.classList.add("peer-checked:bg-green-600", "bg-green-200");
      publish.classList.add("bg-green-600", "hover:bg-green-800");
      publishDateText.classList.add("hidden");
    }
  }
}
function action() {
  let publish = document.getElementById("PublicationPublishButton");
  let publishText = document.getElementById("publishText");
  let publishButton = document.getElementById("publishButton");
  let publishDiv = document.getElementById("publishDiv");
  let publishToggle = document.getElementById("publishToggle");
  let publishDateText = document.getElementById("publishDateText");
  let publishDateTextSpan = document.getElementById("publishDateTextSpan");
  let PublicationPublishModalText = document.getElementById(
    "PublicationPublishModalText"
  );

  if (!publishButton.checked) {
    publishButton.checked = true;
    publishDateTextSpan.innerHTML = "quelques instants";
    publishText.innerHTML = "Dépublier le récit";
    PublicationPublishModalText.innerHTML =
      "Êtes-vous certain(e) de vouloir annuler la publication de votre récit ?";
    publishDiv.classList.remove("bg-green-100", "border-green-500");
    publishDiv.classList.add("bg-red-100", "border-red-500");
    publishToggle.classList.remove("peer-checked:bg-green-600");
    publishToggle.classList.add("peer-checked:bg-red-600");
    publishText.classList.remove("text-green-900");
    publishText.classList.add("text-red-900");
    publish.classList.remove("bg-green-600", "hover:bg-green-800");
    publish.classList.add("bg-red-600", "hover:bg-red-800");
    publishDateText.classList.remove("hidden");
  } else {
    publishButton.checked = false;
    publishText.innerHTML = "Publier le récit";
    PublicationPublishModalText.innerHTML =
      "Êtes-vous certain(e) de vouloir publier votre récit ?";
    publishToggle.classList.remove("peer-checked:bg-red-600");
    publishToggle.classList.add("bg-green-200", "peer-checked:bg-green-600");
    publishDiv.classList.remove("bg-red-100", "border-red-500");
    publishDiv.classList.add("bg-green-100", "border-green-500");
    publishText.classList.remove("text-red-900");
    publishText.classList.add("text-green-900");
    publish.classList.remove("bg-red-600", "hover:bg-red-800");
    publish.classList.add("bg-green-600", "hover:bg-green-800");
    publishDateText.classList.add("hidden");
  }
  // envoi des infos en BDD
  let id = document.getElementById("hideId").value;
  let url = "/story/publish";
  let data = new FormData();
  let buttonStatus = publishButton.checked;
  data.append("pub", id);
  data.append("publish", buttonStatus);
  let typeAlert;
  let textAlert;
  axios
    .post(url, data, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then(function (response) {
      typeAlert = "success";
      if (response.data.value == 1) {
        textAlert = "Votre récit a bien été dépublié !";
      } else {
        textAlert = "Votre récit a bien été publié !";
      }
    })
    .catch(function (error) {
      if (error.response) {
        typeAlert = "error";
        textAlert =
          "Une erreur s'est produite, les modifications n'ont pas été enregistrées";
      }
    })
    .then(function (response) {
      new Noty({
        text: textAlert,
        theme: "semanticui",
        progressBar: true,
        timeout: 2500,
        layout: "bottomCenter",
        type: typeAlert,
        closeWith: ["click", "button"],
        animation: {
          open: "animate__animated animate__fadeInUp", // Animate.css class names
          close: "animate__animated animate__fadeOutDown", // Animate.css class names
        },
      }).show();
    });
}
PublicationPublishButton();
