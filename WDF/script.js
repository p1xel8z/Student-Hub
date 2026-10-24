const themeToggle = document.querySelector("#themeToggle");

themeToggle.addEventListener("click", function() {

    document.body.classList.toggle("dark-theme");

    if (document.body.classList.contains("dark-theme")) {
        localStorage.setItem("theme", "dark");
        themeToggle.textContent = "Light Mode";
    } else {
        localStorage.setItem("theme", "light");
        themeToggle.textContent = "Dark Mode";
    }

});


const savedTheme = localStorage.getItem("theme");

if (savedTheme === "dark") {
    document.body.classList.add("dark-theme");
}

/* ===== Notification ===== */
 
const notificationBanner = document.querySelector("#notificationBanner");
const closeNotification = document.querySelector("#closeNotification");

if (notificationBanner && closeNotification) {
    closeNotification.addEventListener("click",function() {
    notificationBanner.remove();

});
}



/* ===== Slider ===== */

const slides = document.querySelectorAll(".slide");
const prevSlide = document.querySelector("#prevSlide");
const nextSlide = document.querySelector("#nextSlide");

let currentSlide = 0;

if (slides.length > 0 && prevSlide && nextSlide) {

    function showSlide(index) {
        slides[currentSlide].classList.remove("active");

        currentSlide = index;

        slides[currentSlide].classList.add("active");
    }

    nextSlide.addEventListener("click", function() {
        let next = (currentSlide + 1) % slides.length;
        showSlide(next);
    });

    prevSlide.addEventListener("click", function() {
        let previous = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(previous);
    });
}



/* ===== Modal ===== */
 

const infoModal = document.querySelector("#infoModal");
const openModal = document.querySelector("#openModal");
const closeModal = document.querySelector("#closeModal");
if (infoModal && openModal && closeModal) {

    openModal.addEventListener("click", function() {
        infoModal.style.display = "flex";
    });

    closeModal.addEventListener("click", function() {
        infoModal.style.display = "none";
    });

    infoModal.addEventListener("click", function(event) {

    if (event.target === infoModal) {
        infoModal.style.display = "none";
    }

    document.addEventListener("keydown", function(event) {

    if (event.key === "Escape" && infoModal.style.display === "flex") {
        infoModal.style.display = "none";
    }

});

});
}



const faqItems = document.querySelectorAll("main details");

if (faqItems.length > 0) {

    faqItems.forEach(function(item) {

        item.addEventListener("toggle", function() {

            if (item.open) {

                faqItems.forEach(function(otherItem) {

                    if (otherItem !== item) {
                        otherItem.open = false;
                    }

                });

            }

        });

    });

}