import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReservationService } from '../reservation.service';
import { Reservation } from '../reservation';

@Component({
  selector: 'app-reservation-list',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './reservation-list.component.html',
  styleUrls: ['./reservation-list.component.css']
})
export class ReservationListComponent implements OnInit {
  reservations: Reservation[] = [];

  constructor(private reservationService: ReservationService) {}

  ngOnInit(): void {
    this.reservationService.getReservations().subscribe({
      next: (data: Reservation[]) => {
        this.reservations = data;
      },
      error: (error) => {
        console.error('Error fetching reservations:', error);
      }
    });
  }
}
