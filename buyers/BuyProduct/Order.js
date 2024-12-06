// Sample data for orders (Replace this with data fetched from the database)
const orders = [
    { orderId: 101, orderDate: "2023-12-06", totalPrice: 720000 },
    { orderId: 102, orderDate: "2023-12-05", totalPrice: 450000 },
    { orderId: 103, orderDate: "2023-12-04", totalPrice: 900000 },
];

// Load orders dynamically
function loadOrders() {
    const tableBody = document.getElementById("order-body");

    orders.forEach(order => {
        const row = document.createElement("tr");

        // Create table cells
        const orderIdCell = document.createElement("td");
        orderIdCell.textContent = order.orderId;

        const orderDateCell = document.createElement("td");
        orderDateCell.textContent = order.orderDate;

        const totalPriceCell = document.createElement("td");
        totalPriceCell.textContent = order.totalPrice.toLocaleString();

        const actionsCell = document.createElement("td");
        actionsCell.innerHTML = `
            <button class="view-button" onclick="viewDetails(${order.orderId})">View Details</button>
            <button class="delete-button" onclick="deleteOrder(${order.orderId})">Delete</button>
        `;

        // Append cells to the row
        row.appendChild(orderIdCell);
        row.appendChild(orderDateCell);
        row.appendChild(totalPriceCell);
        row.appendChild(actionsCell);

        // Append row to the table body
        tableBody.appendChild(row);
    });
}

// Function to view order details
function viewDetails(orderId) {
    alert(`Viewing details for Order ID: ${orderId}`);
    // Redirect to order details page
    window.location.href = `Order_details.html?orderId=${orderId}`;
}

// Function to delete an order
function deleteOrder(orderId) {
    const confirmation = confirm(`Are you sure you want to delete Order ID: ${orderId}?`);
    if (confirmation) {
        alert(`Order ID: ${orderId} deleted successfully.`);
        // Remove the order from the table (or send a delete request to the server)
        window.location.reload();
    }
}

// Function to navigate to payment or shipping pages
function navigateTo(page) {
    window.location.href = page;
}

// Load orders when the page loads
window.onload = loadOrders;
