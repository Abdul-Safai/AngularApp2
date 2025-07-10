import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ReservationService } from '../reservation.service';
import { Reservation } from '../reservation';

@Component({
  selector: 'app-reservation-list',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './reservation-list.component.html',
  styleUrls: ['./reservation-list.component.css']
})
export class ReservationListComponent implements OnInit {
  reservations: Reservation[] = [];
  filterArea: string = '';
  areas: string[] = [];

  showConfirm = false;
  showUpdate = false;

  reservationIdToCancel: number | null = null;
  editCustomer: any = {};
  selectedUpdateFile: File | null = null; // ✅ Added

  constructor(private reservationService: ReservationService) {}

  ngOnInit(): void {
    this.loadReservations();
    this.reservationService.refreshNeeded$.subscribe(() => {
      this.loadReservations();
    });
  }

  get filteredReservations() {
    if (!this.filterArea) return this.reservations;
    return this.reservations.filter(
      res => res.conservationAreaName === this.filterArea
    );
  }

  loadReservations() {
    this.reservationService.getReservations().subscribe({
      next: (data: any[]) => {
        this.reservations = data.map(res => ({
          ...res,
          total_booked: Number(res.total_booked),
          total_spots: Number(res.total_spots),
          customers: res.customers?.map((cust: any) => ({
            ...cust,
            imageFileName: cust.imageFileName || '' // ✅ Always defined
          })) || []
        }));
        this.areas = [...new Set(this.reservations.map(r => r.conservationAreaName))];
      },
      error: (error) => {
        console.error('❌ Error fetching reservations:', error);
      }
    });
  }

  openConfirmCustomer(customerId: number) {
    this.reservationIdToCancel = customerId;
    this.showConfirm = true;
  }

  confirmCancel() {
    if (this.reservationIdToCancel) {
      this.reservationService.deleteReservationById(this.reservationIdToCancel).subscribe({
        next: () => {
          console.log(`✅ Reservation ID ${this.reservationIdToCancel} cancelled`);
          this.loadReservations();
        },
        error: (error) => {
          console.error('❌ Error cancelling reservation:', error);
        }
      });
      this.reservationIdToCancel = null;
      this.showConfirm = false;
    }
  }

  cancelConfirm() {
    this.reservationIdToCancel = null;
    this.showConfirm = false;
  }

  openUpdateCustomer(customer: any) {
    this.editCustomer = { ...customer };
    this.selectedUpdateFile = null; // ✅ Reset file
    this.showUpdate = true;
  }

  onUpdateFileSelected(event: any): void {
    this.selectedUpdateFile = event.target.files[0] || null;
    console.log('Selected update file:', this.selectedUpdateFile);
  }

  confirmUpdate() {
    const formData = new FormData();
    formData.append('ID', this.editCustomer.ID);
    formData.append('customerName', this.editCustomer.customerName);
    formData.append('conservationAreaName', this.editCustomer.conservationAreaName);
    formData.append('reservationDate', this.editCustomer.reservationDate);
    formData.append('reservationTime', this.editCustomer.reservationTime);
    formData.append('partySize', this.editCustomer.partySize.toString());

    if (this.selectedUpdateFile) {
      formData.append('customerImage', this.selectedUpdateFile, this.selectedUpdateFile.name);
    }

    this.reservationService.updateReservation(formData).subscribe({
      next: () => {
        console.log('✅ Reservation updated:', this.editCustomer);
        this.loadReservations();
      },
      error: (error) => {
        console.error('❌ Error updating reservation:', error);
      }
    });
    this.showUpdate = false;
  }

  cancelUpdate() {
    this.showUpdate = false;
  }
}
