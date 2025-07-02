// reservation.ts
export interface Reservation {
  ID: number;
  customerName: string;
  conservationAreaName: string;
  reservationDate: string;
  reservationTime: string;
  partySize: number;
  total_spots: number;
  spots_booked: number;
}
