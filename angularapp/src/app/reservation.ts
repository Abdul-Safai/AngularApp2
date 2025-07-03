export interface Reservation {
  conservationAreaName: string;
  reservationDate: string;
  reservationTime: string;
  total_booked: number;
  total_spots: number;
  customers: {
    ID: number;                // ✅ this must exist!
    customerName: string;
    spots_booked: number;
  }[];
}
