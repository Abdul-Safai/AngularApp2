import { Component } from '@angular/core';
import { ReservationListComponent } from './reservation-list/reservation-list.component';
import { AddReservationComponent } from './add-reservation.component'; // ✅ Add your form!

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [
    ReservationListComponent, // ✅ Your table
    AddReservationComponent   // ✅ Your form
  ],
  templateUrl: './app.html',
  styleUrls: ['./app.css']
})
export class App {
  protected title = 'angularapp';
}
