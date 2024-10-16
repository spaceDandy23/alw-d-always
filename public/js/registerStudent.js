import { renderSearchedStudents } from './renderSearchedStudents.js';
import { renderClicked } from './renderClicked.js';
import { renderPaginatedLinks } from './pagination.js';





document.addEventListener('DOMContentLoaded', function() {
    let form = document.getElementById('form_register');
    let routesAndToken = {
        search: document.getElementById('app').getAttribute('data-search-url'),
        csrfToken: document.getElementById('app').getAttribute('data-csrf-token')
    };
    let searchInput = '';
    form.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
        }
    });




    document.getElementById('search_button').addEventListener('click',()=>{

        searchInput = document.getElementById('search_student').value;

        fetch(routesAndToken.search, {

            method: "POST",
            headers: {
                'X-CSRF-TOKEN': routesAndToken.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({search: searchInput})


        })
        .then((response) => {
            return response.json();
        })
        .then((data)=> {
            renderSearchedStudents(data.results.data);
            renderClicked(data.results.data);
            renderPaginatedLinks(data.results, searchInput);
        });
    });

    window.loadPage = function(url) {
        fetch(url, {
            method: 'POST', 
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': routesAndToken.csrfToken
            },
            body: JSON.stringify({ search: searchInput })
        })
        .then((response) => {
            return response.json();
        })
        .then((data) => {
            if(data.success){
                console.log(data.results);
                renderSearchedStudents(data.results.data);
                renderPaginatedLinks(data.results, searchInput);
                renderClicked(data.results.data);
            }
    
        })
        .catch((error) => {
            console.error(error);
        });
    };

});