export interface Reservation {
  conservationAreaName: string;
  reservationDate: string;
  reservationTime: string;
  total_booked: number;
  total_spots: number;

  customers: {
    ID: number;
    customerName: string;
    spots_booked: number;
    imageFileName?: string;  // ✅ This is what your API returns
  }[];
}
