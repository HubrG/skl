import axios from "axios";
import { NotyDisplay } from "../Noty";
let mainArticle;
let intervalId;
let currentAnnotation;
let previousContent;
let concatenatedAnnotationText = "";
if (document.querySelector("article")) {
  mainArticle = document.querySelector("article");
  previousContent = mainArticle.innerHTML;
  // Initialiser une chaîne vide pour stocker le texte concaténé des annotations
}
// Ajouter un écouteur d'événements "click" au document
export function Annotation(stop = null) {
  if (
    !document.querySelector("article") &&
    !document.querySelector("article").getAttribute("data-annotable")
  )
    return;
  if (stop === "stop") {
    stopInterval();
  } else {
    startInterval();
  }
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
  // Ajouter des gestionnaires d'événements "mouseenter" et "mouseleave" pour chaque élément
  annotationElements.forEach((element) => {
    element.addEventListener("mouseenter", function () {
      stopInterval();
      handleAnnotationHover(element, true);
    });

    element.addEventListener("mouseleave", function () {
      startInterval();
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
  const markForMe = document.querySelectorAll(".mark-for-me");
  const markForAll = document.querySelectorAll(".mark-for-all");

  let currentSelectedText = "";
  const tooltip = document.getElementById("tools");

  document
    .querySelector("article")
    .addEventListener("mouseup", function (event) {
      const selection = window.getSelection();

      // Trouver l'élément parent avec l'attribut data-annotable
      const annotableElement = document.querySelector('[data-annotable=""]');

      // Vérifier si la sélection se fait à l'intérieur de l'élément annotable
      const isSelectionInAnnotableElement =
        annotableElement && selection.containsNode(selection.anchorNode, true);

      if (selection.toString().trim() !== "" && isSelectionInAnnotableElement) {
        currentSelectedText = selection.toString();
        tooltip.classList.remove("hidden");
        const range = selection.getRangeAt(0);
        const rect = range.getBoundingClientRect();
        if (mainArticle.getAttribute("data-mode") === "mark-for-me") {
          tooltip.style.left = rect.left + window.scrollX - 130 + "px";
          tooltip.style.top = rect.top + window.scrollY - 30 + "px";
        } else {
          tooltip.style.left = rect.left + window.scrollX - 130 + "px";
          tooltip.style.top = rect.top + window.scrollY + 60 + "px";
        }
      } else {
        tooltip.classList.add("hidden");
        resetMarkForAll();
      }
    });

  // *
  // * Ce code gère les interactions utilisateur avec les annotations de texte sur la page.
  // ! MarkforMe
  // Il permet à l'utilisateur de créer des annotations en sélectionnant du texte et en cliquant sur un élément "mark-for-me".
  // Il affiche également une tooltip avec des options d'annotation lorsque l'utilisateur clique sur une annotation existante,
  // et masque la tooltip lorsque l'utilisateur clique en dehors de l'annotation.
  //
  // Sélectionner tous les éléments avec la classe "mark-for-me"
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
  // *
  // * Ce code gère les interactions utilisateur avec les annotations de texte sur la page.
  // ! MarkforAll
  // Il permet à l'utilisateur de créer des annotations en sélectionnant du texte et en cliquant sur un élément "mark-for-me".
  // Il affiche également une tooltip avec des options d'annotation lorsque l'utilisateur clique sur une annotation existante,
  // et masque la tooltip lorsque l'utilisateur clique en dehors de l'annotation.
  //
  // Sélectionner tous les éléments avec la classe "mark-for-me"
  // Ajoutez cette fonction pour supprimer les écouteurs d'événements précédents
  function removeEventListeners(element, event, listener) {
    const clone = element.cloneNode(true);
    element.parentNode.replaceChild(clone, element);
    return clone;
  }

  // Ajouter un écouteur d'événements "click" pour chaque élément "mark-for-all"
  let selectionSave = null;
  markForAll.forEach((mark) => {
    mark.addEventListener("click", function (event) {
      // Obtenir la sélection actuelle de texte
      const selection = window.getSelection();
      if (selection.rangeCount > 0) {
        const range = selection.getRangeAt(0);
        selectionSave = range; // Mettez à jour la variable globale avec un objet Range
      }
      const revisionCommentTextarea = document.getElementById(
        "revision-comment-textarea"
      );
      markForAll.forEach((mark2) => {
        mark2.classList.remove("bg-emerald-600");
        mark2.classList.remove("bg-yellow-600");
        mark2.classList.remove("bg-blue-600");
      });
      if (mark.classList.contains("text-emerald-200")) {
        mark.classList.add("bg-emerald-600");
        revisionCommentTextarea.classList.add(
          "border-emerald-600",
          "bg-emerald-100",
          "text-emerald-800",
          "rounded-tl-none"
        );
        revisionCommentTextarea.classList.remove(
          "border-yellow-600",
          "bg-yellow-100",
          "text-yellow-800",
          "border-blue-600",
          "bg-blue-100",
          "text-blue-800",
          "rounded-tr-none"
        );
      } else if (mark.classList.contains("text-yellow-200")) {
        mark.classList.add("bg-yellow-600");
        revisionCommentTextarea.classList.add(
          "border-yellow-600",
          "bg-yellow-100",
          "text-yellow-800"
        );
        revisionCommentTextarea.classList.remove(
          "border-blue-600",
          "bg-blue-100",
          "text-blue-800",
          "rounded-tl-none",
          "rounded-tr-none",
          "border-emerald-600",
          "bg-emerald-100",
          "text-emerald-800"
        );
      } else {
        mark.classList.add("bg-blue-600");
        revisionCommentTextarea.classList.add(
          "border-blue-600",
          "bg-blue-100",
          "text-blue-800",
          "rounded-tr-none"
        );
        revisionCommentTextarea.classList.remove(
          "border-yellow-600",
          "bg-yellow-100",
          "text-yellow-800",
          "border-emerald-600",
          "bg-emerald-100",
          "text-emerald-800",
          "border-tl-none"
        );
      }

      document.getElementById("revision-comment").style = "display: block;";
      const sendRevision = document.getElementById("send-revision");
      const newSendRevision = removeEventListeners(sendRevision, "click");
      newSendRevision.addEventListener("click", function () {
        var revisionComment = document.getElementById(
          "revision-comment-textarea"
        ).value;
        if (selectionSave) {
          const newSelection = document.createRange();
          newSelection.setStart(
            selectionSave.startContainer,
            selectionSave.startOffset
          );
          newSelection.setEnd(
            selectionSave.endContainer,
            selectionSave.endOffset
          );
          // console.log(newSelection);
          createAnnotationRevision(newSelection, mark, revisionComment);
          tooltip.classList.add("hidden");
        }
      });
    });
  });
  function resetMarkForAll() {
    markForAll.forEach((mark) => {
      mark.classList.remove("bg-emerald-600");
      mark.classList.remove("bg-yellow-600");
      mark.classList.remove("bg-blue-600");
      document.getElementById("revision-comment").style = "display: none;";
      document.getElementById("revision-comment-textarea").value = "";
      document
        .getElementById("revision-comment-textarea")
        .classList.remove(
          "border-emerald-600",
          "bg-emerald-100",
          "text-emerald-800"
        );
    });
    selectionSave = null;
  }

  // Sélectionner l'élément avec l'ID "highlighted-options"
  const tooltiped = document.getElementById("highlighted-options");
  // Sélectionner l'élément avec l'ID "del-hl"
  const delHl = document.getElementById("del-hl");
  // Initialiser la variable pour stocker l'annotation actuelle

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
      if (mainArticle.getAttribute("data-mode") === "mark-for-me") {
        tooltiped.style.left = `${tooltipedX}px`;
        tooltiped.style.top = `${tooltipedY}px`;
      } else {
        tooltiped.style.left = `${tooltipedX}px`;
        var ssss = tooltipedY + 50;
        tooltiped.style.top = ssss + `px`;
      }

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

      // ! Récupérer le commentaire de l'annotation
      // 1. Récupérer tous les éléments ayant la classe 'annotationClass'
      const annotationElements =
        document.getElementsByClassName(annotationClass);

      // 2. Définir une expression régulière pour la structure de la classe annotation recherchée
      const annotationRegex = /^annotation-\d+-\d+$/;

      // 3. Créer une fonction pour récupérer la classe annotation correspondant à la structure recherchée
      function findAnnotationClass(element) {
        const classNames = element.classList;

        for (let i = 0; i < classNames.length; i++) {
          if (annotationRegex.test(classNames[i])) {
            return classNames[i];
          }
        }

        return null;
      }
      let annotationClassComment;
      // 4. Parcourir les éléments et récupérer la classe annotation
      for (const annotationElement of annotationElements) {
        const annotationClass = findAnnotationClass(annotationElement);
        if (annotationClass) {
          annotationClassComment = annotationClass;
        }
      }

      document
        .querySelectorAll(".one-revision-comment")
        .forEach((annotation) => {
          if (
            annotation.getAttribute("data-annotation-class") ===
            annotationClassComment
          ) {
            // On récupère le commentaire de l'annotation à l'intérieur de l'élément qui possède la classe : annotation-comment-user
            const annotationCommentUser = annotation.querySelector(
              ".annotation-comment-user"
            ).innerHTML;
            document.getElementById("hl-comment-user").innerHTML =
              annotationCommentUser;
            // On récupère le commentaire de l'annotation à l'intérieur de l'élément qui possède la classe : annotation-comment-user
            const annotationComment = annotation.querySelector(
              ".annotation-comment-content"
            ).innerHTML;
            document.getElementById("hl-comment-content").innerHTML =
              annotationComment;
            // On récupère l'avatar s'il existe
            const annotationCommentUserAvatar = annotation.querySelector(
              ".annotation-comment-pp"
            ).innerHTML;
            document.getElementById("hl-comment-avatar").innerHTML =
              annotationCommentUserAvatar;
          }
        });
      //

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
  function createAnnotation(selection, mark, comment = null) {
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

    url = "/save-annotation";

    const annotationData = {
      annotation_class: annotationClass,
      content: article,
      comment: comment,
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
        } else {
        }
      });

    selection.removeAllRanges();
  }
  /**
   * * Cette fonction prend en entrée une instance de Selection et un élément <mark> représentant
   * * l'annotation, puis crée et enregistre une annotation en enveloppant le contenu de la sélection
   * * dans un élément <mark>. Elle rassemble également les données d'annotation et envoie une
   * * requête pour enregistrer l'annotation sur le serveur.
   *
   * @param {Selection} selection - L'instance de Selection dont le contenu doit être annoté.
   * @param {Element} mark - L'élément <mark> représentant l'annotation.
   */
  function createAnnotationRevision(selection, mark, comment) {
    const range = selection;
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

    url = "/save-annotation";

    const annotationData = {
      annotation_class: annotationClass,
      content: article,
      comment: comment,
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
        stopInterval();
        document.getElementById("comment-reload").click();
        if (response.data.code === 403) {
          NotyDisplay(
            "Vous avez marqué le texte, toutefois ce marquage n'est pas enregistré car vous n'être pas connecté(e).",
            "info",
            5000
          );
        }
      });
    resetMarkForAll();
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
        stopInterval();
        handleAnnotationHover(element, true);
      });

      element.addEventListener("mouseleave", function () {
        startInterval();
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

  const comments = document.querySelectorAll(".one-revision-comment");

  // ! Hover sur un commentaire
  // ! Hover sur un commentaire
  let scrollTimeout; // Ajoutez cette variable pour stocker l'ID renvoyé par setTimeout
  comments.forEach((comment) => {
    comment.addEventListener("mouseenter", function () {
      stopInterval();
      const annotationClass = comment.getAttribute("data-annotation-class");
      const annotationElements = document.querySelectorAll(
        "." + annotationClass
      );
      annotationElements.forEach((element) => {
        element.classList.add("hovered");
      });

      // Faites défiler la page vers le premier élément avec la classe annotation-X
      if (annotationElements.length > 0) {
        scrollTimeout = setTimeout(() => {
          // Stockez l'ID renvoyé par setTimeout dans scrollTimeout
          annotationElements[0].scrollIntoView({
            behavior: "smooth",
            block: "center",
            inline: "nearest",
          });
        }, 1000);
      }
    });

    comment.addEventListener("mouseleave", function () {
      startInterval(); // Redémarrez l'intervalle lorsque la souris quitte un commentaire
      const annotationClass = comment.getAttribute("data-annotation-class");
      const annotationElements = document.querySelectorAll(
        "." + annotationClass
      );
      annotationElements.forEach((element) => {
        element.classList.remove("hovered");
      });

      clearTimeout(scrollTimeout); // Annulez le setTimeout en utilisant scrollTimeout
    });
  });

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
  // ! Changement de version de l'article en mode révision avec Select
  const versionSelect = document.querySelector("#version-select");
  if (versionSelect) {
    versionSelect.addEventListener("change", function () {
      changeVersion(this);
    });
  }
  function changeVersion(selectElement) {
    const version = selectElement.value;
    const url = new URL(window.location.href);
    url.searchParams.set("version", version);
    window.location.href = url.toString();
  }
  // ! RELOAD ARTICLE si changement

  let isMouseOver = false;
  //  ! interval pour reload en cas de nouveau contenu

  if (
    document.querySelector("article").getAttribute("data-mode") ==
    "mark-for-all"
  ) {
    // startInterval();
  }

  //  ! Suppression depuis les commentaires
  const delComment = document.querySelectorAll(".del-comment");
  delComment.forEach((del) => {
    del.addEventListener("click", function () {
      const dataAnnotation = del.getAttribute("data-annotation-class");
      const annotationElements = document.querySelector("." + dataAnnotation);
      removeAnnotation(annotationElements);
      removeAnnotationComment(dataAnnotation);
      stopInterval();
      //
    });
  });
}
function startInterval(articleNew = false, stop = false) {
  if (
    document.querySelector("article").getAttribute("data-mode") ==
    "mark-for-all"
  ) {
    if (intervalId) {
      clearInterval(intervalId);
    }
    if (stop === "stop") {
      return; // On arrête ici si stop est égal à "stop"
    }
    intervalId = setInterval(function () {
      const version = mainArticle.getAttribute("data-version");
      const chapter = mainArticle.getAttribute("data-chapter");
      const annotationData = {
        version: version,
        article: document.querySelector("article").innerHTML,
        chapter: chapter,
      };
      const url = "/reload-revision";
      axios
        .post(url, annotationData, {
          headers: {
            "Content-Type": "application/json",
          },
        })
        .then((response) => {
          if (
            response.data.message.trim() == response.data.compar.trim() ||
            response.data.message.trim() == ""
          ) {
          } else {
            if (!document.querySelector(".noty_type__info")) {
              NotyDisplay(
                "Un utilisateur vient d'ajouter ou de supprimer une annotation sur cette feuille, la page va se recharger automatiquement.",
                "info",
                4000
              );
            }
            setTimeout(function () {
              previousContent = response.data.message;
              document.querySelector("article").innerHTML =
                response.data.message;
              document.getElementById("tools-frame").classList.add("hidden");
              // document.getElementById("comment-reload").click();
              document.getElementById("reload-article").click();
            }, 5000);
          }
        })
        .catch((error) => {
          console.error(
            "Erreur lors du chargement du contenu de l'article:",
            error
          );
        });
    }, 3000);
  }
}
function removeAnnotationComment(annotationElements) {
  const oneComment = document.querySelectorAll(".one-revision-comment");
  oneComment.forEach((comment) => {
    if (comment.getAttribute("data-annotation-class") == annotationElements) {
      comment.remove();
    }
  });
}
function removeAnnotation(annotation) {
  // console.log(annotation);
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
      // On supprime l'annotation en commentaire
      removeAnnotationComment(uniqueClass);
      stopInterval();
    })
    .catch((error) => {
      console.error("Erreur lors de la suppression de l'annotation :", error);
    });
}
// ! SUppression d'annotation

