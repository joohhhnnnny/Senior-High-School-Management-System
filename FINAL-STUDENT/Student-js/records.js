const sidebar = document.querySelector(".sidebar");
const toggleBtn = document.getElementById("toggleBtn");
const content = document.querySelector('.content');

toggleBtn.addEventListener("click", () => {
    sidebar.classList.toggle("hidden");
    content.style.marginLeft = sidebar.classList.contains("hidden") ? "0" : "250px";
});

async function refreshRecords() {
    const table = document.querySelector('.records-table tbody');
    try {
        const response = await fetch('../Student-php/fetch_records.php');
        const data = await response.json();
        
        if (data.status && data.data.length > 0) {
            table.innerHTML = '';
            data.data.forEach(record => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${record.subject_title}</td>
                    <td>${record.yearLevel}</td>
                    <td>${record.semester.charAt(0).toUpperCase() + record.semester.slice(1)}</td>
                    <td class="grade-cell">${record.final_grade || ''}</td>
                    <td>${record.remarks}</td>
                `;
                if (record.final_grade) {
                    row.classList.add('has-grade');
                }
                table.appendChild(row);
            });
        }
    } catch (error) {
        console.error('Error fetching records:', error);
    }
}

document.addEventListener('DOMContentLoaded', refreshRecords);