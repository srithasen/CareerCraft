
// js/logic.js
document.addEventListener("DOMContentLoaded", function () {
    console.log("JS Loaded - Ready for interactions.");

    // Example progress bar logic
    const progress = document.querySelector("#progress-bar");
    if (progress) {
        let value = progress.getAttribute("data-value");
        progress.style.width = value + "%";
    }
});
