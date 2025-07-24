import { Routes } from '@angular/router';
import { WelcomeComponent } from './pages/welcome/welcome';
import { HomeComponent } from './reservation-list/home.component';
import { AddReservationComponent } from './add-reservation/add-reservation.component';
import { UpdateReservationComponent } from './reservation-list/update-reservation.component';
import { AboutUsComponent } from './about-us/about-us';
import { LoginComponent } from './pages/login/login.component';
import { RegisterComponent } from './pages/register/register.component';

export const routes: Routes = [
  { path: '', component: WelcomeComponent },
  { path: 'home', component: HomeComponent },
  { path: 'add-reservation', component: AddReservationComponent },
  { path: 'update-reservation/:id', component: UpdateReservationComponent },
  { path: 'about-us', component: AboutUsComponent },
  { path: 'login', component: LoginComponent },
  { path: 'register', component: RegisterComponent }
];
