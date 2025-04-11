<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-info" id="viewModalLabel">Appointment Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true" class="text-danger">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="viewDetails">
        <!-- Appointment details will be loaded here via AJAX -->
      </div>
    </div>
  </div>
</div>

<!-- Reschedule Modal -->
<div class="modal fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="rescheduleForm">
        <div class="modal-header">
          <h5 class="modal-title text-warning" id="rescheduleModalLabel">Reschedule Appointment</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true" class="text-danger">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="appointment_id" id="rescheduleAppointmentId">
          <div class="form-group">
            <label for="new_date">New Date:</label>
            <input type="date" name="new_date" id="new_date" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="new_time">New Time:</label>
            <select name="new_time" id="new_time" class="form-control" required>
              <!-- Options will be populated by JavaScript -->
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning">Reschedule</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="cancelForm">
        <div class="modal-header">
          <h5 class="modal-title text-danger" id="cancelModalLabel">Cancel Appointment</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true" class="text-danger">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="appointment_id" id="cancelAppointmentId">
          <p>Are you sure you want to cancel this appointment?</p>
          <div class="form-group">
            <label for="cancellation_reason">Reason for cancellation:</label>
            <textarea name="cancellation_reason" id="cancellation_reason" class="form-control" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger">Yes, Cancel</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">No, Keep It</button>
        </div>
      </form>
    </div>
  </div>
</div>
