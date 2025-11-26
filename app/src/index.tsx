import 'moment/locale/de';

import * as log from 'loglevel';
import moment from 'moment';
import * as React from 'react';
import * as ReactDOM from 'react-dom';
import { BrowserRouter } from 'react-router-dom';

import DateMomentUtils from '@date-io/moment';
import { MuiThemeProvider } from '@material-ui/core';
import { MuiPickersUtilsProvider } from '@material-ui/pickers';

import App from './App';
import Formats from './js/constants/formats';
import getTheme from './js/constants/theme';
import { ServiceWorkerRegistrationService } from './js/services/ServiceWorkerRegistrationService';
import { DeprecationService } from './js/services/DeprecationService';

if (process.env.NODE_ENV === "production") {
  log.setLevel(log.levels.DEBUG);
} else {
  log.enableAll();
}

moment.locale(Formats.DATE.LOCALE);

ReactDOM.render(
  <MuiThemeProvider theme={getTheme()}>
    <MuiPickersUtilsProvider utils={DateMomentUtils} locale={Formats.DATE.LOCALE}>
      <BrowserRouter basename={process.env.PUBLIC_URL}>
        <App />
      </BrowserRouter>
    </MuiPickersUtilsProvider>
  </MuiThemeProvider>,
  document.getElementById("root") as HTMLElement
);

// DeprecationService.run();
ServiceWorkerRegistrationService.register();