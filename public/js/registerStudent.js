import { renderSearchedStudents } from './renderSearchedStudents.js';
import { renderClicked } from './renderClicked.js';
import { renderPaginatedLinks } from './pagination.js';





document.addEventListener('DOMContentLoaded', function() {
    let form = document.getElementById('form_register');
    let routesAndToken = {
        search: document.getElementById('app').getAttribute('data-search-url'),
        csrfToken: document.getElementById('app').getAttribute('data-csrf-token')
    };
    let searchStudentName = '';
    let filterGrade = '';
    let filterSection = '';
    form.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
        }
    });




    document.getElementById('filter_button').addEventListener('click',()=>{


        let fromRegister = true;
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
                fromRegister: fromRegister,
            })


        })
        .then((response) => {
            return response.json();
        })
        .then((data)=> {
            renderSearchedStudents(data.results.data);
            renderClicked(data.results.data);
            renderPaginatedLinks(data.results, searchStudentName);
        });
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
                fromRegister: true,
             })
        })
        .then((response) => {
            return response.json();
        })
        .then((data) => {
            if(data.success){
                console.log(data.results);
                renderSearchedStudents(data.results.data);
                renderPaginatedLinks(data.results, searchStudentName);
                renderClicked(data.results.data);
            }
    
        })
        .catch((error) => {
            console.error(error);
        });
    };

});