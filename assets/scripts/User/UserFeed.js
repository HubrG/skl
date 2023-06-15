export function UserFeed() {
  const choicesDiv = document.getElementById("filter-user-feed-choices");
  if (!choicesDiv) {
    return;
  }

  //   const CommentsF = document.querySelectorAll("f1");
  //   const ForumMessagesF = document.querySelectorAll("f2");
  //   const ForumTopicsF = document.querySelectorAll("f3");
  //   const PublicationsF = document.querySelectorAll("f4");
  //   const PublicationChapterLikesF = document.querySelectorAll("f5");
  //   const PublicationChaptersF = document.querySelectorAll("f6");
  //   const PublicationBookmarksF = document.querySelectorAll("f7");
  //   const PublicationAnnotationsF = document.querySelectorAll("f8");
  //   const PublicationFollowsF = document.querySelectorAll("f9");
  //   const PublicationDownloadsF = document.querySelectorAll("f10");
  //   const PublicationReadsF = document.querySelectorAll("f11");
  //   const ChallengesF = document.querySelectorAll("f12");
  //   const ChallengeMessagesF = document.querySelectorAll("f13");
  //   const UsersF = document.querySelectorAll("f14");
  const filterButton = document.getElementById("filter-user-feed");
  filterButton.addEventListener("click", function () {
    choicesDiv.classList.toggle("hidden");
  });
  const all = document.getElementById("0");
  const ids = [
    "1",
    "2",
    "3",
    "4",
    "5",
    "6",
    "7",
    "8",
    "9",
    "10",
    "11",
    "12",
    "13",
    "14",
  ];

  const checkboxes = ids.map((id) => document.getElementById(id));
  const feedElements = ids.map((id) => document.querySelectorAll(`.f${id}`));

  function hideOrShowElements(elements, show) {
    elements.forEach((element) => {
      if (show) {
        element.style = "display:block !important";
      } else {
        element.style = "display:none !important";
      }
    });
  }

  all.addEventListener("change", function () {
    if (all.checked) {
      checkboxes.forEach((checkbox) => {
        checkbox.checked = false;
      });

      // Afficher tous les éléments lors de la sélection de "Tout"
      feedElements.forEach((elements) => hideOrShowElements(elements, true));
    }
  });

  ids.forEach((id, index) => {
    let element = checkboxes[index];
    let correspondingElements = feedElements[index];

    element.addEventListener("change", function () {
      if (element.checked) {
        all.checked = false;
        // Afficher uniquement les éléments correspondants et cacher tous les autres
        hideOrShowElements(correspondingElements, true);
      } else {
        // Si aucune case n'est cochée, sélectionner 'Tout' et afficher tous les éléments
        if (
          [
            ...document.querySelectorAll(
              "#filter-user-feed-choices input:checked"
            ),
          ].length === 0
        ) {
          all.checked = true;
          feedElements.forEach((elements) =>
            hideOrShowElements(elements, true)
          );
        } else {
          hideOrShowElements(correspondingElements, false);
        }
      }
      // Pour chaque case non cochée, cachez les éléments correspondants
      checkboxes.forEach((checkbox, i) => {
        if (!checkbox.checked) {
          hideOrShowElements(feedElements[i], false);
        }
      });
    });
  });
}
