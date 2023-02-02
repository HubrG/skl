export function ShowChapter() {
  if (document.getElementById("chapContentTurbo")) {
    // ! Fonction qui cache les flèches de navigation si on est au début ou à la fin du chapitre
    const target = document.getElementById("commentFrame");
    if (document.getElementById("arrowNext")) {
      const elN = document.getElementById("arrowNext");
      const observer = new IntersectionObserver(function (entries) {
        if (entries[0].isIntersecting) {
          elN.style.display = "none";
        } else {
          elN.style.display = "flex";
        }
      });
      observer.observe(target);
      if (target.getBoundingClientRect().bottom < window.innerHeight) {
        elN.style.display = "none";
      }
    }
    if (document.getElementById("arrowPrevious")) {
      const elP = document.getElementById("arrowPrevious");
      const observer = new IntersectionObserver(function (entries) {
        if (entries[0].isIntersecting) {
          elP.style.display = "none";
        } else {
          elP.style.display = "flex";
        }
      });
      observer.observe(target);
      if (target.getBoundingClientRect().bottom < window.innerHeight) {
        elP.style.display = "none";
      }
    }

    // ! Fonction qui agrandit le textarea des commentaires
    if (document.getElementById("lastComment")) {
      var lastComment = document.getElementById("lastComment");
      setTimeout(function () {
        lastComment.classList.remove("bg-amber-50");
        lastComment.classList.remove("text-amber-600");
      }, 2000);
    }
    if (document.getElementById("publication_chapter_comment_content")) {
      const textarea = document.getElementById(
        "publication_chapter_comment_content"
      );
      textarea.addEventListener("input", function () {
        this.style.height = "";
        this.style.height = this.scrollHeight + "px";
      });
    }
    var commentContent = document.getElementById(
      "publication_chapter_comment_content"
    );
    var sendComment = document.getElementById("sendComment");
    sendComment.addEventListener("click", function () {
      setTimeout(function () {
        commentContent.value = "";
        commentContent.style.height = "3.4rem";
      }, 100);
    });
  }
}
ShowChapter();
