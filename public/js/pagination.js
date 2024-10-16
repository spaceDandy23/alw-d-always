export function renderPaginatedLinks(paginationData, searchQuery = '') {
    const paginationElement = document.getElementById('pagination');
    const paginationCaption = document.getElementById('pagination_caption');
    paginationElement.innerHTML = '';

    if (paginationData.prev_page_url) {
        const prevButton = `<li class="page-item"><a class="page-link" href="#" onclick="loadPage('${paginationData.prev_page_url}&search=${encodeURIComponent(searchQuery)}')">Previous</a></li>`;
        paginationElement.innerHTML += prevButton;
    }

    for (let i = 1; i <= paginationData.last_page; i++) {
        const activeClass = (paginationData.current_page === i) ? 'active' : '';
        const pageButton = `<li class="page-item ${activeClass}"><a class="page-link" href="#" onclick="loadPage('${paginationData.path}?page=${i}&search=${encodeURIComponent(searchQuery)}')">${i}</a></li>`;
        paginationElement.innerHTML += pageButton;
    }

    if (paginationData.next_page_url) {
        const nextButton = `<li class="page-item"><a class="page-link" href="#" onclick="loadPage('${paginationData.next_page_url}&search=${encodeURIComponent(searchQuery)}')">Next</a></li>`;
        paginationElement.innerHTML += nextButton;
    }
    paginationCaption.innerHTML = `Showing <span class="fw-semibold">${paginationData.from}</span>
                                        to <span class="fw-semibold">${paginationData.to}</span>
                                        of <span class="fw-semibold">${paginationData.total}</span>`;
}



