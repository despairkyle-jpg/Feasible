
// Save location and budget changes
document.addEventListener("change", function (event) {
    if (
        event.target.matches('input[name="loc"]') ||
        event.target.matches("#budget")
    ) {
        saveFilters();
    }
});

// Save property type changes
document.addEventListener("click", function (event) {
    if (event.target.closest("#propertyTypes button")) {
        setTimeout(saveFilters, 0);
    }
});

// SAVE FILTERS
function saveFilters() {
    const selectedLocation = document.querySelector(
        'input[name="loc"]:checked'
    );

    const selectedType = document.querySelector(
        "#propertyTypes button.selected"
    );

    const budget = document.getElementById("budget");

    localStorage.setItem(
        "selectedLocation",
        selectedLocation ? selectedLocation.value : "Iloilo City (All)"
    );

    localStorage.setItem(
        "selectedBudget",
        budget ? budget.value : "10000"
    );

    localStorage.setItem(
        "selectedPropertyType",
        selectedType ? selectedType.textContent.trim() : "Boarding House"
    );
}

// RESTORE FILTERS
function restoreFilters() {
    // Stop if this page doesn't contain the budget slider
    if (!document.getElementById("budget")) return;

    const savedLocation = localStorage.getItem("selectedLocation");
    const savedBudget = localStorage.getItem("selectedBudget");
    const savedType = localStorage.getItem("selectedPropertyType");

    // Restore location
    if (savedLocation) {
        const radio = document.querySelector(
            'input[name="loc"][value="' + savedLocation + '"]'
        );

        if (radio) {
            radio.checked = true;

            document.querySelectorAll(".selected-label").forEach(label => {
                label.textContent = "";
            });

            const selectedLabel = radio
                .closest("label")
                ?.querySelector(".selected-label");

            if (selectedLabel) {
                selectedLabel.textContent = "Selected";
            }
        }
    }

    // Restore budget
    const budget = document.getElementById("budget");
    const budgetValue = document.getElementById("budgetValue");

    if (savedBudget !== null) {
        budget.value = savedBudget;
    }

    if (budgetValue) {
        budgetValue.textContent =
            "₱500 - ₱" +
            Number(budget.value).toLocaleString("en-PH");
    }

    // Restore property type
    document.querySelectorAll("#propertyTypes button").forEach(button => {
        button.classList.toggle(
            "selected",
            button.textContent.trim() === (savedType || "Boarding House")
        );
    });

    // Reapply filters without navigating
    if (savedLocation || savedType || savedBudget) {
        applyFilters(false);
    }
}

const locationSearch = document.getElementById("locationSearch");
const locationOptions = document.querySelectorAll(".location-options label");

/*
  SEARCH MAPPING
  keyword : location
*/
const locationMap = {
  // AREVALO
  arevalo: "Arevalo",
  villa: "Arevalo",
  "villa arevalo": "Arevalo",
  baluarte: "Arevalo",
  calaparan: "Arevalo",

  // CITY PROPER
  "city proper": "City Proper",
  city: "City Proper",
  downtown: "City Proper",
  diversion: "City Proper",
  "plaza libertad": "City Proper",
  "iloilo plaza": "City Proper",
  "molo bridge": "City Proper",
  rizal: "City Proper",
  ui: "City Proper",

  // JARO
  jaro: "Jaro",
  cpu: "Jaro",
  "central philippine university": "Jaro",
  wvsu: "Jaro",
  "western visayas state university": "Jaro",
  "jaro plaza": "Jaro",
  "jaro cathedral": "Jaro",
  balabago: "Jaro",
  tagbak: "Jaro",

  // LA PAZ
  "la paz": "La Paz",
  lapaz: "La Paz",
  "la paz plaza": "La Paz",
  "la paz public market": "La Paz",

  // LAPUZ
  lapuz: "Lapuz",
  "lapuz norte": "Lapuz",
  "lapuz sur": "Lapuz",

  // MANDURRIAO
  mandurriao: "Mandurriao",
  manduraio: "Mandurriao",
  festive: "Mandurriao",
  "festive walk": "Mandurriao",
  "sm city iloilo": "Mandurriao",
  "sm iloilo": "Mandurriao",
  megaworld: "Mandurriao",
  "iloilo business park": "Mandurriao",
  ibp: "Mandurriao",
  "wv medical center": "Mandurriao",
  wvmc: "Mandurriao",
  "western visayas medical center": "Mandurriao",

  // MOLO
  molo: "Molo",
  "molo plaza": "Molo",
  "molo church": "Molo",
  "molo mansion": "Molo",
  "molo boulevard": "Molo",
};

if (locationSearch) {
  locationSearch.addEventListener("input", function () {
  const search = this.value.trim().toLowerCase();

  // Empty search = show everything
  if (search === "") {
    locationOptions.forEach((label) => {
      label.style.display = "";
    });

    return;
  }

  /*
    FIND LOCATION FROM MAP
  */
  let matchedLocation = null;

  // Exact keyword
  if (locationMap[search]) {
    matchedLocation = locationMap[search];
  }

  // Partial keyword
  if (!matchedLocation) {
    for (const keyword in locationMap) {
      if (keyword.includes(search)) {
        matchedLocation = locationMap[keyword];
        break;
      }
    }
  }

  /*
    SHOW ONLY MATCHED LOCATION
  */
  locationOptions.forEach((label) => {
    const input = label.querySelector("input");

    if (matchedLocation) {
      if (input.value === matchedLocation) {
        label.style.display = "";
      } else {
        label.style.display = "none";
      }
    } else {
      // No matching location
      label.style.display = "none";
    }
  });

  /*
    AUTOMATICALLY SELECT LOCATION
  */
  if (matchedLocation) {
    locationOptions.forEach((label) => {
      const input = label.querySelector("input");
      const selectedLabel = label.querySelector(".selected-label");

      if (input.value === matchedLocation) {
        locationOptions.forEach((label) => {
          const radio = label.querySelector('input[type="radio"]');
          const selectedLabel = label.querySelector(".selected-label");

          if (matchedLocation && radio.value === matchedLocation) {
            // Show matching location
            label.style.display = "";

            // DO NOT automatically select
            // radio.checked = true;
          } else {
            // Hide other locations
            label.style.display = "none";
          }
        });
      } else {
        selectedLabel.textContent = "";
      }
    });
  }
});
}
const locationRadios = document.querySelectorAll(
  '.location-options input[type="radio"]',
);

locationRadios.forEach((radio) => {
  radio.addEventListener("click", function () {
    const label = this.closest("label");
    const selectedLabel = label.querySelector(".selected-label");

    // If already selected → cancel it
    if (this.dataset.wasChecked === "true") {
      this.checked = false;
      this.dataset.wasChecked = "false";
      selectedLabel.textContent = "";
    } else {
      // Select this location
      locationRadios.forEach((otherRadio) => {
        otherRadio.dataset.wasChecked = "false";

        const otherLabel = otherRadio.closest("label");
        const otherSelected = otherLabel.querySelector(".selected-label");

        otherSelected.textContent = "";
      });

      this.checked = true;
      this.dataset.wasChecked = "true";
      selectedLabel.textContent = "Selected";
    }
  });
});

// PROPERTY TYPE SELECTION

const propertyTypes = document.querySelectorAll("#propertyTypes button");

propertyTypes.forEach((type) => {
  type.addEventListener("click", function () {
    // Remove selected from all property types
    propertyTypes.forEach((item) => {
      item.classList.remove("selected");
    });

    // Add selected to the clicked property type
    this.classList.add("selected");

    console.log("Selected property type:", this.textContent);
  });
});

// AMENITIES SWITCHES

document.querySelectorAll(".amenity .switch").forEach((switchButton) => {
  switchButton.addEventListener("click", function () {
    this.classList.toggle("on");

    const amenityName = this.parentElement
      .querySelector("span")
      .textContent.trim();

    console.log(amenityName, this.classList.contains("on") ? "ON" : "OFF");
  });
});

