import { startStimulusApp } from '@symfony/stimulus-bridge';

// Registers Stimulus controllers from controllers.json and in the controllers/ directory
export const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.[jt]sx?$/
));

// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);

// differents modules
import Dropdown from 'stimulus-dropdown'
import { Application } from '@hotwired/stimulus'
import Notification from 'stimulus-notification'
import { Datepicker } from 'stimulus-datepicker'

const application = Application.start()
application.register('dropdown', Dropdown)
application.register('notification', Notification)
application.register('datepicker', Datepicker)


