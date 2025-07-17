import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ReservationService } from '../reservation.service';

@Component({
  selector: 'app-add-reservation',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './add-reservation.component.html',
  styleUrls: ['./add-reservation.component.css']
})
export class AddReservationComponent {
  customerName = '';
  conservationAreaName = '';
  reservationDate = '';
  reservationTime = '';
  partySize: number = 1;

  areas = ['Lakeview Park', 'Greenhill Trail', 'Sunset Woods', 'Maple Creek'];

  constructor(private reservationService: ReservationService, private router: Router) {}

  submitReservation() {
    const formData = new FormData();
    formData.append('customerName', this.customerName);
    formData.append('conservationAreaName', this.conservationAreaName);
    formData.append('reservationDate', this.reservationDate);
    formData.append('reservationTime', this.reservationTime);
    formData.append('partySize', this.partySize.toString());

    this.reservationService.createReservation(formData).subscribe({
      next: () => {
        alert('Reservation successfully added!');
        this.router.navigate(['/']);
      },
      error: err => console.error('Error creating reservation', err)
    });
  }

  goToList() {
    this.router.navigate(['/']);
  }
}
