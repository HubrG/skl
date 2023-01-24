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
import Notification from "stimulus-notification";
import { Datepicker } from "stimulus-datepicker";
const application = Application.start();
application.register("notification", Notification);
application.register("datepicker", Datepicker);
// ! Noty
window.Noty = require("noty");
// ! Quill Js
