import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReservationService } from '../reservation.service';
import { Reservation } from '../reservation';

@Component({
  selector: 'app-reservation-list',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './reservation-list.component.html',
  styleUrls: ['./reservation-list.component.css']
})
export class ReservationListComponent implements OnInit {
  reservations: Reservation[] = [];

  // ✅ New modal state
  showConfirm = false;
  pendingId: number | null = null;

  constructor(private reservationService: ReservationService) {}

  ngOnInit(): void {
    this.loadReservations();

    this.reservationService.refreshNeeded$.subscribe(() => {
      this.loadReservations();
    });
  }

  loadReservations() {
    this.reservationService.getReservations().subscribe({
      next: (data: Reservation[]) => {
        this.reservations = data;
      },
      error: (error) => {
        console.error('Error fetching reservations:', error);
      }
    });
  }

  // ✅ New: open custom modal
  openConfirm(id: number) {
    this.pendingId = id;
    this.showConfirm = true;
  }

  // ✅ New: confirm deletion
  confirmCancel() {
    if (this.pendingId !== null) {
      this.reservationService.deleteReservation(this.pendingId).subscribe({
        next: () => {
          console.log(`✅ Reservation ${this.pendingId} cancelled`);
          this.loadReservations();
        },
        error: (error) => {
          console.error('❌ Error cancelling reservation:', error);
        }
      });
      this.pendingId = null;
      this.showConfirm = false;
    }
  }

  // ✅ New: close modal without deleting
  cancelConfirm() {
    this.pendingId = null;
    this.showConfirm = false;
  }
}
