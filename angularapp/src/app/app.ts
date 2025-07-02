import { Component } from '@angular/core';
import { ReservationListComponent } from './reservation-list/reservation-list.component';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [ReservationListComponent],
  templateUrl: './app.html',
  styleUrls: ['./app.css']  // ✅ Plural: styleUrls
})
export class App {
  protected title = 'angularapp';
}
