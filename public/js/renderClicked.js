export function renderClicked(students){


    document.querySelectorAll('.student').forEach((value) => {
        value.addEventListener('click', function(){
            let student = students[parseInt(this.getAttribute('data-key'),10)];

            let studentFullName = student.name.split(',');
            
            

            document.getElementById('student_id').value = student.id;
            document.getElementById('first_name').value = studentFullName[1];
            document.getElementById('last_name').value = studentFullName[0];
            document.getElementById('section').value = student.section.grade, "-", student.section.section;

        });

    });

}
