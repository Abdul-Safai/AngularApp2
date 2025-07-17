import { Routes } from '@angular/router';
import { HomeComponent } from './reservation-list/home.component';
import { UpdateReservationComponent } from './reservation-list/update-reservation.component';
import { AddReservationComponent } from './add-reservation/add-reservation.component'; // ✅ Make sure this import is correct

export const routes: Routes = [
  { path: '', component: HomeComponent },
  { path: 'update-reservation/:id', component: UpdateReservationComponent },
  { path: 'add-reservation', component: AddReservationComponent } // ✅ Add this route
];
