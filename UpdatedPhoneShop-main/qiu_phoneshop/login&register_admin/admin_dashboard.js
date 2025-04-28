Orders.forEach((order) => {
    const tr = document.createElement('tr');
    const trContent = `
        <td>${order.productName}</td>
        <td>${order.productNumber}</td>
        <td>${order.customer}</td>
        <td>${order.price}</td>
        <td>${order.status}</td>
        <td>${order.date}</td>
    `;
    tr.innerHTML = trContent;
    document.querySelector('table tbody').appendChild(tr);
});

const sidebarLinks = document.querySelectorAll('.sidebar a');

sidebarLinks.forEach(link => {
    link.addEventListener('click', () => {
        // Remove "active" from all links
        sidebarLinks.forEach(l => l.classList.remove('active'));
        
        // Add "active" to the clicked link
        link.classList.add('active');
    });
});