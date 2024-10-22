document.addEventListener('DOMContentLoaded', function () {

    const monthSelects = document.querySelectorAll('select[id^="month"]');
    const daySelects = document.querySelectorAll('select[id^="day"]');


    function getDaysInMonth(month, year) {
        return new Date(year, month, 0).getDate();
    }

    function updateDays(monthSelect, daySelect) {
        const month = parseInt(monthSelect.value);


        const year = new Date().getFullYear();
        daySelect.innerHTML = '<option value="">-- Select Day --</option>';
        
        if (!isNaN(month)) {
            const daysInMonth = getDaysInMonth(month, year);
            for (let day = 1; day <= daysInMonth; day++) {
                const option = document.createElement('option');
                option.value = day;
                option.textContent = day;
                daySelect.appendChild(option);
            }
        }

        
    }

    monthSelects.forEach((monthSelect, index) => {
        const daySelect = daySelects[index]; 
        monthSelect.addEventListener('change', function () {
            updateDays(monthSelect, daySelect);
        });
    });




});