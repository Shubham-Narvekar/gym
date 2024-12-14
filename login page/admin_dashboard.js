// Function to switch sections
function showSection(sectionId) {
    const sections = document.querySelectorAll('.dashboard-section');
    sections.forEach(section => {
        section.classList.add('hidden'); // Hide all sections
    });
    document.getElementById(sectionId).classList.remove('hidden'); // Show selected section
}

document.getElementById('purchase_date').max = new Date().toISOString().split('T')[0];
document.getElementById('maintenance_date').max = new Date().toISOString().split('T')[0];
const maxDateTime = new Date().toISOString().slice(0, 16); // YYYY-MM-DDTHH:MM
document.getElementById('check_in_time').max = maxDateTime;
document.getElementById('check_out_time').max = maxDateTime;

