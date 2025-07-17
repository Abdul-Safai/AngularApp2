import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ReservationListComponent } from './reservation-list.component';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule, FormsModule, ReservationListComponent],
  template: `
    <header class="list-header">
  <h1 class="main-title">Reservation System</h1>
</header>

    <app-reservation-list></app-reservation-list>
  `
})
export class HomeComponent {}
