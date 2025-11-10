<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Resume Builder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0; padding: 0;
            background: url('bg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: black;
        }
        .resume-container {
            max-width: 800px;
            margin:0 auto;
            background-color: #ffffffff;
            padding: 2rem;
            border: 1px solid #191a1aff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transform: scale(0.9);
            transition: transform 0.3s ease-in-out;
            min-height: 1100px; /* Ensures a consistent page size for PDF */
        }
        .sidebar-right {
            width: 300px;
            position: fixed;
            right: 0;
            top: 0;
            height: 100%;
            background-color: grey;
            border-left: 1px solid #3eb221ff;
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
            z-index: 50;
            overflow-y: auto;
        }
        .sidebar-right.show {
            transform: translateX(0);
        }
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 100;
        }
        .modal.show {
            display: flex;
        }
        .cursor-grab {
            cursor: grab;
        }
        /* Style to ensure drag handle is visible on resume section headers */
        .resume-container [data-section] h2, 
        .resume-container [data-section] h3,
        .resume-container [data-section] h4 {
            cursor: grab;
        }

        /* Set base font size for the resume container only */
        .resume-container {
            font-size: 16px;
        }

        /* A class for dynamically changing font size */
        .fs-12 { font-size: 12px; }
        .fs-14 { font-size: 14px; }
        .fs-16 { font-size: 16px; }
        .fs-18 { font-size: 18px; }
        .fs-20 { font-size: 20px; }
        .fs-24 { font-size: 24px; }
        .fs-30 { font-size: 30px; }
        .fs-36 { font-size: 36px; }
        .fs-48 { font-size: 48px; }

        /* The following styles are for a clean custom section that works with font-size changes */
        .custom-section {
            border-bottom: 2px solid #ccc;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
            position: relative;
        }
        .custom-section h3 {
            font-size: 1.5rem;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="flex justify-between items-center bg-white p-4 shadow-md sticky top-0 z-40">
        <div class="flex space-x-2">
            <button id="templates-btn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Templates</button>
            <button id="font-btn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Fonts</button>
            <div class="flex items-center space-x-2">
                <button id="font-size-btn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Font Size</button>
            </div>
            <button id="manage-btn" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Manage Sections</button>
        </div>
        <h1 class="text-xl font-semibold">Resume Builder</h1>
        <button id="dashboard-btn" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-900">Dashboard</button>
    </div>

    <div class="resume-container" id="resume-preview">
        </div>

    <div class="sidebar-right" id="sidebar-right">
        <div class="p-4 overflow-y-auto h-full">
            <h2 class="text-lg font-semibold mb-4">Manage Sections</h2>
            <div id="manage-sections-container" class="space-y-3">
                </div>
            <div class="mt-4">
                <h3 class="font-semibold mb-2">Custom Sections</h3>
                <div id="custom-sections-container"></div>
                <button id="add-custom-section" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 mt-2 w-full">+ Add Custom Section</button>
            </div>
            <div class="mt-4">
                <h3 class="font-semibold mb-2">Add Education Entry</h3>
                <button id="add-education-entry" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 w-full">+ Add Education</button>
            </div>
        </div>
    </div>

    <div class="modal" id="template-modal">
        <div class="bg-white p-6 rounded-lg shadow-lg w-3/4 max-w-2xl">
            <h3 class="text-lg font-semibold mb-4">Select a Template</h3>
            <div class="grid grid-cols-3 gap-4">
                <button class="template-btn border p-3 hover:border-blue-500" data-template="1">Template 1</button>
                <button class="template-btn border p-3 hover:border-blue-500" data-template="2">Template 2</button>
                <button class="template-btn border p-3 hover:border-blue-500" data-template="3">Template 3</button>
                <button class="template-btn border p-3 hover:border-blue-500" data-template="4">Template 4</button>
                <button class="template-btn border p-3 hover:border-blue-500" data-template="5">Template 5</button>
                <button class="template-btn border p-3 hover:border-blue-500" data-template="6">Template 6</button>
                <button class="template-btn border p-3 hover:border-blue-500" data-template="7">Template 7</button>
            </div>
            <div class="mt-4 text-right">
                <button id="close-modal" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Close</button>
            </div>
        </div>
    </div>

    <div class="modal" id="font-modal">
        <div class="bg-white p-6 rounded-lg shadow-lg w-3/4 max-w-2xl">
            <h3 class="text-lg font-semibold mb-4">Select a Font</h3>
            <div class="grid grid-cols-2 gap-4">
                <button class="font-select-btn border p-3 hover:border-blue-500" data-font="Arial, sans-serif" style="font-family: Arial, sans-serif;">Arial</button>
                <button class="font-select-btn border p-3 hover:border-blue-500" data-font="Georgia, serif" style="font-family: Georgia, serif;">Georgia</button>
                <button class="font-select-btn border p-3 hover:border-blue-500" data-font="'Times New Roman', serif" style="font-family: 'Times New Roman', serif;">Times New Roman</button>
                <button class="font-select-btn border p-3 hover:border-blue-500" data-font="'Courier New', monospace" style="font-family: 'Courier New', monospace;">Courier New</button>
                <button class="font-select-btn border p-3 hover:border-blue-500" data-font="Verdana, sans-serif" style="font-family: Verdana, sans-serif;">Verdana</button>
                <button class="font-select-btn border p-3 hover:border-blue-500" data-font="Helvetica, Arial, sans-serif" style="font-family: Helvetica, Arial, sans-serif;">Helvetica</button>
                <button class="font-select-btn border p-3 hover:border-blue-500" data-font="sans-serif" style="font-family: sans-serif;">Default Sans-Serif</button>
                <button class="font-select-btn border p-3 hover:border-blue-500" data-font="serif" style="font-family: serif;">Default Serif</button>
            </div>
            <div class="mt-4 text-right">
                <button id="close-font-modal" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Close</button>
            </div>
        </div>
    </div>

    <div class="modal" id="font-size-modal">
        <div class="bg-white p-6 rounded-lg shadow-lg w-3/4 max-w-2xl">
            <h3 class="text-lg font-semibold mb-4">Select Font Size</h3>
            <div class="grid grid-cols-4 gap-4">
                <button class="font-size-select-btn border p-3 hover:border-blue-500" data-font-size="12px">12</button>
                <button class="font-size-select-btn border p-3 hover:border-blue-500" data-font-size="14px">14</button>
                <button class="font-size-select-btn border p-3 hover:border-blue-500" data-font-size="16px">16</button>
                <button class="font-size-select-btn border p-3 hover:border-blue-500" data-font-size="18px">18</button>
                <button class="font-size-select-btn border p-3 hover:border-blue-500" data-font-size="20px">20</button>
                <button class="font-size-select-btn border p-3 hover:border-blue-500" data-font-size="24px">24</button>
                <button class="font-size-select-btn border p-3 hover:border-blue-500" data-font-size="30px">30</button>
                <button class="font-size-select-btn border p-3 hover:border-blue-500" data-font-size="36px">36</button>
                <button class="font-size-select-btn border p-3 hover:border-blue-500" data-font-size="48px">48</button>
            </div>
            <div class="mt-4 text-right">
                <button id="close-font-size-modal" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Close</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const templatesBtn = document.getElementById('templates-btn');
            const fontBtn = document.getElementById('font-btn');
            const manageBtn = document.getElementById('manage-btn');
            const sidebar = document.getElementById('sidebar-right');
            const templateModal = document.getElementById('template-modal');
            const fontModal = document.getElementById('font-modal');
            const closeModal = document.getElementById('close-modal');
            const closeFontModal = document.getElementById('close-font-modal');
            const resume = document.getElementById('resume-preview');
            const dashboardBtn = document.getElementById('dashboard-btn');
            const addCustomBtn = document.getElementById('add-custom-section');
            const addEducationBtn = document.getElementById('add-education-entry');
            const manageSectionsContainer = document.getElementById('manage-sections-container');
            const fontSizetBtn = document.getElementById('font-size-btn');
            const fontSizeModal = document.getElementById('font-size-modal');
            const closeFontSizeModal = document.getElementById('close-font-size-modal');
            
            let customCount = 0;
            let lastUsedFontSize = '16px';
            const shufflableContainerId = 'shufflable-content';

            const templates = {
                '1': `
                    <div class="mb-6 text-center header-block">
                        <h2 class="text-2xl font-bold contenteditable" contenteditable="true">Your Name</h2>
                        <p class="text-gray-500 contenteditable" contenteditable="true">Phone Number | email@example.com | City, State</p>
                    </div>
                    <div id="${shufflableContainerId}">
                        <div id="summary-section" data-section-type="main" class="mb-4 border-b pb-4">
                            <h3 class="font-semibold text-lg contenteditable" contenteditable="true">Summary</h3>
                            <p class="text-gray-600 contenteditable" contenteditable="true">Write a brief summary about yourself highlighting strengths and achievements.</p>
                        </div>
                        <div id="experience-section" data-section-type="main" class="mb-4 border-b pb-4">
                            <h3 class="font-semibold text-lg contenteditable" contenteditable="true">Work Experience</h3>
                            <div class="experience-entry-container">
                                <div class="experience-entry">
                                    <h4 class="font-medium contenteditable" contenteditable="true">Company A | Location</h4>
                                    <p class="text-sm text-gray-500 contenteditable" contenteditable="true">Role | Start-End</p>
                                    <p class="text-gray-600 contenteditable" contenteditable="true">Key responsibilities and accomplishments.</p>
                                </div>
                            </div>
                        </div>
                        <div id="skills-section" data-section-type="main" class="mb-4 border-b pb-4">
                            <h3 class="font-semibold text-lg contenteditable" contenteditable="true">Skills</h3>
                            <p class="text-gray-600 contenteditable" contenteditable="true">List of your work-related talents and skills.</p>
                        </div>
                        <div id="education-section" data-section-type="main" class="mb-4 border-b pb-4">
                            <h3 class="font-semibold text-lg contenteditable" contenteditable="true">Education</h3>
                            <div class="education-entry-container">
                                <div class="education-entry">
                                    <h4 class="font-medium contenteditable" contenteditable="true">Graduated School | Location</h4>
                                    <p class="text-sm text-gray-500 contenteditable" contenteditable="true">Field of Study | Graduation Date</p>
                                    <p class="text-gray-600 contenteditable" contenteditable="true">Details about educational background.</p>
                                </div>
                            </div>
                        </div>
                        <div id="languages-section" data-section-type="main" class="mb-4 border-b pb-4">
                            <h3 class="font-semibold text-lg contenteditable" contenteditable="true">Languages</h3>
                            <p class="text-gray-600 contenteditable" contenteditable="true">List of languages and proficiency levels.</p>
                        </div>
                        <div id="certificates-section" data-section-type="main" class="mb-4 border-b pb-4">
                            <h3 class="font-semibold text-lg contenteditable" contenteditable="true">Certificates</h3>
                            <p class="text-gray-600 contenteditable" contenteditable="true">Relevant certifications or courses.</p>
                        </div>
                        <div id="awards-section" data-section-type="main" class="mb-4 border-b pb-4">
                            <h3 class="font-semibold text-lg contenteditable" contenteditable="true">Awards</h3>
                            <p class="text-gray-600 contenteditable" contenteditable="true">Awards or honors received.</p>
                        </div>
                    </div>
                `,
                '2': `
                    <div class="flex resume-structure">
                        <div class="w-1/3 bg-gray-100 p-4 sidebar-column" id="sidebar-content">
                            <h2 class="text-xl font-bold mb-4 contenteditable" contenteditable="true">Your Name</h2>
                            <p class="text-sm text-gray-700 contenteditable" contenteditable="true">Phone | Email | LinkedIn</p>
                            <div class="mt-6 header-block">
                                <h3 class="font-semibold text-md mb-2">Contact</h3>
                                <p class="text-sm contenteditable" contenteditable="true">123 Main St, Anytown</p>
                                <p class="text-sm contenteditable" contenteditable="true">555-123-4567</p>
                                <p class="text-sm contenteditable" contenteditable="true">your.email@example.com</p>
                            </div>
                            <div id="skills-section" data-section-type="sidebar" class="mt-6">
                                <h3 class="font-semibold text-md mb-2">Skills</h3>
                                <ul class="list-disc list-inside text-sm">
                                    <li class="contenteditable" contenteditable="true">JavaScript</li>
                                    <li class="contenteditable" contenteditable="true">React</li>
                                    <li class="contenteditable" contenteditable="true">Node.js</li>
                                </ul>
                            </div>
                            <div id="languages-section" data-section-type="sidebar" class="mt-6">
                                <h3 class="font-semibold text-md mb-2">Languages</h3>
                                <ul class="list-disc list-inside text-sm">
                                    <li class="contenteditable" contenteditable="true">English (Native)</li>
                                    <li class="contenteditable" contenteditable="true">Spanish (Intermediate)</li>
                                </ul>
                            </div>
                            <div id="certificates-section" data-section-type="sidebar" class="mt-6">
                                <h3 class="font-semibold text-md mb-2">Certificates</h3>
                                <p class="text-sm contenteditable" contenteditable="true">Relevant certifications or courses.</p>
                            </div>
                        </div>
                        <div class="w-2/3 p-4 main-content-column" id="${shufflableContainerId}">
                            <div id="summary-section" data-section-type="main" class="mb-6 border-b pb-4">
                                <h3 class="font-semibold text-lg mb-2">Summary</h3>
                                <p class="text-gray-800 contenteditable" contenteditable="true">Highly motivated individual with a passion for web development and a proven ability to learn new technologies quickly.</p>
                            </div>
                            <div id="experience-section" data-section-type="main" class="mb-6 border-b pb-4">
                                <h3 class="font-semibold text-lg mb-2">Work Experience</h3>
                                <div class="experience-entry-container">
                                    <div class="experience-entry mb-4">
                                        <h4 class="font-medium contenteditable" contenteditable="true">Senior Developer | Tech Solutions Inc. | 2020 - Present</h4>
                                        <ul class="list-disc list-inside text-gray-700">
                                            <li class="contenteditable" contenteditable="true">Developed and maintained web applications using React and Node.js.</li>
                                            <li class="contenteditable" contenteditable="true">Collaborated with cross-functional teams to deliver high-quality software.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div id="education-section" data-section-type="main" class="mb-6 border-b pb-4">
                                <h3 class="font-semibold text-lg mb-2">Education</h3>
                                <div class="education-entry-container">
                                    <div class="education-entry">
                                        <h4 class="font-medium contenteditable" contenteditable="true">University of Technology | BS in Computer Science | 2016 - 2020</h4>
                                        <p class="text-sm text-gray-600 contenteditable" contenteditable="true">Graduated with Honors.</p>
                                    </div>
                                </div>
                            </div>
                            <div id="awards-section" data-section-type="main" class="mb-6 border-b pb-4">
                                <h3 class="font-semibold text-lg mb-2">Awards</h3>
                                <p class="text-gray-800 contenteditable" contenteditable="true">Employee of the Year - 2022</p>
                            </div>
                        </div>
                    </div>
                `,
                '3': `
                    <div class="bg-blue-100 p-6 mb-6 header-block">
                        <h2 class="text-3xl font-extrabold text-blue-800 contenteditable" contenteditable="true">JANE DOE</h2>
                        <p class="text-blue-600 contenteditable" contenteditable="true">Web Developer | Creative Thinker</p>
                        <div class="flex justify-center space-x-4 mt-2 text-sm text-blue-700">
                            <p class="contenteditable" contenteditable="true">jane.doe@email.com</p>
                            <p class="contenteditable" contenteditable="true">(123) 456-7890</p>
                            <p class="contenteditable" contenteditable="true">LinkedIn Profile</p>
                        </div>
                    </div>
                    <div class="p-4" id="${shufflableContainerId}">
                        <div id="summary-section" data-section-type="main" class="mb-6">
                            <h3 class="font-bold text-xl text-blue-700 mb-2 contenteditable" contenteditable="true">Summary</h3>
                            <p class="text-gray-800 contenteditable" contenteditable="true">Passionate and innovative web developer with 5+ years of experience in crafting user-centric applications and dynamic websites.</p>
                        </div>
                        <div id="experience-section" data-section-type="main" class="mb-6">
                            <h3 class="font-bold text-xl text-blue-700 mb-2 contenteditable" contenteditable="true">Work Experience</h3>
                            <div class="experience-entry-container border-l-4 border-blue-500 pl-4 mb-4">
                                <h4 class="font-semibold text-lg contenteditable" contenteditable="true">Frontend Developer | Creative Agency | 2021 - Present</h4>
                                <ul class="list-disc list-inside text-gray-600">
                                    <li class="contenteditable" contenteditable="true">Developed responsive interfaces using Vue.js.</li>
                                    <li class="contenteditable" contenteditable="true">Optimized website performance by 25%.</li>
                                    <li class="contenteditable" contenteditable="true">Mentored junior developers on best practices.</li>
                                </ul>
                            </div>
                        </div>
                        <div id="education-section" data-section-type="main" class="mb-6">
                            <h3 class="font-bold text-xl text-blue-700 mb-2 contenteditable" contenteditable="true">Education</h3>
                            <div class="education-entry-container border-l-4 border-blue-500 pl-4">
                                <div class="education-entry">
                                    <h4 class="font-semibold text-lg contenteditable" contenteditable="true">Bachelor of Arts in Digital Media | State University | 2017 - 2021</h4>
                                    <p class="text-sm text-gray-600 contenteditable" contenteditable="true">Specialization in Web Design.</p>
                                </div>
                            </div>
                        </div>
                        <div id="skills-section" data-section-type="main" class="mb-6">
                            <h3 class="font-bold text-xl text-blue-700 mb-2 contenteditable" contenteditable="true">Skills</h3>
                            <div class="flex flex-wrap gap-2">
                                <span class="bg-blue-200 text-blue-800 text-sm px-3 py-1 rounded-full contenteditable" contenteditable="true">HTML/CSS</span>
                                <span class="bg-blue-200 text-blue-800 text-sm px-3 py-1 rounded-full contenteditable" contenteditable="true">JavaScript</span>
                                <span class="bg-blue-200 text-blue-800 text-sm px-3 py-1 rounded-full contenteditable" contenteditable="true">Vue.js</span>
                                <span class="bg-blue-200 text-blue-800 text-sm px-3 py-1 rounded-full contenteditable" contenteditable="true">UI/UX Design</span>
                            </div>
                        </div>
                        <div id="languages-section" data-section-type="main" class="mb-6">
                            <h3 class="font-bold text-xl text-blue-700 mb-2 contenteditable" contenteditable="true">Languages</h3>
                            <p class="text-gray-800 contenteditable" contenteditable="true">English, Spanish</p>
                        </div>
                        <div id="certificates-section" data-section-type="main" class="mb-6">
                            <h3 class="font-bold text-xl text-blue-700 mb-2 contenteditable" contenteditable="true">Certificates</h3>
                            <p class="text-gray-800 contenteditable" contenteditable="true">Web Development Bootcamp</p>
                        </div>
                        <div id="awards-section" data-section-type="main" class="mb-6">
                            <h3 class="font-bold text-xl text-blue-700 mb-2 contenteditable" contenteditable="true">Awards</h3>
                            <p class="text-gray-800 contenteditable" contenteditable="true">Hackathon Winner - 2020</p>
                        </div>
                    </div>
                `,
                '4': `
                    <div class="p-6">
                        <div class="mb-8 pb-4 border-b-2 border-gray-300 text-center header-block">
                            <h2 class="text-4xl font-extrabold text-gray-900 contenteditable" contenteditable="true">JOHN DOE</h2>
                            <p class="text-xl text-gray-600 contenteditable" contenteditable="true">Senior Software Engineer</p>
                            <div class="mt-4 flex justify-center space-x-6 text-gray-700 text-sm">
                                <span class="contenteditable" contenteditable="true">john.doe@email.com</span>
                                <span class="contenteditable" contenteditable="true">| (123) 456-7890</span>
                                <span class="contenteditable" contenteditable="true">| LinkedIn.com/in/johndoe</span>
                            </div>
                        </div>
                        <div id="${shufflableContainerId}">
                            <div id="summary-section" data-section-type="main" class="mb-8">
                                <h3 class="text-2xl font-bold text-gray-800 mb-4 border-l-4 border-blue-500 pl-3">Summary</h3>
                                <p class="text-gray-700 leading-relaxed contenteditable" contenteditable="true">Highly skilled and innovative Senior Software Engineer with 10+ years of experience in developing robust and scalable web applications. Proficient in full-stack development, cloud platforms, and agile methodologies.</p>
                            </div>
                            <div id="experience-section" data-section-type="main" class="mb-8">
                                <h3 class="text-2xl font-bold text-gray-800 mb-4 border-l-4 border-blue-500 pl-3">Work Experience</h3>
                                <div class="experience-entry-container">
                                    <div class="experience-entry mb-6">
                                        <div class="flex justify-between items-baseline mb-1">
                                            <h4 class="font-semibold text-lg contenteditable" contenteditable="true">Lead Developer | Tech Innovations Inc.</h4>
                                            <span class="text-gray-600 text-sm contenteditable" contenteditable="true">Jan 2018 – Present | San Francisco, CA</span>
                                        </div>
                                        <ul class="list-disc list-inside text-gray-700 space-y-1">
                                            <li class="contenteditable" contenteditable="true">Managed a portfolio of 10+ software development projects with budgets up to $2M.</li>
                                            <li class="contenteditable" contenteditable="true">Implemented agile methodologies, increasing team efficiency by 20%.</li>
                                            <li class="contenteditable" contenteditable="true">Mentored junior developers and conducted code reviews.</li>
                                        </ul>
                                    </div>
                                    <div class="experience-entry mb-6">
                                        <div class="flex justify-between items-baseline mb-1">
                                            <h4 class="font-semibold text-lg contenteditable" contenteditable="true">Software Engineer | Creative Solutions Co.</h4>
                                            <span class="text-gray-600 text-sm contenteditable" contenteditable="true">Jun 2013 – Dec 2017 | New York, NY</span>
                                        </div>
                                        <ul class="list-disc list-inside text-gray-700 space-y-1">
                                            <li class="contenteditable" contenteditable="true">Developed and maintained client-facing web applications.</li>
                                            <li class="contenteditable" contenteditable="true">Collaborated with UX/UI designers to implement new features.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div id="skills-section" data-section-type="main" class="mb-8">
                                <h3 class="text-2xl font-bold text-gray-800 mb-4 border-l-4 border-blue-500 pl-3">Skills</h3>
                                <div class="flex flex-wrap gap-3 text-gray-700">
                                    <span class="bg-gray-100 px-4 py-1 rounded-full contenteditable" contenteditable="true">JavaScript</span>
                                    <span class="bg-gray-100 px-4 py-1 rounded-full contenteditable" contenteditable="true">Python</span>
                                    <span class="bg-gray-100 px-4 py-1 rounded-full contenteditable" contenteditable="true">React</span>
                                    <span class="bg-gray-100 px-4 py-1 rounded-full contenteditable" contenteditable="true">Node.js</span>
                                    <span class="bg-gray-100 px-4 py-1 rounded-full contenteditable" contenteditable="true">AWS</span>
                                    <span class="bg-gray-100 px-4 py-1 rounded-full contenteditable" contenteditable="true">Docker</span>
                                    <span class="bg-gray-100 px-4 py-1 rounded-full contenteditable" contenteditable="true">SQL</span>
                                    <span class="bg-gray-100 px-4 py-1 rounded-full contenteditable" contenteditable="true">Agile</span>
                                </div>
                            </div>
                            <div id="education-section" data-section-type="main" class="mb-8">
                                <h3 class="text-2xl font-bold text-gray-800 mb-4 border-l-4 border-blue-500 pl-3">Education</h3>
                                <div class="education-entry-container">
                                    <div class="education-entry mb-4">
                                        <h4 class="font-semibold text-lg contenteditable" contenteditable="true">Master of Business Administration | University of Oregon</h4>
                                        <p class="text-gray-600 text-sm contenteditable" contenteditable="true">Graduated May 2016</p>
                                    </div>
                                    <div class="education-entry">
                                        <h4 class="font-semibold text-lg contenteditable" contenteditable="true">Bachelor of Science in Computer Science | Oregon State University</h4>
                                        <p class="text-gray-600 text-sm contenteditable" contenteditable="true">Graduated May 2014</p>
                                    </div>
                                </div>
                            </div>
                            <div id="languages-section" data-section-type="main" class="mb-8">
                                <h3 class="text-2xl font-bold text-gray-800 mb-4 border-l-4 border-blue-500 pl-3">Languages</h3>
                                <p class="text-gray-700 contenteditable" contenteditable="true">English (Native), Spanish (Fluent)</p>
                            </div>
                            <div id="certificates-section" data-section-type="main" class="mb-8">
                                <h3 class="text-2xl font-bold text-gray-800 mb-4 border-l-4 border-blue-500 pl-3">Certificates</h3>
                                <p class="text-gray-700 contenteditable" contenteditable="true">AWS Certified Solutions Architect, Certified Scrum Master</p>
                            </div>
                            <div id="awards-section" data-section-type="main" class="mb-8">
                                <h3 class="text-2xl font-bold text-gray-800 mb-4 border-l-4 border-blue-500 pl-3">Awards</h3>
                                <p class="text-gray-700 contenteditable" contenteditable="true">Innovator of the Year (2020), Dean''s List (2009-2011)</p>
                            </div>
                        </div>
                    </div>
                `,
                '5': `
                    <div class="flex resume-structure">
                        <div class="w-2/3 p-6 main-content-column" id="${shufflableContainerId}">
                            <div class="mb-8 header-block">
                                <h2 class="text-4xl font-bold text-gray-800 contenteditable" contenteditable="true">ALICE SMITH</h2>
                                <p class="text-xl text-blue-600 contenteditable" contenteditable="true">Marketing Specialist</p>
                            </div>
                            <div id="summary-section" data-section-type="main" class="mb-6 pb-4 border-b border-gray-200">
                                <h3 class="text-xl font-semibold text-gray-700 mb-2">Summary</h3>
                                <p class="text-gray-700 leading-relaxed contenteditable" contenteditable="true">Results-driven Marketing Specialist with 5 years of experience in digital marketing, content creation, and campaign management. Proven ability to increase brand visibility and drive engagement across various platforms.</p>
                            </div>
                            <div id="experience-section" data-section-type="main" class="mb-6 pb-4 border-b border-gray-200">
                                <h3 class="text-xl font-semibold text-gray-700 mb-3">Work Experience</h3>
                                <div class="experience-entry-container">
                                    <div class="experience-entry mb-4">
                                        <div class="flex justify-between items-baseline mb-1">
                                            <h4 class="font-medium text-lg contenteditable" contenteditable="true">Digital Marketing Lead | Growth Solutions Agency</h4>
                                            <span class="text-gray-500 text-sm contenteditable" contenteditable="true">Mar 2020 – Present</span>
                                        </div>
                                        <ul class="list-disc list-inside text-gray-700 ml-4 space-y-1">
                                            <li class="contenteditable" contenteditable="true">Managed and optimized digital marketing campaigns, achieving a 25% increase in lead generation.</li>
                                            <li class="contenteditable" contenteditable="true">Developed comprehensive content strategies across social media, blog, and email.</li>
                                            <li class="contenteditable" contenteditable="true">Utilized analytics tools to track performance and identify growth opportunities.</li>
                                        </ul>
                                    </div>
                                    <div class="experience-entry mb-4">
                                        <div class="flex justify-between items-baseline mb-1">
                                            <h4 class="font-medium text-lg contenteditable" contenteditable="true">Marketing Coordinator | Innovate Brand Co.</h4>
                                            <span class="text-gray-500 text-sm contenteditable" contenteditable="true">Jul 2018 – Feb 2020</span>
                                        </div>
                                        <ul class="list-disc list-inside text-gray-700 ml-4 space-y-1">
                                            <li class="contenteditable" contenteditable="true">Assisted in planning and executing marketing events and promotions.</li>
                                            <li class="contenteditable" contenteditable="true">Created marketing collateral and presentations.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div id="education-section" data-section-type="main" class="mb-6 pb-4 border-b border-gray-200">
                                <h3 class="text-xl font-semibold text-gray-700 mb-3">Education</h3>
                                <div class="education-entry-container">
                                    <div class="education-entry">
                                        <h4 class="font-medium text-lg contenteditable" contenteditable="true">Bachelor of Business Administration in Marketing | State University</h4>
                                        <p class="text-gray-600 text-sm contenteditable" contenteditable="true">Graduated May 2018</p>
                                    </div>
                                </div>
                            </div>
                            <div id="awards-section" data-section-type="main" class="mb-6 pb-4 border-b border-gray-200">
                                <h3 class="text-xl font-semibold text-gray-700 mb-3">Awards</h3>
                                <p class="text-gray-700 contenteditable" contenteditable="true">Marketing Excellence Award (2021)</p>
                            </div>
                        </div>
                        <div class="w-1/3 bg-blue-50 p-6 text-gray-800 sidebar-column" id="sidebar-content">
                            <div class="mb-6 header-block">
                                <h3 class="text-lg font-semibold mb-2">Contact</h3>
                                <p class="text-sm contenteditable" contenteditable="true">123 Market St, City, State</p>
                                <p class="text-sm contenteditable" contenteditable="true">(555) 987-6543</p>
                                <p class="text-sm contenteditable" contenteditable="true">alice.smith@email.com</p>
                                <p class="text-sm contenteditable" contenteditable="true">linkedin.com/in/alicesmith</p>
                            </div>
                            <div id="skills-section" data-section-type="sidebar" class="mb-6">
                                <h3 class="text-lg font-semibold mb-2">Skills</h3>
                                <ul class="list-disc list-inside text-sm space-y-1">
                                    <li class="contenteditable" contenteditable="true">SEO/SEM</li>
                                    <li class="contenteditable" contenteditable="true">Content Marketing</li>
                                    <li class="contenteditable" contenteditable="true">Social Media Management</li>
                                    <li class="contenteditable" contenteditable="true">Google Analytics</li>
                                    <li class="contenteditable" contenteditable="true">Email Marketing</li>
                                </ul>
                            </div>
                            <div id="languages-section" data-section-type="sidebar" class="mb-6">
                                <h3 class="text-lg font-semibold mb-2">Languages</h3>
                                <ul class="list-disc list-inside text-sm space-y-1">
                                    <li class="contenteditable" contenteditable="true">English (Native)</li>
                                    <li class="contenteditable" contenteditable="true">French (Intermediate)</li>
                                </ul>
                            </div>
                            <div id="certificates-section" data-section-type="sidebar" class="mb-6">
                                <h3 class="text-lg font-semibold mb-2">Certificates</h3>
                                <p class="text-sm contenteditable" contenteditable="true">Google Ads Certification</p>
                                <p class="text-sm contenteditable" contenteditable="true">HubSpot Content Marketing</p>
                            </div>
                        </div>
                    </div>
                `,
                '6': `
                    <div class="p-8">
                        <div class="text-center mb-10 pb-6 border-b-4 border-gray-400 header-block">
                            <h1 class="text-5xl font-extrabold text-gray-800 uppercase contenteditable" contenteditable="true">SARAH JOHNSON</h1>
                            <p class="text-2xl text-gray-600 mt-2 font-light contenteditable" contenteditable="true">Project Manager | PMP Certified</p>
                            <div class="mt-4 text-lg text-gray-700 space-x-8">
                                <span class="contenteditable" contenteditable="true">(111) 222-3333</span>
                                <span class="contenteditable" contenteditable="true">| sarah.j@email.com</span>
                                <span class="contenteditable" contenteditable="true">| Portland, OR</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 resume-structure">
                            <div class="md:col-span-2 main-content-column" id="${shufflableContainerId}">
                                <div id="summary-section" data-section-type="main" class="mb-8">
                                    <h2 class="text-3xl font-bold text-gray-800 mb-4 pb-2 border-b-2 border-blue-500">Summary</h2>
                                    <p class="text-gray-700 leading-relaxed contenteditable" contenteditable="true">Highly organized and results-oriented Project Manager with 7 years of experience in leading complex projects from conception to completion. Adept at cross-functional team leadership, risk management, and stakeholder communication to ensure on-time and within-budget delivery.</p>
                                </div>
                                <div id="experience-section" data-section-type="main" class="mb-8">
                                    <h2 class="text-3xl font-bold text-gray-800 mb-4 pb-2 border-b-2 border-blue-500">Work Experience</h2>
                                    <div class="experience-entry-container">
                                        <div class="experience-entry mb-6">
                                            <h3 class="text-xl font-semibold text-gray-800 contenteditable" contenteditable="true">Senior Project Manager | Global Tech Solutions</h3>
                                            <p class="text-gray-600 text-sm mb-2 contenteditable" contenteditable="true">August 2019 – Present | Portland, OR</p>
                                            <ul class="list-disc list-inside text-gray-700 ml-4 space-y-1">
                                                <li class="contenteditable" contenteditable="true">Managed a portfolio of 10+ software development projects with budgets up to $2M.</li>
                                                <li class="contenteditable" contenteditable="true">Implemented agile methodologies, increasing team efficiency by 20%.</li>
                                                <li class="contenteditable" contenteditable="true">Facilitated communication between technical teams, stakeholders, and clients.</li>
                                            </ul>
                                        </div>
                                        <div class="experience-entry mb-6">
                                            <h3 class="text-xl font-semibold text-gray-800 contenteditable" contenteditable="true">Project Coordinator | Innovative Software Co.</h3>
                                            <p class="text-gray-600 text-sm mb-2 contenteditable" contenteditable="true">July 2016 – July 2019 | Seattle, WA</p>
                                            <ul class="list-disc list-inside text-gray-700 ml-4 space-y-1">
                                                <li class="contenteditable" contenteditable="true">Supported senior project managers in project planning and execution.</li>
                                                <li class="contenteditable" contenteditable="true">Tracked project milestones and prepared progress reports.</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div id="education-section" data-section-type="main" class="mb-8">
                                    <h2 class="text-3xl font-bold text-gray-800 mb-4 pb-2 border-b-2 border-blue-500">Education</h2>
                                    <div class="education-entry-container">
                                        <div class="education-entry mb-4">
                                            <h3 class="text-xl font-semibold text-gray-800 contenteditable" contenteditable="true">Master of Business Administration | University of Oregon</h3>
                                            <p class="text-gray-600 text-sm contenteditable" contenteditable="true">Graduated May 2016</p>
                                        </div>
                                        <div class="education-entry">
                                            <h3 class="text-xl font-semibold text-gray-800 contenteditable" contenteditable="true">Bachelor of Science in Computer Science | Oregon State University</h3>
                                            <p class="text-gray-600 text-sm contenteditable" contenteditable="true">Graduated May 2014</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="sidebar-column">
                                <div id="skills-section" data-section-type="sidebar" class="mb-8">
                                    <h2 class="text-3xl font-bold text-gray-800 mb-4 pb-2 border-b-2 border-blue-500">Skills</h2>
                                    <ul class="list-disc list-inside text-gray-700 space-y-1">
                                        <li class="contenteditable" contenteditable="true">Project Management (PMP)</li>
                                        <li class="contenteditable" contenteditable="true">Agile & Scrum</li>
                                        <li class="contenteditable" contenteditable="true">Risk Management</li>
                                        <li class="contenteditable" contenteditable="true">Jira, Asana, Trello</li>
                                        <li class="contenteditable" contenteditable="true">Budgeting</li>
                                        <li class="contenteditable" contenteditable="true">Stakeholder Communication</li>
                                    </ul>
                                </div>
                                <div id="languages-section" data-section-type="sidebar" class="mb-8">
                                    <h2 class="text-3xl font-bold text-gray-800 mb-4 pb-2 border-b-2 border-blue-500">Languages</h2>
                                    <p class="text-gray-700 contenteditable" contenteditable="true">English (Native), German (Conversational)</p>
                                </div>
                                <div id="certificates-section" data-section-type="sidebar" class="mb-8">
                                    <h2 class="text-3xl font-bold text-gray-800 mb-4 pb-2 border-b-2 border-blue-500">Certificates</h2>
                                    <p class="text-gray-700 contenteditable" contenteditable="true">Project Management Professional (PMP)</p>
                                    <p class="text-gray-700 contenteditable" contenteditable="true">Certified Scrum Master (CSM)</p>
                                </div>
                                <div id="awards-section" data-section-type="sidebar" class="mb-8">
                                    <h2 class="text-3xl font-bold text-gray-800 mb-4 pb-2 border-b-2 border-blue-500">Awards</h2>
                                    <p class="text-gray-700 contenteditable" contenteditable="true">Employee of the Quarter (2020)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `,
                '7': `
                    <div class="p-8 bg-gray-100 rounded-lg shadow-inner" id="${shufflableContainerId}">
                        <div class="flex justify-between items-center mb-6 header-block">
                            <div>
                                <h2 class="text-4xl font-extrabold text-indigo-700 contenteditable" contenteditable="true">LISA WONG</h2>
                                <p class="text-xl text-indigo-500 font-light contenteditable" contenteditable="true">Data Scientist</p>
                            </div>
                            <div class="text-sm text-right text-gray-600">
                                <p class="contenteditable" contenteditable="true">lisa.wong@email.com</p>
                                <p class="contenteditable" contenteditable="true">555-555-5555</p>
                                <p class="contenteditable" contenteditable="true">github.com/lisawong</p>
                            </div>
                        </div>
                        <div id="summary-section" data-section-type="main" class="py-4 border-b-2 border-indigo-400">
                            <h3 class="text-2xl font-bold text-indigo-700 mb-2">Summary</h3>
                            <p class="text-gray-700 leading-relaxed contenteditable" contenteditable="true">Data Scientist with 4 years of experience specializing in machine learning and predictive modeling. Proficient in Python, SQL, and various data analysis tools. Proven ability to translate complex datasets into actionable business insights.</p>
                        </div>
                        <div id="experience-section" data-section-type="main" class="py-4 border-b-2 border-indigo-400">
                            <h3 class="text-2xl font-bold text-indigo-700 mb-2">Work Experience</h3>
                            <div class="experience-entry-container space-y-4">
                                <div class="experience-entry">
                                    <h4 class="text-xl font-semibold text-gray-800 contenteditable" contenteditable="true">Senior Data Analyst | TechCorp</h4>
                                    <p class="text-sm text-gray-600 contenteditable" contenteditable="true">June 2021 – Present | San Diego, CA</p>
                                    <ul class="list-disc list-inside text-gray-700 ml-4">
                                        <li class="contenteditable" contenteditable="true">Developed and implemented machine learning models that improved customer churn prediction by 15%.</li>
                                        <li class="contenteditable" contenteditable="true">Used Python and SQL to clean and analyze large datasets, providing key insights to the product team.</li>
                                        <li class="contenteditable" contenteditable="true">Collaborated with engineers to deploy models into production environments.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div id="skills-section" data-section-type="main" class="py-4 border-b-2 border-indigo-400">
                            <h3 class="text-2xl font-bold text-indigo-700 mb-2">Skills</h3>
                            <div class="flex flex-wrap gap-2 text-sm text-white">
                                <span class="bg-indigo-600 px-3 py-1 rounded-full contenteditable" contenteditable="true">Python</span>
                                <span class="bg-indigo-600 px-3 py-1 rounded-full contenteditable" contenteditable="true">SQL</span>
                                <span class="bg-indigo-600 px-3 py-1 rounded-full contenteditable" contenteditable="true">Machine Learning</span>
                                <span class="bg-indigo-600 px-3 py-1 rounded-full contenteditable" contenteditable="true">TensorFlow</span>
                                <span class="bg-indigo-600 px-3 py-1 rounded-full contenteditable" contenteditable="true">Tableau</span>
                            </div>
                        </div>
                        <div id="education-section" data-section-type="main" class="py-4 border-b-2 border-indigo-400">
                            <h3 class="text-2xl font-bold text-indigo-700 mb-2">Education</h3>
                            <div class="education-entry-container space-y-2">
                                <div class="education-entry">
                                    <h4 class="font-semibold text-lg contenteditable" contenteditable="true">Master of Science in Data Science | University of California, San Diego</h4>
                                    <p class="text-gray-600 text-sm contenteditable" contenteditable="true">Graduated May 2020</p>
                                </div>
                                <div class="education-entry">
                                    <h4 class="font-semibold text-lg contenteditable" contenteditable="true">Bachelor of Science in Computer Engineering | University of Southern California</h4>
                                    <p class="text-gray-600 text-sm contenteditable" contenteditable="true">Graduated May 2018</p>
                                </div>
                            </div>
                        </div>
                        <div id="awards-section" data-section-type="main" class="py-4 border-b-2 border-indigo-400">
                            <h3 class="text-2xl font-bold text-indigo-700 mb-2">Awards</h3>
                            <p class="text-gray-700 contenteditable" contenteditable="true">Best Data Project Award (2022)</p>
                        </div>
                    </div>
                `
            };

            let currentTemplateId = '1';
            let sortableInstance;
            let lastSelectedFontSize = '16px'; // New state variable

            function applyTemplate(templateId) {
                const templateHtml = templates[templateId];
                if (!templateHtml) {
                    console.error(`Error: Template with ID "${templateId}" not found.`);
                    return;
                }

                // Destroy existing Sortable instance to prevent conflicts
                if (sortableInstance) {
                    sortableInstance.destroy();
                }

                // Temporarily store dynamic sections (Custom and additional Education entries)
                const oldCustomSections = Array.from(document.querySelectorAll('[id^="custom-section-"]'));
                const oldEducationEntries = Array.from(document.querySelectorAll('#education-section .education-entry-container .education-entry:not(:first-child)'));
                
                // Clear current resume content
                resume.innerHTML = templateHtml;
                currentTemplateId = templateId;
                
                // Re-append dynamic sections and entries to the new template's structure
                const newEducationContainer = resume.querySelector('#education-section .education-entry-container');
                const sortContainer = getSortableContainer();
                
                oldCustomSections.forEach(el => sortContainer.appendChild(el));
                if (newEducationContainer) {
                    oldEducationEntries.forEach(el => newEducationContainer.appendChild(el));
                }
                
                setupDynamicBehavior();
            }

            function setupDynamicBehavior() {
                addCustomBtn.removeEventListener('click', addCustomSectionHandler);
                addCustomBtn.addEventListener('click', addCustomSectionHandler);
                addEducationBtn.removeEventListener('click', addEducationEntryHandler);
                addEducationBtn.addEventListener('click', addEducationEntryHandler);
                
                setupDownloadButton();
                setupContentEditable();
                generateManageSectionsControls();
                setupSortable(); 
            }

            function generateManageSectionsControls() {
                const manageContainer = document.getElementById('manage-sections-container');
                if (!manageContainer) return;
                manageContainer.innerHTML = '';
                
                const existingSections = resume.querySelectorAll('[id$="-section"], [id^="custom-section-"]');

                existingSections.forEach(section => {
                    const sectionId = section.id;
                    // Get the section name from the HTML, default to a generated name
                    const sectionName = section.querySelector('h2, h3, h4, h5, h6')?.textContent || section.id.split('-')[0].charAt(0).toUpperCase() + section.id.split('-')[0].slice(1);
                    
                    const controlDiv = document.createElement('div');
                    controlDiv.setAttribute('data-section', sectionId);
                    
                    // Drag handle for sidebar controls
                    const dragHandle = `<span class="cursor-grab text-gray-500">&#x2630;</span>`;
                    
                    controlDiv.classList.add('flex', 'justify-between', 'items-center', 'space-y-3', 'py-1');
                    controlDiv.innerHTML = `
                        <div class="flex items-center space-x-2">
                            ${dragHandle}
                            <span>${sectionName}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" data-section="${sectionId}" class="toggle-section" ${section.style.display !== 'none' ? 'checked' : ''}>
                            ${section.id.startsWith('custom-') ? `<button class="text-red-500 ml-2 remove-custom">X</button>` : ''}
                        </div>
                    `;
                    manageContainer.appendChild(controlDiv);
                });
                setupToggleSections();
            }

            function clearAllDynamicElements() {
                const customContainer = document.getElementById('custom-sections-container');
                if (customContainer) {
                    customContainer.innerHTML = '';
                }
                document.querySelectorAll('[id^="custom-section-"]').forEach(el => el.remove());
                
                document.querySelectorAll('.education-entry-container').forEach(container => {
                    const firstEntry = container.querySelector('.education-entry');
                    if (firstEntry) {
                        Array.from(container.children).forEach((entry, index) => {
                            if (index > 0) entry.remove();
                        });
                    }
                });
            }
            
            function setupDownloadButton() {
                let oldDownloadBtn = document.getElementById('download-btn');
                if (oldDownloadBtn) {
                    oldDownloadBtn.remove();
                }
                
                const newDownloadBtn = document.createElement('button');
                newDownloadBtn.id = 'download-btn';
                newDownloadBtn.className = 'bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 mt-6';
                newDownloadBtn.textContent = 'Download PDF';
                newDownloadBtn.addEventListener('click', handleDownload);
                resume.appendChild(newDownloadBtn);
            }

            function setupContentEditable() {
                document.querySelectorAll('#resume-preview [contenteditable="true"]').forEach(el => {
                    el.removeEventListener('focusout', handleContentEdit);
                    el.addEventListener('focusout', handleContentEdit);
                });
            }

            function setupToggleSections() {
                document.querySelectorAll('.toggle-section').forEach(toggle => {
                    toggle.removeEventListener('change', handleToggleChange);
                    toggle.addEventListener('change', handleToggleChange);
                    const section = document.getElementById(toggle.dataset.section);
                    if (section) section.style.display = toggle.checked ? '' : 'none';
                });
            }

            function handleToggleChange(e) {
                const section = document.getElementById(e.target.dataset.section);
                if (section) section.style.display = e.target.checked ? '' : 'none';
            }

            function handleDownload() {
                resume.style.transform = 'scale(1)';
                html2pdf().from(resume).set({
                    margin: [0.2, 0.2, 0.2, 0.2],
                    filename: 'resume.pdf',
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2 },
                    jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
                }).save().finally(() => {
                    resume.style.transform = 'scale(0.9)';
                });
            }

            function handleContentEdit(e) {
                console.log('Content changed:', e.target.id || e.target.className, e.target.textContent);
            }

            function setupSortable() {
                const mainContainer = getSortableContainer();
                const sidebarContainer = resume.querySelector('.sidebar-column');

                if (!mainContainer) {
                    console.error("Could not find a valid sortable container in the current template.");
                    return;
                }
                
                // Initialize Sortable on the main content column
                sortableInstance = Sortable.create(mainContainer, {
                    animation: 150,
                    handle: 'h2, h3, h4', // Allow dragging on headers
                    filter: '.header-block', // Do not drag the header block
                    draggable: '[id$="-section"], [id^="custom-section-"]', // Only drag main and custom sections
                    onEnd: function (evt) {
                        syncSidebarOrder();
                    }
                });

                // Set up the sidebar sorting controls
                Sortable.create(manageSectionsContainer, {
                    animation: 150,
                    handle: '.cursor-grab',
                    onEnd: function (evt) {
                        const newOrder = Array.from(manageSectionsContainer.children).map(el => el.dataset.section);
                        reorderResumeSections(newOrder);
                    }
                });
            }
            
            function getSortableContainer() {
                const mainContentColumn = resume.querySelector('.main-content-column');
                if (mainContentColumn) {
                    return mainContentColumn;
                }
                const shufflable = document.getElementById(shufflableContainerId);
                if(shufflable) {
                    return shufflable;
                }
                return document.getElementById('resume-preview');
            }

            function reorderResumeSections(newOrder) {
                const mainContentColumn = resume.querySelector('.main-content-column');
                const targetContainer = mainContentColumn || document.getElementById(shufflableContainerId) || resume;
                
                const mainSections = newOrder.map(id => document.getElementById(id)).filter(el => el && el.dataset.sectionType === 'main');
                const sidebarSections = newOrder.map(id => document.getElementById(id)).filter(el => el && el.dataset.sectionType === 'sidebar');

                // Reorder main content sections
                mainSections.forEach(section => {
                    targetContainer.appendChild(section);
                });

                // Reorder sidebar content sections
                const sidebarContainer = resume.querySelector('.sidebar-column');
                if (sidebarContainer) {
                    sidebarSections.forEach(section => {
                        sidebarContainer.appendChild(section);
                    });
                }
                
                // Keep the download button at the end
                const downloadBtn = document.getElementById('download-btn');
                if (downloadBtn) {
                    resume.appendChild(downloadBtn);
                }

                syncSidebarOrder();
            }
            
            function syncSidebarOrder() {
                const mainContainer = getSortableContainer();
                const sidebarContainer = resume.querySelector('.sidebar-column');

                const currentResumeSections = [];
                if (mainContainer) {
                    Array.from(mainContainer.children).forEach(el => {
                        if (el.id && (el.id.endsWith('-section') || el.id.startsWith('custom-section-'))) {
                            currentResumeSections.push(el.id);
                        }
                    });
                }
                if (sidebarContainer) {
                    Array.from(sidebarContainer.children).forEach(el => {
                        if (el.id && el.id.endsWith('-section')) {
                            currentResumeSections.push(el.id);
                        }
                    });
                }

                const manageSections = Array.from(manageSectionsContainer.children);
                
                currentResumeSections.forEach(id => {
                    const control = manageSections.find(el => el.dataset.section === id);
                    if (control) {
                        manageSectionsContainer.appendChild(control);
                    }
                });
            }
            
            function createCustomSectionHTML() {
                const customSection = document.createElement('div');
                customSection.id = `custom-section-${++customCount}`;
                customSection.dataset.sectionType = 'main'; // Custom sections go in the main column
                // Use a generic class for custom sections for consistent styling across templates
                customSection.classList.add('custom-section');
                customSection.innerHTML = `
                    <h3 class="font-semibold text-lg contenteditable" contenteditable="true" style="font-size: ${lastSelectedFontSize};">Custom Section ${customCount}</h3>
                    <p class="text-gray-600 contenteditable" contenteditable="true" style="font-size: ${lastSelectedFontSize};">Write your content here...</p>
                `;

                const removeBtn = document.createElement('button');
                removeBtn.classList.add('remove-custom', 'absolute', 'top-2', 'right-2', 'text-red-500', 'hover:text-red-700', 'text-sm');
                removeBtn.textContent = 'X';
                customSection.prepend(removeBtn);
                
                removeBtn.addEventListener('click', (e) => {
                    e.target.closest('[id^="custom-section-"]').remove();
                    document.querySelector(`[data-section="${customSection.id}"]`)?.remove();
                    syncSidebarOrder();
                });
                
                return customSection;
            }

            function addCustomSectionHandler() {
                const newCustomSection = createCustomSectionHTML();
                const sortContainer = getSortableContainer();
                
                if (sortContainer) {
                    sortContainer.appendChild(newCustomSection);
                } else {
                    resume.appendChild(newCustomSection);
                }
                
                const sectionId = newCustomSection.id;
                const sectionName = `Custom Section ${customCount}`;
                
                const sidebarDiv = document.createElement('div');
                sidebarDiv.classList.add('flex', 'justify-between', 'items-center', 'space-y-3', 'py-1');
                sidebarDiv.dataset.section = sectionId;
                
                const dragHandle = `<span class="cursor-grab text-gray-500">&#x2630;</span>`;
                
                sidebarDiv.innerHTML = `
                    <div class="flex items-center space-x-2">
                        ${dragHandle}
                        <span>${sectionName}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" data-section="${sectionId}" class="toggle-section" checked>
                        <button class="text-red-500 ml-2 remove-custom">X</button>
                    </div>
                `;
                manageSectionsContainer.appendChild(sidebarDiv);
                
                sidebarDiv.querySelector('.toggle-section').addEventListener('change', handleToggleChange);
                const removeBtn = sidebarDiv.querySelector('.remove-custom');
                removeBtn.addEventListener('click', (e) => {
                    document.getElementById(sectionId)?.remove();
                    e.target.closest('[data-section]').remove();
                    syncSidebarOrder();
                });
                
                setupContentEditable();
                syncSidebarOrder();
            }

            function cloneEducationEntryHTML() {
                const tempResume = document.createElement('div');
                tempResume.innerHTML = templates[currentTemplateId];
                const originalEntry = tempResume.querySelector('.education-entry-container .education-entry');
                
                if (originalEntry) {
                    const clonedEntry = originalEntry.cloneNode(true);
                    const removeBtn = clonedEntry.querySelector('.remove-education');
                    if (removeBtn) removeBtn.remove();
                    
                    const newRemoveBtn = document.createElement('button');
                    newRemoveBtn.classList.add('remove-education', 'absolute', 'top-0', 'right-0', 'text-red-500', 'hover:text-red-700');
                    newRemoveBtn.textContent = 'X';
                    newRemoveBtn.addEventListener('click', (e) => {
                        e.target.closest('.education-entry').remove();
                    });
                    clonedEntry.style.position = 'relative';
                    clonedEntry.prepend(newRemoveBtn);

                    clonedEntry.querySelectorAll('[contenteditable="true"]').forEach(el => {
                        el.textContent = el.tagName === 'H4' ? 'New School | Location' : el.className.includes('text-sm') ? 'Field of Study | Graduation Date' : 'Details about educational background.';
                        // Apply the last used font size
                        el.style.fontSize = lastSelectedFontSize;
                    });

                    return clonedEntry;
                }
                return null;
            }

            function addEducationEntryHandler() {
                const educationContainer = document.getElementById('education-section');
                if (!educationContainer) {
                    console.warn("Education section not found in the current template.");
                    return;
                }

                let targetContainer = educationContainer.querySelector('.education-entry-container');
                if (!targetContainer) {
                    targetContainer = document.createElement('div');
                    targetContainer.classList.add('education-entry-container');
                    educationContainer.appendChild(targetContainer);
                }
                
                const newEducationEntry = cloneEducationEntryHTML();
                if (!newEducationEntry) {
                     console.warn("Could not clone education entry from template.");
                     return;
                }
                
                targetContainer.appendChild(newEducationEntry);

                newEducationEntry.querySelectorAll('[contenteditable="true"]').forEach(el => {
                    el.addEventListener('focusout', handleContentEdit);
                });
                const removeBtn = newEducationEntry.querySelector('.remove-education');
                if (removeBtn) {
                    removeBtn.addEventListener('click', (e) => {
                        e.target.closest('.education-entry').remove();
                    });
                }
            }
            
            function handleFontSelection(font) {
                document.body.style.fontFamily = font;
            }

            function applyFontSizeToSelection(size) {
                const selection = window.getSelection();
                if (!selection.rangeCount) return;

                const range = selection.getRangeAt(0);
                const selectedText = range.extractContents();

                const span = document.createElement('span');
                span.style.fontSize = size;
                span.appendChild(selectedText);
                range.insertNode(span);
                
                // Clear the selection
                selection.removeAllRanges();
                
                // Update the last used font size
                lastSelectedFontSize = size;
            }

            templatesBtn.addEventListener('click', () => templateModal.classList.add('show'));
            closeModal.addEventListener('click', () => templateModal.classList.remove('show'));

            fontBtn.addEventListener('click', () => fontModal.classList.add('show'));
            closeFontModal.addEventListener('click', () => fontModal.classList.remove('show'));

            fontSizetBtn.addEventListener('click', () => fontSizeModal.classList.add('show'));
            closeFontSizeModal.addEventListener('click', () => fontSizeModal.classList.remove('show'));

            document.querySelectorAll('.font-size-select-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const size = e.target.dataset.fontSize;
                    applyFontSizeToSelection(size);
                    fontSizeModal.classList.remove('show');
                });
            });

            document.querySelectorAll('.font-select-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    handleFontSelection(btn.dataset.font);
                    fontModal.classList.remove('show');
                });
            });
            
            manageBtn.addEventListener('click', () => sidebar.classList.toggle('show'));
            dashboardBtn.addEventListener('click', () => window.location.href = 'dashboard_main.php');
            
            document.querySelectorAll('.template-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const templateId = btn.dataset.template;
                    applyTemplate(templateId);
                    templateModal.classList.remove('show');
                });
            });
            
            applyTemplate('1');
        });
    </script>
</body>
</html>