   //SCREEN NAVIGATION//


function showScreen(screenId) {

    document.querySelectorAll(".screen").forEach(screen => {
        screen.style.display = "none";
    });

    const screen = document.getElementById(screenId);

    if (screen) {
        screen.style.display = "block";
    }

    updateActiveNav(screenId);

    localStorage.setItem("ownerScreen", screenId);
}


/*LOG OUT */
function logoutOwner() {

    const confirmLogout =
        confirm("Are you sure you want to log out?");

    if (!confirmLogout) {
        return;
    }

    // Clear owner dashboard screen
    localStorage.removeItem("ownerScreen");

    // Go to login form
    window.location.href = "log_in.html";
}


/* =========================
   ACTIVE NAVIGATION
========================= */

function updateActiveNav(screenId) {

    document.querySelectorAll(".bottom-nav button")
        .forEach(button => {
            button.classList.remove("nav-active");
        });


    const buttons = document.querySelectorAll(".bottom-nav button");

    if (screenId === "dashboard") {
        buttons[0].classList.add("nav-active");
    }

    else if (screenId === "properties") {
        buttons[1].classList.add("nav-active");
    }

    else if (screenId === "rooms") {
        buttons[2].classList.add("nav-active");
    }
}


/* =========================
   EDIT PROPERTY
========================= */
function editProperty(propertyId) {

    localStorage.setItem("editingPropertyId", propertyId);

    fetch("get_property.php?id=" + propertyId)
        .then(response => response.json())
        .then(data => {

            console.log(data);

            if (data.success) {

                document.getElementById("propertyId").value = data.property.id;

                document.getElementById("propertyName").value =
                    data.property.property_name;

                document.getElementById("propertyLocation").value =
                    data.property.location;

                document.getElementById("propertyPrice").value =
                    data.property.price;

                document.getElementById("propertyDescription").value =
                    data.property.description || "";

                showScreen("editProperty");

            } else {

                alert(data.message);

            }

        })
        .catch(error => {

            console.error(error);
            alert("Failed to load property.");

        });
}


/* =========================
   SAVE PROPERTY
========================= */

function saveProperty(event) {

    event.preventDefault();

    const name =
        document.getElementById("propertyName").value;

    alert(
        "Property information for " +
        name +
        " has been updated."
    );

    showScreen("properties");
}


/* =========================
   ROOM MODAL
========================= */
function editRoom(roomId, roomNumber, price, availability) {

    document.getElementById("roomNumber").value = roomNumber;

    document.getElementById("roomPrice").value = price;

    document.getElementById("roomAvailability").value = availability;

    // Store the room ID so we know which room to update
    document.getElementById("roomModal").dataset.roomId = roomId;

    document.getElementById("roomModal").style.display = "flex";
}


function closeRoomModal() {

    document.getElementById("roomModal").style.display = "none";

}


async function saveRoom() {
    const roomId = document.getElementById("roomModal").dataset.roomId;
    const roomNumber = document.getElementById("roomNumber").value.trim();
    const price = document.getElementById("roomPrice").value;
    const availability = document.getElementById("roomAvailability").value;

    if (!roomId || !roomNumber || price === "" || Number(price) < 0) {
        alert("Please enter a valid room number and price.");
        return;
    }

    try {
        const response = await fetch("update_room.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                room_id: roomId,
                room_number: roomNumber,
                price: price,
                availability: availability
            })
        });

        const result = await response.json();

        if (result.success) {
            alert("Room updated successfully!");
            closeRoomModal();
            location.reload();
        } else {
            alert(result.message || "Failed to update room.");
        }
    } catch (error) {
        console.error("Save room error:", error);
        alert("Could not save the room. Check your PHP file and database connection.");
    }
}


/* =========================
   ADD PROPERTY
========================= */

function openAddProperty() {

    document.getElementById("propertyModal")
        .style.display = "flex";

}


function closePropertyModal() {

    document.getElementById("propertyModal")
        .style.display = "none";

}


/* =========================
   CLOSE MODAL WHEN CLICKING
   OUTSIDE
========================= */

window.addEventListener("click", function(event) {

    const roomModal =
        document.getElementById("roomModal");

    const propertyModal =
        document.getElementById("propertyModal");


    if (event.target === roomModal) {
        closeRoomModal();
    }

    if (event.target === propertyModal) {
        closePropertyModal();
    }

});






