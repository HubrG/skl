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
import Quill from "quill";
var toolbarOptions = [
  ["bold", "italic", "underline", "strike"], // toggled buttons
  ["blockquote"],

  [{ header: 1 }, { header: 2 }], // custom button values
  [{ list: "ordered" }, { list: "bullet" }],
  [{ script: "sub" }, { script: "super" }], // superscript/subscript
  [{ indent: "-1" }, { indent: "+1" }], // outdent/indent
  [{ direction: "rtl" }], // text direction

  [{ header: [1, 2, 3, 4, 5, 6, false] }],

  [{ align: [] }],

  ["clean"], // remove formatting button
];
var options = {
  placeholder: "Compose an epic...",
  modules: {
    toolbar: toolbarOptions,
  },
  theme: "bubble",
};
const quill = new Quill("#editor", options);
