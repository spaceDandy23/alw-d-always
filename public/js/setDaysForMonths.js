document.addEventListener('DOMContentLoaded', function () {
    const startMonthSelect = document.getElementById('start_month');
    const startDaySelect = document.getElementById('start_day');
    const endMonthSelect = document.getElementById('end_month');
    const endDaySelect = document.getElementById('end_day');

    function getDaysInMonth(month, year) {
        return new Date(year, month, 0).getDate();
    }

    console.log('aguy');
    

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

    startMonthSelect.addEventListener('change', function () {
        updateDays(startMonthSelect, startDaySelect);
    });

    endMonthSelect.addEventListener('change', function () {
        updateDays(endMonthSelect, endDaySelect);

    });
});
