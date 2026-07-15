const BASE_URL = 'http://localhost:8000/api';
const loader = document.getElementById('loader');

// Helper: Toggle loading spinner
const toggleLoader = (show) => {
    loader.style.display = show ? 'block' : 'none';
};

// Helper: Fetch and render data
async function loadData() {
    toggleLoader(true);
    try {
        // Fetch Faculties
        const fRes = await fetch(`${BASE_URL}/GetFaculty.php`);
        const fJson = await fRes.json();
        const fList = document.getElementById('facultyList');
        const fSelect = document.getElementById('facultySelect');

        fList.innerHTML = '';
        fSelect.innerHTML = '';

        fJson.data.forEach(f => {
            fList.innerHTML += `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    ${f.name}
                    <button class="btn btn-sm btn-outline-primary" onclick="openEditModal(${f.faculty_id}, '${f.name}', 'faculty')">Edit</button>
                </li>`;
            fSelect.innerHTML += `<option value="${f.faculty_id}">${f.name}</option>`;
        });

        // Fetch Departments
        const dRes = await fetch(`${BASE_URL}/GetDepartment.php`);
        const dJson = await dRes.json();
        const dList = document.getElementById('deptList');

        dList.innerHTML = '';
        dJson.data.forEach(d => {
            dList.innerHTML += `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    ${d.name} (Faculty ID: ${d.faculty_id})
                    <button class="btn btn-sm btn-outline-primary" onclick="openEditModal(${d.department_id}, '${d.name}', 'department', ${d.faculty_id})">Edit</button>
                </li>`;
        });
    } catch (err) { console.error('Error fetching data:', err); }
    finally { toggleLoader(false); }
}

// POST: Add New Faculty (FormData)
document.getElementById('facultyForm').onsubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData();
    formData.append('facultyname', document.getElementById('facultyName').value);

    await fetch(`${BASE_URL}/AddFaculty.php`, { method: 'POST', body: formData });
    document.getElementById('facultyName').value = '';
    loadData();
};

// POST: Add New Department (FormData)
document.getElementById('deptForm').onsubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData();
    formData.append('name', document.getElementById('deptName').value);
    formData.append('faculty_id', document.getElementById('facultySelect').value);

    await fetch(`${BASE_URL}/AddDepartment.php`, { method: 'POST', body: formData });
    document.getElementById('deptName').value = '';
    loadData();
};

// Modal: Open for Edit
window.openEditModal = (id, name, type, facultyId = null) => {
    document.getElementById('editId').value = id;
    document.getElementById('editName').value = name;
    document.getElementById('editType').value = type;
    document.getElementById('editFacultyId').value = facultyId || '';
    new bootstrap.Modal(document.getElementById('editModal')).show();
};

// PUT: Update Data (JSON)
window.submitEdit = async () => {
    const id = document.getElementById('editId').value;
    const name = document.getElementById('editName').value;
    const type = document.getElementById('editType').value;
    const facultyId = document.getElementById('editFacultyId').value;

    const endpoint = type === 'faculty' ? 'EditFaculty.php' : 'EditDepartment.php';
    const payload = type === 'faculty'
        ? { faculty_id: parseInt(id), name: name }
        : { department_id: parseInt(id), name: name, faculty_id: parseInt(facultyId) };

    toggleLoader(true);
    await fetch(`${BASE_URL}/${endpoint}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });

    bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
    loadData();
};

// Initial run
loadData();