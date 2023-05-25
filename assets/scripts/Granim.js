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
    direction: "top-bottom",
    isPausedWhenNotInView: true,

    states: {
      "default-state": {
        gradients: [
          ["#a8a8a8", "#dbdbdb"],
          ["#FF6B6B", "#556270"],
          ["#80d3fe", "#7ea0c4"],
          ["#f0ab51", "#eceba3"],
        ],
        transitionSpeed: 7000,
      },
    },
  });
  granimInstance;
}
GranimImg();
