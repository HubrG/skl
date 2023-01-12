
const TurboHelper = class {

    constructor() {
        document.addEventListener('turbo:before-cache', () => {

           


        });
        document.addEventListener('turbo:before-fetch-response', function (event) {
          
            
          });
        document.addEventListener('turbo:submit-start', (event) => {
            const submitter = event.detail.formSubmission.submitter;
            submitter.toggleAttribute('disabled', true);
            submitter.classList.add('turbo-submit-disabled');
        })

        document.addEventListener('turbo:render', () => {
            this.initializeWeatherWidget();
            init()
        });

        this.initializeTransitions();
    
    }


    initializeTransitions() {
        
        document.addEventListener('turbo:visit', () => {
            // fade out the old body
            document.body.classList.add('turbo-loading');
        });

        document.addEventListener('turbo:before-render', (event) => {
            if (this.isPreviewRendered()) {
                // this is a preview that has been instantly swapped
                // remove .turbo-loading so the preview starts fully opaque
                event.detail.newBody.classList.remove('turbo-loading');
                // start fading out 1 frame later after opacity starts full
                requestAnimationFrame(() => {
                    document.body.classList.add('turbo-loading');
                });
            } else {
                const isRestoration = event.detail.newBody.classList.contains('turbo-loading');
                if (isRestoration) {
                    // this is a restoration (back button). Remove the class
                    // so it simply starts with full opacity

                    event.detail.newBody.classList.remove('turbo-loading');

                    return;
                }

                // when we are *about* to render a fresh page
                // we should already be faded out, so start us faded out
                event.detail.newBody.classList.add('turbo-loading');
            }
  });
        
        document.addEventListener('turbo:render', () => {
            if (!this.isPreviewRendered()) {
                // if this is a preview, then we do nothing: stay faded out
                // after rendering the REAL page, we first allow the .turbo-loading to
                // instantly start the page at lower opacity. THEN remove the class
                // one frame later, which allows the fade in
                requestAnimationFrame(() => {
                    document.body.classList.remove('turbo-loading');
                });
            }

        });

    }
    reenableSubmitButtons() {
        document.querySelectorAll('.turbo-submit-disabled').forEach((button) => {
            button.toggleAttribute('disabled', false);
            button.classList.remove('turbo-submit-disabled');
        });
    }
}

export default new TurboHelper();


