import Quill from "quill";
import { NotyDisplay } from "./Noty";
import { axiosChapter } from "./Publication/Chapter";

export function quillEditor() {
  if (!document.getElementById("editor")) {
    return;
  }
  var toolbarOptions = [
    ["bold", "italic", "underline", "strike"], // toggled buttons
    ["blockquote"],
    [{ size: ["small", false, "large", "huge"] }],
    [{ header: [2, 3, 4, 5, 6], size: ["small", false, "large", "huge"] }], // custom button values
    [{ list: "ordered" }, { list: "bullet" }],
    ["link", "image"],
    [{ align: [] }],
    ["clean"], // remove formatting button
  ];
  var options = {
    placeholder: "Contenu de votre chapitre...",
    theme: "bubble",
    modules: {
      toolbar: toolbarOptions,
      clipboard: {
        matchVisual: false,
        formats: ["bold", "italic", "underline", "align", "link", "header"],
      },
    },
    formats: [
      "bold",
      "italic",
      "underline",
      "link",
      "paragraph",
      "header",
      "blockquote",
      "list",
      "align",
      "list",
      "image",
      "size",
    ],
  };
  if (document.getElementById("editor")) {
    const quill = new Quill("#editor", options);

    quill.root.innerHTML = document.getElementById("editorHTML").value;
    let buttonSelector = document.getElementById("button-selector");
    let clickedIndex = null;
    const addImg = document.getElementById("add-img-chapter");

    // Dès que le file chhange, on lance la fonction
    addImg.addEventListener("change", (event) => {
      AddChapterImg(addImg.files[0], "img", quill, clickedIndex);
    });

    document.addEventListener("click", (e) => {
      if (!e.target.closest("p")) {
        buttonSelector.style.display = "none";
      }
    });

    // Affiche le sélecteur de bouton lors d'un clic sur un paragraphe
    document.getElementById("editor").addEventListener("click", (e) => {
      let clickedElement = e.target;
      let paragraphElement = clickedElement.closest("p");

      if (paragraphElement) {
        let rect = paragraphElement.getBoundingClientRect();

        // Ajoute la hauteur du paragraphe pour afficher la tooltip en-dessous
        buttonSelector.style.left =
          document.getElementById("editor").offsetLeft -
          buttonSelector.offsetWidth +
          13 +
          "px";
        buttonSelector.style.top =
          rect.top + rect.height + window.scrollY + "px";
        buttonSelector.style.display = "block";
      }
    });
    // ! On observe le DOM pour vérifier les balises img supprimées, et on suppirme les IMG référentes (en BDD et sur Cloudinary)
    // Fonction pour extraire les URL des images à partir d'un Delta
    function extractImageUrls(delta) {
      const imageUrls = [];
      delta.ops.forEach((op) => {
        if (typeof op.insert === "object" && op.insert !== null) {
          if (op.insert.image) {
            imageUrls.push(op.insert.image);
          }
        }
      });
      return imageUrls;
    }
    // Variable pour stocker les URL des images actuelles
    let currentImageUrls = extractImageUrls(quill.getContents());

    // Écoutez l'événement 'text-change' pour détecter la suppression des images
    quill.on("text-change", (delta, oldDelta, source) => {
      if (source === "user") {
        const newImageUrls = extractImageUrls(quill.getContents());

        // Trouvez les images supprimées en comparant les URL des images actuelles et nouvelles
        const deletedImageUrls = currentImageUrls.filter(
          (url) => !newImageUrls.includes(url)
        );

        if (deletedImageUrls.length > 0) {
          // Exécutez votre code ici pour chaque image supprimée
          deletedImageUrls.forEach((deletedImageUrl) => {
            DeleteChapterImg(deletedImageUrl);
          });
        }

        // Mettez à jour les URL des images actuelles
        currentImageUrls = newImageUrls;
      }
    });
  }
}
// ! Instanciation de la fonction
if (document.getElementById("editorHTML")) {
  const quillContainer = document.querySelector("#editor");
  quillContainer.addEventListener("click", (e) => {
    if (e.target.tagName === "IMG") {
      // DeleteChapterImg(e.target);
      // e.target.remove();
    }
  });
  quillEditor();
}
// ! Axios
function DeleteChapterImg(pic) {
  const url = "/story/chapter/deleteimg";
  var hideIdChap = document.getElementById("hideIdChap").value;
  let data = new FormData();
  data.append("id", hideIdChap);
  data.append("pic", pic);
  axios
    .post(url, data, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then(function (response) {
      if (response.status === 200) {
        console.log(response.data.delimg);
        axiosChapter();
      } else {
        var notyText =
          "<span class='text-base font-medium'>Erreur</span><br />Une erreur est survenue lors de la suppression de votre image";
        var notyTimeout = 4500;
        var notyType = "error";
        NotyDisplay(notyText, notyType, notyTimeout);
      }
    });
}
function AddChapterImg(file, type, quill, clickedIndex) {
  if (!file) {
    return;
  }
  document.body.classList.add("opacity-50");
  if (file.size > 10000000) {
    var notyText =
      "<span class='text-base font-medium'>Fichier trop volumineux</span><br />Le fichier ne doit pas dépasser 10mo.";
    var notyTimeout = 4500;
    var notyType = "error";
    NotyDisplay(notyText, notyType, notyTimeout);
    document.body.classList.remove("opacity-50");
    return;
  }
  // On crée un objet FormData
  const url = "/story/chapter/addimg";
  const formData = new FormData();
  // On ajoute le fichier à l'objet FormData
  formData.append("pic", file);
  formData.append("id", document.getElementById("hideIdChap").value);
  // On envoie la requête
  axios
    .post(url, formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    })
    .then((response) => {
      document.getElementById("add-img-chapter").value = "";
      if (response.status === 200) {
        let pic = response.data.cloudinary;
        // Obtenir la ligne où se trouve le curseur
        const [currentLine] = quill.getLine(clickedIndex);
        // Trouver la position du dernier caractère de cette ligne
        const lineEndPosition =
          quill.getIndex(currentLine) + currentLine.length();
        // Insérer deux sauts de ligne après cette position pour créer un nouveau paragraphe
        // Insérer l'image dans le nouveau paragraphe
        quill.insertEmbed(lineEndPosition + 1, "image", pic);
        clickedIndex = null;
        document.body.classList.remove("opacity-50");
        axiosChapter();
      } else {
        var notyText =
          "<span class='text-base font-medium'>Erreur</span><br/>" +
          response.data.value;
        var notyTimeout = 3500;
        var notyType = "error";
        NotyDisplay(notyText, notyType, notyTimeout);
        document.body.classList.remove("opacity-50");
      }
    });
}
