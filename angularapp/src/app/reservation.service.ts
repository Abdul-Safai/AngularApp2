import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';   // ✅ This line!
import { Observable } from 'rxjs';
import { Reservation } from './reservation';

@Injectable({
  providedIn: 'root'
})
export class ReservationService {
  private apiUrl = 'http://localhost/AngularApp2/angularapp_api/get_reservations.php';

  constructor(private http: HttpClient) {}   // ✅ Use it here!

  getReservations(): Observable<Reservation[]> {
    return this.http.get<Reservation[]>(this.apiUrl);
  }

  createReservation(reservation: any) {
    return this.http.post(
      'http://localhost/AngularApp2/angularapp_api/add_reservation.php',
      reservation
    );
  }
}
