
const searchInput = document.querySelector("#searchInput");
const categoryFilter = document.querySelector("#categoryFilter");
const sortSelect = document.querySelector("#sortSelect");

const eventsContainer = document.querySelector("#eventsContainer");
const loadingMessage = document.querySelector("#loadingMessage");
const errorMessage = document.querySelector("#errorMessage");
const eventSelect = document.querySelector("#eventId");
const csrfToken = document.querySelector("#csrfToken");
const registrationMessage = document.querySelector("#registrationMessage");

let allEvents = [];
let currentPage = 1;
const eventsPerPage = 5;

async function loadEvents() {
    try {
        const response = await fetch("events-api.php");

        if (!response.ok) {
            throw new Error("Failed to load events");
        }

        const data = await response.json();

        allEvents = data.events;
        csrfToken.value = data.csrfToken;

        eventSelect.innerHTML = '<option value="">-- Select an event --</option>';

        allEvents.forEach(event => {
            const option = document.createElement("option");
            option.value = event.event_id;
            option.textContent = event.title + " — " + event.event_date;
            eventSelect.appendChild(option);
        });

        loadingMessage.textContent = "";
        updateEvents();
    } catch (error) {
        loadingMessage.textContent = "";
        errorMessage.textContent =
            "Unable to load events from MySQL. Check XAMPP and the database connection.";
        eventSelect.innerHTML =
            '<option value="">Events could not be loaded</option>';
    }
}

function updateEvents() {
    const searchText = searchInput.value.toLowerCase();
    const category = categoryFilter.value;

    let filteredEvents = allEvents.filter(event =>
        event.title.toLowerCase().includes(searchText)
    );

    if (category !== "all") {
        filteredEvents = filteredEvents.filter(event =>
            event.category === category
        );
    }

    if (sortSelect.value === "name") {
        filteredEvents.sort((a, b) =>
            a.title.localeCompare(b.title)
        );
    } else {
        filteredEvents.sort((a, b) =>
            new Date(a.event_date) - new Date(b.event_date)
        );
    }

    currentPage = 1;
    displayEvents(filteredEvents);
}

function displayEvents(events) {
    eventsContainer.innerHTML = "";

    if (events.length === 0) {
        eventsContainer.textContent = "No matching events found.";
        createPagination(events);
        return;
    }

    const start = (currentPage - 1) * eventsPerPage;
    const pageEvents = events.slice(start, start + eventsPerPage);

    pageEvents.forEach(event => {
        const card = document.createElement("article");
        card.className = "event-card";

        const title = document.createElement("h3");
        title.textContent = event.title;

        const category = document.createElement("p");
        category.textContent = "Category: " + (event.category || "Other");

        const date = document.createElement("p");
        date.textContent = "Date: " + event.event_date;

        const location = document.createElement("p");
        location.textContent = "Location: " + (event.location || "To be announced");

        const button = document.createElement("button");
        button.type = "button";
        button.textContent = "Register";
        button.addEventListener("click", () => {
            eventSelect.value = String(event.event_id);
            document.querySelector("#eventRegistration").scrollIntoView({
                behavior: "smooth",
                block: "start"
            });
        });

        card.append(title, category, date, location, button);
        eventsContainer.appendChild(card);
    });

    createPagination(events);
}

function createPagination(events) {
    const pagination = document.querySelector("#pagination");
    pagination.innerHTML = "";

    const totalPages = Math.ceil(events.length / eventsPerPage);

    for (let i = 1; i <= totalPages; i++) {
        const button = document.createElement("button");
        button.type = "button";
        button.textContent = i;

        button.addEventListener("click", () => {
            currentPage = i;
            displayEvents(events);
        });

        pagination.appendChild(button);
    }
}

searchInput.addEventListener("input", updateEvents);
categoryFilter.addEventListener("change", updateEvents);
sortSelect.addEventListener("change", updateEvents);

// Show the result after the PHP form processor redirects back here.
const resultMessages = {
    success: "Registration successful!",
    invalid: "Please check your student details and selected event.",
    "student-not-found": "Student not found. Register your student details first.",
    duplicate: "You are already registered for this event.",
    error: "Registration failed. Please try again."
};

const result = new URLSearchParams(window.location.search).get("registration");

if (resultMessages[result]) {
    registrationMessage.textContent = resultMessages[result];
    document.querySelector("#eventRegistration").scrollIntoView({
        block: "start"
    });
}

loadEvents();
