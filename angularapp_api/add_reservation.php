import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { HttpClient, HttpHeaders } from '@angular/common/http';

@Component({
  selector: 'app-add-reservation',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './add-reservation.html',
  styleUrls: ['./add-reservation.css']
})
export class AddReservationComponent {
  reservation = {
    customerName: '',
    conservationAreaName: '',
    reservationDate: '',
    reservationTime: '',
    partySize: 1
  };

  areas: string[] = [
    'East Conservation Area',
    'West Conservation Area',
    'South Conservation Area',
    'North Conservation Area'
  ];

  selectedFile: File | null = null;

  constructor(private http: HttpClient, private router: Router) {}

  onFileSelected(event: any) {
    this.selectedFile = event.target.files[0] || null;
  }

  onSubmit() {
    const formData = new FormData();
    formData.append('customerName', this.reservation.customerName);
    formData.append('conservationAreaName', this.reservation.conservationAreaName);
    formData.append('reservationDate', this.reservation.reservationDate);
    formData.append('reservationTime', this.reservation.reservationTime);
    formData.append('partySize', this.reservation.partySize.toString());
    formData.append('spots_booked', this.reservation.partySize.toString());
    formData.append('total_spots', '30');

    if (this.selectedFile) {
      formData.append('customerImage', this.selectedFile, this.selectedFile.name);
    }

    this.http.post('http://localhost/AngularApp2/angularapp_api/add_reservation.php', formData)
      .subscribe({
        next: () => {
          alert('✅ Reservation added successfully!');
          this.router.navigate(['/']);
        },
        error: (err) => {
          console.error('❌ Failed to add reservation:', err);
          alert('Something went wrong. Try again.');
        }
      });
  }
}
