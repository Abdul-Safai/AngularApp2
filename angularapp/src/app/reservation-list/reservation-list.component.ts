import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { ReservationService } from '../reservation.service';
import { Reservation } from '../reservation';

@Component({
  selector: 'app-reservation-list',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './reservation-list.component.html',
  styleUrls: ['./reservation-list.component.css']
})
export class ReservationListComponent implements OnInit {
  reservations: Reservation[] = [];
  filterArea: string = '';
  areas: string[] = ['East Conservation Area', 'West Conservation Area', 'South Conservation Area', 'North Conservation Area'];

  showConfirm = false;
  reservationIdToCancel: number | null = null;

  constructor(private reservationService: ReservationService) {}

  ngOnInit(): void {
    this.loadReservations();
    this.reservationService.refreshNeeded$.subscribe(() => {
      this.loadReservations();
    });
  }

  get filteredReservations() {
    if (!this.filterArea) return this.reservations;
    return this.reservations.filter(res => res.conservationAreaName === this.filterArea);
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
            imageFileName: cust.imageFileName || ''
          })) || []
        }));
      },
      error: (error) => {
        console.error('❌ Error fetching reservations:', error);
      }
    });
  }

  getImageSrc(imageFileName: string | null | undefined): string {
  if (imageFileName && imageFileName.trim() !== '') {
    return 'http://localhost/AngularApp2/angularapp_api/uploads/' + imageFileName.trim();
  }
  return 'assets/images/placeholder.png';
}


  onImageError(event: any) {
    event.target.src = 'assets/images/placeholder.png';
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
}
