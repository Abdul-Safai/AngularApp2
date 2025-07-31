export interface Reservation {
  conservationAreaName: string;
  reservationDate: string;
  reservationTime: string;
  total_booked: number;
  total_spots: number;
  customers: {
    ID: number;
    customerName: string;
    emailAddress?: string; // ✅ Add this line
    spots_booked: number;
    imageFileName?: string;
  }[];
}
