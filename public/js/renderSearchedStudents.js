export function renderSearchedStudents(students){


    console.log(students);


    let studentData = ``;
    Object.values(students).forEach((values, key) => {
                studentData += `
                                <tr data-key="${key}" class="student">
                                <td>${values.name}</td>
                                <td>${values.section.grade}-${values.section.section}</td>
                                </tr>`;
            });
    document.getElementById('students_searched').innerHTML = studentData;
}

