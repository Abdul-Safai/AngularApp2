import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ReservationService } from '../reservation.service';

@Component({
  selector: 'app-update-reservation',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './update-reservation.component.html',
  styleUrls: ['./update-reservation.component.css']
})
export class UpdateReservationComponent implements OnInit {
  customer: any = {};
  originalCustomer: any = {};
  areas: string[] = [
    'South Conservation Area',
    'North Conservation Area',
    'East Conservation Area',
    'West Conservation Area'
  ];
  selectedUpdateFile: File | null = null;
  imagePreviewUrl: string | null = null;
  imageAlert: string = '';
  loading: boolean = true;
  successMessage: string = '';
  today: string = new Date().toISOString().split('T')[0];

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private reservationService: ReservationService
  ) {}

  ngOnInit(): void {
    const id = this.route.snapshot.paramMap.get('id');
    if (!id) {
      this.router.navigate(['/home']);
      return;
    }

    this.reservationService.getReservations().subscribe(data => {
      for (let res of data) {
        for (let cust of res.customers || []) {
          if (cust.ID == +id) {
            this.customer = { ...cust };
            this.originalCustomer = { ...cust };
          }
        }
      }
      this.loading = false;
    });
  }

  onUpdateFileSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
      const file = input.files[0];
      const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];

      if (!allowedTypes.includes(file.type)) {
        this.imageAlert = '❌ Only JPG, JPEG, PNG, or WEBP images are allowed.';
        this.selectedUpdateFile = null;
        this.imagePreviewUrl = null;
        return;
      }

      this.imageAlert = '';
      this.selectedUpdateFile = file;

      const reader = new FileReader();
      reader.onload = () => {
        this.imagePreviewUrl = reader.result as string;
      };
      reader.readAsDataURL(file);
    }
  }

  onSubmit(): void {
    const formData = new FormData();
    formData.append('ID', this.customer.ID);

    if (this.customer.customerName.trim() !== this.originalCustomer.customerName) {
      formData.append('customerName', this.customer.customerName.trim());
    }

    if (this.customer.emailAddress !== this.originalCustomer.emailAddress) {
      formData.append('emailAddress', this.customer.emailAddress.trim());
    }

    if (this.customer.conservationAreaName !== this.originalCustomer.conservationAreaName) {
      formData.append('conservationAreaName', this.customer.conservationAreaName);
    }

    if (this.customer.reservationDate !== this.originalCustomer.reservationDate) {
      formData.append('reservationDate', this.customer.reservationDate);
    }

    if (this.customer.reservationTime !== this.originalCustomer.reservationTime) {
      formData.append('reservationTime', this.customer.reservationTime);
    }

    if (this.customer.partySize !== this.originalCustomer.partySize) {
      formData.append('partySize', this.customer.partySize.toString());
    }

    if (this.selectedUpdateFile) {
      formData.append('customerImage', this.selectedUpdateFile, this.selectedUpdateFile.name);
    }

    if (formData.entries().next().done) {
      alert('❗ No changes were made.');
      return;
    }

    this.reservationService.updateReservation(formData).subscribe({
      next: (res: any) => {
        this.successMessage = '✅ Reservation updated successfully. Confirmation email sent!';
        this.imagePreviewUrl = null;
        this.selectedUpdateFile = null;
        setTimeout(() => this.router.navigate(['/home']), 3000);
      },
      error: err => {
        alert('❌ Failed to update. Try again.');
        console.error(err);
      }
    });
  }

  goBack(): void {
    this.router.navigate(['/home']);
  }
}
