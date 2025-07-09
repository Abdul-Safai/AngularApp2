import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ReservationService } from '../app/reservation.service';

@Component({
  selector: 'app-add-reservation',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './add-reservation.component.html',
  styleUrls: ['./add-reservation.component.css']
})
export class AddReservationComponent {
  newReservation = {
    customerName: '',
    conservationAreaName: '',
    reservationDate: '',
    reservationTime: '',
    partySize: 1
  };

  successMessage: string | null = null;
  showForm: boolean = false; // ✅ Toggle flag for the form

  constructor(private reservationService: ReservationService) {}

  toggleForm(): void {
    this.showForm = !this.showForm;
  }

  onSubmit(): void {
    this.reservationService.createReservation(this.newReservation).subscribe({
      next: (response: any) => {
        console.log('Reservation added:', response);
        this.successMessage = '✅ Reservation added successfully!';

        this.newReservation = {
          customerName: '',
          conservationAreaName: '',
          reservationDate: '',
          reservationTime: '',
          partySize: 1
        };

        setTimeout(() => {
          this.successMessage = null;
        }, 2000);

        this.showForm = false; // ✅ Auto-hide after submit
      },
      error: (error: any) => {
        console.error('Error:', error.error?.details ?? error);
        this.successMessage = '❌ Failed to add reservation. Please try again.';
      }
    });
  }
}
