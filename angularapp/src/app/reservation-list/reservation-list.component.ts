import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms'; // ✅ Needed for ngModel
import { ReservationService } from '../reservation.service';
import { Reservation } from '../reservation';

@Component({
  selector: 'app-reservation-list',
  standalone: true,
  imports: [CommonModule, FormsModule], // ✅ Use FormsModule for [(ngModel)]
  templateUrl: './reservation-list.component.html',
  styleUrls: ['./reservation-list.component.css']
})
export class ReservationListComponent implements OnInit {
  reservations: Reservation[] = [];

  filterArea: string = '';     // ✅ Area filter binding
  areas: string[] = [];        // ✅ Unique areas for dropdown

  showConfirm = false;
  showUpdate = false;

  reservationIdToCancel: number | null = null;
  editCustomer: any = {};

  constructor(private reservationService: ReservationService) {}

  ngOnInit(): void {
    this.loadReservations();
    this.reservationService.refreshNeeded$.subscribe(() => {
      this.loadReservations();
    });
  }

  // ✅ Getter to apply filter dynamically
  get filteredReservations() {
    if (!this.filterArea) {
      return this.reservations;
    }
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
          customers: res.customers || []
        }));
        // ✅ Build unique areas list for filter dropdown
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
    this.showUpdate = true;
  }

  confirmUpdate() {
    this.reservationService.updateReservation(this.editCustomer).subscribe({
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
