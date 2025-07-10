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

  selectedFile: File | null = null;
  successMessage: string | null = null;
  showForm: boolean = false;

  constructor(private reservationService: ReservationService) {}

  toggleForm(): void {
    this.showForm = !this.showForm;
  }

  onFileSelected(event: any): void {
    this.selectedFile = event.target.files[0] || null;
    console.log('Selected file:', this.selectedFile);
  }

  onSubmit(): void {
    const formData = new FormData();
    formData.append('customerName', this.newReservation.customerName);
    formData.append('conservationAreaName', this.newReservation.conservationAreaName);
    formData.append('reservationDate', this.newReservation.reservationDate);
    formData.append('reservationTime', this.newReservation.reservationTime);
    formData.append('partySize', this.newReservation.partySize.toString());

    if (this.selectedFile) {
      formData.append('customerImage', this.selectedFile, this.selectedFile.name);
    }

    this.reservationService.createReservation(formData).subscribe({
      next: (response: any) => {
        console.log('✅ Reservation added:', response);
        this.successMessage = '✅ Reservation added successfully!';
        this.resetForm();
      },
      error: (error: any) => {
        console.error('❌ Error:', error.error?.details ?? error);
        this.successMessage = '❌ Failed to add reservation. Please try again.';
      }
    });
  }

  resetForm(): void {
    this.newReservation = {
      customerName: '',
      conservationAreaName: '',
      reservationDate: '',
      reservationTime: '',
      partySize: 1
    };
    this.selectedFile = null;
    this.showForm = false;

    setTimeout(() => {
      this.successMessage = null;
    }, 2000);
  }
}
