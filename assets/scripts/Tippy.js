import tippy, { animateFill } from "tippy.js";
import "tippy.js/dist/tippy.css";
import "tippy.js/themes/translucent.css";

export function TippyC() {
  tippy("[data-tippy-content]", {
    // default
    theme: "material",
    allowHTML: true,

    // parse `content` strings as HTML

    // fade in/out
    arrow: true,
    // show arrow
    // arrow type

    // animate the fill color
    delay: [0, 0],
    // delay before showing/hiding tooltip
    duration: [500, 0],
    // duration of the animation
    // flip tooltip on boundary collision
  });
}
TippyC();