/* =========================
   RESTORE LAST SCREEN
========================= */
document.addEventListener("DOMContentLoaded", function() {

    const savedScreen =
        localStorage.getItem("ownerScreen");

    const editingPropertyId =
        localStorage.getItem("editingPropertyId");


    if (
        savedScreen &&
        document.getElementById(savedScreen)
    ) {

        showScreen(savedScreen);

    } else {

        showScreen("dashboard");

    }


    // Reload property details if user was editing
    if (
        savedScreen === "editProperty" &&
        editingPropertyId
    ) {

        editProperty(editingPropertyId);


    }

    const pendingTab =
    document.querySelector(".maintenance-tab.active");

if (pendingTab) {
    filterMaintenance("pending", pendingTab);
}

});

  /* SAVE PROPERTY*/

function saveProperty(event) {

    event.preventDefault();

    const propertyId = document.getElementById("propertyId").value;
    const name = document.getElementById("propertyName").value;
    const propertyLocation = document.getElementById("propertyLocation").value;
    const price = document.getElementById("propertyPrice").value;
    const description = document.getElementById("propertyDescription").value;

    const formData = new FormData();

    formData.append("property_id", propertyId);
    formData.append("property_name", name);
   formData.append("location", propertyLocation);
    formData.append("price", price);
    formData.append("description", description);

    fetch("owner_update_property.php", {
        method: "POST",
        body: formData
    })
    .then(response => {

        console.log("Response status:", response.status);

        return response.text();

    })
    .then(text => {

        console.log("PHP response:", text);

        try {

            const data = JSON.parse(text);

            if (data.success) {

                alert("Property information has been updated successfully.");

                showScreen("properties");

                setTimeout(() => {
                    location.reload();
                }, 300);

            } else {

                alert(data.message);

            }

        } catch (error) {

            console.error("JSON parsing error:", error);

        }

    })
    .catch(error => {

        console.error("Fetch error:", error);

    });
}


/*SEARCH TENANTS */

function searchTenants() {

    const input =
        document.getElementById("tenantSearch");

    const search =
        input.value.toLowerCase();

    const tenants =
        document.querySelectorAll(".tenant-card");

    tenants.forEach(tenant => {

        const text =
            tenant.textContent.toLowerCase();

        if (text.includes(search)) {

            tenant.style.display = "flex";

        } else {

            tenant.style.display = "none";

        }

    });

}

/*BOOKING REQUESTS*/


function approveBooking(bookingId) {

    const confirmApprove =
        confirm("Are you sure you want to approve this booking?");

    if (!confirmApprove) {
        return;
    }

    fetch("approve_booking.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "booking_id=" + bookingId
    })

    .then(response => response.json())

    .then(data => {

        if (data.success) {

            alert("Booking approved successfully.");

            location.reload();

        } else {

            alert(data.message);

        }

    })

    .catch(error => {

        console.error(error);

        alert("Something went wrong.");

    });
}


function rejectBooking(bookingId) {

    const confirmReject =
        confirm("Are you sure you want to reject this booking?");

    if (!confirmReject) {
        return;
    }

    fetch("reject_booking.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "booking_id=" + bookingId
    })

    .then(response => response.json())

    .then(data => {

        if (data.success) {

            alert("Booking rejected.");

            location.reload();

        } else {

            alert(data.message);

        }

    })

    .catch(error => {

        console.error(error);

        alert("Something went wrong.");

    });
}



/*MAINTENANCE*/


function filterMaintenance(status, button) {

    // Change active tab
    document
        .querySelectorAll(".maintenance-tab")
        .forEach(tab => {
            tab.classList.remove("active");
        });

    button.classList.add("active");


    // Get all requests
    const requests =
        document.querySelectorAll(
            ".maintenance-request-card"
        );


    let visibleCount = 0;


    requests.forEach(request => {

        const requestStatus =
            request.dataset.status;


        if (requestStatus === status) {

            request.style.display = "flex";

            visibleCount++;

        } else {

            request.style.display = "none";

        }

    });


    // Show empty message if no request
    let emptyMessage =
        document.getElementById(
            "maintenanceEmptyFilter"
        );


    if (!emptyMessage) {

        emptyMessage =
            document.createElement("div");

        emptyMessage.id =
            "maintenanceEmptyFilter";

        emptyMessage.className =
            "empty-maintenance";

        emptyMessage.innerHTML = `
            <i class="fa-solid fa-screwdriver-wrench"></i>

            <h3>No ${formatMaintenanceStatus(status)} Requests</h3>

            <p>
                There are currently no maintenance
                requests in this category.
            </p>
        `;

        document
            .querySelector(".maintenance-request-list")
            .appendChild(emptyMessage);
    }


    if (visibleCount === 0) {

        emptyMessage.style.display = "block";

    } else {

        emptyMessage.style.display = "none";

    }

}


function formatMaintenanceStatus(status) {

    if (status === "in_progress") {
        return "In Progress";
    }

    return status.charAt(0).toUpperCase()
        + status.slice(1);
}