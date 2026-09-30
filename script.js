



// RESTORE FILTERS


function restoreFilters() {

    const savedLocation =
        localStorage.getItem("selectedLocation");

    const savedPropertyType =
        localStorage.getItem("selectedPropertyType");

    const savedBudget =
        localStorage.getItem("selectedBudget");


    // Restore location
    if (savedLocation) {

        const radios =
            document.querySelectorAll(
                'input[name="loc"]'
            );

        radios.forEach(radio => {

            if (radio.value === savedLocation) {
                radio.checked = true;
                radio.dataset.wasChecked = "true";

                const label =
                    radio.closest("label");

                if (label) {
                    const selectedLabel =
                        label.querySelector(".selected-label");

                    if (selectedLabel) {
                        selectedLabel.textContent = "Selected";
                    }
                }
            }
        });
    }


    // Restore property type
    if (savedPropertyType) {

        const buttons =
            document.querySelectorAll(
                "#propertyTypes button"
            );

        buttons.forEach(button => {

            if (
                button.textContent.trim() ===
                savedPropertyType
            ) {
                button.classList.add("selected");
            } else {
                button.classList.remove("selected");
            }

        });
    }


    // Restore budget
    if (savedBudget) {

        const budgetSlider =
            document.getElementById("budget");

        if (budgetSlider) {
            budgetSlider.value = savedBudget;
        }
    }


    // Reapply filters
    if (
        savedLocation ||
        savedPropertyType ||
        savedBudget
    ) {
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
  "arevalo": "Arevalo",
  "villa": "Arevalo",
  "villa arevalo": "Arevalo",
  "baluarte": "Arevalo",
  "calaparan": "Arevalo",

  // CITY PROPER
  "city proper": "City Proper",
  "city": "City Proper",
  "downtown": "City Proper",
  "diversion": "City Proper",
  "plaza libertad": "City Proper",
  "iloilo plaza": "City Proper",
  "molo bridge": "City Proper",
  "rizal": "City Proper",
  "ui": "City Proper",
  

  // JARO
  "jaro": "Jaro",
  "cpu": "Jaro",
  "central philippine university": "Jaro",
  "wvsu": "Jaro",
  "western visayas state university": "Jaro",
  "jaro plaza": "Jaro",
  "jaro cathedral": "Jaro",
  "balabago": "Jaro",
  "tagbak": "Jaro",

  // LA PAZ
  "la paz": "La Paz",
  "lapaz": "La Paz",
  "la paz plaza": "La Paz",
  "la paz public market": "La Paz",

  // LAPUZ
  "lapuz": "Lapuz",
  "lapuz norte": "Lapuz",
  "lapuz sur": "Lapuz",

  // MANDURRIAO
  "mandurriao": "Mandurriao",
  "manduraio": "Mandurriao",
  "festive": "Mandurriao",
  "festive walk": "Mandurriao",
  "sm city iloilo": "Mandurriao",
  "sm iloilo": "Mandurriao",
  "megaworld": "Mandurriao",
  "iloilo business park": "Mandurriao",
  "ibp": "Mandurriao",
  "wv medical center": "Mandurriao",
  "wvmc": "Mandurriao",
  "western visayas medical center": "Mandurriao",

  // MOLO
  "molo": "Molo",
  "molo plaza": "Molo",
  "molo church": "Molo",
  "molo mansion": "Molo",
  "molo boulevard": "Molo"
};


locationSearch.addEventListener("input", function () {

  const search = this.value
    .trim()
    .toLowerCase();

  // Empty search = show everything
  if (search === "") {

    locationOptions.forEach(label => {
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
  locationOptions.forEach(label => {

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

    locationOptions.forEach(label => {

      const input = label.querySelector("input");
      const selectedLabel =
        label.querySelector(".selected-label");

      if (input.value === matchedLocation) {

        locationOptions.forEach(label => {

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
const locationRadios = document.querySelectorAll(
  '.location-options input[type="radio"]'
);

locationRadios.forEach(radio => {

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
      locationRadios.forEach(otherRadio => {

        otherRadio.dataset.wasChecked = "false";

        const otherLabel = otherRadio.closest("label");
        const otherSelected =
          otherLabel.querySelector(".selected-label");

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

    const amenityName =
      this.parentElement.querySelector("span").textContent.trim();

    console.log(
      amenityName,
      this.classList.contains("on") ? "ON" : "OFF"
    );
  });
});

// AVAILABLE NOW


const availableNowSwitch =
  document.querySelector(".compact-card .switch.on");

if (availableNowSwitch) {
  availableNowSwitch.addEventListener("click", function () {
    this.classList.toggle("on");

    console.log(
      "Available Now:",
      this.classList.contains("on")
    );
  });
}
// SMART MATCH


const smartMatchSwitch =
  document.querySelector(".gold-switch");

if (smartMatchSwitch) {
  smartMatchSwitch.addEventListener("click", function () {
    this.classList.toggle("on");

    console.log(
      "Smart Match:",
      this.classList.contains("on")
    );
  });
}


 
    // MONTHLY BUDGET
  
const budgetSlider = document.getElementById("budget");
const budgetValue = document.getElementById("budgetValue");
const resultBudget = document.getElementById("resultBudget");

function updateBudget() {
    const maxBudget = Number(budgetSlider.value);

    const budgetText =
        "₱500 - ₱" + maxBudget.toLocaleString("en-PH");

    if (budgetValue) {
        budgetValue.textContent = budgetText;
    }

    if (resultBudget) {
        resultBudget.textContent = budgetText;
    }
}

// Update while moving the slider
budgetSlider.addEventListener("input", updateBudget);

// Set the initial value
updateBudget();




function applyFilters(goToResults = true) {

  
    // LOCATION
  
    const selectedLocation = document.querySelector(
        'input[name="loc"]:checked'
    );
let locationText = "Iloilo City (All)";

if (selectedLocation) {
    locationText = selectedLocation.value;

    localStorage.setItem(
        "selectedLocation",
        locationText
    );
} else {
    localStorage.removeItem("selectedLocation");
}

    const resultLocation =
        document.getElementById("resultLocation");

    if (resultLocation) {
        resultLocation.textContent = locationText;
    }


    // PROPERTY TYPE
  

    const selectedPropertyType =
        document.querySelector(
            "#propertyTypes button.selected"
        );

    let propertyTypeText = "All Properties";

if (selectedPropertyType) {

    propertyTypeText =
        selectedPropertyType.textContent.trim();

    localStorage.setItem(
        "selectedPropertyType",
        propertyTypeText
    );

} else {

    localStorage.removeItem(
        "selectedPropertyType"
    );
}

    const resultPropertyType =
        document.getElementById("resultPropertyType");

    if (resultPropertyType) {
        resultPropertyType.textContent =
            propertyTypeText;
    }


  
    // BUDGET
   

    const budgetSlider =
        document.getElementById("budget");

    const maxBudget =
        Number(budgetSlider.value);

        localStorage.setItem(
    "selectedBudget",
    maxBudget
);

    const resultBudget =
        document.getElementById("resultBudget");

    if (resultBudget) {
        resultBudget.textContent =
            "₱500 - ₱" +
            maxBudget.toLocaleString("en-PH");
    }


    // FILTER RENTAL CARDS


const rentalCards =
    document.querySelectorAll(".rental-card");

let resultCount = 0;

rentalCards.forEach(card => {

    const cardLocation =
        card.dataset.location;

    const cardPrice =
        Number(card.dataset.price);

    const cardTypes =
        card.dataset.type
            ? card.dataset.type
                .split("/")
                .map(type => type.trim())
            : [];


   
    // LOCATION
  
    const locationMatch =
        locationText === "Iloilo City (All)" ||
        cardLocation === locationText;


    
    // BUDGET
   

    const budgetMatch =
        cardPrice >= 500 &&
        cardPrice <= maxBudget;


   
    // PROPERTY TYPE
   

    const typeMatch =
        propertyTypeText === "All Properties" ||
        cardTypes.includes(propertyTypeText);


    
    // SHOW / HIDE
 

    if (
        locationMatch &&
        budgetMatch &&
        typeMatch
    ) {

        card.style.display = "";

        resultCount++;

    } else {

        card.style.display = "none";

    }

});



    // RESULT COUNT
   

    const resultCountElement =
        document.querySelector(".sort-bar b");

    if (resultCountElement) {
        resultCountElement.textContent =
            resultCount + " rentals";
    }


    
    // GO TO RESULTS
    
if (goToResults) {
    showScreen("results");
}

}





document.querySelectorAll(".gallery").forEach(gallery => {

    const slides = gallery.querySelector(".slides");
    const slideItems = gallery.querySelectorAll(".slide");
    const dots = gallery.querySelectorAll(".dot");
    const photoNumber = gallery.querySelector(".photoNumber");

    let current = 0;

    function showSlide(index) {
        const total = slideItems.length;

        current = (index + total) % total;

        slides.style.transform = `translateX(-${current * 100}%)` ;

        dots.forEach((dot, i) => {
            dot.classList.toggle("active", i === current);
        });

        if (photoNumber) {
            photoNumber.textContent = current + 1;
        }
    }

    dots.forEach(dot => {
        dot.addEventListener("click", () => {
            showSlide(Number(dot.dataset.index));
        });
    });

    let touchStartX = 0;

    gallery.addEventListener("touchstart", e => {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    gallery.addEventListener("touchend", e => {
        const dx = e.changedTouches[0].screenX - touchStartX;

        if (Math.abs(dx) > 45) {
            showSlide(current + (dx < 0 ? 1 : -1));
        }
    }, { passive: true });

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
    showScreen("property28");
}




function toggleHeart(event, heart) {

    event.stopPropagation();

    const card = heart.closest(".rental-card");
    const propertyId = card.dataset.id;

    let savedProperties =
        JSON.parse(localStorage.getItem("savedProperties")) || [];

    if (savedProperties.includes(propertyId)) {

        // Remove from saved
        savedProperties =
            savedProperties.filter(id => id !== propertyId);

        heart.textContent = "♡";
        heart.classList.remove("saved");

    } else {

        // Add to saved
        savedProperties.push(propertyId);

        heart.textContent = "♥";
        heart.classList.add("saved");
    }

    localStorage.setItem(
        "savedProperties",
        JSON.stringify(savedProperties)
    );
}

function restoreSavedHearts() {

    const savedProperties =
        JSON.parse(localStorage.getItem("savedProperties")) || [];

    document.querySelectorAll(".rental-card").forEach(card => {

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

    const savedContainer =
        document.getElementById("savedProperties");

    if (!savedContainer) return;

    const savedIds =
        JSON.parse(localStorage.getItem("savedProperties")) || [];

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

    const allProperties =
        document.querySelectorAll(".rental-card");

    allProperties.forEach(card => {

        const propertyId = card.dataset.id;

        if (savedIds.includes(propertyId)) {

            const clone = card.cloneNode(true);

            // Prevent duplicate/incorrect click behavior
            clone.onclick = null;

            const heart = clone.querySelector(".heart");

            if (heart) {
                heart.onclick = function(event) {
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
        name: "Barrida Boarding House",
        image: "images//barridabh1.jpg",
        location: "Jaro, Iloilo City",
        rating: "5 (6)",
        price: 4500,
        rooms: [
            {
                id: "room_1_01",
                name: "Room A",
                type: "Double",
                occupants: "4-5 occupant",
                size: "16 sqm",
                available: true
            },
            {
                id: "room_1_02",
                name: "Room B",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "15 sqm",
                available: true
            },
            {
                id: "room_1_01",
                name: "Room C",
                type: "Family Room",
                occupants: "8-10 occupant",
                size: "25 sqm",
                available: true
            },
            {
                id: "room_1_02",
                name: "Room D",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "13 sqm",
                available: true
            }
        ]
    },
    
    
    

    {
        id: "property2",
        name: "InY Residences",
        image: "images/madu-bh-InY1.jpg",
        location: "Manduriao, Iloilo City",
        rating: "4.0 (9)",
        price: 2200,


        rooms: [
            {
                id: "room_2_01",
                name: "Room A-12",
                type: "Single",
                occupants: "1 occupant",
                size: "12 sqm",
                available: true
            },
            {
                id: "room_2_02",
                name: "Room A-13",
                type: "Double",
                occupants: "1-2 occupant",
                size: "12 sqm",
                available: true
            }
        ]
    },

    {
        id: "property3",
        name: "Shi's Place",
        image: "images/madu-bh-shi1.jpg",
        location: "Manduriao, Iloilo City",
        rating: "4.5 (5)",
        price: 3000,


         rooms: [
            {
                id: "room_3_01",
                name: "Room A-12",
                type: "Single",
                occupants: "1 occupant",
                size: "12 sqm",
                available: true
            },
            {
                id: "room_3_02",
                name: "Room A-13",
                type: "Double",
                occupants: "1-2 occupant",
                size: "12 sqm",
                available: true
            }
        ]
    },

    {
        id: "property4",
        name: "DM Pal",
        image: "images/dmpalbh1.jpg",
        location: "Manduriao, Iloilo City",
        rating: "5 (5)",
        price: 10000,

         rooms: [
            {
                id: "room_4_01",
                name: "Room A-12",
                type: "Single",
                occupants: "1 occupant",
                size: "12 sqm",
                available: true
            },
            {
                id: "room_4_02",
                name: "Room A-13",
                type: "Double",
                occupants: "1-2 occupant",
                size: "12 sqm",
                available: true
            }
        ]
    },

     {
        id: "property5",
        name: "Rex Appartment",
        image: "images/rexapart1.avif",
        location: "Manduriao, Iloilo City",
        rating: "5 (5)",
        price: 12000,

         rooms: [
            {
                id: "room_5_01",
                name: "Room A-12",
                type: "Studio Unit",
                occupants: "1 occupant",
                size: "12 sqm",
                available: true
            },
            {
                id: "room_5_02",
                name: "Room A-13",
                type: "Fully Funished Studio Unit",
                occupants: "1-2 occupant",
                size: "25 sqm",
                available: true
            }
        ]
    },
{
        id: "property6",
        name: "Skyleigh Appartment",
        image: "images/skyleighapart2.webp",
        location: "La Paz, Iloilo City",
        rating: "4 (6)",
        price: 11000,

        rooms: [
            {
                id: "room_6_01",
                name: "Room A-12",
                type: "Double",
                occupants: "2 occupant",
                size: "25 sqm",
                available: true
            },
            {
                id: "room_6_02",
                name: "Room A-13",
                type: "Deluxe ",
                occupants: "2 occupant",
                size: "20 sqm",
                available: true
            },
            {
                id: "room_6_03",
                name: "Room B-01",
                type: "Tripple",
                occupants: "3 occupants",
                size: "21 sqm",
                available: true
            },

              {
                id: "room_6_04",
                name: "Room B-02",
                type: "Deluxe ",
                occupants: "1-4 occupant",
                size: "25 sqm",
                available: true
            }
        ]
     },
 {
        id: "property7",
        name: "AJ Boarding House",
        image: "images/AJbh1.jpg",
        location: "La Paz, Iloilo City",
        rating: "4 (6)",
        price: 5500,

        rooms: [
            {
                id: "room_7_01",
                name: "Room A-12",
                type: "Double",
                occupants: "2 occupant",
                size: "25 sqm",
                available: true
            },
            {
                id: "room_7_02",
                name: "Room A-13",
                type: "Deluxe ",
                occupants: "2 occupant",
                size: "20 sqm",
                available: true
            },
            {
                id: "room_7_03",
                name: "Room B-01",
                type: "Tripple",
                occupants: "3 occupants",
                size: "21 sqm",
                available: true
            },

              {
                id: "room_7_04",
                name: "Room B-02",
                type: "Deluxe ",
                occupants: "1-4 occupant",
                size: "25 sqm",
                available: true
            }

            
        ]
 },

     {
        id: "property8",
        name: "A&L Place",
        image: "images/a&lplace.jpg",
        location: "La Paz, Iloilo City",
        rating: "4 (6)",
        price: 600,

        rooms: [
            {
                id: "room_8_01",
                name: "Room A-12",
                type: "Double",
                occupants: "2 occupant",
                size: "25 sqm",
                available: true
            },
            {
                id: "room_8_02",
                name: "Room A-13",
                type: "Deluxe ",
                occupants: "2 occupant",
                size: "20 sqm",
                available: true
            },
            {
                id: "room_8_03",
                name: "Room B-01",
                type: "Tripple",
                occupants: "3 occupants",
                size: "21 sqm",
                available: true
            }
        ]

},
     {
        id: "property9",
        name: "Eastville Dormitory",
        image: "images/eastvilledorm1.jpg",
        location: "Molo, Iloilo City",
        rating: "4 (6)",
        price: 3000,

        rooms: [
            {
                id: "room_9_01",
                name: "Room A-12",
                type: "Double",
                occupants: "2 occupant",
                size: "25 sqm",
                available: true
            },
            {
                id: "room_9_02",
                name: "Room A-13",
                type: "Deluxe ",
                occupants: "2 occupant",
                size: "20 sqm",
                available: true
            }
        ]
    },

     {
        id: "property10",
        name: "Eastville Dormitory",
        image: "images/moloW&Abh1.jpg",
        location: "Molo, Iloilo City",
        rating: "4 (6)",
        price: 2500,

        rooms: [
            {
                id: "room_10_01",
                name: "Room A",
                type: "Double",
                occupants: "4-5 occupant",
                size: "16 sqm",
                available: true
            },
            {
                id: "room_10_02",
                name: "Room B",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "15 sqm",
                available: true
            },
            {
                id: "room_10_01",
                name: "Room C",
                type: "Family Room",
                occupants: "8-10 occupant",
                size: "25 sqm",
                available: true
            },
            {
                id: "room_10_02",
                name: "Room D",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "13 sqm",
                available: true
            }
        ]
    },


     {
        id: "property11",
        name: "E. Alabado Dormitory",
        image: "images/moloalabadodorm1.jpg",
        location: "Molo, Iloilo City",
        rating: "4 (6)",
        price: 1800,

        rooms: [
            {
                id: "room_11_01",
                name: "Room A",
                type: "Double",
                occupants: "4-5 occupant",
                size: "16 sqm",
                available: true
            },
            {
                id: "room_11_02",
                name: "Room B",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "15 sqm",
                available: true
            },
            {
                id: "room_11_01",
                name: "Room C",
                type: "Family Room",
                occupants: "8-10 occupant",
                size: "25 sqm",
                available: true
            }
        ]
    },

    {
        id: "property12",
        name: "J&J Boarding House",
        image: "images/j&jbh1.jpg",
        location: "Lapuz, Iloilo City",
        rating: "4 (6)",
        price: 2000,

        rooms: [
            {
                id: "room_12_01",
                name: "Room A",
                type: "Double",
                occupants: "4-5 occupant",
                size: "16 sqm",
                available: true
            },
            {
                id: "room_12_02",
                name: "Room B",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "15 sqm",
                available: true
            },
            {
                id: "room_12_01",
                name: "Room C",
                type: "Family Room",
                occupants: "8-10 occupant",
                size: "25 sqm",
                available: true
            },
            {
                id: "room_12_02",
                name: "Room D",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "13 sqm",
                available: true
            }
        ]
    },


    {
        id: "property13",
        name: " IBH/Isonza",
        image: "images/arevalo bh1.jpg",
        location: "Molo, Iloilo City",
        rating: "4 (6)",
        price: 7000,

        rooms: [
            {
                id: "room_13_01",
                name: "Room A",
                type: "Double",
                occupants: "4-5 occupant",
                size: "16 sqm",
                available: true
            },
            {
                id: "room_13_02",
                name: "Room B",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "15 sqm",
                available: true
            },
            {
                id: "room_13_01",
                name: "Room C",
                type: "Family Room",
                occupants: "8-10 occupant",
                size: "25 sqm",
                available: true
            },
            {
                id: "room_13_02",
                name: "Room D",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "13 sqm",
                available: true
            }
        ]
    },

    {
        id: "property14",
        name: " KNJ Boarding House",
        image: "images/arevalo bh1.jpg",
        location: "Villa Arevalo, Iloilo City",
        rating: "4 (6)",
        price: 1800,

        rooms: [
            {
                id: "room_14_01",
                name: "Room A",
                type: "Double",
                occupants: "4-5 occupant",
                size: "16 sqm",
                available: true
            },
            {
                id: "room_14_02",
                name: "Room B",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "15 sqm",
                available: true
            },
            {
                id: "room_14_01",
                name: "Room C",
                type: "Family Room",
                occupants: "8-10 occupant",
                size: "25 sqm",
                available: true
            },
            {
                id: "room_14_02",
                name: "Room D",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "13 sqm",
                available: true
            }
        ]
    },
     {
        id: "property15",
        name: " Hotel Madiaas",
        image: "images/madiaastrans3.jpg",
        location: "City Proper, Iloilo City",
        rating: "4 (6)",
        price: 889,

        rooms: [
            {
                id: "room_15_01",
                name: "Room A",
                type: "Double",
                occupants: "4-5 occupant",
                size: "16 sqm",
                available: true
            },
            {
                id: "room_15_02",
                name: "Room B",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "15 sqm",
                available: true
            },
            {
                id: "room_15_01",
                name: "Room C",
                type: "Family Room",
                occupants: "8-10 occupant",
                size: "25 sqm",
                available: true
            },
            {
                id: "room_15_02",
                name: "Room D",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "13 sqm",
                available: true
            }
        ]
    },

    {
        id: "property16",
        name: " Norma Boarding House",
        image: "images/propernormabh1.jpg",
        location: "City Proper, Iloilo City",
        rating: "4 (6)",
        price: 4000,

        rooms: [
            {
                id: "room_16_01",
                name: "Room A",
                type: "Double",
                occupants: "4-5 occupant",
                size: "16 sqm",
                available: true
            },
            {
                id: "room_16_02",
                name: "Room B",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "15 sqm",
                available: true
            },
            {
                id: "room_16_01",
                name: "Room C",
                type: "Family Room",
                occupants: "8-10 occupant",
                size: "25 sqm",
                available: true
            },
            {
                id: "room_16_02",
                name: "Room D",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "13 sqm",
                available: true
            }
        ]
    },

    {
        id: "property17",
        name: " Rian's Apartment",
        image: "images/rianapart2.avif",
        location: "Arevalo, Iloilo City",
        rating: "5 (6)",
        price: 6000,

        rooms: [
            {
                id: "room_17_01",
                name: "Room A",
                type: "Double",
                occupants: "4-5 occupant",
                size: "16 sqm",
                available: true
            },
            {
                id: "room_17_02",
                name: "Room B",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "15 sqm",
                available: true
            },
            {
                id: "room_17_01",
                name: "Room C",
                type: "Family Room",
                occupants: "8-10 occupant",
                size: "25 sqm",
                available: true
            },
            {
                id: "room_17_02",
                name: "Room D",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "13 sqm",
                available: true
            }
        ]
    },

    {
        id: "property18",
        name: " Bernwoods Extyn B&B Dormitory",
        image: "images/bbdorm_trans1.jpg",
        location: "Arevalo, Iloilo City",
        rating: "5 (6)",
        price: 1100,
         price: 6000,

        rooms: [
            {
                id: "room_18_01",
                name: "Room A",
                type: "Double",
                occupants: "4-5 occupant",
                size: "16 sqm",
                available: true
            },
            {
                id: "room_18_02",
                name: "Room B",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "15 sqm",
                available: true
            },
            {
                id: "room_18_01",
                name: "Room C",
                type: "Family Room",
                occupants: "8-10 occupant",
                size: "25 sqm",
                available: true
            },
            {
                id: "room_18_02",
                name: "Room D",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "13 sqm",
                available: true
            }
        ]
    },

    {
        id: "property19",
        name: " Andeng's Boarding House Rental",
        image: "images/andengarevalospace1.jpg",
        location: "Arevalo, Iloilo City",
        rating: "5 (6)",
        price: 6000,
         price: 7500,

        rooms: [
            {
                id: "room_19_01",
                name: "Room A",
                type: "Double",
                occupants: "4-5 occupant",
                size: "16 sqm",
                available: true
            },
            {
                id: "room_19_02",
                name: "Room B",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "15 sqm",
                available: true
            },
            {
                id: "room_19_01",
                name: "Room C",
                type: "Family Room",
                occupants: "8-10 occupant",
                size: "25 sqm",
                available: true
            },
            {
                id: "room_19_02",
                name: "Room D",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "13 sqm",
                available: true
            }
        ]
    },

     {
        id: "property20",
        name: "RAC Homes Apartment",
        image: "images/racapart2.avif",
        location: "City Proper, Iloilo City",
        rating: "5 (6)",
        price: 4500,
         price: 7500,

        rooms: [
            {
                id: "room_20_01",
                name: "Room A",
                type: "Double",
                occupants: "4-5 occupant",
                size: "16 sqm",
                available: true
            },
            {
                id: "room_20_02",
                name: "Room B",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "15 sqm",
                available: true
            },
            {
                id: "room_20_01",
                name: "Room C",
                type: "Family Room",
                occupants: "8-10 occupant",
                size: "25 sqm",
                available: true
            },
            {
                id: "room_20_02",
                name: "Room D",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "13 sqm",
                available: true
            }
        ]
    },

    {
        id: "property21",
        name: "Angel Heart Ladies Dormitory",
        image: "images/angeldorm1.avif",
        location: "City Proper, Iloilo City",
        rating: "5 (6)",
        price: 6000,
        rooms: [
            {
                id: "room_21_01",
                name: "Room A",
                type: "Double",
                occupants: "4-5 occupant",
                size: "16 sqm",
                available: true
            },
            {
                id: "room_21_02",
                name: "Room B",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "15 sqm",
                available: true
            },
            {
                id: "room_21_01",
                name: "Room C",
                type: "Family Room",
                occupants: "8-10 occupant",
                size: "25 sqm",
                available: true
            },
            {
                id: "room_21_02",
                name: "Room D",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "13 sqm",
                available: true
            }
        ]
    },

    {
        id: "property22",
        name: "Dignity Boarding House",
        image: "images/Dignitybed1.jpg",
        location: "Mandurriao, Iloilo City",
        rating: "5 (6)",
        price: 7800,
        rooms: [
            {
                id: "room_22_01",
                name: "Room A",
                type: "Double",
                occupants: "4-5 occupant",
                size: "16 sqm",
                available: true
            },
            {
                id: "room_22_02",
                name: "Room B",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "15 sqm",
                available: true
            },
            {
                id: "room_22_01",
                name: "Room C",
                type: "Family Room",
                occupants: "8-10 occupant",
                size: "25 sqm",
                available: true
            },
            {
                id: "room_22_02",
                name: "Room D",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "13 sqm",
                available: true
            }
        ]
    },

     {
        id: "property23",
        name: "Deion Apartment",
        image: "images/dionapart2.avif",
        location: "Jaro, Iloilo City",
        rating: "5 (6)",
        price: 8200,
        rooms: [
            {
                id: "room_23_01",
                name: "Room A",
                type: "Double",
                occupants: "4-5 occupant",
                size: "16 sqm",
                available: true
            },
            {
                id: "room_23_02",
                name: "Room B",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "15 sqm",
                available: true
            },
            {
                id: "room_23_01",
                name: "Room C",
                type: "Family Room",
                occupants: "8-10 occupant",
                size: "25 sqm",
                available: true
            },
            {
                id: "room_23_02",
                name: "Room D",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "13 sqm",
                available: true
            }
        ]
    },

      {
        id: "property24",
        name: "Juvy's Dormitory",
        image: "images/juvydorm3.jpg",
        location: "Jaro, Iloilo City",
        rating: "5 (6)",
        price: 10000,
        rooms: [
            {
                id: "room_24_01",
                name: "Room A",
                type: "Double",
                occupants: "4-5 occupant",
                size: "16 sqm",
                available: true
            },
            {
                id: "room_24_02",
                name: "Room B",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "15 sqm",
                available: true
            },
            {
                id: "room_24_01",
                name: "Room C",
                type: "Family Room",
                occupants: "8-10 occupant",
                size: "25 sqm",
                available: true
            },
            {
                id: "room_24_02",
                name: "Room D",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "13 sqm",
                available: true
            }
        ]
    },

     {
        id: "property25",
        name: "Nina's Townhouse",
        image: "images/ninatrans1.png",
        location: "Jaro, Iloilo City",
        rating: "5 (6)",
         price: 3500,
        price: 13000,

        rooms: [
            {
                id: "room_25_01",
                name: "Room A",
                type: "Double",
                occupants: "4-5 occupant",
                size: "16 sqm",
                available: true
            },
            {
                id: "room_25_02",
                name: "Room B",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "15 sqm",
                available: true
            },
            {
                id: "room_25_01",
                name: "Room C",
                type: "Family Room",
                occupants: "8-10 occupant",
                size: "25 sqm",
                available: true
            },
            {
                id: "room_25_02",
                name: "Room D",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "13 sqm",
                available: true
            }
        ]
    },

    {
        id: "property26",
        name: " D3A RESIDENCE ",
        image: "images/tjgarciaapart1.avif",
        location: "La Paz, Iloilo City",
        rating: "5 (6)",
         price: 4500,
        

        rooms: [
            {
                id: "room_26_01",
                name: "Room A",
                type: "Double",
                occupants: "4-5 occupant",
                size: "16 sqm",
                available: true
            },
            {
                id: "room_26_02",
                name: "Room B",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "15 sqm",
                available: true
            },
            {
                id: "room_26_01",
                name: "Room C",
                type: "Family Room",
                occupants: "8-10 occupant",
                size: "25 sqm",
                available: true
            },
            {
                id: "room_26_02",
                name: "Room D",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "13 sqm",
                available: true
            }
        ]
    },

     {
        id: "property27",
        name: "JRD BED SPACE ",
        image: "images/jrdbed1.jpg",
        location: "La Paz, Iloilo City",
        rating: "5 (6)",
         price: 4000,
        

        rooms: [
            {
                id: "room_27_01",
                name: "Room A",
                type: "Double",
                occupants: "4-5 occupant",
                size: "16 sqm",
                available: true
            },
            {
                id: "room_27_02",
                name: "Room B",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "15 sqm",
                available: true
            },
            {
                id: "room_27_01",
                name: "Room C",
                type: "Family Room",
                occupants: "8-10 occupant",
                size: "25 sqm",
                available: true
            },
            {
                id: "room_27_02",
                name: "Room D",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "13 sqm",
                available: true
            }
        ]
    },

     {
        id: "property28",
        name: "S&L Apartment ",
        image: "images/slapart1.jpg",
        location: "Lapuz, Iloilo City",
        rating: "5 (6)",
         price: 4000,
        

        rooms: [
            {
                id: "room_28_01",
                name: "Room A",
                type: "Double",
                occupants: "4-5 occupant",
                size: "16 sqm",
                available: true
            },
            {
                id: "room_28_02",
                name: "Room B",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "15 sqm",
                available: true
            },
            {
                id: "room_28_01",
                name: "Room C",
                type: "Family Room",
                occupants: "8-10 occupant",
                size: "25 sqm",
                available: true
            },
            {
                id: "room_28_02",
                name: "Room D",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "13 sqm",
                available: true
            }
        ]
    },

     {
        id: "property29",
        name: "S&L Apartment ",
        image: "images/bertzdorm1.jpg",
        location: "Lapuz, Iloilo City",
        rating: "5 (6)",
         price: 6000,
        

        rooms: [
            {
                id: "room_29_01",
                name: "Room A",
                type: "Double",
                occupants: "4-5 occupant",
                size: "16 sqm",
                available: true
            },
            {
                id: "room_29_02",
                name: "Room B",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "15 sqm",
                available: true
            },
            {
                id: "room_29_01",
                name: "Room C",
                type: "Family Room",
                occupants: "8-10 occupant",
                size: "25 sqm",
                available: true
            },
            {
                id: "room_29_02",
                name: "Room D",
                type: "Deluxe ",
                occupants: "2-3 occupant",
                size: "13 sqm",
                available: true
            }
        ]
    },

    

   
  

    

];

let selectedProperty = null;




//Room Selection

let selectedRoomId = null;

function loadRooms(propertyId) {


    

    const property = properties.find(
        property => property.id === propertyId
    );

    if (!property) {
        console.log("Property not found:", propertyId);
        return;
    }

    const roomOptions = document.getElementById("roomOptions");

    roomOptions.innerHTML = "";

    const availableRooms = property.rooms.filter(
        room => room.available === true
    );

    availableRooms.forEach(room => {

        const option = document.createElement("div");

        option.className = "room-option";

        option.dataset.roomId = room.id;
        option.dataset.room =
            `${room.name} (${room.type})`;

        option.dataset.occupants =
            `${room.occupants} · ${room.size}`;

        option.innerHTML = `
            <b>${room.name} (${room.type})</b>
            <span>${room.occupants} · ${room.size}</span>
        `;

        option.addEventListener("click", function () {

            document.getElementById("bookingRoom").textContent =
                this.dataset.room;

            document.getElementById("bookingOccupants").textContent =
                this.dataset.occupants;

            selectedRoomId = this.dataset.roomId;

            roomOptions.classList.remove("show");

            console.log("Selected room:", selectedRoomId);
        });

        roomOptions.appendChild(option);

        
    });
}

/*RESERVE NOW */

function reserveNow(propertyId) {

    selectedProperty = properties.find(
        property => property.id === propertyId
    );

    if (!selectedProperty) {
        console.error("Property not found:", propertyId);
        return;
    }

    // Save selected property
    localStorage.setItem(
        "selectedPropertyId",
        propertyId
    );

    // Booking screen
    document.getElementById("bookingImage").src =
        selectedProperty.image;

    document.getElementById("bookingName").textContent =
        selectedProperty.name;

    document.getElementById("bookingLocation").textContent =
        "⌖ " + selectedProperty.location;

    document.getElementById("bookingRating").textContent =
        selectedProperty.rating;

    // Price
    const price =
        "₱" + selectedProperty.price.toLocaleString() + ".00";

    document.getElementById("monthlyRent").textContent =
        price;

    document.getElementById("reservationPayment").textContent =
        price;

    document.getElementById("dueToday").textContent =
        price;

    // Payment screen
    document.getElementById("paymentImage").src =
        selectedProperty.image;

    document.getElementById("paymentName").textContent =
        selectedProperty.name;

    document.getElementById("paymentPrice").textContent =
        price;

    // Payment button
    document.getElementById("confirmPaymentButton").innerHTML =
        '<i class="fa-solid fa-lock"></i> Confirm Payment — ' + price;

    // Success screen
    document.getElementById("successImage").src =
        selectedProperty.image;

    document.getElementById("successName").textContent =
        selectedProperty.name;

    document.getElementById("successLocation").textContent =
        "⌖ " + selectedProperty.location;

    document.getElementById("successPrice").textContent =
        price;

    // Go to booking
    showScreen("booking");

    setTimeout(() => {
        loadRooms(propertyId);
    }, 0);
}

/*BACK*/function backToProperty() {

    const propertyId = localStorage.getItem("selectedPropertyId");

    if (!propertyId) {
        showScreen("results");
        return;
    }

    const id = Number(propertyId);

    const screenId = id === 1
        ? "property"
        : `property${id - 1}`;

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



function confirmReservation() {
  closeModal();
  showToast("Reservation request started!");
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

document.getElementById("reserveModal").addEventListener("click", e => {
  if (e.target.id === "reserveModal") closeModal();
});



// SELECTED ROOM


const roomBox = document.getElementById("roomBox");
const roomOptions = document.getElementById("roomOptions");

roomBox.addEventListener("click", function () {
    roomOptions.classList.toggle("show");
});


// MOVE-IN DATE
const dateBox = document.getElementById("dateBox");
const moveInDate = document.getElementById("moveInDate");
const selectedDate = document.getElementById("selectedDate");

dateBox.addEventListener("click", function () {
  moveInDate.showPicker();
});

moveInDate.addEventListener("change", function () {
  if (!this.value) return;

  const date = new Date(this.value + "T00:00:00");

  const formattedDate = date.toLocaleDateString("en-US", {
    month: "long",
    day: "numeric",
    year: "numeric"
  });

  selectedDate.textContent = formattedDate;

  // Update success page
  const successDate = document.getElementById("successMoveInDate");

  if (successDate) {
    successDate.textContent = formattedDate;
  }
});

//Show Screen//
function showScreen(screenId) {

    // Hide all screens
    document.querySelectorAll(".screen").forEach(screen => {
        screen.classList.remove("active");
        screen.style.display = "none";
    });

    // Show selected screen
    const selectedScreen = document.getElementById(screenId);

    if (selectedScreen) {
        selectedScreen.classList.add("active");
        selectedScreen.style.display = "block";
    }

    // Update active navigation button
    document.querySelectorAll(".bottom-nav button").forEach(button => {
        button.classList.remove("nav-active");
    });

    const activeButton = document.querySelector(
        `.bottom-nav button[data-screen="${screenId}"]`
    );

    if (activeButton) {
        activeButton.classList.add("nav-active");
    }

    // Save current screen
    localStorage.setItem("currentScreen", screenId);
}

// RESTORE SCREEN AFTER REFRESH
document.addEventListener("DOMContentLoaded", function () {

    const savedScreen = localStorage.getItem("currentScreen");
    const savedPropertyId = localStorage.getItem("selectedPropertyId");

    // Restore selected property
    if (savedPropertyId) {

        const property = properties.find(
            p => String(p.id) === String(savedPropertyId)
        );

        if (property) {

            selectedProperty = property;

            const price =
                "₱" + property.price.toLocaleString() + ".00";

            // Booking screen
            document.getElementById("bookingImage").src =
                property.image;

            document.getElementById("bookingName").textContent =
                property.name;

            document.getElementById("bookingLocation").textContent =
                "⌖ " + property.location;

            document.getElementById("bookingRating").textContent =
                property.rating;

            document.getElementById("monthlyRent").textContent =
                price;

            document.getElementById("reservationPayment").textContent =
                price;

            document.getElementById("dueToday").textContent =
                price;

            // Payment screen
            document.getElementById("paymentImage").src =
                property.image;

            document.getElementById("paymentName").textContent =
                property.name;

            document.getElementById("paymentPrice").textContent =
                price;

            document.getElementById("confirmPaymentButton").innerHTML =
                '<i class="fa-solid fa-lock"></i> Confirm Payment — ' + price;

            // Success screen
            document.getElementById("successImage").src =
                property.image;

            document.getElementById("successName").textContent =
                property.name;

            document.getElementById("successLocation").textContent =
                "⌖ " + property.location;

            document.getElementById("successPrice").textContent =
                price;

            // Restore rooms
            loadRooms(property.id);
        }
    }

    // Restore saved hearts
    restoreSavedHearts();

    // Restore saved properties
    displaySavedProperties();

    restoreFilters();

    // Restore the last screen
    if (savedScreen && document.getElementById(savedScreen)) {

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

paymentMethods.forEach(method => {

    method.addEventListener("click", function () {

        // Remove selected state from ALL payment methods
        paymentMethods.forEach(item => {
            item.classList.remove("selected-method");

            const icon = item.querySelector(".payment-select-icon");

            if (icon) {
                icon.className =
                    "fa-regular fa-circle payment-select-icon";
            }
        });

        // Select the clicked payment method
        this.classList.add("selected-method");

        const icon = this.querySelector(".payment-select-icon");

        if (icon) {
            icon.className =
                "fa-solid fa-circle-dot payment-select-icon";
        }

        // Save selected payment method
        selectedPaymentMethod = this.dataset.method;

        // Update success screen
        const successPaymentMethod =
            document.getElementById("successPaymentMethod");

        if (successPaymentMethod) {
            successPaymentMethod.textContent =
                selectedPaymentMethod;
        }

        console.log("Selected payment:", selectedPaymentMethod);
    });

});


function updateMyBooking() {

    document.getElementById("myBookingName").textContent =
        document.getElementById("bookingName").textContent;

    document.getElementById("myBookingLocation").textContent =
        document.getElementById("bookingLocation").textContent;

    document.getElementById("myBookingRoom").textContent =
        document.getElementById("bookingRoom").textContent;

    document.getElementById("myBookingOccupants").textContent =
        document.getElementById("bookingOccupants").textContent;

    document.getElementById("myBookingDate").textContent =
        document.getElementById("selectedDate").textContent;

    document.getElementById("myBookingRent").textContent =
        document.getElementById("monthlyRent").textContent;

    document.getElementById("myBookingPayment").textContent =
        document.getElementById("reservationPayment").textContent;

    document.getElementById("myBookingMethod").textContent =
        document.getElementById("successPaymentMethod").textContent;

    document.getElementById("myBookingPaid").textContent =
        document.getElementById("dueToday").textContent;

    document.getElementById("myBookingImage").src =
        document.getElementById("successImage").src;
}

