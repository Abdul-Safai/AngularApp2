import { Component } from '@angular/core';
import { RouterModule } from '@angular/router';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterModule], // ✅ Needed for <router-outlet>
  templateUrl: './app.html'
})
export class AppComponent {} // ✅ You can name it AppComponent or App, just be consistent
