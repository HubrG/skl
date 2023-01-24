// fonction de temps de lecture
import Quill from "quill";
export function ReadTime() {
  if (document.getElementById("editor")) {
    var div = document.getElementById("editor");
    div.addEventListener("keyup", function () {
      var text = div.textContent; // texte sans balises
      var paragraphs = div.innerHTML.split("<p>"); // nombre de paragraphes
      var numParagraphs = paragraphs.length - 1; // nombre de paragraphes (ajustement)
      let words = text.trim().split(" ").length; // compteur de mots
      let signs = text.replace(/\s+/g, "").length;
      // ! Calcul du temps de lecture
      const wordCount = words;
      const readingSpeed = 200;
      const readingTime = (wordCount / readingSpeed) * 60; // calcul du temps de lecture en secondes
      const minutes = Math.floor(readingTime / 60); // calcul des minutes
      const seconds = Math.floor(readingTime % 60); // calcul des secondes
      // !
      // ! affichage des statistiques
      // !
      // * affichage du temps de lecture
      document.getElementById("readTime").innerHTML =
        minutes +
        ` ${minutes > 1 ? "minutes" : "minute"} et ` +
        seconds +
        ` ${seconds > 1 ? "secondes" : "seconde"}` +
        " de lecture";
      // * affichage du nombre de mots
      document.getElementById("wordCount").innerHTML =
        words + ` ${words > 1 ? "mots" : "mot"}`;
      // * affichage du nombre de signes
      document.getElementById("signCount").innerHTML =
        signs + ` ${signs > 1 ? "signes" : "signe"}`;
      // * affichage de nombre de paragraphes
      document.getElementById("paragraphCount").innerHTML =
        numParagraphs + ` ${numParagraphs > 1 ? "paragraphes" : "paragraphe"}`;
      // * suppression du formatage du texte
    });
  }
}
ReadTime();
