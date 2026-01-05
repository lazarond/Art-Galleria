
// Confirm Passord Function
document.addEventListener('DOMContentLoaded', function () {

    const form = document.querySelector('.register-form');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const errorContainer = document.createElement('div');

    errorContainer.classList.add('error-container');
    form.insertBefore(errorContainer, form.querySelector('button'));

    form.addEventListener('submit', function (event) {
        errorContainer.innerHTML = ''; 

        let errors = [];

        // Check if passwords match
        if (password.value !== confirmPassword.value) {
            errors.push("Passwords do not match.");
        }

        // Display errors 
        if (errors.length > 0) {

            event.preventDefault(); // Prevent form submission
            errors.forEach(error => {
                const errorElement = document.createElement('p');
                errorElement.classList.add('error');
                errorElement.textContent = error;
                errorContainer.appendChild(errorElement);

            });
        }
    });
});

// Toggle Function (Buyer/Seller)

document.addEventListener('DOMContentLoaded', function () {
    // Role toggle functionality
    const roleToggle = document.getElementById('role');
    const roleText = document.getElementById('role-text');

    if (roleToggle && roleText) {
        roleToggle.addEventListener('change', function () {
            if (this.checked) {
                roleText.textContent = 'Seller';
                this.value = 'seller';
            } else {
                roleText.textContent = 'Buyer';
                this.value = 'buyer';
            }
        });
    }
});

// Confirmation Message
function confirmLogout() {
    return confirm("Are you sure you want to logout?");
}

// Place Bid Function
function placeBid(auctionId) {
    let bidAmount = prompt("Enter your bid amount:");
    if (bidAmount) {
        fetch("place_bid.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `auction_id=${auctionId}&bid_amount=${bidAmount}`
        })
        .then(response => response.text())
        .then(data => {
            alert(data);
            location.reload(); // Refresh page to show updated bids
        })
        .catch(error => console.error("Error:", error));
    }
}

// Mark notifications as read
document.addEventListener("DOMContentLoaded", function () {
    const notifications = document.querySelectorAll("#notifications li");

    notifications.forEach(notification => {
        notification.addEventListener("click", function () {
            const notificationId = this.dataset.id;

            fetch("mark_notification_read.php", {
                method: "POST",
                body: JSON.stringify({ id: notificationId }),
                headers: { "Content-Type": "application/json" }
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      this.classList.add("read"); // Change style to read
                  }
              });
        });
    });
});

// Toggling Notifications
document.addEventListener("DOMContentLoaded", function () {
    const notificationButton = document.getElementById("notificationButton");
    const notificationPanel = document.getElementById("notificationPanel");

    notificationButton.addEventListener("click", function () {
        notificationPanel.classList.toggle("active");
        notificationPanel.style.display = notificationPanel.style.display === "block" ? "none" : "block";
    });

    // Close notifications if clicking outside
    document.addEventListener("click", function (event) {
        if (!notificationButton.contains(event.target) && !notificationPanel.contains(event.target)) {
            notificationPanel.style.display = "none";
        }
    });
});

function fetchUpcomingAuctions() {
    fetch('fetch_upcoming_auctions.php') 
    .then(response => response.json())
    .then(data => {
        let container = document.querySelector('.buyer-dashboard-auction-cards');
        container.innerHTML = '';

        if (data.length > 0) {
            data.forEach(auction => {
                container.innerHTML += `
                    <div class="buyer-dashboard-auction-card">
                        <img src="uploads/${auction.image}" alt="${auction.title}">
                        <h3>${auction.title}</h3>
                        <p>Starting Bid: ₱${parseFloat(auction.starting_bid).toFixed(2)}</p>
                        <p>Starts On: ${new Date(auction.start_time).toLocaleString()}</p>
                    </div>
                `;
            });
        } else {
            container.innerHTML = '<p>No upcoming auctions at the moment.</p>';
        }
    });
}

// Update Auctions
function updateAuctions() {
    fetch('update_bids.php')
    .then(response => response.json())
    .then(data => {
        let auctionList = document.getElementById('auction-list');
        auctionList.innerHTML += `
            <div class="buyer-dashboard-auction-card" onclick="showAuctionDetails(
                    '${auction.id}',
                    '${auction.title.replace(/'/g, "\\'")}', // Escape quotes
                    '${auction.current_bid}',
                    '${auction.end_time}',
                    '${auction.image}'
                )">
                <img src="${auction.image}" alt="${auction.title}" style="width: 100%; height: auto;">
                <h3>${auction.title}</h3>
                <p>Current Bid: ${auction.current_bid}</p>
                <p><strong>Ends On:</strong> ${auction.end_time}</p>
                
                <button onclick="showAuctionDetails(
                    '${auction.id}',
                    '${auction.title.replace(/'/g, "\\'")}',
                    '${auction.current_bid}',
                    '${auction.end_time}',
                    '${auction.image}'
                )">View Details</button>
            </div>
        `;
    })
    .catch(error => console.error('Error updating auctions:', error));
}

// Automatically refresh every 5 seconds
setInterval(updateAuctions, 5000);
updateAuctions(); 


// Place bid function
function placeBid(auctionId) {
    let bidAmount = document.getElementById("bidAmount").value;
    
    if (!bidAmount || isNaN(bidAmount) || parseFloat(bidAmount) <= 0) {
        alert("Please enter a valid bid amount.");
        return;
    }

    fetch("place_bid.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "auction_id=" + auctionId + "&bid_amount=" + encodeURIComponent(bidAmount)
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
        closeModal(); 
    })
    .catch(error => console.error("Error:", error));
}

// Display auction details
function showAuctionDetails(id, title, description, currentBid, endTime, imageSrc) {
    document.getElementById("modalTitle").textContent = title;
    document.getElementById("modalDescription").textContent = description;
    document.getElementById("modalCurrentBid").textContent = currentBid;
    document.getElementById("modalEndTime").textContent = endTime;
    document.getElementById("modalImage").src = imageSrc;

    // Reset bid input field
    document.getElementById("bidAmount").value = "";

    // Set the Place Bid button to call placeBid function with the correct auctionId
    document.getElementById("placeBidButton").onclick = function() {
        placeBid(id);
    };

    document.getElementById("auctionModal").style.display = "block";
}

function closeModal() {
    document.getElementById("auctionModal").style.display = "none";
}

window.onclick = function(event) {
    if (event.target === document.getElementById("auctionModal")) {
        closeModal();
    }
};
