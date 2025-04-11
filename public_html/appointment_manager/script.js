// script.js

$(document).ready(function() {
    // Handle View button click
    $('.btn-view').on('click', function() {
        var appointmentId = $(this).data('id');
        $('#viewDetails').html('Loading...');
        $.ajax({
            url: 'view_appointment.php',
            type: 'GET',
            data: { id: appointmentId },
            success: function(response) {
                $('#viewDetails').html(response);
                $('#viewModal').modal('show');
            },
            error: function() {
                $('#viewDetails').html('Error loading appointment details.');
            }
        });
    });

    // Handle Reschedule button click
    $('.btn-reschedule').on('click', function() {
        var appointmentId = $(this).data('id');
        $('#rescheduleAppointmentId').val(appointmentId);
        $('#new_date').val('');
        $('#new_time').html('<option value="" disabled selected>Select time</option>');
        $('#rescheduleModal').modal('show');
    });

    // Populate available times when date is selected
    $('#new_date').on('change', function() {
        var selectedDate = $(this).val();
        var appointmentId = $('#rescheduleAppointmentId').val();
        if (selectedDate) {
            // Fetch available times via AJAX
            $.ajax({
                url: 'fetch_unavailable_times.php',
                type: 'GET',
                data: { date: selectedDate, appointment_id: appointmentId },
                success: function(response) {
                    $('#new_time').html(response);
                },
                error: function() {
                    $('#new_time').html('<option value="">Error loading times</option>');
                }
            });
        }
    });

    // Handle Reschedule form submit
    $('#rescheduleForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'reschedule_appointment.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                alert(response);
                $('#rescheduleModal').modal('hide');
                location.reload();
            },
            error: function() {
                alert('Error rescheduling appointment.');
            }
        });
    });

    // Handle Cancel button click
    $('.btn-cancel').on('click', function() {
        var appointmentId = $(this).data('id');
        $('#cancelAppointmentId').val(appointmentId);
        $('#cancellation_reason').val('');
        $('#cancelModal').modal('show');
    });

    // Handle Cancel form submit
    $('#cancelForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'cancel_appointment.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                alert(response);
                $('#cancelModal').modal('hide');
                location.reload();
            },
            error: function() {
                alert('Error canceling appointment.');
            }
        });
    });
});