function handleDeleteClick() {
  if (currentAnnotation) {
    removeAnnotation(currentAnnotation);
  }
}
function stopInterval() {
  if (intervalId) {
    clearInterval(intervalId);
  }
}
// Lorsque la frame se recharge
document.addEventListener("turbo:frame-load", (event) => {
  if (document.querySelector("article")) {
    Annotation();
    const delHl = document.getElementById("del-hl");
    // Supprimez d'abord l'écouteur d'événements existant
    delHl.removeEventListener("click", handleDeleteClick);
    // Ajoutez ensuite le nouvel écouteur d'événements
    delHl.addEventListener("click", handleDeleteClick);
  }
}); // Lorsque la frame se recharge

// Lorsque la frame se recharge
document.addEventListener("turbo:load", (event) => {
  if (document.querySelector("article")) {
    Annotation();
    const delHl = document.getElementById("del-hl");
    // Supprimez d'abord l'écouteur d'événements existant
    delHl.removeEventListener("click", handleDeleteClick);
    // Ajoutez ensuite le nouvel écouteur d'événements
    delHl.addEventListener("click", handleDeleteClick);
  }
});
// Lorsque la frame se recharge
document.addEventListener("turbo:render", (event) => {
  if (document.querySelector("article")) {
    Annotation();
    const delHl = document.getElementById("del-hl");
    // Supprimez d'abord l'écouteur d'événements existant
    delHl.removeEventListener("click", handleDeleteClick);
    // Ajoutez ensuite le nouvel écouteur d'événements
    delHl.addEventListener("click", handleDeleteClick);
  }
});
