## ✅ Testing Summary

**Make the Add Reservation Form styled the same as the Update Reservation Form.**  
***Done*** — Styling consistency achieved between Add and Update forms.

**Test adding a reservation with valid data.**  
***Done*** — Reservation and customer record created successfully.

**Test adding a reservation with empty data.**  
***Done*** — No record added. Page remains on Add Reservation without submitting.

**Test adding a duplicate customer (same email or image).**  
***Done*** — Duplicate allowed; however, image was not duplicated in `uploads` folder.  
***Action Item:*** Prevent adding reservations with duplicate customer emails or image files.  
***Action Item:*** Also prevent these duplicates during update.  
***Testing suggestions:*** Preventing the image upload until form validation passed resolved this issue for Add Reservation. Update functionality now correctly prevents duplicate entries.  
**Issue:** Cancel operation during update still triggers changes.  
***Action Item:*** Fix cancel behavior to ensure no data/image is modified when Cancel is clicked.

**Test adding an invalid image format.**  
***Done*** — Invalid image format accepted and uploaded.  
***Action Item:*** Restrict to valid formats (e.g., `.jpg`, `.jpeg`, `.png`, `.webp`).

**Add a confirmation dialog before deleting a customer reservation.**  
***Done*** — Confirmation modal appears and works properly.

**Test updating a reservation with valid data.**  
***Done*** — Update works and reflects in the list.

**Test updating with empty data.**  
***Done*** — Form buttons disabled until all fields are filled.

**Test updating with invalid data.**  
***Done*** — Allows invalid formats such as numbers in names, bad email addresses, and future dates.  
***Action Item:*** Add input validation for all fields.

**Test adding customer with non-numeric spot count.**  
***Done*** — Prevented via input type.  
***Action Item:*** Add extra validation for edge cases via Angular form validation.

**Test updating image with invalid format.**  
***Done*** — Image accepted and stored.  
***Action Item:*** Block unsupported formats during update.

**Test deleting a reservation or customer.**  
***Done*** — Works fine and removes image from server.

**Test reservation list display.**  
***Done*** — Lists appear correctly with nested customers.  
***Action Item:*** Improve layout spacing and spacing consistency.

**Test list auto-refresh after Add or Update.**  
***Done*** — Updates reflected immediately.

**Test input character limits.**  
***Done*** — Long text triggers backend error.  
***Action Item:*** Limit input length using Angular + backend validation.

**Test Cancel in Update after changing fields.**  
***Done*** — Changes still submitted even after clicking Cancel.  
***Action Item:*** Ensure cancel returns without saving and deletes no image.

**Test when selecting two images in Add Reservation.**  
***Done*** — Only final image uploaded on actual submission.

**Test clicking Cancel in Add after selecting an image.**  
***Done*** — No record or image added. Behavior is as expected.

**Test About Us navigation from reservation list.**  
***Done*** — Button works as expected.

**Test return button from About Us to reservation list.**  
***Done*** — Button works properly and routes to home page.
