import axios from "axios";
import { NotyDisplay } from "../Noty";

export function Annotation() {
  if (!document.querySelector("article")) return;

  // * Informations préalables
  /** À propos de "data-mode"
   * Le mode de l'annotation est défini par l'attribut "data-mode" de l'élément <article>.
   * Il peut avoir l'une des valeurs suivantes :
   * - "mark-for-me" : le mode par défaut, qui permet à l'utilisateur de voir ses propres annotations (ou marquages) existantes et d'en créer de nouvelles.
   * - "mark-for-all" : le mode qui permet à l'utilisateur de voir les annotations (ou marquages) des autres utilisateurs en mode « Révision »
   */
  // *
  /**
   * Ce morceau de code sélectionne tous les éléments avec la classe "annotation" (éléments <mark>),
   * puis ajoute des gestionnaires d'événements "mouseenter" et "mouseleave" pour chaque élément.
   * Lorsque la souris survole un élément d'annotation, la fonction handleAnnotationHover est appelée
   * pour ajouter ou supprimer une classe de survol personnalisée (custom-hover) sur tous les éléments
   * ayant la même classe aléatoire d'annotation.
   */
  const annotationElements = document.querySelectorAll("mark");
  let mainArticle = document.querySelector("article");
  console.log(mainArticle.getAttribute("data-mode"));
  // Ajouter des gestionnaires d'événements "mouseenter" et "mouseleave" pour chaque élément
  annotationElements.forEach((element) => {
    element.addEventListener("mouseenter", function () {
      handleAnnotationHover(element, true);
    });

    element.addEventListener("mouseleave", function () {
      handleAnnotationHover(element, false);
    });
  });

  function handleAnnotationHover(element, isHovered) {
    // Récupérer la classe aléatoire à partir de la liste des classes de l'élément
    const randomClass = Array.from(element.classList).find((className) =>
      className.startsWith("annotation-")
    );
    // Récupérer la classe hl-X à partir de la liste des classes de l'élément
    const hlClass = Array.from(element.classList).find((className) =>
      className.startsWith("hl-")
    );

    // Extraire le numéro après "hl-" pour obtenir le ColorName
    const ColorName = hlClass.slice(3);

    // Sélectionner tous les éléments ayant la même classe aléatoire
    const sameClassElements = document.querySelectorAll("." + randomClass);

    // Ajouter ou supprimer la classe "custom-hover" pour tous les éléments ayant la même classe aléatoire
    sameClassElements.forEach((elem) => {
      if (isHovered) {
        elem.classList.add("custom-hover-hl-" + ColorName);
      } else {
        elem.classList.remove("custom-hover-hl-" + ColorName);
      }
    });
  }
  // *
  /**
   * * Ce morceau de code ajoute un gestionnaire d'événements "mouseup" à l'ensemble du document.
   * Lorsque l'utilisateur relâche le bouton de la souris après une sélection de texte,
   * il vérifie si du texte est sélectionné. Si c'est le cas, il affiche une info-bulle
   * près de la sélection contenant des outils pour interagir avec le texte sélectionné.
   * Si aucune sélection n'est présente, l'info-bulle est masquée.
   */
  let currentSelectedText = "";
  const tooltip = document.getElementById("tools");
  document.addEventListener("mouseup", function (event) {
    const selection = window.getSelection();
    if (selection.toString().trim() !== "") {
      currentSelectedText = selection.toString();
      tooltip.classList.remove("hidden");
      const range = selection.getRangeAt(0);
      const rect = range.getBoundingClientRect();
      tooltip.style.left = rect.left + window.scrollX - 130 + "px";
      tooltip.style.top = rect.top + window.scrollY - 30 + "px";
    } else {
      tooltip.classList.add("hidden");
    }
  });
  // *
  // * Ce code gère les interactions utilisateur avec les annotations de texte sur la page.
  // Il permet à l'utilisateur de créer des annotations en sélectionnant du texte et en cliquant sur un élément "mark-for-me".
  // Il affiche également une tooltip avec des options d'annotation lorsque l'utilisateur clique sur une annotation existante,
  // et masque la tooltip lorsque l'utilisateur clique en dehors de l'annotation.
  //
  // Sélectionner tous les éléments avec la classe "mark-for-me"
  const markForMe = document.querySelectorAll(".mark-for-me");
  const markForAll = document.querySelectorAll(".mark-for-all");
  // Ajouter un écouteur d'événements "click" pour chaque élément "mark-for-me"
  markForMe.forEach((mark) => {
    mark.addEventListener("click", function () {
      // Obtenir la sélection actuelle de texte
      const selection = window.getSelection();
      // Créer une annotation à partir de la sélection de texte et de l'élément "mark"
      createAnnotation(selection, mark);
      // Cacher la tooltip en ajoutant la classe "hidden"
      tooltip.classList.add("hidden");
    });
  });
  // Ajouter un écouteur d'événements "click" pour chaque élément "mark-for-all"
  markForAll.forEach((mark) => {
    mark.addEventListener("click", function () {
      // Obtenir la sélection actuelle de texte
      const selection = window.getSelection();
      // Créer une annotation à partir de la sélection de texte et de l'élément "mark"
      createAnnotation(selection, mark);
      // Cacher la tooltip en ajoutant la classe "hidden"
      tooltip.classList.add("hidden");
    });
  });

  // Sélectionner l'élément avec l'ID "highlighted-options"
  const tooltiped = document.getElementById("highlighted-options");
  // Sélectionner l'élément avec l'ID "del-hl"
  const delHl = document.getElementById("del-hl");
  // Initialiser la variable pour stocker l'annotation actuelle
  let currentAnnotation;

  // Initialiser une chaîne vide pour stocker le texte concaténé des annotations
  let concatenatedAnnotationText = "";
  // Ajouter un écouteur d'événements "click" au document
  document.addEventListener("click", function (event) {
    // Si l'élément sur lequel l'utilisateur a cliqué a la classe "annotation"
    if (event.target.matches(".annotation")) {
      // Obtenir la position de l'élément d'annotation
      const annotationRect = event.target.getBoundingClientRect();
      // Calculer les coordonnées X et Y pour positionner la tooltiped au-dessus de l'élément d'annotation
      const tooltipedX =
        window.pageXOffset + annotationRect.left + annotationRect.width / 2;
      const tooltipedY =
        window.pageYOffset + annotationRect.top - tooltiped.offsetHeight;

      // Positionner la tooltiped en utilisant les coordonnées calculées
      tooltiped.style.left = `${tooltipedX}px`;
      tooltiped.style.top = `${tooltipedY}px`;

      // Afficher la tooltiped en supprimant la classe "hidden"
      tooltiped.classList.remove("hidden");

      // Change la couleur du boutton de suppression
      const styles = {
        "hl-1": ["text-emerald-400"],
        "hl-2": ["text-amber-400"],
        "hl-3": ["text-red-400"],
        default: ["text-blue-400"],
      };
      delHl.className = "";
      const classes =
        styles[
          event.target.classList.contains("hl-1")
            ? "hl-1"
            : event.target.classList.contains("hl-2")
            ? "hl-2"
            : event.target.classList.contains("hl-3")
            ? "hl-3"
            : "default"
        ];
      delHl.classList.add(...classes);

      // Stocker l'élément d'annotation actuel
      currentAnnotation = event.target;

      // Obtenir la classe d'annotation (ex: "annotation-XXXX")
      const annotationClass = event.target.className;

      // Récupérer tous les éléments avec la même classe d'annotation
      const annotationElements =
        document.getElementsByClassName(annotationClass);

      // Concaténer le contenu de tous les éléments d'annotation
      for (const elem of annotationElements) {
        concatenatedAnnotationText += elem.textContent;
      }
    } else {
      // Si l'utilisateur clique ailleurs, cacher la tooltiped
      tooltiped.classList.add("hidden");
    }
  });

  /**
   * * Cette fonction prend en entrée une instance de Selection et un élément <mark> représentant
   * * l'annotation, puis crée et enregistre une annotation en enveloppant le contenu de la sélection
   * * dans un élément <mark>. Elle rassemble également les données d'annotation et envoie une
   * * requête pour enregistrer l'annotation sur le serveur.
   *
   * @param {Selection} selection - L'instance de Selection dont le contenu doit être annoté.
   * @param {Element} mark - L'élément <mark> représentant l'annotation.
   */
  function createAnnotation(selection, mark) {
    const range = selection.getRangeAt(0);
    const commonAncestor = range.commonAncestorContainer;
    let random = Math.floor(Math.random() * 1000) + 1;
    const annotationClass = "annotation-" + Date.now() + "-" + random;
    let textNodeRanges;
    if (commonAncestor.nodeType === Node.TEXT_NODE) {
      textNodeRanges = [range];
    } else {
      textNodeRanges = getTextNodesInRange(range);
    }

    let combinedContent = "";

    textNodeRanges.forEach((nodeRange) => {
      wrapAnnotation(
        nodeRange,
        annotationClass,
        mark.getAttribute("data-color")
      );
      const content = nodeRange.toString();
      combinedContent += content;
    });
    const article = document.querySelector("article").innerHTML;
    const chapter = mainArticle.getAttribute("data-chapter");
    const annotationMode = mainArticle.getAttribute("data-mode");
    const version = mainArticle.getAttribute("data-version");
    var url = null;
    if (annotationMode === "mark-for-me") {
      url = "/save-annotation";
    } else {
      url = "/save-review";
    }

    const annotationData = {
      annotation_class: annotationClass,
      content: article,
      mode: annotationMode,
      version: version,
      content_plain: combinedContent,
      color: mark.getAttribute("data-color"),
      chapter: chapter,
    };
    axios
      .post(url, annotationData, {
        headers: {
          "Content-Type": "application/json",
        },
      })
      .then((response) => {
        // Si erreur 403
        if (response.data.code === 403) {
          NotyDisplay(
            "Vous avez marqué le texte, toutefois ce marquage n'est pas enregistré car vous n'être pas connecté(e).",
            "info",
            5000
          );
        }
      });

    selection.removeAllRanges();
  }
  /**
   * * Cette fonction prend en entrée une instance de Range, une classe d'annotation et une couleur,
   * puis enveloppe le contenu de la plage donnée dans un élément <mark> avec la classe et la couleur
   * d'annotation fournies. De plus, elle ajoute des gestionnaires d'événements "mouseenter" et
   * "mouseleave" pour chaque élément d'annotation afin de gérer les interactions de survol.
   *
   * @param {Range} range - L'instance de Range dont le contenu doit être enveloppé avec un élément d'annotation.
   * @param {string} annotationClass - La classe CSS à ajouter à l'élément d'annotation.
   * @param {string} color - La couleur de l'annotation.
   */
  function wrapAnnotation(range, annotationClass, color) {
    const mark = document.createElement("mark");
    mark.classList.add("annotation");
    mark.classList.add(annotationClass);
    mark.classList.add("hl-" + color);
    // Sélectionner tous les éléments avec la classe "annotation"
    let annotationElements = document.querySelectorAll("mark");

    // Ajouter des gestionnaires d'événements "mouseenter" et "mouseleave" pour chaque élément
    annotationElements.forEach((element) => {
      element.addEventListener("mouseenter", function () {
        handleAnnotationHover(element, true);
      });

      element.addEventListener("mouseleave", function () {
        handleAnnotationHover(element, false);
      });
    });
    range.surroundContents(mark);
  }
  /**
   * * Cette fonction prend en entrée une instance de Range et retourne tous les nœuds de texte
   * qui se trouvent à l'intérieur de cette plage (Range). Les nœuds de texte vides et les
   * nœuds qui ne sont pas des enfants d'éléments annotables sont filtrés.
   *
   * @param {Range} range - L'instance de Range à partir de laquelle les nœuds de texte sont extraits.
   * @returns {Array} textNodes - Un tableau de nœuds de texte qui sont à l'intérieur de la plage donnée.
   */
  function getTextNodesInRange(range) {
    const iterator = document.createNodeIterator(
      range.commonAncestorContainer,
      NodeFilter.SHOW_TEXT,
      {
        acceptNode: (node) => {
          const nodeRange = document.createRange();
          nodeRange.selectNodeContents(node);
          return range.intersectsNode(node)
            ? NodeFilter.FILTER_ACCEPT
            : NodeFilter.FILTER_REJECT;
        },
      }
    );
    const textNodes = [];
    let currentNode;
    while ((currentNode = iterator.nextNode())) {
      const nodeRange = document.createRange();
      nodeRange.selectNodeContents(currentNode);
      if (range.intersectsNode(currentNode)) {
        if (currentNode === range.startContainer) {
          nodeRange.setStart(currentNode, range.startOffset);
        }
        if (currentNode === range.endContainer) {
          nodeRange.setEnd(currentNode, range.endOffset);
        }
        textNodes.push(nodeRange);
      }
    }
    // Filtrer les nœuds de texte vides et les nœuds qui ne sont pas enfants d'éléments annotables
    return textNodes.filter((nodeRange) => {
      const parentNode = nodeRange.startContainer.parentNode;
      return (
        nodeRange.toString().trim() !== "" &&
        parentNode.closest("[data-annotable]") !== null
      );
    });
  }
  // *
  // * La fonction removeAnnotation supprime l'annotation donnée de la page, puis met à jour le contenu de l'article
  // et envoie une requête pour supprimer l'annotation du côté serveur. Elle prend en paramètre l'élément d'annotation
  // à supprimer et effectue les étapes suivantes :
  // 1. Identifie la classe unique de l'annotation (annotation-XXXX).
  // 2. Sélectionne tous les éléments d'annotation avec la même classe unique.
  // 3. Pour chaque élément d'annotation, déplace son contenu dans le parent et supprime l'élément d'annotation.
  // 4. Met à jour le contenu de l'article et le chapitre.
  // 5. Envoie une requête POST au serveur pour supprimer l'annotation, en incluant le contenu mis à jour de l'article, le chapitre et la classe d'annotation unique.
  function removeAnnotation(annotation) {
    const uniqueClass = Array.from(annotation.classList).find((className) =>
      className.startsWith("annotation-")
    );
    const annotations = document.querySelectorAll(".annotation." + uniqueClass);
    annotations.forEach((annotation) => {
      const parent = annotation.parentNode;
      while (annotation.firstChild) {
        parent.insertBefore(annotation.firstChild, annotation);
      }
      parent.removeChild(annotation);
    });
    // Mettre à jour la variable 'article' après avoir supprimé l'annotation du DOM
    const article = document.querySelector("article").innerHTML;
    const chapter = document
      .querySelector("article")
      .getAttribute("data-chapter");
    const version = mainArticle.getAttribute("data-version");

    let annotationMode = mainArticle.getAttribute("data-mode");
    var url = null;
    if (annotationMode === "mark-for-me") {
      url = "/delete-annotation";
    } else {
      url = "/delete-review";
    }
    const annotationData = {
      content: article,
      chapter: chapter,
      mode: annotationMode,
      version: version,
      annotation_class: uniqueClass,
    };
    axios
      .post(url, annotationData, {
        headers: {
          "Content-Type": "application/json",
        },
      })
      .then(() => {
        console.log("Annotation Supprimée");
      })
      .catch((error) => {
        console.error("Erreur lors de la suppression de l'annotation :", error);
      });
  }

  // ! Partager sur les réseaux sociaux
  const twitterB = document.querySelectorAll(".shareTwitter");
  twitterB.forEach((twitter) => {
    twitter.addEventListener("click", function () {
      if (currentSelectedText.trim() !== "") {
        // Partager la sélection de texte sur Twitter
        shareNw("twitter", currentSelectedText);
      } else if (concatenatedAnnotationText) {
        // Si aucune sélection de texte, partager le texte annoté
        shareNw("twitter", concatenatedAnnotationText);
      }
    });
  });

  const fbB = document.querySelectorAll(".shareFb");
  fbB.forEach((fb) => {
    fb.addEventListener("click", function () {
      if (currentSelectedText.trim() !== "") {
        // Partager la sélection de texte sur Facebook
        shareNw("facebook", currentSelectedText);
      } else if (concatenatedAnnotationText) {
        // Si aucune sélection de texte, partager le texte annoté
        shareNw("facebook", concatenatedAnnotationText);
      }
    });
  });

  const lkB = document.querySelectorAll(".shareLk");
  lkB.forEach((lk) => {
    lk.addEventListener("click", function () {
      if (currentSelectedText.trim() !== "") {
        // Partager la sélection de texte sur LinkedIn
        shareNw("linkedin", currentSelectedText);
      } else if (concatenatedAnnotationText) {
        // Si aucune sélection de texte, partager le texte annoté
        shareNw("linkedin", concatenatedAnnotationText);
      }
    });
  });

  function shareNw(nw, selectedText) {
    // !
    let url = window.location.href;
    let urlShare = "";
    switch (nw) {
      case "twitter":
        urlShare = `https://twitter.com/share?url=${url}&via=ScrilabEditions&text=${encodeURIComponent(
          selectedText.trim()
        )}`;
        window.open(urlShare, "_blank");
        break;
      case "facebook":
        urlShare = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
        window.open(urlShare, "_blank");
        break;
      case "linkedin":
        urlShare = `https://www.linkedin.com/shareArticle/?mini=true&url=${url}`;
        window.open(urlShare, "_blank");
        break;
      default:
        return;
    }
  }
  // ! SUppression d'annotation
  delHl.addEventListener("click", function () {
    if (currentAnnotation) {
      removeAnnotation(currentAnnotation);
      console.log("ok");
    }
  });
}
