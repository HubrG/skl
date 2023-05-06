import axios from "axios";

export function Annotation() {
  if (!document.getElementById("article")) return;
  restoreAnnotations();

  document.addEventListener("mouseup", function (event) {
    const selection = window.getSelection();
    if (selection.toString().trim() !== "") {
      createAnnotation(selection);
    }
  });

  document.addEventListener("click", function (event) {
    if (event.target.matches(".annotation")) {
      removeAnnotation(event.target);
    }
  });
  function getNodeGlobalOffset(node) {
    let offset = 0;
    const iterator = document.createNodeIterator(
      node.parentNode,
      NodeFilter.SHOW_TEXT
    );
    let currentNode;
    while ((currentNode = iterator.nextNode()) && currentNode !== node) {
      offset += currentNode.textContent.length;
    }
    return offset;
  }
  function restoreAnnotations() {
    axios.get("/annotations").then((response) => {
      const annotations = response.data;
      console.log("Annotations récupérées :", annotations);
      const annotableArticle = document.querySelector("[data-annotable]");

      const groupedAnnotations = annotations.reduce((groups, annotation) => {
        if (!groups[annotation.annotationClass]) {
          groups[annotation.annotationClass] = [];
        }
        groups[annotation.annotationClass].push(annotation);
        return groups;
      }, {});

      for (const annotationClass in groupedAnnotations) {
        const group = groupedAnnotations[annotationClass];
        group.forEach((annotation) => {
          const textNodes = getTextNodes(annotableArticle); // Recalculez les index des noeuds texte ici
          applyAnnotation(annotation, textNodes);
        });
      }
    });
  }

  function applyAnnotation(annotation, textNodes) {
    for (let i = 0; i < textNodes.length; i++) {
      const node = textNodes[i];
      const nodeOffset = getNodeGlobalOffset(node);
      const nodeLength = node.textContent.length;

      if (
        nodeOffset <= annotation.startOffset &&
        nodeOffset + nodeLength >= annotation.endOffset
      ) {
        const mark = document.createElement("mark");
        mark.classList.add("annotation");
        mark.classList.add(annotation.annotationClass);

        const range = document.createRange();
        range.setStart(node, Math.max(annotation.startOffset - nodeOffset, 0));
        range.setEnd(
          node,
          Math.min(annotation.endOffset - nodeOffset, nodeLength)
        );

        range.surroundContents(mark);
        console.log("Annotation appliquée :", mark, "Node :", node);
        break;
      }
    }
  }

  function getTextNodes(element) {
    const iterator = document.createNodeIterator(element, NodeFilter.SHOW_TEXT);
    const textNodes = [];
    let currentNode;
    while ((currentNode = iterator.nextNode())) {
      textNodes.push(currentNode);
    }
    return textNodes;
  }
  function createAnnotation(selection) {
    const range = selection.getRangeAt(0);
    const commonAncestor = range.commonAncestorContainer;
    const annotationClass = "annotation-" + Date.now();

    let textNodeRanges;
    if (commonAncestor.nodeType === Node.TEXT_NODE) {
      textNodeRanges = [range];
    } else {
      textNodeRanges = getTextNodesInRange(range);
    }

    textNodeRanges.forEach((nodeRange) => {
      wrapAnnotation(nodeRange, annotationClass);
      const content = nodeRange.toString();
      const startOffset =
        getNodeGlobalOffset(nodeRange.startContainer) + nodeRange.startOffset;
      const endOffset =
        getNodeGlobalOffset(nodeRange.endContainer) + nodeRange.endOffset;
    });
    const article = document.getElementById("article").innerHTML;
    const annotationData = {
      annotation_class: annotationClass,
      content: article,
    };
    axios
      .post("/save-annotation", annotationData, {
        headers: {
          "Content-Type": "application/json",
        },
      })
      .then(() => {
        console.log("Annotation enregistrée");
      });

    selection.removeAllRanges();
  }

  function wrapAnnotation(range, annotationClass) {
    const mark = document.createElement("mark");
    mark.classList.add("annotation");
    mark.classList.add(annotationClass);
    range.surroundContents(mark);
  }

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

    // Filter out empty text nodes and nodes that are not children of annotable elements
    return textNodes.filter((nodeRange) => {
      const parentNode = nodeRange.startContainer.parentNode;
      return (
        nodeRange.toString().trim() !== "" &&
        parentNode.closest("[data-annotable]") !== null
      );
    });
  }

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
  }
}
