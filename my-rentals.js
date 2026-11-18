async function loadMyRentals() {
    const rentalsList = document.getElementById("rentalsList");

    try {
        const response = await fetch("get_my_rentals.php");
        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || "Unable to load rentals.");
        }

        const rentals = data.rentals || [];

        document.getElementById("totalBookings").textContent =
            rentals.length;

        document.getElementById("pendingBookings").textContent =
            rentals.filter(r => r.booking_status === "pending").length;

        document.getElementById("approvedBookings").textContent =
            rentals.filter(r => r.booking_status === "approved").length;

        rentalsList.innerHTML = "";

        if (rentals.length === 0) {
            rentalsList.textContent = "You have no rental bookings yet.";
            return;
        }

        rentals.forEach(rental => {
            const card = document.createElement("article");
            card.className = "rental-card";

            const image = document.createElement("img");
            image.alt = rental.property_name || "Rental property";

            if (rental.image) {
                image.src = rental.image;
            } else {
                image.style.display = "none";
            }

            image.addEventListener("error", () => {
                image.style.display = "none";
            });

            const content = document.createElement("div");
            content.className = "rental-content";

            const title = document.createElement("h3");
            title.textContent = rental.property_name || "Rental property";

            const location = document.createElement("p");
            location.textContent =
                rental.address || rental.location || "Address unavailable";

            const room = document.createElement("p");
            room.textContent =
                "Room: " + (rental.room_number || "Not specified");

            const moveIn = document.createElement("p");
            moveIn.textContent = "Move-in date: " + rental.move_in_date;

            const paymentMethod = document.createElement("p");
            paymentMethod.textContent =
                "Payment method: " + rental.payment_method;

            const amount = document.createElement("p");
            amount.className = "rental-price";
            amount.textContent =
                "₱" + Number(rental.amount).toLocaleString("en-PH", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

            const status = document.createElement("span");
            status.className =
                "rental-status status-" + rental.booking_status;
            status.textContent =
                "Booking: " + rental.booking_status.toUpperCase();

            const paymentStatus = document.createElement("p");
            paymentStatus.textContent =
                "Payment: " +
                (rental.payment_status || "pending").toUpperCase();

            // Rental duration and ongoing status
            const rentalPeriod = document.createElement("p");
            const rentalState = document.createElement("p");

            rentalPeriod.className = "rental-period";
            rentalState.className = "rental-state";

            const startDate = new Date(
                rental.move_in_date + "T00:00:00"
            );

            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (
                rental.booking_status === "approved" &&
                startDate <= today
            ) {
                let months =
                    (today.getFullYear() - startDate.getFullYear()) * 12 +
                    today.getMonth() - startDate.getMonth();

                let anniversary = new Date(startDate);
                anniversary.setMonth(
                    anniversary.getMonth() + months
                );

                if (anniversary > today) {
                    months--;
                    anniversary = new Date(startDate);
                    anniversary.setMonth(
                        anniversary.getMonth() + months
                    );
                }

                const days = Math.floor(
                    (today - anniversary) / 86400000
                );

                const years = Math.floor(months / 12);
                const remainingMonths = months % 12;

                rentalPeriod.textContent =
                    "Rental duration: " +
                    (years ? years + " year(s) " : "") +
                    (remainingMonths ? remainingMonths + " month(s) " : "") +
                    days + " day(s)";

                rentalState.textContent =
                    "Rental: Ongoing — no fixed end date";

            } else if (rental.booking_status === "approved") {
                rentalPeriod.textContent =
                    "Move-in date: " + rental.move_in_date;

                rentalState.textContent = "Rental: Move-in upcoming";

            } else {
                rentalPeriod.textContent =
                    "Rental duration begins after approval and move-in.";

                rentalState.textContent =
                    "Rental: " + rental.booking_status.toUpperCase();
            }

            content.append(
                title,
                location,
                room,
                moveIn,
                rentalPeriod,
                paymentMethod,
                amount,
                status,
                paymentStatus,
                rentalState
            );

            // Only pending bookings can be cancelled
            if (rental.booking_status === "pending") {
                const cancelButton = document.createElement("button");

                cancelButton.type = "button";
                cancelButton.className = "cancel-rental-button";
                cancelButton.textContent = "Cancel Booking";

                cancelButton.addEventListener("click", () => {
                    cancelRental(rental.booking_id);
                });

                content.appendChild(cancelButton);
            }

            card.append(image, content);
            rentalsList.appendChild(card);
        });

    } catch (error) {
        console.error("Failed to load rentals:", error);

        rentalsList.textContent =
            error.message || "Unable to load rentals. Please try again.";
    }
}


// Cancel a pending booking
async function cancelRental(bookingId) {
    const confirmed = confirm(
        "Are you sure you want to cancel this booking?"
    );

    if (!confirmed) return;

    try {
        const formData = new FormData();
        formData.append("booking_id", bookingId);

        const response = await fetch("cancel_booking.php", {
            method: "POST",
            body: formData
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            alert(data.message || "Unable to cancel booking.");
            return;
        }

        alert("Booking cancelled successfully.");

        await loadMyRentals();

    } catch (error) {
        console.error("Cancellation error:", error);
        alert("Something went wrong while cancelling the booking.");
    }
}


// Load rentals once when the page opens
loadMyRentals();