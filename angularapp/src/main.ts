import { bootstrapApplication } from '@angular/platform-browser';
import { AppComponent } from './app/app'; // ✅ use correct class name and path
import { appConfig } from './app/app.config';

bootstrapApplication(AppComponent, appConfig)
  .catch(err => console.error(err));
