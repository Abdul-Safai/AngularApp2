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
  areas: string[] = [];
  selectedUpdateFile: File | null = null;
  loading: boolean = true;

  constructor(
    private route: ActivatedRoute,
    public router: Router,
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
        this.areas.push(res.conservationAreaName);
      }
      this.areas = [...new Set(this.areas)];
      this.loading = false;
    });
  }

  onUpdateFileSelected(event: any): void {
    this.selectedUpdateFile = event.target.files[0] || null;
  }

  onSubmit(): void {
    const formData = new FormData();
    formData.append('ID', this.customer.ID);

    if (
      this.customer.customerName &&
      this.customer.customerName.trim() !== this.originalCustomer.customerName
    ) {
      formData.append('customerName', this.customer.customerName.trim());
    }

    if (
      this.customer.conservationAreaName &&
      this.customer.conservationAreaName !== this.originalCustomer.conservationAreaName
    ) {
      formData.append('conservationAreaName', this.customer.conservationAreaName);
    }

    if (
      this.customer.reservationDate &&
      this.customer.reservationDate !== this.originalCustomer.reservationDate &&
      this.customer.reservationDate.match(/^\d{4}-\d{2}-\d{2}$/)
    ) {
      formData.append('reservationDate', this.customer.reservationDate);
    }

    if (
      this.customer.reservationTime &&
      this.customer.reservationTime !== this.originalCustomer.reservationTime
    ) {
      formData.append('reservationTime', this.customer.reservationTime);
    }

    if (
      this.customer.partySize &&
      this.customer.partySize !== this.originalCustomer.partySize
    ) {
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
      next: () => {
        alert('✅ Reservation updated successfully!');
        this.router.navigate(['/home']);
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
