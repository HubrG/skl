import axios from "axios";

function submitForm() {
  document.getElementById("submit-form").click();
}
function createKeywordTag(keyword) {
  const selectedKeyword = document.createElement("span");
  selectedKeyword.classList.add("kwTag");

  const keywordText = document.createElement("span");
  keywordText.textContent = keyword;

  selectedKeyword.appendChild(keywordText);
  return selectedKeyword;
}
function addClickEventToKwTag(selectedKeyword, keyword) {
  const keywordInputValue = document.getElementById("keyword");
  selectedKeyword.addEventListener("click", () => {
    selectedKeyword.remove(); // Supprimer le mot-clé de la liste
    // On supprime le mot clé de la liste des mots clés dans l'input hidden
    keywordInputValue.value = keywordInputValue.value.replace(
      keyword + ",",
      ","
    );
    if (!document.querySelector(".kwTag")) {
      keywordInputValue.value = "";
    }
    submitForm();
    // keywords.push(keyword); // Remettre le mot clé dans la liste des suggestions
  });
}

function handleKeywordClick(keyword, keywordInput, kwSelected, listItem) {
  const selectedKeyword = createKeywordTag(keyword);
  keywordInput.value = "";

  const keywordInputValue = document.getElementById("keyword");
  keywordInputValue.value += keyword + ",";

  document.getElementById("suggestion-list").classList.add("hidden");

  addClickEventToKwTag(selectedKeyword, keyword);

  kwSelected.appendChild(selectedKeyword);

  listItem.remove(); // Supprimer la suggestion de la liste
}

function generateSuggestionList(keywords, initialKeywordList = []) {
  const addedKeywords = Array.from(
    document.querySelectorAll("#kwSelected span.kwTag span")
  ).map((el) => el.textContent);
  const allAddedKeywords = [...addedKeywords, ...initialKeywordList];
  const filteredKeywords = keywords.filter(
    (kw) => !allAddedKeywords.includes(kw)
  );
  return filteredKeywords;
}

function findKwTag(keyword) {
  const kwTags = document.querySelectorAll(".kwTag");
  let foundTag = null;

  kwTags.forEach((tag) => {
    if (tag.textContent.trim() === keyword) {
      foundTag = tag;
    }
  });

  return foundTag;
}

export function searchPredictionKw() {
  if (!document.getElementById("keyword-input")) return;
  // Get initial keyword list from the hidden input
  const keywordInputValue = document.getElementById("keyword");
  const initialKeywordList = keywordInputValue.value.split(",");

  if (initialKeywordList.length > 0) {
    initialKeywordList.forEach((keyword) => {
      if (keyword) {
        const existingKwTag = findKwTag(keyword);
        if (existingKwTag) {
          addClickEventToKwTag(existingKwTag, keyword);
        }
      }
    });
  }
  // !
  axios
    .post("/search/getkw", [], {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then((response) => {
      let kws = response.data.keywords;
      let keywords = kws.map((kw) => kw.name);

      const keywordInput = document.getElementById("keyword-input");
      const suggestionList = document.getElementById("suggestion-list");
      const kwSelected = document.getElementById("kwSelected");
      const arrowIcon = document.getElementById("arrow-icon");
      // Au chargement de la page
      const initialKeywords = document.getElementById("keyword").value;
      const initialKeywordList = initialKeywords
        ? initialKeywords.split(",")
        : [];
      //

      const handleBlur = () => {
        setTimeout(() => {
          suggestionList.classList.add("hidden");
        }, 100);
      };

      keywordInput.addEventListener("focus", () => {
        keywordInput.removeEventListener("blur", handleBlur);
        keywordInput.addEventListener("blur", handleBlur);
      });

      keywordInput.addEventListener("input", (e) => {
        const searchTerm = e.target.value.trim().toLowerCase();
        suggestionList.innerHTML = "";

        if (searchTerm !== "") {
          const filteredKeywords = generateSuggestionList(
            keywords,
            initialKeywordList
          );
          const suggestions = filteredKeywords.filter((keyword) =>
            keyword.toLowerCase().includes(searchTerm)
          );
          suggestions.forEach((suggestion) => {
            const listItem = document.createElement("li");
            listItem.textContent = suggestion;
            listItem.classList.add("suggestion-list-item");

            listItem.addEventListener("mouseup", (e) => {
              e.preventDefault();
              handleKeywordClick(
                suggestion,
                keywordInput,
                kwSelected,
                listItem
              );
              keywordInput.focus();
              submitForm();
            });

            suggestionList.appendChild(listItem);
          });
          suggestionList.classList.remove("hidden");
        } else {
          suggestionList.classList.add("hidden");
        }
      });

      arrowIcon.addEventListener("click", (e) => {
        e.stopPropagation();

        if (suggestionList.classList.contains("hidden")) {
          suggestionList.innerHTML = "";
          const filteredKeywords = generateSuggestionList(
            keywords,
            initialKeywordList
          );
          filteredKeywords.forEach((keyword) => {
            const listItem = document.createElement("li");
            listItem.textContent = keyword;
            listItem.classList.add(
              "p-2",
              "cursor-pointer",
              "hover:bg-gray-200"
            );

            listItem.addEventListener("mousedown", (e) => {
              e.preventDefault();
              handleKeywordClick(keyword, keywordInput, kwSelected, listItem);
              keywordInput.focus();
              submitForm();
            });
            suggestionList.appendChild(listItem);
          });
          suggestionList.classList.remove("hidden");
        } else {
          suggestionList.classList.add("hidden");
        }
      });
      keywordInput.addEventListener("blur", handleBlur);
    });
}
searchPredictionKw();