// AVAILABLE NOW

const availableNowSwitch = document.querySelector(".compact-card .switch.on");

if (availableNowSwitch) {
  availableNowSwitch.addEventListener("click", function () {
    this.classList.toggle("on");

    console.log("Available Now:", this.classList.contains("on"));
  });
}
// SMART MATCH

const smartMatchSwitch = document.querySelector(".gold-switch");

if (smartMatchSwitch) {
  smartMatchSwitch.addEventListener("click", function () {
    this.classList.toggle("on");

    console.log("Smart Match:", this.classList.contains("on"));
  });
}

// MONTHLY BUDGET

const budgetSlider = document.getElementById("budget");
const budgetValue = document.getElementById("budgetValue");
const resultBudget = document.getElementById("resultBudget");

function updateBudget() {
  if (!budgetSlider) return;

  const maxBudget = Number(budgetSlider.value);
  const budgetText = "₱500 - ₱" + maxBudget.toLocaleString("en-PH");

  if (budgetValue) {
    budgetValue.textContent = budgetText;
  }

  if (resultBudget) {
    resultBudget.textContent = budgetText;
  }
}
// Update while moving the slider
if (budgetSlider) {
  budgetSlider.addEventListener("input", updateBudget);

  // Set the initial value
  updateBudget();
}

// Set the initial value
updateBudget();

function applyFilters(goToResults = true) {
  // LOCATION

  const selectedLocation = document.querySelector('input[name="loc"]:checked');
  let locationText = "Iloilo City (All)";

  if (selectedLocation) {
    locationText = selectedLocation.value;

    localStorage.setItem("selectedLocation", locationText);
  } else {
    localStorage.removeItem("selectedLocation");
  }

  const resultLocation = document.getElementById("resultLocation");

  if (resultLocation) {
    resultLocation.textContent = locationText;
  }

  // PROPERTY TYPE

  const selectedPropertyType = document.querySelector(
    "#propertyTypes button.selected",
  );

  let propertyTypeText = "All Properties";

  if (selectedPropertyType) {
    propertyTypeText = selectedPropertyType.textContent.trim();

    localStorage.setItem("selectedPropertyType", propertyTypeText);
  } else {
    localStorage.removeItem("selectedPropertyType");
  }

  const resultPropertyType = document.getElementById("resultPropertyType");

  if (resultPropertyType) {
    resultPropertyType.textContent = propertyTypeText;
  }

  // BUDGET
  const budgetSlider = document.getElementById("budget");

  const maxBudget = budgetSlider
    ? Number(budgetSlider.value)
    : Number(localStorage.getItem("selectedBudget")) || 8000;

  if (budgetSlider) {
    localStorage.setItem("selectedBudget", maxBudget);
  }

  const resultBudget = document.getElementById("resultBudget");

  if (resultBudget) {
    resultBudget.textContent =
      "₱500 - ₱" + maxBudget.toLocaleString("en-PH");
  }

  // FILTER RENTAL CARDS

  const rentalCards = document.querySelectorAll(".rental-card");

  let resultCount = 0;

  rentalCards.forEach((card) => {
    const cardLocation = card.dataset.location;

    const cardPrice = Number(card.dataset.price);

    const cardTypes = card.dataset.type
      ? card.dataset.type.split("/").map((type) => type.trim())
      : [];

    // LOCATION

    const locationMatch =
      locationText === "Iloilo City (All)" ||
     (cardLocation || "").toLowerCase().includes(locationText.toLowerCase());

    // BUDGET

    const budgetMatch = cardPrice >= 500 && cardPrice <= maxBudget;

    // PROPERTY TYPE

    const typeMatch =
      propertyTypeText === "All Properties" ||
      cardTypes.includes(propertyTypeText);

    // SHOW / HIDE

    if (locationMatch && budgetMatch && typeMatch) {
      card.style.display = "";

      resultCount++;
    } else {
      card.style.display = "none";
    }
  });

  // RESULT COUNT
const resultCountElement = document.getElementById("rentalCount");

if (resultCountElement) {
    resultCountElement.textContent = resultCount;
}
  // GO TO RESULTS

  if (goToResults) {
    showScreen("results");
  }
}

document.querySelectorAll(".gallery").forEach((gallery) => {
  const slides = gallery.querySelector(".slides");
  const slideItems = gallery.querySelectorAll(".slide");
  const dots = gallery.querySelectorAll(".dot");
  const photoNumber = gallery.querySelector(".photoNumber");

  let current = 0;

  function showSlide(index) {
    const total = slideItems.length;

    current = (index + total) % total;

    slides.style.transform = `translateX(-${current * 100}%)`;

    dots.forEach((dot, i) => {
      dot.classList.toggle("active", i === current);
    });

    if (photoNumber) {
      photoNumber.textContent = current + 1;
    }
  }

  dots.forEach((dot) => {
    dot.addEventListener("click", () => {
      showSlide(Number(dot.dataset.index));
    });
  });

  let touchStartX = 0;

  gallery.addEventListener(
    "touchstart",
    (e) => {
      touchStartX = e.changedTouches[0].screenX;
    },
    { passive: true },
  );

  gallery.addEventListener(
    "touchend",
    (e) => {
      const dx = e.changedTouches[0].screenX - touchStartX;

      if (Math.abs(dx) > 45) {
        showSlide(current + (dx < 0 ? 1 : -1));
      }
    },
    { passive: true },
  );

  showSlide(0);
});

function openPropertyShell() {
  showScreen("property");
}

function goBack() {
  showScreen("results");
}

function openPropertyShell_barrida() {
  showScreen("property1");
}

function openPropertyShell_inybh() {
  showScreen("property2");
}

function openPropertyShell_Shisbh() {
  showScreen("property3");
}

function openPropertyShell_DMpalbh() {
  showScreen("property4");
}

function openPropertyShell_rexapart() {
  showScreen("property5");
}

function openPropertyShell_skyleiapart() {
  showScreen("property6");
}

function openPropertyShell_AJapart() {
  showScreen("property7");
}

function openPropertyShell_ALtrans() {
  showScreen("property8");
}

function openPropertyShell_Eastville() {
  showScreen("property9");
}

function openPropertyShell_W_Abhtrans() {
  showScreen("property10");
}

function openPropertyShell_alabado() {
  showScreen("property11");
}

function openPropertyShell_j_jbh() {
  showScreen("property12");
}

function openPropertyShell_isunza() {
  showScreen("property13");
}

function openPropertyShell_knjbh() {
  showScreen("property14");
}

function openPropertyShell_madiaas() {
  showScreen("property15");
}

function openPropertyShell_norma() {
  showScreen("property16");
}

function openPropertyShell_rian() {
  showScreen("property17");
}

function openPropertyShell_b_b() {
  showScreen("property18");
}

function openPropertyShell_andeng() {
  showScreen("property19");
}

function openPropertyShell_rac() {
  showScreen("property20");
}

function openPropertyShell_angel() {
  showScreen("property21");
}

function openPropertyShell_dignity() {
  showScreen("property22");
}

function openPropertyShell_deion() {
  showScreen("property23");
}

function openPropertyShell_juvy() {
  showScreen("property24");
}

function openPropertyShell_nina() {
  showScreen("property25");
}

function openPropertyShell_d3a() {
  showScreen("property26");
}

function openPropertyShell_jrd() {
  showScreen("property27");
}

function openPropertyShell_sl() {
  showScreen("property28");
}

function openPropertyShell_bertz() {
  showScreen("property29");
}

//CLICKABLE PROPERTIES 


document.querySelectorAll(".rental-card").forEach((card) => {
  card.addEventListener("click", function () {
    const propertyId = this.dataset.id;
    if (propertyId) {
      window.location.href =
        "properties_details.php?id=" + encodeURIComponent(propertyId);
    }
  });
}); 
// Prevent the heart button from opening property details

 document.querySelectorAll(".heart").forEach(button => { button.addEventListener("click", 
function (event) { event.stopPropagation(); }); });

