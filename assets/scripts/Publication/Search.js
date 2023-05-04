export function Search() {
  if (!document.getElementById("search-form")) return;

  const submited = document.getElementById("submited");
  if (
    submited.getAttribute("data-submited") == "ok" &&
    window.innerWidth < 1024
  ) {
    var element = document.getElementById("results");
    element.scrollIntoView({ behavior: "smooth" });
  }

  // Sélectionnez le formulaire en utilisant l'identifiant que vous avez ajouté
  var form = document.getElementById("search-form");

  // Stockez le dernier input modifié
  var lastModifiedInput = null;
  function submitForm() {
    document.getElementById("submit-form").click();
  }

  function refocusSearchTextElement() {
    if (lastModifiedInput && lastModifiedInput.type === "text") {
      // Remettez le focus sur l'élément avec l'ID "searchText"
      var searchTextElement = document.getElementById("searchText");
      if (searchTextElement && searchTextElement.value != "") {
        searchTextElement.focus();
        // Positionnez le curseur à la fin du texte déjà écrit
        var textLength = searchTextElement.value.length;
        searchTextElement.setSelectionRange(textLength, textLength);
      }
    }
  }

  var textInputs = form.querySelectorAll('input[type="text"], textarea');
  var timeoutId;

  textInputs.forEach(function (input) {
    input.addEventListener("input", function () {
      if (input.classList.contains("no-search")) return;
      lastModifiedInput = input;

      // Annulez le précédent setTimeout, s'il en existe un
      if (timeoutId) {
        clearTimeout(timeoutId);
      }

      // Créez un nouveau setTimeout et stockez son ID
      timeoutId = setTimeout(function () {
        submitForm();
      }, 500);
    });
  });

  // Ajoutez un écouteur d'événements 'change' pour les cases à cocher
  var checkboxes = form.querySelectorAll('input[type="checkbox"]');
  checkboxes.forEach(function (checkbox) {
    checkbox.addEventListener("change", function () {
      lastModifiedInput = false;
      submitForm();
    });
  });

  // Ajoutez un écouteur d'événements 'change' pour les cases à cocher
  var radios = form.querySelectorAll('input[type="radio"]');
  radios.forEach(function (radio) {
    radio.addEventListener("change", function () {
      lastModifiedInput = false;
      submitForm();
    });
  });

  // Ajoutez un écouteur d'événements 'change' pour l'élément select
  var select = form.querySelectorAll("select");
  select.forEach(function (select) {
    select.addEventListener("change", function () {
      lastModifiedInput = false;
      submitForm();
    });
  });

  // Ajoutez un écouteur d'événements 'turbo:before-cache' pour remettre le focus sur l'élément avec l'ID "searchText" avant que la page ne soit mise en cache
  document.addEventListener("turbo:load", function () {
    refocusSearchTextElement();
  });
}
Search();
