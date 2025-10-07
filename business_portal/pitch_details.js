console.log("pitch_details.js loaded");

let year = document.querySelector('.day');
if (year) {
  year.textContent = new Date().getFullYear();
}

//image slideshow/carousel animations
let slideIndex = 1;
showSlides(slideIndex);

// Next/previous controls
function plusSlides(n) {
  showSlides(slideIndex += n);
}

// Thumbnail image controls
function currentSlide(n) {
  showSlides(slideIndex = n);
}

function showSlides(n) {
  let i;
  let slides = document.getElementsByClassName("mySlides");
  let dots = document.getElementsByClassName("dot");
  if (n > slides.length) {slideIndex = 1}
  if (n < 1) {slideIndex = slides.length}
  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";
  }
  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active", "");
  }
  slides[slideIndex-1].style.display = "block";
  dots[slideIndex-1].className += " active";
}



// --- touch support for mobile ---
let startX = 0;
let endX = 0;
let slidesContainer = document.querySelector(".slideshow-container");

slidesContainer.addEventListener("touchstart", function(e) {
  startX = e.touches[0].clientX;
}, false);

slidesContainer.addEventListener("touchmove", function(e) {
  endX = e.touches[0].clientX;
}, false);

slidesContainer.addEventListener("touchend", function() {
  let diffX = startX - endX;
  if (Math.abs(diffX) > 50) {
    if (diffX > 0) {
      plusSlides(1); 
    } else {
      plusSlides(-1); 
    }
  }
}, false);