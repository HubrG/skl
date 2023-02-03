//
// ajout de keywords sur AddPublication
//
export function addKeyword() {
  if (document.getElementById("keyValue")) {
    const inputKey = document.getElementById("keyValue");
    const inputPost = document.getElementById("hideId");
    inputKey.addEventListener("keydown", (event) => {
      if (
        event.code === "Space" ||
        event.code === "KeyM" ||
        event.code === "Comma"
      ) {
        if (!document.getElementById("keyw_" + inputKey.value.toUpperCase())) {
          const url =
            "/story/add_key/" + inputPost.value + "/" + inputKey.value;
          axios.post(url).then(function (response) {
            let page;
            if (document.getElementById("editPage")) {
              page = "edit";
            } else {
              page = "add";
            }
            let result = response.data["value"];
            let newKey = document.createElement("div");
            newKey.setAttribute("id", "keyw_" + result.toUpperCase());
            let key = document.createTextNode(result);
            newKey.appendChild(key);
            document.getElementById("keyList").appendChild(newKey);
            document.getElementById("keyw_" + result.toUpperCase()).innerHTML =
              result +
              " &nbsp;<a href='/story/" +
              page +
              "/del_key/" +
              inputPost.value +
              "/" +
              result +
              "'><i class='fa-solid fa-delete-left hover:text-red-400'></i></a>";
            inputKey.value = "";
          });
        } else {
          inputKey.value = "";
        }
      }
    });
    inputKey.addEventListener("keyup", (event) => {
      if (event.code === "Space") {
        inputKey.value = "";
      }
    });
  }
}
addKeyword();
