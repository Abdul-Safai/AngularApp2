export interface Reservation {
  conservationAreaName: string;
  reservationDate: string;
  reservationTime: string;
  total_booked: number;
  total_spots: number;

  // ✅ Each reservation has a list of customers
  customers: {
    ID: number;                // ✅ Needed for cancel/update
    customerName: string;
    spots_booked: number;

    // ✅ Optional image URL for customer
    imageUrl?: string;
  }[];

  // ✅ OPTIONAL: you can also add available spots as a derived field if needed
  // available_spots?: number; // <-- optional, you can also calculate this in your template
}
