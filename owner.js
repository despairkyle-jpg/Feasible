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

function editProperty() {

    showScreen("editProperty");

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

function editRoom(room, price) {

    document.getElementById("roomNumber").value = room;

    document.getElementById("roomPrice").value = price;

    document.getElementById("roomModal").style.display = "flex";
}


function closeRoomModal() {

    document.getElementById("roomModal").style.display = "none";

}


function saveRoom() {

    const room =
        document.getElementById("roomNumber").value;

    const price =
        document.getElementById("roomPrice").value;

    const availability =
        document.getElementById("roomAvailability").value;

    alert(
        room +
        " updated.\n\n" +
        "Price: ₱" +
        Number(price).toLocaleString() +
        "\nAvailability: " +
        availability
    );

    closeRoomModal();
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

    if (
        savedScreen &&
        document.getElementById(savedScreen)
    ) {

        showScreen(savedScreen);

    } else {

        showScreen("dashboard");

    }

});