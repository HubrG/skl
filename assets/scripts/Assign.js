export function Assign() {
  let textareas = document.querySelectorAll(".assign-user");
  if (textareas.length === 0) return;
  textareas.forEach((textarea) => {
    let sibling = textarea.nextElementSibling;

    while (sibling) {
      if (sibling.classList.contains("assign-user-dropdown")) {
        break;
      }
      sibling = sibling.nextElementSibling;
    }

    let dropdown = sibling;

    if (!dropdown) {
      console.error("No dropdown found for textarea", textarea);
      return;
    }

    let currentSelectionIndex = -1; // index of the currently selected user

    textarea.addEventListener("input", function (e) {
      let value = e.target.value;
      let searchTerm = value.includes("@") ? value.split("@").pop().trim() : "";

      if (searchTerm !== "") {
        axiosAssign(searchTerm, e, dropdown, textarea);
      } else {
        dropdown.innerHTML = "";
      }

      // reset selection index when text changes
      currentSelectionIndex = -1;
    });

    // Keydown listener for arrow keys
    textarea.addEventListener("keydown", function (e) {
      // number of users in dropdown
      const numUsers = dropdown.children.length;

      // arrow down
      if (e.keyCode === 40 && dropdown.style.display !== "none") {
        // remove the highlight from the currently selected user
        if (currentSelectionIndex !== -1) {
          dropdown.children[currentSelectionIndex].classList.remove(
            "highlight"
          );
        }
        // update current selection index
        currentSelectionIndex = (currentSelectionIndex + 1) % numUsers;
      }

      // arrow up
      else if (e.keyCode === 38 && dropdown.style.display !== "none") {
        // remove the highlight from the currently selected user
        if (currentSelectionIndex !== -1) {
          dropdown.children[currentSelectionIndex].classList.remove(
            "highlight"
          );
        }
        // update current selection index
        currentSelectionIndex--;
        if (currentSelectionIndex < 0) currentSelectionIndex = numUsers - 1;
      }

      // highlight the newly selected user
      if (
        numUsers > 0 &&
        dropdown.style.display !== "none" &&
        dropdown.children[currentSelectionIndex] // check that the element exists
      ) {
        dropdown.children[currentSelectionIndex].classList.add("highlight");
      }

      // if enter is pressed, select the user
      if (
        e.keyCode === 13 &&
        currentSelectionIndex !== -1 &&
        dropdown.style.display !== "none"
      ) {
        e.preventDefault(); // prevent form submission
        dropdown.children[currentSelectionIndex].click();
      }
    });
    document.addEventListener("click", function (e) {
      if (dropdown) {
        const isClickInsideDropdown = dropdown.contains(e.target);
        if (!isClickInsideDropdown) {
          dropdown.style.display = "none";
        }
      }
    });
  });
}

function axiosAssign(searchTerm, event, dropdown, textarea) {
  axios
    .get("/api/users", {
      params: {
        username: searchTerm,
      },
    })
    .then((response) => {
      // clear out old results
      dropdown.innerHTML = "";
      // iterate over each user and create a new div for them
      response.data["hydra:member"].forEach((user) => {
        const div = document.createElement("div");
        if (user.profil_picture != null) {
          div.innerHTML =
            "<img src='" +
            user.profil_picture +
            "' class='h-5 w-5 rounded-full'>" +
            user.nickname +
            " <small>(@" +
            user.username +
            ")</small>";
        } else {
          // On crée une div avec l'initiale du nom d'utilisateur
          div.innerHTML =
            `<div class="avatar-assign-user">
              <div class="text-sm text-gray-700">
                ${user.nickname.charAt(1)}
              </div>
            </div>` +
            user.nickname +
            " <small>(@" +
            user.username +
            ")</small>";
        }
        div.addEventListener("click", () => {
          textarea.value = textarea.value.replace(
            /@(\w+)$/,
            `@${user.username} `
          );
          dropdown.style.display = "none";
          textarea.focus();
        });
        dropdown.appendChild(div);
      });

      // show dropdown if there are results
      if (response.data["hydra:member"].length > 0) {
        // Obtenir la position du curseur
        var x = event.clientX;
        var y = event.clientY;
        //
        dropdown.style.display = "block";
        dropdown.style.position = "absolute";
        dropdown.style.left = x + "px";
        dropdown.style.top = y + textarea.offsetHeight + "px";
      } else {
        dropdown.style.display = "none";
      }
    });
}

Assign();
