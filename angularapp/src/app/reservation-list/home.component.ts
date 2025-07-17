import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { AddReservationComponent } from '../add-reservation.component';
import { ReservationListComponent } from './reservation-list.component';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule, FormsModule, AddReservationComponent, ReservationListComponent],
  template: `
    <header class="main-header">
      <h1>Reservation System</h1>
    </header>

    <app-add-reservation></app-add-reservation>
    <app-reservation-list></app-reservation-list>
  `
})
export class HomeComponent {}
