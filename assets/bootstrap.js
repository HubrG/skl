import { startStimulusApp } from "@symfony/stimulus-bridge";
import { ShowChapter } from "./scripts/Publication/ChapterShow";

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

/* global EpubMaker */
