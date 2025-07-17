import { Routes } from '@angular/router';
import { HomeComponent } from './reservation-list/home.component';
import { UpdateReservationComponent } from './reservation-list/update-reservation.component';
import { AddReservationComponent } from './add-reservation/add-reservation.component';
import { AboutUsComponent } from './about-us/about-us'; // ✅ Import AboutUsComponent

export const routes: Routes = [
  { path: '', component: HomeComponent },
  { path: 'update-reservation/:id', component: UpdateReservationComponent },
  { path: 'add-reservation', component: AddReservationComponent },
  { path: 'about-us', component: AboutUsComponent } // ✅ MAKE SURE path is 'about-us'
];
