//list of images used for the background 
const images = [
    "https://cdn.pixabay.com/photo/2024/09/21/02/13/global-business-9062781_1280.jpg",
    "https://cdn.pixabay.com/photo/2017/05/31/11/17/office-2360063_1280.jpg",

];

let current = 0;
const bg1 = document.querySelector(".bg1");
const bg2 = document.querySelector(".bg2");
let showingBg1 = true;


function changeBackground() {
    const nextImage = images[current];

    if (showingBg1) {
        bg2.style.backgroundImage = `url(${nextImage})`;
        bg2.style.opacity = 1;
        bg1.style.opacity = 0;
    } else {
        bg1.style.backgroundImage = `url(${nextImage})`;
        bg1.style.opacity = 1;
        bg2.style.opacity = 0;
    }

    showingBg1 = !showingBg1;
    current = (current + 1) % images.length;
}


bg1.style.backgroundImage = `url(${images[0]})`;
bg1.style.opacity = 1;
current = 1;

setInterval(changeBackground, 5000);


let copyrightDate = document.querySelector('.day')

copyrightDate.textContent = new Date().getFullYear()


