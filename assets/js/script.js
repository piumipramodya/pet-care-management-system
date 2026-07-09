$(document).ready(function () {
    $(".cancel-button").click(function () {
        const appointmentID = $(this).data("id");
        const button = $(this); // Reference to the button clicked

        // Send AJAX request to cancel the appointment
        $.ajax({
            url: "cancel_appointment_ajax.php", // Ensure correct file path
            method: "POST",
            data: { appointmentID: appointmentID },
            success: function (response) {
                try {
                    const res = JSON.parse(response); // Parse JSON response
                    if (res.success) {
                        // Update the status in the table
                        $("#appointment-" + appointmentID + " .status").text("Cancelled");
                        // Replace the cancel button with "Not Available"
                        button.replaceWith('<span class="disabled">Not Available</span>');
                        // Show success message
                        $("#success-message").text("Appointment successfully cancelled!").fadeIn().delay(2000).fadeOut();
                    } else {
                        // Show an error message in case of failure
                        $("#success-message").text("Error: " + res.message).css("color", "red").fadeIn().delay(3000).fadeOut();
                    }
                } catch (e) {
                    console.error("Invalid response from server.");
                }
            },
            error: function () {
                // Handle AJAX request errors
                $("#success-message").text("An error occurred. Please try again.").css("color", "red").fadeIn().delay(3000).fadeOut();
            }
        });
    });
});
