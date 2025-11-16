
const form = document.getElementById('resumeForm');
const preview = document.getElementById('resumePreview');

form.addEventListener('input', () => {
    const formData = new FormData(form);
    preview.innerHTML = `
        <h1>${formData.get('full_name') || 'Full Name'}</h1>
        <p>Email: ${formData.get('email') || 'Email'}</p>
        <p>Phone: ${formData.get('phone') || 'Phone'}</p>
        <h3>Education</h3>
        <p>${formData.get('education') || ''}</p>
        <h3>Skills</h3>
        <p>${formData.get('skills') || ''}</p>
        <h3>Experience</h3>
        <p>${formData.get('experience') || ''}</p>
        <h3>Projects</h3>
        <p>${formData.get('projects') || ''}</p>
        <h3>Certifications</h3>
        <p>${formData.get('certifications') || ''}</p>
    `;
});
