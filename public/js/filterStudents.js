import { renderSearchedStudents } from './renderSearchedStudents.js';
import { renderPaginatedLinks } from './pagination.js';

document.addEventListener('DOMContentLoaded', function() {

    let routesAndToken = {
        search: document.getElementById('app').getAttribute('data-filter-url'),
        csrfToken: document.getElementById('app').getAttribute('data-csrf-token')
    };
    let searchStudentName = '';
    let filterGrade = '';
    let filterSection = '';
    let students = {}; // Store selected students

    document.getElementById('filter_button').addEventListener('click', () => {
        searchStudentName = document.getElementById('search_student').value;
        filterGrade = document.getElementById('filter_grade').value;
        filterSection = document.getElementById('filter_section').value;

        fetch(routesAndToken.search, {
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': routesAndToken.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                name: searchStudentName,
                grade: filterGrade,
                section: filterSection,
            })
        })
        .then((response) => response.json())
        .then((data) => {
            renderSearchedStudents(data.results.data);
            renderPaginatedLinks(data.results, searchStudentName);
            setStudentClickEvent(data.results.data);
        })
        .catch((error) => console.error(error));
    });

    window.loadPage = function(url) {
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': routesAndToken.csrfToken
            },
            body: JSON.stringify({ 
                search: searchStudentName,
                fromRegister: true
            })
        })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                renderSearchedStudents(data.results.data);
                renderPaginatedLinks(data.results, searchStudentName);
                setStudentClickEvent(data.results.data);
            }
        })
        .catch((error) => console.error(error));
    };

    function setStudentClickEvent(data) {
        document.querySelectorAll('.student').forEach((element) => {
            element.addEventListener('click', function() {
                const studentKey = parseInt(this.getAttribute('data-key'), 10);
                students[studentKey] = data[studentKey];
                renderClickedStudents();
            });
        });
    }

    function renderClickedStudents() {
        let studentData = '';
        Object.values(students).forEach((student, key) => {
            studentData += `
                <tr data-key="${key}" class="students-clicked">
                    <td>${student.name}</td>
                    <td>${student.grade}</td>
                    <td>${student.section}</td>
                </tr>`;
        });
        const clickedContainer = document.getElementById('students_clicked');
        clickedContainer.innerHTML = studentData;

        clickedContainer.querySelectorAll('.students-clicked').forEach((row) => {
            row.addEventListener('click', function() {
                const studentKey = parseInt(this.getAttribute('data-key'), 10);
                delete students[studentKey];
                renderClickedStudents();
            });
        });
    }
});