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
  customerEmail = '';
  conservationAreaName = '';
  reservationDate = '';
  reservationTime = '';
  partySize: number = 1;
  selectedImage!: File;

  alertMessage: string = '';
  successMessage: string = '';
  today: string = new Date().toISOString().split('T')[0];

  areas = [
    'South Conservation Area',
    'North Conservation Area',
    'East Conservation Area',
    'West Conservation Area'
  ];

  constructor(
    private reservationService: ReservationService,
    private router: Router
  ) {}

  onFileSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
      this.selectedImage = input.files[0];
    }
  }

  submitReservation(): void {
    const formData = new FormData();
    formData.append('customerName', this.customerName);
    formData.append('emailAddress', this.customerEmail);
    formData.append('conservationAreaName', this.conservationAreaName);
    formData.append('reservationDate', this.reservationDate);
    formData.append('reservationTime', this.reservationTime);
    formData.append('partySize', this.partySize.toString());
  
    if (this.selectedImage) {
      formData.append('customerImage', this.selectedImage, this.selectedImage.name);
    }
  
    this.reservationService.createReservation(formData).subscribe({
      next: (response: any) => {
        console.log('✅ Reservation successful:', response);
        this.successMessage = '✅ Reservation created successfully. Confirmation email sent!';
        setTimeout(() => this.router.navigate(['/home']), 3000);
      },
      error: err => {
        if (err.status === 409 && err.error?.error?.includes('Duplicate')) {
          this.alertMessage = '❌ Duplicate reservation! Please choose a different time.';
        } else {
          this.alertMessage = '❌ Failed to create reservation. Please provide all required information.';
        }
        console.error('❌ Error creating reservation', err);
      }
    });
  }
  

  closeAlert(): void {
    this.alertMessage = '';
    this.successMessage = '';
  }

  goToList(): void {
    this.router.navigate(['/home']);
  }
}