function toggleHeart(event, heart, propertyId = null) { event.preventDefault(); 
  event.stopPropagation(); 
  // Get property ID from the details page or rental card 

  if (propertyId === null) { const card = heart.closest(".rental-card"); 
    if (!card) return; propertyId = card.dataset.id; } propertyId = String(propertyId); 
    let savedProperties = JSON.parse(localStorage.getItem("savedProperties")) || [];
   // Compare IDs as strings

   const isSaved = savedProperties.map(String).includes(propertyId); 
   if (isSaved) { savedProperties = savedProperties.filter( (id) => String(id) !== propertyId ); 
    heart.textContent = "♡"; heart.classList.remove("saved");
   }
    else { savedProperties.push(propertyId);
     heart.textContent = "♥";
     heart.classList.add("saved"); 
    }
     localStorage.setItem( "savedProperties", JSON.stringify(savedProperties) ); }

function restoreSavedHearts() {
  const savedProperties =
    JSON.parse(localStorage.getItem("savedProperties")) || [];

  document.querySelectorAll(".rental-card").forEach((card) => {
    const propertyId = card.dataset.id;
    const heart = card.querySelector(".heart");

    if (!heart) return;

    if (savedProperties.includes(propertyId)) {
      heart.textContent = "♥";
      heart.classList.add("saved");
    }
  });
}

/*  SAVE PROPERTIES */

function displaySavedProperties() {
  const savedContainer = document.getElementById("savedProperties");

  if (!savedContainer) return;

  const savedIds = JSON.parse(localStorage.getItem("savedProperties")) || [];

  savedContainer.innerHTML = "";

  if (savedIds.length === 0) {
    savedContainer.innerHTML = `
            <div class="empty-saved">
                <img src="images/save.png">
                <h2>No Saved Properties</h2>
                <p>Properties you save will appear here.</p>
            </div>
        `;

    return;
  }

  const allProperties = document.querySelectorAll(".rental-card");

  allProperties.forEach((card) => {
    const propertyId = card.dataset.id;

    if (savedIds.includes(propertyId)) {
      const clone = card.cloneNode(true);

      // Prevent duplicate/incorrect click behavior
      clone.onclick = null;

      const heart = clone.querySelector(".heart");

      if (heart) {
        heart.onclick = function (event) {
          toggleHeart(event, this);
          displaySavedProperties();
        };
      }

      savedContainer.appendChild(clone);
    }
  });
}

