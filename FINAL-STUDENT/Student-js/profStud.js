document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector(".sidebar");
    const toggleBtn = document.getElementById("toggleBtn");
    const content = document.querySelector('.content');

    toggleBtn.addEventListener("click", () => {
        sidebar.classList.toggle("hidden");
        content.style.marginLeft = sidebar.classList.contains("hidden") ? "0" : "250px";
    });
});