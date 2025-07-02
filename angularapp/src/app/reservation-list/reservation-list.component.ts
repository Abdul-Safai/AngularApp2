import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';      // ✅ Required for *ngFor
import { ReservationService } from '../reservation.service';
import { Reservation } from '../reservation';

@Component({
  selector: 'app-reservation-list',
  standalone: true,
  imports: [CommonModule],                           // ✅ Must import CommonModule
  templateUrl: './reservation-list.component.html'   // ✅ File must exist!
})
export class ReservationListComponent implements OnInit {
  reservations: Reservation[] = [];

  constructor(private reservationService: ReservationService) {}

  ngOnInit(): void {
    this.reservationService.getReservations().subscribe(data => {
      this.reservations = data;
    });
  }
}
