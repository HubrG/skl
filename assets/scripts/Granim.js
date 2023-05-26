import Granim from "granim";
export function GranimImg() {
  //
  if (document.getElementById("canvas-image-blending") == null) {
    return;
  }
  var canvas = document.getElementById("canvas-image-blending");
  // Set canvas size
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;
  var granimInstance = new Granim({
    element: "#canvas-image-blending",
    direction: "radial",
    isPausedWhenNotInView: true,

    states: {
      "default-state": {
        gradients: [
          ["#ff9966", "#ff5e62"],
          ["#00F260", "#0575E6"],
          ["#e1eec3", "#f05053"],
        ],
        transitionSpeed: 3000,
      },
    },
  });
  granimInstance;
}
GranimImg();
