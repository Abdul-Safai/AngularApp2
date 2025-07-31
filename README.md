<h2>✅ Testing Summary</h2>

<ul>
  <li><strong>Make the Add Reservation Form styled the same as the Update Reservation Form.</strong><br>
    <em>Done</em> — Styling consistency achieved between Add and Update forms.
  </li>

  <li><strong>Test adding a reservation with valid data.</strong><br>
    <em>Done</em> — Reservation and customer record created successfully.
  </li>

  <li><strong>Test adding a reservation with empty data.</strong><br>
    <em>Done</em> — No record added. Page remains on Add Reservation without submitting.
  </li>

  <li><strong>Test adding a duplicate customer (same email or image).</strong><br>
    <em>Done</em> — Duplicate allowed; however, image was not duplicated in uploads folder.<br>
    <strong>Action Item:</strong> Prevent adding reservations with duplicate customer emails or image files.<br>
    <strong>Testing suggestions:</strong> Preventing the image upload until form validation passed resolved this issue for Add Reservation. Update functionality now correctly prevents duplicate entries.
  </li>

  <li><strong>Test adding an invalid image format.</strong><br>
    <em>Done</em> — Invalid image format accepted and uploaded.<br>
    <strong>Action Item:</strong> Restrict to valid formats (.jpg, .jpeg, .png, .webp).
  </li>

  <li><strong>Add a confirmation dialog before deleting a customer reservation.</strong><br>
    <em>Done</em> — Confirmation modal appears and works properly.
  </li>

  <li><strong>Test updating a reservation with valid data.</strong><br>
    <em>Done</em> — Update works and reflects in the list.
  </li>

  <li><strong>Test updating with empty data.</strong><br>
    <em>Done</em> — Form buttons disabled until all fields are filled.
  </li>

  <li><strong>Test updating with invalid data.</strong><br>
    <em>Done</em> — Allows invalid formats like numbers in names or bad emails.<br>
    <strong>Action Item:</strong> Add input validation for all fields.
  </li>

  <li><strong>Test adding customer with non-numeric spot count.</strong><br>
    <em>Done</em> — Prevented via input type.<br>
    <strong>Action Item:</strong> Add extra validation via Angular for edge cases.
  </li>

  <li><strong>Test updating image with invalid format.</strong><br>
    <em>Done</em> — Image accepted and stored.<br>
    <strong>Action Item:</strong> Block unsupported formats during update.
  </li>

  <li><strong>Test deleting a reservation or customer.</strong><br>
    <em>Done</em> — Works fine and removes image from server.
  </li>

  <li><strong>Test reservation list display.</strong><br>
    <em>Done</em> — Lists appear correctly with nested customers.<br>
    <strong>Action Item:</strong> Improve layout spacing and consistency.
  </li>

  <li><strong>Test list auto-refresh after Add or Update.</strong><br>
    <em>Done</em> — Updates reflected immediately.
  </li>

  <li><strong>Test input character limits.</strong><br>
    <em>Done</em> — Long text triggered backend error.<br>
    <strong>Action Item:</strong> Limit input length via Angular + backend validation.
  </li>

  <li><strong>Test Cancel in Update after changing fields.</strong><br>
    <em>Done</em> — Changes still submitted even after clicking Cancel.<br>
    <strong>Action Item:</strong> Ensure Cancel returns without saving and deletes no image.
  </li>

  <li><strong>Test when selecting two images in Add Reservation.</strong><br>
    <em>Done</em> — Only final image uploaded on actual submission.
  </li>

  <li><strong>Test clicking Cancel in Add after selecting an image.</strong><br>
    <em>Done</em> — No record or image added. Behavior is as expected.
  </li>

  <li><strong>Test About Us navigation from reservation list.</strong><br>
    <em>Done</em> — Button works as expected.
  </li>

  <li><strong>Test return button from About Us to reservation list.</strong><br>
    <em>Done</em> — Button works properly and routes to home page.
  </li>

  <hr>

  <h3>✅ Additional Features Implemented</h3>
  <li><strong>Login Lockout After 3 Failed Attempts</strong><br>
    Users are temporarily locked out for 5 minutes after 3 failed login attempts.<br>
    ✔️ Tested: Lockout activates, countdown shows, form disabled, alert displays.
  </li>

  <li><strong>Email Notifications</strong><br>
    Confirmation emails sent after Add and Update, including full reservation details.
  </li>

  <li><strong>Success Messages & UI Feedback</strong><br>
    Alerts shown after form actions. Update form includes image preview and validation.
  </li>

  <li><strong>Conservation Area Dropdown Fix</strong><br>
    Full area list always shown on update form, even if original area is not in current reservations.
  </li>
</ul>