const properties = [
  {
    id: "property1",
    databaseId: 41,
    name: "Barrida Boarding House",
    image: "images//barridabh1.jpg",
    location: "Jaro, Iloilo City",
    rating: "5 (6)",
    price: 4500,

    rooms: [
      {
        id: "room_1_01",
        databaseId: 21,
        name: "Room A-01",
        type: "Double",
        occupants: "4-5 occupant",
        size: "16 sqm",
        available: true,
      },
      {
        id: "room_1_02",
        databaseId: 22,
        name: "Room B-02",
        type: "Deluxe",
        occupants: "2-3 occupant",
        size: "15 sqm",
        available: true,
      },
      {
        id: "room_1_03",
        databaseId: 23,
        name: "Room C-03",
        type: "Family Room",
        occupants: "8-10 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_1_04",
        databaseId: 24,
        name: "Room D-04",
        type: "Deluxe",
        occupants: "2-3 occupant",
        size: "13 sqm",
        available: true,
      },

      {
        id: "room_1_05",
        databaseId: 25,
        name: "Room A-05",
        type: "Double",
        occupants: "4-5 occupant",
        size: "16 sqm",
        available: true,
      },
      {
        id: "room_1_06",
        databaseId: 26,
        name: "Room B-06",
        type: "Deluxe",
        occupants: "2-3 occupant",
        size: "15 sqm",
        available: true,
      },
      {
        id: "room_1_07",
        databaseId: 27,
        name: "Room C-07",
        type: "Family Room",
        occupants: "8-10 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_1_08",
        databaseId: 28,
        name: "Room D-08",
        type: "Deluxe",
        occupants: "2-3 occupant",
        size: "13 sqm",
        available: true,
      },
      {
        id: "room_1_09",
        databaseId: 29,
        name: "Room A-09",
        type: "Double",
        occupants: "4-5 occupant",
        size: "16 sqm",
        available: true,
      },
      {
        id: "room_1_10",
        databaseId: 30,
        name: "Room B-10",
        type: "Deluxe",
        occupants: "2-3 occupant",
        size: "15 sqm",
        available: true,
      },
      {
        id: "room_1_11",
        databaseId: 31,
        name: "Room C-11",
        type: "Family Room",
        occupants: "8-10 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_1_12",
        databaseId: 32,
        name: "Room D-12",
        type: "Deluxe",
        occupants: "2-3 occupant",
        size: "13 sqm",
        available: true,
      },
    ],
  },

  {
    id: "property2",
    databaseId: 44,
    name: "InY Residences",
    image: "images/madu-bh-InY1.jpg",
    location: "Manduriao, Iloilo City",
    rating: "4.0 (9)",
    price: 2200,

    rooms: [
      {
        id: "room_2_01",
        databaseId: 41,
        name: "Room A-12",
        type: "Single",
        occupants: "1 occupant",
        size: "12 sqm",
        available: true,
      },
      {
        id: "room_2_02",
        databaseId: 42,
        name: "Room A-13",
        type: "Double",
        occupants: "1-2 occupant",
        size: "12 sqm",
        available: true,
      },
      {
        id: "room_2_03",
        databaseId: 43,
        name: "Room A-14",
        type: "Single",
        occupants: "1 occupant",
        size: "12 sqm",
        available: true,
      },
      {
        id: "room_2_04",
        databaseId: 44,
        name: "Room A-15",
        type: "Single",
        occupants: "1 occupant",
        size: "12 sqm",
        available: true,
      },
    ],
  },
  {
    id: "property3",
    databaseId: 45,
    name: "Shi's Place",
    image: "images/madu-bh-shi1.jpg",
    location: "Manduriao, Iloilo City",
    rating: "4.5 (5)",
    price: 3000,

    rooms: [
      {
        id: "room_3_01",
        databaseId: 45,
        name: "Room A-01",
        type: "Single",
        occupants: "1 occupant",
        size: "12 sqm",
        available: true,
      },
    ],
  },

  {
    id: "property4",
    databaseId: 46,
    name: "DM Pal",
    image: "images/dmpalbh1.jpg",
    location: "Manduriao, Iloilo City",
    rating: "5 (5)",
    price: 10000,

    rooms: [
      {
        id: "room_4_01",
        databaseId: 46,
        name: "Room A-12",
        type: "Single",
        occupants: "1 occupant",
        size: "12 sqm",
        available: true,
      },
      {
        id: "room_4_02",
        databaseId: 47,
        name: "Room A-13",
        type: "Double",
        occupants: "1-2 occupant",
        size: "12 sqm",
        available: true,
      },
      {
        id: "room_4_03",
        databaseId: 48,
        name: "Room A-14",
        type: "Single",
        occupants: "1 occupant",
        size: "12 sqm",
        available: true,
      },
      {
        id: "room_4_04",
        databaseId: 49,
        name: "Room A-15",
        type: "Double",
        occupants: "1-2 occupant",
        size: "12 sqm",
        available: true,
      },
    ],
  },

  {
    id: "property5",
    databaseId: 55,
    name: "Rex Appartment",
    image: "images/rexapart1.avif",
    location: "Manduriao, Iloilo City",
    rating: "5 (5)",
    price: 12500,

    rooms: [
      {
        id: "room_5_01",
        databaseId: 82,
        name: "Room A-12",
        type: "Studio Unit",
        occupants: "1 occupant",
        size: "12 sqm",
        available: true,
      },
      {
        id: "room_5_02",
        databaseId: 83,
        name: "Room A-13",
        type: "Fully Funished Studio Unit",
        occupants: "1-2 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_5_01",
        databaseId: 84,
        name: "Room c-4",
        type: "Studio Unit",
        occupants: "1 occupant",
        size: "12 sqm",
        available: true,
      },
      {
        id: "room_5_02",
        databaseId: 85,
        name: "Room D-15",
        type: "Fully Funished Studio Unit",
        occupants: "1-2 occupant",
        size: "25 sqm",
        available: true,
      },
    ],
  },
  {
    id: "property6",
    databaeId: 53,
    name: "Skyleigh Appartment",
    image: "images/skyleighapart2.webp",
    location: "La Paz, Iloilo City",
    rating: "4 (6)",
    price: 11500,

    rooms: [
      {
        id: "room_6_01",
        databaeId: 78,
        name: "Room A-12",
        type: "Double",
        occupants: "2 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_6_02",
        databaeId: 79,
        name: "Room A-13",
        type: "Deluxe ",
        occupants: "2 occupant",
        size: "20 sqm",
        available: true,
      },
      {
        id: "room_6_03",
        databaeId: 80,
        name: "Room B-14",
        type: "Tripple",
        occupants: "3 occupants",
        size: "21 sqm",
        available: true,
      },

      {
        id: "room_6_04",
        databaeId: 81,
        name: "Room B-15",
        type: "Deluxe ",
        occupants: "1-4 occupant",
        size: "25 sqm",
        available: true,
      },
    ],
  },
  {
    id: "property7",
    databaseId: 42,
    name: "AJ Boarding House",
    image: "images/AJbh1.jpg",
    location: "La Paz, Iloilo City",
    rating: "4 (6)",
    price: 5500,

    rooms: [
      {
        id: "room_7_01",
        databaseId: 33,
        name: "Room A-12",
        type: "Double",
        occupants: "2 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_7_02",
        databaseId: 34,
        name: "Room A-13",
        type: "Deluxe ",
        occupants: "2 occupant",
        size: "20 sqm",
        available: true,
      },
      {
        id: "room_7_03",
        databaseId: 35,
        name: "Room B-01",
        type: "Tripple",
        occupants: "3 occupants",
        size: "21 sqm",
        available: true,
      },

      {
        id: "room_7_04",
        databaseId: 36,
        name: "Room B-02",
        type: "Deluxe ",
        occupants: "1-4 occupant",
        size: "25 sqm",
        available: true,
      },
    ],
  },

  {
    id: "property8",
    databaseId: 61,
    name: "A&L Place",
    image: "images/a&lplace.jpg",
    location: "La Paz, Iloilo City",
    rating: "4 (6)",
    price: 600,

    rooms: [
      {
        id: "room_8_01",
        databaseId: 114,
        name: "Room A-12",
        type: "Double",
        occupants: "2 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_8_02",
        databaseId: 115,
        name: "Room A-13",
        type: "Deluxe ",
        occupants: "2 occupant",
        size: "20 sqm",
        available: true,
      },
      {
        id: "room_8_03",
        databaseId: 116,
        name: "Room B-01",
        type: "Tripple",
        occupants: "3 occupants",
        size: "21 sqm",
        available: true,
      },
    ],
  },
  {
    id: "property9",
    databaseId: 63,
    name: "Eastville Dormitory",
    image: "images/eastvilledorm1.jpg",
    location: "Molo, Iloilo City",
    rating: "4 (6)",
    price: 3000,

    rooms: [
      {
        id: "room_9_01",
        databaseId: 121,
        name: "Room A-12",
        type: "Double",
        occupants: "2 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_9_02",
        databaseId: 122,
        name: "Room A-13",
        type: "Deluxe ",
        occupants: "2 occupant",
        size: "20 sqm",
        available: true,
      },
    ],
  },

  {
    id: "property10",
    databaseId: 47,
    name: "W&A Boarding House/Transient",
    image: "images/moloW&Abh1.jpg",
    location: "Molo, Iloilo City",
    rating: "4 (6)",
    price: 2500,

    rooms: [
      {
        id: "room_10_01",
        databaseId: 54,
        name: "Room A-01",
        type: "Double",
        occupants: "4-5 occupant",
        size: "16 sqm",
        available: true,
      },
      {
        id: "room_10_02",
        databaseId: 55,
        name: "Room B-02",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "15 sqm",
        available: true,
      },
      {
        id: "room_10_01",
        databaseId: 56,
        name: "Room C-03",
        type: "Family Room",
        occupants: "8-10 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_10_02",
        databaseId: 57,
        name: "Room D-04",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "13 sqm",
        available: true,
      },
    ],
  },

  {
    id: "property11",
    databaeId: 67,
    name: "E. Alabado Dormitory",
    image: "images/moloalabadodorm1.jpg",
    location: "Molo, Iloilo City",
    rating: "4 (6)",
    price: 1800,

    rooms: [
      {
        id: "room_11_01",
        databaeId: 135,
        name: "Room A",
        type: "Double",
        occupants: "4-5 occupant",
        size: "16 sqm",
        available: true,
      },
      {
        id: "room_11_02",
        databaeId: 136,
        name: "Room B",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "15 sqm",
        available: true,
      },
      {
        id: "room_11_01",
        databaeId: 137,
        name: "Room C",
        type: "Family Room",
        occupants: "8-10 occupant",
        size: "25 sqm",
        available: true,
      },
    ],
  },

  {
    id: "property12",
    databaseId: 43,
    name: "J&J Boarding House",
    image: "images/j&jbh1.jpg",
    location: "Lapuz, Iloilo City",
    rating: "4 (6)",
    price: 2000,

    rooms: [
      {
        id: "room_12_01",
        databaseId: 37,
        name: "Room A-01",
        type: "Double",
        occupants: "4-5 occupant",
        size: "16 sqm",
        available: true,
      },
      {
        id: "room_12_02",
        databaseId: 38,
        name: "Room B-02",
        type: "Deluxe",
        occupants: "2-3 occupant",
        size: "15 sqm",
        available: true,
      },
      {
        id: "room_12_03",
        databaseId: 39,
        name: "Room C-03",
        type: "Family Room",
        occupants: "8-10 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_12_04",
        databaseId: 40,
        name: "Room D-04",
        type: "Deluxe",
        occupants: "2-3 occupant",
        size: "13 sqm",
        available: true,
      },
    ],
  },

  {
    id: "property13",
    databaseId: 48,
    name: " IBH/Isonza",
    image: "images/arevalo bh1.jpg",
    location: "Molo, Iloilo City",
    rating: "4 (6)",
    price: 7000,

    rooms: [
      {
        id: "room_13_01",
        databaseId: 58,
        name: "Room A-01",
        type: "Double",
        occupants: "4-5 occupant",
        size: "16 sqm",
        available: true,
      },
      {
        id: "room_13_02",
        databaseId: 59,
        name: "Room B-02",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "15 sqm",
        available: true,
      },
      {
        id: "room_13_01",
        databaseId: 60,
        name: "Room C-03",
        type: "Family Room",
        occupants: "8-10 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_13_02",
        databaseId: 61,
        name: "Room D-04",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "13 sqm",
        available: true,
      },
    ],
  },

  {
    id: "property14",
    databaseId: 49,
    name: " KNJ Boarding House",
    image: "images/arevalo bh1.jpg",
    location: "Villa Arevalo, Iloilo City",
    rating: "4 (6)",
    price: 1800,

    rooms: [
      {
        id: "room_14_01",
        databaseId: 62,
        name: "Room A-01",
        type: "Double",
        occupants: "4-5 occupant",
        size: "16 sqm",
        available: true,
      },
      {
        id: "room_14_02",
        databaseId: 63,
        name: "Room B-02",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "15 sqm",
        available: true,
      },
      {
        id: "room_14_01",
        databaseId: 64,
        name: "Room C-03",
        type: "Family Room",
        occupants: "8-10 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_14_02",
        databaseId: 65,
        name: "Room D-04",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "13 sqm",
        available: true,
      },
    ],
  },
  {
    id: "property15",
    databaseId: 62,
    name: " Hotel Madiaas",
    image: "images/madiaastrans3.jpg",
    location: "City Proper, Iloilo City",
    rating: "4 (6)",
    price: 889,

    rooms: [
      {
        id: "room_15_01",
        databaseId: 117,
        name: "Room A",
        type: "Double",
        occupants: "4-5 occupant",
        size: "16 sqm",
        available: true,
      },
      {
        id: "room_15_02",
        databaseId: 118,
        name: "Room B",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "15 sqm",
        available: true,
      },
      {
        id: "room_15_01",
        databaseId: 119,
        name: "Room C",
        type: "Family Room",
        occupants: "8-10 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_15_02",
        databaseId: 120,
        name: "Room D",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "13 sqm",
        available: true,
      },
    ],
  },

  {
    id: "property16",
    databaseId: 50,
    name: " Norma Boarding House",
    image: "images/propernormabh1.jpg",
    location: "City Proper, Iloilo City",
    rating: "4 (6)",
    price: 4000,

    rooms: [
      {
        id: "room_16_01",
        databaseId: 66,
        name: "Room A-01",
        type: "Double",
        occupants: "4-5 occupant",
        size: "16 sqm",
        available: true,
      },
      {
        id: "room_16_02",
        databaseId: 67,
        name: "Room B-02",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "15 sqm",
        available: true,
      },
      {
        id: "room_16_01",
        databaseId: 68,
        name: "Room C-03",
        type: "Family Room",
        occupants: "8-10 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_16_02",
        databaseId: 69,
        name: "Room D-04",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "13 sqm",
        available: true,
      },
    ],
  },

  {
    id: "property17",
    databaseId: 56,
    name: " Rian's Apartment",
    image: "images/rianapart2.avif",
    location: "Arevalo, Iloilo City",
    rating: "5 (6)",
    price: 6000,
    price: 12000,

    rooms: [
      {
        id: "room_17_01",
        databaseId: 86,
        name: "Room A-01",
        type: "Double",
        occupants: "4-5 occupant",
        size: "16 sqm",
        available: true,
      },
      {
        id: "room_17_02",
        databaseId: 87,
        name: "Room B-13",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "15 sqm",
        available: true,
      },
      {
        id: "room_17_01",
        databaseId: 88,
        name: "Room C-12",
        type: "Family Room",
        occupants: "8-10 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_17_02",
        databaseId: 89,
        name: "Room D-03",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "13 sqm",
        available: true,
      },
    ],
  },

  {
    id: "property18",
    databaseId: 68,
    name: " Bernwoods Extyn B&B Dormitory",
    image: "images/bbdorm_trans1.jpg",
    location: "Arevalo, Iloilo City",
    rating: "5 (6)",
    price: 1100,
    price: 6000,

    rooms: [
      {
        id: "room_18_01",
        databaseId: 138,
        name: "Room A",
        type: "Double",
        occupants: "4-5 occupant",
        size: "16 sqm",
        available: true,
      },
      {
        id: "room_18_02",
        databaseId: 139,
        name: "Room B",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "15 sqm",
        available: true,
      },
      {
        id: "room_18_01",
        databaseId: 140,
        name: "Room C",
        type: "Family Room",
        occupants: "8-10 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_18_02",
        databaseId: 141,
        name: "Room D",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "13 sqm",
        available: true,
      },
    ],
  },

  {
    id: "property19",
    databaseId: 51,
    name: " Andeng's Boarding House Rental",
    image: "images/andengarevalospace1.jpg",
    location: "Arevalo, Iloilo City",
    rating: "5 (6)",
    price: 6000,
    price: 7500,

    rooms: [
      {
        id: "room_19_01",
        databaseId: 70,
        name: "Room A-01",
        type: "Double",
        occupants: "4-5 occupant",
        size: "16 sqm",
        available: true,
      },
      {
        id: "room_19_02",
        databaseId: 71,
        name: "Room B-02",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "15 sqm",
        available: true,
      },
      {
        id: "room_19_01",
        databaseId: 72,
        name: "Room C-03",
        type: "Family Room",
        occupants: "8-10 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_19_02",
        databaseId: 73,
        name: "Room D-04",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "13 sqm",
        available: true,
      },
    ],
  },

  {
    id: "property20",
    databaseId: 71,
    name: "RAC Homes Apartment",
    image: "images/racapart2.avif",
    location: "City Proper, Iloilo City",
    rating: "5 (6)",
    price: 4500,
    price: 7500,

    rooms: [
      {
        id: "room_20_01",
        databaseId: 146,
        name: "Room A",
        type: "Double",
        occupants: "4-5 occupant",
        size: "16 sqm",
        available: true,
      },
      {
        id: "room_20_02",
        databaseId: 147,
        name: "Room B",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "15 sqm",
        available: true,
      },
      {
        id: "room_20_01",
        databaseId: 148,
        name: "Room C",
        type: "Family Room",
        occupants: "8-10 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_20_02",
        databaseId: 149,
        name: "Room D",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "13 sqm",
        available: true,
      },
    ],
  },

  {
    id: "property21",
    databaseId: 66,
    name: "Angel Heart Ladies Dormitory",
    image: "images/angeldorm1.avif",
    location: "City Proper, Iloilo City",
    rating: "5 (6)",
    price: 6000,
    rooms: [
      {
        id: "room_21_01",
        databaseId: 131,
        name: "Room A",
        type: "Double",
        occupants: "4-5 occupant",
        size: "16 sqm",
        available: true,
      },
      {
        id: "room_21_02",
        databaseId: 132,
        name: "Room B",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "15 sqm",
        available: true,
      },
      {
        id: "room_21_01",
        databaseId: 133,
        name: "Room C",
        type: "Family Room",
        occupants: "8-10 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_21_02",
        databaseId: 134,
        name: "Room D",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "13 sqm",
        available: true,
      },
    ],
  },

  {
    id: "property22",
    databaseId: 52,
    name: "Dignity Boarding House",
    image: "images/Dignitybed1.jpg",
    location: "Mandurriao, Iloilo City",
    rating: "5 (6)",
    price: 7800,
    rooms: [
      {
        id: "room_22_01",
        databaseId: 74,
        name: "Room A-01",
        type: "Double",
        occupants: "4-5 occupant",
        size: "16 sqm",
        available: true,
      },
      {
        id: "room_22_02",
        databaseId: 75,
        name: "Room B-02",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "15 sqm",
        available: true,
      },
      {
        id: "room_22_01",
        databaseId: 76,
        name: "Room C-03",
        type: "Family Room",
        occupants: "8-10 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_22_02",
        databaseId: 77,
        name: "Room D-04",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "13 sqm",
        available: true,
      },
    ],
  },

  {
    id: "property23",
    databaseId: 57,
    name: "Deion Apartment",
    image: "images/dionapart2.avif",
    location: "Jaro, Iloilo City",
    rating: "5 (6)",
    price: 8200,
    rooms: [
      {
        id: "room_23_01",
        databaseId: 90,
        name: "Room A-1",
        type: "Double",
        occupants: "4-5 occupant",
        size: "16 sqm",
        available: true,
      },
      {
        id: "room_23_02",
        databaseId: 91,
        name: "Room B-3",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "15 sqm",
        available: true,
      },
      {
        id: "room_23_01",
        databaseId: 92,
        name: "Room C-5",
        type: "Family Room",
        occupants: "8-10 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_23_02",
        databaseId: 93,
        name: "Room D-6",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "13 sqm",
        available: true,
      },
    ],
  },

  {
    id: "property24",
    databaseId: 69,
    name: "Juvy's Dormitory",
    image: "images/juvydorm3.jpg",
    location: "Jaro, Iloilo City",
    rating: "5 (6)",
    price: 10000,
    rooms: [
      {
        id: "room_24_01",
        databaseId: 142,
        name: "Room A",
        type: "Double",
        occupants: "4-5 occupant",
        size: "16 sqm",
        available: true,
      },
      {
        id: "room_24_02",
        databaseId: 143,
        name: "Room B",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "15 sqm",
        available: true,
      },
      {
        id: "room_24_01",
        databaseId: 144,
        name: "Room C",
        type: "Family Room",
        occupants: "8-10 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_24_02",
        databaseId: 145,
        name: "Room D",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "13 sqm",
        available: true,
      },
    ],
  },

  {
    id: "property25",
    databaseId: 58,
    name: "Nina's Townhouse",
    image: "images/ninatrans1.png",
    location: "Jaro, Iloilo City",
    rating: "5 (6)",
    price: 3500,
    price: 13000,

    rooms: [
      {
        id: "room_25_01",
        databaseId: 94,
        name: "Room A-11",
        type: "Double",
        occupants: "4-5 occupant",
        size: "16 sqm",
        available: true,
      },
      {
        id: "room_25_02",
        databaseId: 95,
        name: "Room B-4",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "15 sqm",
        available: true,
      },
      {
        id: "room_25_01",
        databaseId: 96,
        name: "Room C-2",
        type: "Family Room",
        occupants: "8-10 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_25_02",
        databaseId: 97,
        name: "Room D-01",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "13 sqm",
        available: true,
      },
    ],
  },

  {
    id: "property26",
    databaseId: 64,
    name: " D3A RESIDENCE ",
    image: "images/tjgarciaapart1.avif",
    location: "La Paz, Iloilo City",
    rating: "5 (6)",
    price: 4500,

    rooms: [
      {
        id: "room_26_01",
        databaseId: 123,
        name: "Room A",
        type: "Double",
        occupants: "4-5 occupant",
        size: "16 sqm",
        available: true,
      },
      {
        id: "room_26_02",
        databaseId: 124,
        name: "Room B",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "15 sqm",
        available: true,
      },
      {
        id: "room_26_01",
        databaseId: 125,
        name: "Room C",
        type: "Family Room",
        occupants: "8-10 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_26_02",
        databaseId: 126,
        name: "Room D",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "13 sqm",
        available: true,
      },
    ],
  },

  {
    id: "property27",
    databaseId: 60,
    name: "JRD BED SPACE ",
    image: "images/jrdbed1.jpg",
    location: "La Paz, Iloilo City",
    rating: "5 (6)",
    price: 4000,

    rooms: [
      {
        id: "room_27_01",
        databaseId: 110,
        name: "Room A",
        type: "Double",
        occupants: "4-5 occupant",
        size: "16 sqm",
        available: true,
      },
      {
        id: "room_27_02",
        databaseId: 111,
        name: "Room B",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "15 sqm",
        available: true,
      },
      {
        id: "room_27_01",
        databaseId: 112,
        name: "Room C",
        type: "Family Room",
        occupants: "8-10 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_27_02",
        databaseId: 113,
        name: "Room D",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "13 sqm",
        available: true,
      },
    ],
  },

  {
    id: "property28",
    databaseId: 59,
    name: "S&L Apartment ",
    image: "images/slapart1.jpg",
    location: "Lapuz, Iloilo City",
    rating: "5 (6)",
    price: 500,
    pricec: 3500,

    rooms: [
      {
        id: "room_28_01",
        databaseId: 102,
        name: "Room A-02",
        type: "Double",
        occupants: "4-5 occupant",
        size: "16 sqm",
        available: true,
      },
      {
        id: "room_28_02",
        databaseId: 103,
        name: "Room B-03",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "15 sqm",
        available: true,
      },
      {
        id: "room_28_01",
        databaseId: 104,
        name: "Room C-04",
        type: "Family Room",
        occupants: "8-10 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_28_02",
        databaseId: 105,
        name: "Room D-05",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "13 sqm",
        available: true,
      },
    ],
  },

  {
    id: "property29",
    databaseId: 65,
    name: "Bertz Residence ",
    image: "images/bertzdorm1.jpg",
    location: "Lapuz, Iloilo City",
    rating: "5 (6)",
    price: 6000,

    rooms: [
      {
        id: "room_29_01",
        databaseId: 127,
        name: "Room A",
        type: "Double",
        occupants: "4-5 occupant",
        size: "16 sqm",
        available: true,
      },
      {
        id: "room_29_02",
        databaseId: 128,
        name: "Room B",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "15 sqm",
        available: true,
      },
      {
        id: "room_29_01",
        databaseId: 129,
        name: "Room C",
        type: "Family Room",
        occupants: "8-10 occupant",
        size: "25 sqm",
        available: true,
      },
      {
        id: "room_29_02",
        databaseId: 130,
        name: "Room D",
        type: "Deluxe ",
        occupants: "2-3 occupant",
        size: "13 sqm",
        available: true,
      },
    ],
  },
];

