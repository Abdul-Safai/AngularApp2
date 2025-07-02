import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, Subject } from 'rxjs';
import { tap } from 'rxjs/operators';
import { Reservation } from './reservation';

@Injectable({
  providedIn: 'root'
})
export class ReservationService {
  private getUrl = 'http://localhost/AngularApp2/angularapp_api/get_reservations.php';
  private addUrl = 'http://localhost/AngularApp2/angularapp_api/add_reservation.php';
  private deleteUrl = 'http://localhost/AngularApp2/angularapp_api/delete_reservation.php';

  // ✅ Subject to trigger auto-refresh
  private refreshNeeded = new Subject<void>();
  get refreshNeeded$() {
    return this.refreshNeeded.asObservable();
  }

  constructor(private http: HttpClient) {}

  getReservations(): Observable<Reservation[]> {
    return this.http.get<Reservation[]>(this.getUrl);
  }

  createReservation(reservation: any) {
    return this.http.post(this.addUrl, reservation).pipe(
      tap(() => {
        console.log('✅ Reservation added — emitting refresh');
        this.refreshNeeded.next();
      })
    );
  }

  deleteReservation(id: number) {
    return this.http.post(this.deleteUrl, { id: id }).pipe(
      tap(() => {
        console.log(`✅ Reservation ${id} deleted — emitting refresh`);
        this.refreshNeeded.next();
      })
    );
  }
}
