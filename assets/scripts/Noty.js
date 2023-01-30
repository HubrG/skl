window.Noty = require("noty");
export function NotyDisplay(notyText, notyType, notyTimeout) {
  new Noty({
    text: notyText,
    theme: "semanticui",
    progressBar: true,
    timeout: notyTimeout,
    layout: "bottomCenter",
    type: notyType,
    closeWith: ["click", "button"],
    animation: {
      open: "animate__animated animate__fadeInUp", // Animate.css class names
      close: "animate__animated animate__fadeOutDown", // Animate.css class names
    },
  }).show();
}