let selectedProperty = null;

//Room Selection

let selectedRoomId = null;

async function loadRooms(propertyId) {
    const roomOptions = document.getElementById("roomOptions");
    const bookingRoom = document.getElementById("bookingRoom");
    const bookingOccupants = document.getElementById("bookingOccupants");

    if (!roomOptions || !bookingRoom || !bookingOccupants) return;

    selectedRoomId = null;

    bookingRoom.dataset.roomNumber = "";
    roomOptions.innerHTML = "";

    bookingRoom.textContent = "Loading rooms...";
    bookingOccupants.textContent = "Please wait";

    try {
        const response = await fetch(
            "get_rooms.php?property_id=" +
            encodeURIComponent(propertyId)
        );

        if (!response.ok) {
            throw new Error("HTTP error " + response.status);
        }

        const data = await response.json();

        if (!data.success || !Array.isArray(data.rooms) ||
            data.rooms.length === 0) {
            bookingRoom.textContent = "No available rooms";
            bookingOccupants.textContent =
                "No rooms are currently available.";
            return;
        }

        data.rooms.forEach(function (room) {
            const option = document.createElement("div");
            option.className = "room-option";

            const name = document.createElement("b");
            name.textContent = "Room " + room.room_number;

            const details = document.createElement("span");
            details.textContent =
                "₱" + Number(room.price).toLocaleString("en-PH", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + " / month";

            option.appendChild(name);
            option.appendChild(details);

           option.addEventListener("click", function () {
    selectedRoomId = Number(room.id);

    bookingRoom.textContent = "Room " + room.room_number;
    bookingRoom.dataset.roomNumber = room.room_number;

    bookingOccupants.textContent = details.textContent;

    roomOptions.classList.remove("show");

    console.log("Selected room ID:", selectedRoomId);
    console.log("Actual room number:", bookingRoom.dataset.roomNumber);
});

            roomOptions.appendChild(option);
        });

        bookingRoom.textContent = "Select a room";
        bookingOccupants.textContent = "Choose an available room";

    } catch (error) {
        console.error("Room loading failed:", error);

        bookingRoom.textContent = "Unable to load rooms";
        bookingOccupants.textContent =
            "Check your connection and try again.";
    }
}


// Room dropdown functionality 
document.addEventListener("DOMContentLoaded", function () { 
  const roomBox = document.getElementById("roomBox"); 
  const roomOptions = document.getElementById("roomOptions");
   if (!roomBox || !roomOptions) return; 
   // Open or close the dropdown 
  roomBox.addEventListener("click", function () {
     roomOptions.classList.toggle("show"); 
    });
     // Close dropdown when clicking outside 
  document.addEventListener("click", function (event) { 
    if ( !roomBox.contains(event.target) && !roomOptions.contains(event.target) ) { 
      roomOptions.classList.remove("show"); } }); });

/*RESERVE NOW */

function reserveNow(propertyId) {
    console.log("🔥 RESERVE NOW:", propertyId);

    // Find the selected property
    selectedProperty = properties.find(
        property =>
            String(property.id) === String(propertyId) ||
            String(property.databaseId) === String(propertyId)
    );

    if (!selectedProperty) {
        console.error("Property not found:", propertyId);
        return;
    }

    // Save selected property ID
    localStorage.setItem(
        "selectedPropertyId",
        selectedProperty.databaseId
    );

    console.log("Selected property:", selectedProperty);
    console.log("Database ID:", selectedProperty.databaseId);

    // Prepare booking details
    const bookingImage = document.getElementById("bookingImage");
    const bookingName = document.getElementById("bookingName");
    const bookingLocation = document.getElementById("bookingLocation");
    const bookingRating = document.getElementById("bookingRating");

    if (bookingImage) bookingImage.src = selectedProperty.image;
    if (bookingName) bookingName.textContent = selectedProperty.name;
    if (bookingLocation) {
        bookingLocation.textContent = "⌖ " + selectedProperty.location;
    }
    if (bookingRating) bookingRating.textContent = selectedProperty.rating;

    // Price
    const price =
        "₱" + Number(selectedProperty.price).toLocaleString("en-PH") + ".00";

    const monthlyRent = document.getElementById("monthlyRent");
    const reservationPayment = document.getElementById("reservationPayment");
    const dueToday = document.getElementById("dueToday");

    if (monthlyRent) monthlyRent.textContent = price;
    if (reservationPayment) reservationPayment.textContent = price;
    if (dueToday) dueToday.textContent = price;

    // Payment details
    const paymentImage = document.getElementById("paymentImage");
    const paymentName = document.getElementById("paymentName");
    const paymentPrice = document.getElementById("paymentPrice");
    const confirmPaymentButton =
        document.getElementById("confirmPaymentButton");

    if (paymentImage) paymentImage.src = selectedProperty.image;
    if (paymentName) paymentName.textContent = selectedProperty.name;
    if (paymentPrice) paymentPrice.textContent = price;

    if (confirmPaymentButton) {
        confirmPaymentButton.innerHTML =
            '<i class="fa-solid fa-lock"></i> Confirm Payment — ' + price;
    }

    // Success details
    const successImage = document.getElementById("successImage");
    const successName = document.getElementById("successName");
    const successLocation = document.getElementById("successLocation");
    const successPrice = document.getElementById("successPrice");

    if (successImage) successImage.src = selectedProperty.image;
    if (successName) successName.textContent = selectedProperty.name;
    if (successLocation) {
        successLocation.textContent = "⌖ " + selectedProperty.location;
    }
    if (successPrice) successPrice.textContent = price;

    // Open the reservation modal ONLY
    const modal = document.getElementById("reserveModal");

    if (modal) {
        modal.classList.add("show");
        modal.setAttribute("aria-hidden", "false");
    } else {
        console.error("Reservation modal not found.");
        return;
    }

    // Load available rooms
    loadRooms(selectedProperty.databaseId);
}

function openReserveModal() {
    const modal = document.getElementById("reserveModal");

    if (!modal) {
        console.error("reserveModal element not found!");
        return;
    }

    modal.classList.add("show");
    modal.setAttribute("aria-hidden", "false");

    console.log("Reservation modal opened!");
}

function closeModal() {
    const modal = document.getElementById("reserveModal");

    if (!modal) return;

    modal.classList.remove("show");
    modal.setAttribute("aria-hidden", "true");
}

/*BACK*/ 

function backToProperty() {
  const propertyId = localStorage.getItem("selectedPropertyId");

  if (!propertyId) {
    showScreen("results");
    return;
  }

  const id = Number(propertyId);

  const screenId = id === 1 ? "property" : `property${id - 1}`;

  if (document.getElementById(screenId)) {
    showScreen(screenId);
  } else {
    showScreen("results");
  }
}

function closeModal() {
  document.getElementById("reserveModal").classList.remove("open");
  document.getElementById("reserveModal").setAttribute("aria-hidden", "true");
}
function confirmReservation(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const modal = document.getElementById("reserveModal");
    const moveIn = document.getElementById("moveIn");

    if (!moveIn || !moveIn.value) {
        alert("Please select your move-in date.");
        return;
    }

    // Save reservation details
    localStorage.setItem("moveInDate", moveIn.value);

    const persons = modal?.querySelector("select");

    if (persons) {
        localStorage.setItem(
            "reservationPersons",
            persons.value
        );
    }

    // Close the reservation card
    if (modal) {
        modal.classList.remove("show");
        modal.setAttribute("aria-hidden", "true");
    }

    // Open the booking screen
    if (typeof showScreen === "function") {
        showScreen("booking");
    } else {
        console.error("showScreen() is not defined.");
    }
}

function messageLandlord() {
  showToast("Opening a message with Rodrigo...");
}

function showToast(message) {
  const toast = document.getElementById("toast");
  toast.textContent = message;
  toast.classList.add("show");
  clearTimeout(window.toastTimer);
  window.toastTimer = setTimeout(() => toast.classList.remove("show"), 2200);
}

const reserveModal = document.getElementById("reserveModal");

if (reserveModal) {
  reserveModal.addEventListener("click", (e) => {
    if (e.target.id === "reserveModal") {
      closeModal();
    }
  });
}


function closeModal(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const modal = document.getElementById("reserveModal");

    if (modal) {
        modal.classList.remove("show");
        modal.setAttribute("aria-hidden", "true");

        console.log("Reservation modal closed");
    }
}

// MOVE-IN DATE
const dateBox = document.getElementById("dateBox");
const moveInDate = document.getElementById("moveInDate");
const selectedDate = document.getElementById("selectedDate");

if (dateBox && moveInDate) {
  dateBox.addEventListener("click", function () {
    if (typeof moveInDate.showPicker === "function") {
      moveInDate.showPicker();
    } else {
      moveInDate.focus();
    }
  });
}

if (moveInDate) {
  moveInDate.addEventListener("change", function () {
    if (!this.value) return;

    const date = new Date(this.value + "T00:00:00");

    const formattedDate = date.toLocaleDateString("en-US", {
      month: "long",
      day: "numeric",
      year: "numeric",
    });

    if (selectedDate) {
      selectedDate.textContent = formattedDate;
    }

    // Update success page
    const successDate = document.getElementById("successMoveInDate");

    if (successDate) {
      successDate.textContent = formattedDate;
    }
  });
}
function showScreen(screenId) {
    // Find the requested screen
    const selectedScreen = document.getElementById(screenId);

    // Stop if the screen does not exist on this page
    if (!selectedScreen) {
        console.warn("Screen not found:", screenId);
        return;
    }

    // Hide all screens
    document.querySelectorAll(".screen").forEach(screen => {
        screen.classList.remove("active");
        screen.style.display = "none";
    });

    // Display only the selected screen
    selectedScreen.classList.add("active");
    selectedScreen.style.display = "block";

    // Save the current screen
    localStorage.setItem("currentScreen", screenId);

    // Update navigation buttons
    document.querySelectorAll(".bottom-nav button").forEach(button => {
        button.classList.remove("nav-active");
    });

    const profileScreens = [
        "profile",
        "account",
        "my-rentals",
        "maintenance",
        "reviews",
        "account-settings",
        "help-support"
    ];

    const navScreen = profileScreens.includes(screenId)
        ? "profile"
        : screenId;

    const activeButton = document.querySelector(
        `.bottom-nav button[data-screen="${navScreen}"]`
    );

    if (activeButton) {
        activeButton.classList.add("nav-active");
    }

    // Scroll to the top
    window.scrollTo(0, 0);

    console.log("Showing screen:", screenId);
}
/* OPEN BOOKING SCREEN */

function openBookingScreen() { const property = window.currentProperty;
   if (!property || !property.id) { console.error("Selected property data is missing:", property); 
    alert("Unable to load this property's details. Please refresh the page."); 
    return; } 
    // Store the actual database property ID 
  localStorage.setItem("selectedPropertyId", String(property.id)); 
  // Load details for this exact property 
  updateBookingProperty(property); 
  // Load available rooms from MySQL 
  loadRooms(property.id); 
  // Set the earliest move-in date to today 
  const dateInput = document.getElementById("bookingMoveIn");
  if (dateInput) { const today = new Date(); 
    const localDate = new Date( today.getTime() - today.getTimezoneOffset() * 60000 ).toISOString().split("T")[0];
  dateInput.min = localDate; if (!dateInput.value || dateInput.value < localDate) { dateInput.value = localDate; } } 
  // Open the Booking Request screen 
  showScreen("booking"); }

function updateBookingProperty(property) {
    if (!property) return;

    const price = "₱" + Number(property.price).toLocaleString("en-PH", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    function setText(id, value) {
        const element = document.getElementById(id);
        if (element) element.textContent = value ?? "";
    }

    function setImage(id, value) {
        const element = document.getElementById(id);
        if (element && value) {
            element.src = value;
        }
    }

    // Booking Request
    setImage("bookingImage", property.image);
    setText("bookingName", property.name);
    setText("bookingLocation", "⌖ " + property.location);
    setText("bookingRating", property.rating);
    setText("monthlyRent", price);
    setText("reservationPayment", price);
    setText("dueToday", price);

    // Payment
    setImage("paymentImage", property.image);
    setText("paymentName", property.name);
    setText("paymentPrice", price);

    // Success screen
    setImage("successImage", property.image);
    setText("successName", property.name);
    setText("successLocation", "⌖ " + property.location);
    setText("successPrice", price);
}

// RESTORE SCREEN AFTER REFRESH
document.addEventListener("DOMContentLoaded", function () {
    const savedScreen = localStorage.getItem("currentScreen");

    // Restore the exact property loaded by PHP
    if (window.currentProperty) {
        selectedProperty = window.currentProperty;

        updateBookingProperty(window.currentProperty);

        // Set default move-in date
        const moveInInput = document.getElementById("bookingMoveIn");

        if (moveInInput) {
            const today = new Date();

            const localDate = new Date(
                today.getTime() - today.getTimezoneOffset() * 60000
            ).toISOString().split("T")[0];

            moveInInput.min = localDate;

            if (!moveInInput.value || moveInInput.value < localDate) {
                moveInInput.value = localDate;
            }
        }
    }

    // Restore saved hearts
    restoreSavedHearts();

    // Restore saved properties
    displaySavedProperties();

    restoreFilters();

    // Open the correct initial screen
    if (
        document.getElementById("property1") &&
        document.getElementById("booking")
    ) {
        showScreen("property1");
    } else if (savedScreen && document.getElementById(savedScreen)) {
        showScreen(savedScreen);
    } else {
        showScreen("filters");
    }
});
// ================================
// PAYMENT METHOD SELECTION
// ================================

let selectedPaymentMethod = "GCash";

const paymentMethods = document.querySelectorAll(".payment-method");

paymentMethods.forEach((method) => {
  method.addEventListener("click", function () {
    // Remove selected state from ALL payment methods
    paymentMethods.forEach((item) => {
      item.classList.remove("selected-method");

      const icon = item.querySelector(".payment-select-icon");

      if (icon) {
        icon.className = "fa-regular fa-circle payment-select-icon";
      }
    });

    // Select the clicked payment method
    this.classList.add("selected-method");

    const icon = this.querySelector(".payment-select-icon");

    if (icon) {
      icon.className = "fa-solid fa-circle-dot payment-select-icon";
    }

    // Save selected payment method
    selectedPaymentMethod = this.dataset.method;

    // Update success screen
    const successPaymentMethod = document.getElementById(
      "successPaymentMethod",
    );

    if (successPaymentMethod) {
      successPaymentMethod.textContent = selectedPaymentMethod;
    }

    console.log("Selected payment:", selectedPaymentMethod);
  });
});

/*  SUBMIT BOOKING*/

function submitBooking() {
    console.log("Confirm Payment clicked");

    const storedPropertyId = localStorage.getItem("selectedPropertyId");
    const propertyId = Number(storedPropertyId);

    const bookingRoom = document.getElementById("bookingRoom");

    // Get the exact room number selected by the renter
    const room = bookingRoom.dataset.roomNumber || "";

    const occupants = document.getElementById("bookingOccupants").textContent;
    const moveInDate = document.getElementById("bookingMoveIn").value;
    const paymentMethod = selectedPaymentMethod;

    const amountText = document.getElementById("dueToday").textContent;
    const amount = parseFloat(
        amountText.replace("₱", "").replace(/,/g, "")
    );

    // Validate booking details
    if (!propertyId || propertyId <= 0) {
        alert("No property selected.");
        return;
    }

    if (!room) {
        alert("Please select a room first.");
        showScreen("booking");
        return;
    }

    if (!moveInDate) {
        alert("Please select a move-in date.");
        showScreen("booking");
        return;
    }

    if (!paymentMethod) {
        alert("Please select a payment method.");
        return;
    }

    if (Number.isNaN(amount) || amount < 0) {
        alert("Invalid payment amount.");
        return;
    }

    // Prepare booking information
    const formData = new FormData();
    formData.append("property_id", propertyId);
    formData.append("room_number", room);
    formData.append("occupants", occupants);
    formData.append("move_in_date", moveInDate);
    formData.append("payment_method", paymentMethod);
    formData.append("amount", amount);

    console.log("Sending booking:", {
        propertyId,
        room,
        occupants,
        moveInDate,
        paymentMethod,
        amount
    });

    // Submit booking to PHP
    fetch("save_booking.php", {
        method: "POST",
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error("Server returned HTTP " + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log("PHP response:", data);

        if (!data.success) {
            alert(data.message || "Unable to save your booking.");
            return;
        }

        console.log("Booking saved. Booking ID:", data.booking_id);

        // Update property details
        if (window.currentProperty) {
            updateBookingProperty(window.currentProperty);
        }

        // Update success screen
        document.getElementById("successRoom").textContent = "Room " + room;
        document.getElementById("successMoveInDate").textContent = moveInDate;
        document.getElementById("successPaymentMethod").textContent = paymentMethod;

        // Open success screen
        showScreen("success");
    })
    .catch(error => {
        console.error("Booking error:", error);
        alert("Something went wrong while submitting the booking. Check the browser console.");
    });
}

/*Update booking*/

function updateMyBooking() {
    const fields = [
        ["myBookingName", "bookingName"],
        ["myBookingLocation", "bookingLocation"],
        ["myBookingRoom", "bookingRoom"],
        ["myBookingOccupants", "bookingOccupants"],
        ["myBookingDate", "selectedDate"],
        ["myBookingRent", "monthlyRent"],
        ["myBookingPayment", "reservationPayment"],
        ["myBookingMethod", "successPaymentMethod"],
        ["myBookingPaid", "dueToday"]
    ];

    fields.forEach(([targetId, sourceId]) => {
        const target = document.getElementById(targetId);
        const source = document.getElementById(sourceId);

        if (target && source) {
            target.textContent = source.textContent;
        }
    });

    const myBookingImage = document.getElementById("myBookingImage");
    const successImage = document.getElementById("successImage");

    if (myBookingImage && successImage) {
        myBookingImage.src = successImage.src;
    }
}

/*SHOW RENTAL DETAILS */

function showRentalDetails() {
  alert("Rental details will be displayed here.");
}

function showRentalPeriod() {
  alert("Rental period details will be displayed here.");
}

function showRentalAgreement() {
  alert("Rental agreement will be displayed here.");
}

/*REVIEW */
function setReviewRating(button, rating) {
  const starGroup = button.closest(".review-star-group");

  if (!starGroup) return;

  const stars = starGroup.querySelectorAll("button");

  stars.forEach((star, index) => {
    if (index < rating) {
      star.classList.add("active");
    } else {
      star.classList.remove("active");
    }
  });

  starGroup.dataset.rating = rating;
}
