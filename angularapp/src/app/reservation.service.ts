import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, Subject } from 'rxjs';
import { tap } from 'rxjs/operators';
import { Reservation } from './reservation';

@Injectable({
  providedIn: 'root'
})
export class ReservationService {
  private apiUrl = 'http://localhost/AngularApp2/angularapp_api/get_reservations.php';

  private refreshNeeded = new Subject<void>();
  get refreshNeeded$() {
    return this.refreshNeeded.asObservable();
  }

  constructor(private http: HttpClient) {}

  // ✅ Fetch all reservations
  getReservations(): Observable<Reservation[]> {
    return this.http.get<Reservation[]>(this.apiUrl);
  }

  // ✅ Create a new reservation WITH FILE UPLOAD
  createReservation(formData: FormData) {
    return this.http.post(
      'http://localhost/AngularApp2/angularapp_api/create_reservation.php', // ✅ POINT TO THE CORRECT FILE!
      formData
    ).pipe(
      tap(() => {
        console.log('✅ Emitting refresh after create!');
        this.refreshNeeded.next();
      })
    );
  }

  // ✅ Delete a reservation by ID
  deleteReservationById(id: number) {
    return this.http.post(
      'http://localhost/AngularApp2/angularapp_api/delete_reservation.php',
      { id: id }
    ).pipe(
      tap(() => {
        console.log('✅ Emitting refresh after delete:', id);
        this.refreshNeeded.next();
      })
    );
  }

  // ✅ Update an existing reservation by ID
  updateReservation(reservation: any) {
    return this.http.post(
      'http://localhost/AngularApp2/angularapp_api/update_reservation.php',
      reservation
    ).pipe(
      tap(() => {
        console.log('✅ Emitting refresh after update:', reservation);
        this.refreshNeeded.next();
      })
    );
  }
}
