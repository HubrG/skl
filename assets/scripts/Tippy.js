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
    delay: [1000, 0],

    // animate the fill color
    // delay before showing/hiding tooltip
    duration: [500, 0],
    // duration of the animation
    // flip tooltip on boundary collision
  });
}
TippyC();
