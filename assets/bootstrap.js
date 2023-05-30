import { startStimulusApp } from "@symfony/stimulus-bridge";

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
import LiveController from "@symfony/ux-live-component";
import "@symfony/ux-live-component/styles/live.css";

const application = Application.start();
app.register("live", LiveController);

import lozad from "lozad";

const observer = lozad(".lozad");
observer.observe();
