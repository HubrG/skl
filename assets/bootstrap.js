import { startStimulusApp } from "@symfony/stimulus-bridge";
import "../node_modules/tippy.js/dist/tippy.css";

// Registers Stimulus controllers from controllers.json and in the controllers/ directory
export const app = startStimulusApp(
  require.context(
    "@symfony/stimulus-bridge/lazy-controller-loader!./controllers",
    true,
    /\.[jt]sx?$/
  )
);
// ! differents modules Stimulus

import { Application } from "@hotwired/stimulus";

const application = Application.start();
