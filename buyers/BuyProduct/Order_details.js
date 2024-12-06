// Sample data for the order (Replace this with your dynamic data)
const orderDetails = [
    { productName: "Cotton T-Shirt", unitPrice: 180000, quantity: 2 },
    { productName: "Classic Oxford Shirt", unitPrice: 280000, quantity: 1 },
    { productName: "Chino Pants", unitPrice: 300000, quantity: 3 },
];

// Function to populate order details
function loadOrderDetails() {
    const tableBody = document.getElementById("order-details-body");
    const totalAmountElement = document.getElementById("total-amount");
    let totalAmount = 0;

    orderDetails.forEach(item => {
        const row = document.createElement("tr");

        // Create table cells
        const productNameCell = document.createElement("td");
        productNameCell.textContent = item.productName;

        const unitPriceCell = document.createElement("td");
        unitPriceCell.textContent = item.unitPrice.toLocaleString();

        const quantityCell = document.createElement("td");
        quantityCell.textContent = item.quantity;

        const totalPriceCell = document.createElement("td");
        const totalPrice = item.unitPrice * item.quantity;
        totalPriceCell.textContent = totalPrice.toLocaleString();

        // Append cells to the row
        row.appendChild(productNameCell);
        row.appendChild(unitPriceCell);
        row.appendChild(quantityCell);
        row.appendChild(totalPriceCell);

        // Add the row to the table
        tableBody.appendChild(row);

        // Update total amount
        totalAmount += totalPrice;
    });

    // Update total amount display
    totalAmountElement.textContent = totalAmount.toLocaleString();
}

// Function to go back to the previous page
function goBack() {
    window.history.back();
}

// Load the order details when the page loads
window.onload = loadOrderDetails;
