import { Routes } from '@angular/router';
import { HomeComponent } from './reservation-list/home.component';
import { UpdateReservationComponent } from './reservation-list/update-reservation.component';

export const routes: Routes = [
  { path: '', component: HomeComponent },
  { path: 'update-reservation/:id', component: UpdateReservationComponent }
];
