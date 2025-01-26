import "./bootstrap";
import Alpine from "alpinejs";
import "swiper/css/bundle";
import Swiper from "swiper/bundle";

import Echo from "laravel-echo";
import Pusher from "pusher-js";

import Chart from 'chart.js/auto'
import html2pdf from 'html2pdf.js';

window.Alpine = Alpine;
window.Chart = Chart;
window.html2pdf = html2pdf;

Alpine.start();

let countDownId;

function setupAuctionEcho() {
    const placeBidButton = document.querySelector("#place-bid-btn"); // Using class selector
    if (!placeBidButton) return;
    const auctionId = window.location.pathname.split('/')[2];
    const currentUserId = document.querySelector('meta[name="user-id"]').content || null;
    if (!auctionId) return;
    initializeEcho();

    // Listen to the specific auction channel
    let echo = window.Echo.channel("auction." + auctionId);
    echo.listen(".my-event", (event) => {
        const bids = event.bids;
        let delay = 2000;
        let j = 0;
        let w = 0;
        for (let i = bids.length - 1; i >= 0; i--) {
            setTimeout(() => {
                let currentBid = bids[i];

                let formattedBid =
                    "€ " +
                    new Intl.NumberFormat("en-US").format(currentBid.value);
                // Handle the new bid (e.g., update the UI with the new bid value)
                if (
                    document.getElementById("auction-status").innerText ==
                    "Starting Bid"
                )
                    document.getElementById("auction-status").innerText =
                        "Current Bid";

                let minBidValue =
                    Number(currentBid.value) + Number(event.bidInterval);

                let formattedMinBid =
                    "€ " + new Intl.NumberFormat("en-US").format(minBidValue);

                document.getElementById("current-bid").innerText = formattedBid;

                document.getElementById(
                    "bid-text"
                ).placeholder = `${formattedMinBid} or up`;
                // Update available balance
                
                if (j == 0 && (event.surprassedBidder.length !== 0)){
                   if (currentUserId !== null && event.surprassedBidder[0].bidderId == parseInt(currentUserId,10)){
                    document.getElementById("current-available-balance-user").innerHTML = 
                    `Available: ${event.surprassedBidder[0].availableBalance} €`;
                   }  
                }

                if(currentUserId == currentBid.bidderId){
                    document.getElementById("current-available-balance-user").innerHTML = 
                    `Available: ${currentBid.availableBalance} €`;
                }

                // Update the bid history

                let bidHistoryContainer = document.querySelector(".user-bids");

                let allBids =
                    bidHistoryContainer.querySelectorAll("div.user-info");

                if (allBids.length === 3) {
                    // Remove the last bid and store it in a variable
                    let lastBid = allBids[allBids.length - 1];

                    let clonedBid = lastBid.cloneNode(true);

                    // Remove the last bid from the container
                    lastBid.remove();

                    let button = document.getElementById("show-bid-history");

                    if (!button) {
                        // Create the button
                        button = document.createElement("button");
                        button.className = "dropdown-button mt-2";
                        button.id = "show-bid-history";
                        button.textContent = "Show More Bids";

                        // Append the button to the desired container (e.g., body or another element)
                        const container = document.getElementById(
                            "bid-history-container"
                        );
                        container.parentNode.insertBefore(button, container); // Insert before the div

                        // Unhide the bid history container
                        container.classList.remove("hidden");
                        setUpToggleBidHistory();
                    }

                    let dropdownContainer = document.querySelector(
                        ".dropdown-bid-history"
                    );

                    if (
                        dropdownContainer &&
                        !dropdownContainer.classList.contains("hidden")
                    ) {
                        // Prepend the new element (with the removed bid) to the dropdown
                        clonedBid.classList.remove("user-info");

                        clonedBid.classList.add("bid-item", "p-2");

                        dropdownContainer.prepend(clonedBid);
                    }
                }

                let newBidDiv = document.createElement("div");
                newBidDiv.classList.add(
                    "user-info",
                    "flex",
                    "flex-row",
                    "justify-between",
                    "items-center"
                );
                newBidDiv.innerHTML = `
            <a href="/profile/${currentBid.bidderId}" class="username text-base sm:text-lg font-semibold">${currentBid.username}</a>
            <p class="time text-sm sm:text-base text-gray-500">${currentBid.timestamp}</p>
            <p class="amount text-sm sm:text-base font-medium text-blue-500">€ ${currentBid.value}</p>
        `;

                let allThingsInsideBids = bidHistoryContainer.children;

                if (allThingsInsideBids.length === 1) {
                    // Clear everything inside bidHistoryContainer
                    bidHistoryContainer.innerHTML = "";

                    // Add the "Top Bids" heading
                    const topBidsHeading = document.createElement("p");
                    topBidsHeading.classList.add(
                        "text-xl",
                        "sm:text-2xl",
                        "font-semibold",
                        "text-gray-700",
                        "text-start",
                        "mb-2"
                    );
                    topBidsHeading.textContent = "Top Bids";
                    bidHistoryContainer.appendChild(topBidsHeading);

                    bidHistoryContainer.appendChild(newBidDiv);
                } else {
                    bidHistoryContainer.insertBefore(
                        newBidDiv,
                        allThingsInsideBids[1]
                    );
                }
                
                j++;
            }, w * delay);
            w++;
        }
    });
}

function initializeSwiper() {
    const swiperContainer = document.querySelector("#swiper-1");
    if (!swiperContainer) return;

    new Swiper("#swiper-1", {
        effect: "coverflow",
        slidesPerView: 1,
        centeredSlides: true,
        lazyLoading: true,     
        keyboard: {
            enabled: true,
        },
        navigation: {
            nextEl: "#nav-right",
            prevEl: "#nav-left",
        },
        pagination: {
            el: ".swiper-custom-pagination",
            clickable: true,
            renderBullet: function (index, className) {
                return `
                    <div class=${className}> 
                        <span class="number">${index + 1}</span>
                        <span class="line"></span>
                    </div>`;
            },
        },
        breakpoints: {
            800: {
                spaceBetween: -30, // Minimal space
                slidesPerView: 1.5,
            },
        },
        coverflowEffect: {
            slideShadows: false, // Enable/disable shadows
            
        }
    });
}

// Add Euro Symbol Functionality
function setupEuroSymbolInput() {
    const bidTextArea = document.querySelector("#bid-text");
    if (!bidTextArea) return;

    bidTextArea.addEventListener("input", (event) => {
        const input = event.target;

        // Allow only numbers
        input.value = input.value.replace(/[^0-9]/g, "");

        // Remove leading zeros
        input.value = input.value.replace(/^0+/, "");

        // Add the Euro sign if it's not already there
        if (input.value && !input.value.includes("€")) {
            input.value = input.value.replace(/([0-9]+)/, "€ $1");
        }
    });
}

function toggleDropdownBidHistory() {
    let dropdownContent = document.querySelector(".dropdown-bid-history");

    const auctionElement = document.querySelector(".auction-name");

    const auctionId = window.location.pathname.split('/')[2];


    if (!auctionId) return;

    // Check if the dropdown content exists before toggling
    if (!dropdownContent) {
        return;
    }

    // If dropdown is already visible, no need to do anything
    if (!dropdownContent.classList.contains("hidden")) {
        dropdownContent.classList.add("hidden");
        dropdownContent.innerHTML = "";
        return;
    }

    // Show the dropdown first
    dropdownContent.classList.remove("hidden");
    // Check if more bids need to be loaded
    loadMoreBids(auctionId);
}

function loadMoreBids(auctionId) {
    let bidHistoryContainer = document.querySelector("#bid-history-container");
    // Fetch additional bids from the server using AJAX
    fetch(`/auctions/${auctionId}/more-bids`)
        .then((response) => response.json())
        .then((data) => {
            
            data.bids.forEach((bid) => {
                const bidDiv = document.createElement("div");
                bidDiv.classList.add(
                    "bid-item",
                    "flex",
                    "flex-row",
                    "justify-between",
                    "items-center",
                    "p-2"
                );
                bidDiv.innerHTML = `
                    <a href="/profile/${bid.bidderId}" class="username text-base sm:text-lg font-semibold">${bid.username}</a>
                    <p class="time text-sm sm:text-base text-gray-500">${bid.timestamp}</p>
                    <p class="amount text-sm sm:text-base font-medium text-blue-500">€ ${bid.value}</p>
                `;
                bidHistoryContainer.appendChild(bidDiv);
            });
        })
}

function setUpToggleBidHistory() {
    const dropdownButton = document.querySelector(".dropdown-button");

    // Check if the dropdown button exists before attaching the event listener
    if (!dropdownButton) {
        return;
    }

    // Attach the toggleDropdownVisibility function to the button click
    dropdownButton.addEventListener("click", function (event) {
        event.preventDefault(); // Prevent default behavior
        toggleDropdownBidHistory(); // Your custom function
    });
}

// Countdown update function
function updateCountdown() {
    const countdownElement = document.getElementById("countdown");
    if (!countdownElement) return;

    const endTime = new Date(
        countdownElement.getAttribute("data-endtime")
    ).getTime();
    const now = new Date().getTime();
    const distance = endTime - now;

    // Check if auction has ended
    if (distance <= 0) {
        document.getElementById("closes").innerHTML = "Auction Ended";
        clearInterval(countDownId); // Stop countdown
    } else {
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor(
            (distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
        );
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        countdownElement.innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;
    }
}

function place_max_bid(auctionId, userId) {
    let bidValue = document.getElementById("bid-text").value;

    bidValue = bidValue.replace(/[^0-9\.]/g, "");

    if (!bidValue || isNaN(bidValue) || parseFloat(bidValue) <= 0) {
        showPopUp("Please enter a valid input",'error');
        return;
    }

    fetch(`/auctions/${auctionId}/place-max-bid`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"), // Include CSRF token
        },
        body: JSON.stringify({ value: bidValue }),
    })
        .then((response) => response.json())
        .then((data) => {
            showPopUp(data.reply, data.success ? 'success' : 'error');
            if (data.success) {

                document.getElementById("bid-text").value = "";

                const autoBidContainer =
                document.getElementById("autoBid-container");

            if(data.isAutoBidActive){
                
                    // Create the new content with the max bid and cancel button
            autoBidContainer.innerHTML = `
            <p id="max-bid-text" class="m-0 text-xl font-bold text-green-600">
                Current Max Bid: <span class="text-2xl font-extrabold text-green-800">€${data.bid.max}</span>
            </p>
            <button id="cancel-autoBid-btn" data-auction-id="${auctionId}" data-user-id="${userId}" class="border-2 border-red-500 bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 ml-auto">
                Cancel AutoBid
            </button>
        `;
            setUpCancelAutoBid();

            if (data.noAddedBids){
                document.getElementById("current-available-balance-user").innerHTML = 
                    `Available: ${data.availableBalance} €`;
            }
        }

            } 
        })
        .catch((error) => {
            alert(error);
        });
}

function place_bid(auctionId) {
    let bidValue = document.getElementById("bid-text").value;

    // Remove the Euro symbol and any other non-numeric characters (except for the decimal point)
    bidValue = bidValue.replace(/[^0-9\.]/g, "");

    // Validate the bid value
    if (!bidValue || isNaN(bidValue) || parseFloat(bidValue) <= 0) {
        showPopUp("Please enter a valid input",'error');
        return;
    }

    // Send the bid to the server using Fetch API
    fetch(`/auctions/${auctionId}/place-bid`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"), // Include CSRF token
        },
        body: JSON.stringify({ value: bidValue }),
    })
        .then((response) => response.json())
        .then((data) => {
            showPopUp(data.reply, data.success ? 'success' : 'error');

            if (data.success) {
                // Optionally update the highest bid in the UI
                document.getElementById("bid-text").value = "";
            } 
        })
}

function setUpPlaceBid() {
    // Make sure to use the correct class or ID selector
    const placeBidButton = document.querySelector("#place-bid-btn"); // Using class selector

    if (!placeBidButton) return;
    placeBidButton.addEventListener("click", function (event) {
        event.preventDefault();
        const auctionId = window.location.pathname.split('/')[2];
        place_bid(auctionId); // Pass the auction ID dynamically
    });
}

function setUpPlaceMaxBid() {
    // Make sure to use the correct class or ID selector
    const placeBidButton = document.querySelector("#place-max-bid-btn"); // Using class selector

    if (!placeBidButton) return;
    placeBidButton.addEventListener("click", function (event) {
        event.preventDefault();
        const auctionId = window.location.pathname.split('/')[2];
        const userId = placeBidButton.getAttribute("data-user-id");
        place_max_bid(auctionId, userId); // Pass the auction ID dynamically
    });
}

function isLoggedIn() {
    const metaTag = document.querySelector('meta[name="is-logged-in"]');
    return metaTag ? metaTag.getAttribute("content") === "true" : false;
}

function checkAndRemoveAuction(auctionId) {
    fetch(`/auctions/${auctionId}/cancel`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"), // Include CSRF token
        },
    })
        .then((response) => response.json())
        .then((data) => {
            showPopUp(data.reply, data.success ? 'success' : 'error');

            if (data.success) {
             setTimeout(() => {
                    window.location.href = "/auctions";
                }, 2000);
            } 
        })
}

function setUpRemoveAuction() {
    const removeAuctionButtons = document.getElementById("cancel-auction-btn");
    if (!removeAuctionButtons) return;

    removeAuctionButtons.addEventListener("click", function (event) {
        event.preventDefault();
        const auctionId = window.location.pathname.split('/')[2];
        checkAndRemoveAuction(auctionId);
    });
}

function cancelAutoBid(auctionId) {
    fetch(`/auctions/${auctionId}/cancel-autobid`, {
        method: "DELETE",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
    })
        .then((response) => response.json())
        .then((data) => {
            showPopUp(data.reply, data.success ? 'success' : 'error');

            if (data.success) {
                // Hide the Cancel AutoBid button and show a message
               
                const autoBidContainer =
                    document.getElementById("autoBid-container");
                autoBidContainer.innerHTML = "<p id='no-current-auto-bid' >No current AutoBid set</p>"; // Clear the div and add the message
                    document.getElementById("current-available-balance-user").innerHTML = 
                        `Available: ${data.availableBalance} €`;
            } else {
                const autoBidContainer =
                    document.getElementById("autoBid-container");
                autoBidContainer.innerHTML = "<p id='no-current-auto-bid' >No current AutoBid set</p>"; // Clear the div and add the message
                
            }
        })
}

function setUpCancelAutoBid() {
    const cancelBtn = document.getElementById("cancel-autoBid-btn");
    if (cancelBtn) {
        cancelBtn.addEventListener("click", function (event) {
            event.preventDefault();
            const auctionId = window.location.pathname.split('/')[2];
            cancelAutoBid(auctionId);
        });
    }
}

function setupAuctionChat() {
    const chatBox = document.getElementById("chat-box");
    const chatForm = document.getElementById("chat-form");
    const chatInput = document.getElementById("chat-input");
    if (chatBox && chatForm && chatInput) {
        // Initialize Echo if not already initialized
        initializeEcho();
        setupLoadMoreMessages();
        // Replace `auctionId` with the dynamic auction ID
        const auctionId = window.location.pathname.split('/')[2];

        // Listen for messages on the chat channel
        const chatChannel = window.Echo.channel("auction-chat." + auctionId);

        chatChannel.listen(".message.sent", (event) => {
            const userIds = Object.values(event.userIds);
            const message = event.message;
            const userId = parseInt(
                document
                    .querySelector("#chat-container")
                    .getAttribute("data-user-id")
            );
            // this function does not exist
            if (userIds.includes(userId)) {
                const profileImage = event.userImage;
                const currentMessageDate = message.formatted_date;
                const noMessagesDiv = chatBox.querySelector(".no-messages");
                // If chatBox has no messages, add the first date separator
                if (noMessagesDiv) {
                    addDateSeparator(chatBox, currentMessageDate);

                    chatBox.removeChild(noMessagesDiv); // Remove the "no-messages" class
                } else {
                    // If chatBox already has messages, check the last message's date
                    const lastMessage = chatBox.querySelector(
                        ".my-message:last-child, .other-message:last-child"
                    );
                    if (lastMessage) {
                        const lastMessageDate =
                        lastMessage.getAttribute("data-date");

                        const date = new Date(lastMessageDate);
                        // Format the date to "d/m/Y" (day/month/year)
                        const day = date.getDate().toString().padStart(2, '0');  // Ensure two digits for day
                        const month = (date.getMonth() + 1).toString().padStart(2, '0');  // Ensure two digits for month
                        const year = date.getFullYear();  // Get the full year

                        // Combine to get the formatted date
                        const formattedDate = `${day}/${month}/${year}`;

                        if (formattedDate !== currentMessageDate) {
                            addDateSeparator(chatBox, currentMessageDate); // Add a new date separator
                        }
                    }
                }
                // Need to add the photo of the user
                const messageHours = new Date(
                    message.timestamp
                ).toLocaleTimeString([], {
                    hour: "2-digit",
                    minute: "2-digit",
                });
                const chatOtherMessage = `
    <div class="chat-other-message" data-date="${message.timestamp}">
        <div class="chat-profile-container">
            <a href="/profile/${
                message.general_user_id
            }" class="chat-profile-link">
                <img src="${profileImage}"
                     alt="User Image" 
                     class="chat-profile-image">
            </a>
            <a href="/profile/${
                message.general_user_id
            }" class="chat-user-name-link">
                <p class="chat-user-name">${message.username}</p>
            </a>
        </div>
        <div class="chat-message-info">
            <p class="chat-message-content">${message.text}</p>
            <p class="chat-message-time">${messageHours}</p>
        </div>
    </div>`;
                // Append the new message to the chatBox
                chatBox.insertAdjacentHTML("beforeend", chatOtherMessage);

                // Scroll to the bottom of the chat box
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        });

        // Handle form submission
        chatForm.addEventListener("submit", (e) => {
            e.preventDefault();

            const messageText = chatInput.value.trim();
            if (!messageText) return;


            // Create and append the content
            chatInput.disabled = true;
            const sendButton = chatForm.querySelector("button[type='submit']");

            if (sendButton) {
                sendButton.disabled = true;
                sendButton.innerHTML = `
            <span class="loading-spinner"></span>
        `;
            }

            chatInput.value = "";
            // Send the message using an AJAX request or fetch
            fetch("/send-message", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    ).content,
                },
                body: JSON.stringify({
                    auction_id: auctionId,
                    message: messageText,
                }),
            })
                .then((response) => response.json())
                .then((data) => {
                    if(!data.success){
                        showPopUp('Auction already Finished', 'error');
                        return;
                    }
                    // Extract the fields from the server response
                    const { formatted_date, text, timestamp } = data.message;

                    const currentMessageDate = formatted_date;
                    const noMessagesDiv = chatBox.querySelector(".no-messages");
                    // If chatBox has no messages, add the first date separator
                    if (noMessagesDiv) {
                        addDateSeparator(chatBox, currentMessageDate);
                        
                        chatBox.removeChild(noMessagesDiv); // Remove the "no-messages" class
                    } else {
                        // If chatBox already has messages, check the last message's date
                        const lastMessage = chatBox.querySelector(
                            ".my-message:last-child, .other-message:last-child"
                        );
                        if (lastMessage) {
                            const lastMessageDate =
                                lastMessage.getAttribute("data-date");

                                const date = new Date(lastMessageDate);
                                // Format the date to "d/m/Y" (day/month/year)
                                const day = date.getDate().toString().padStart(2, '0');  // Ensure two digits for day
                                const month = (date.getMonth() + 1).toString().padStart(2, '0');  // Ensure two digits for month
                                const year = date.getFullYear();  // Get the full year

                                // Combine to get the formatted date
                                const formattedDate = `${day}/${month}/${year}`;
                            if (formattedDate !== currentMessageDate) {
                                addDateSeparator(chatBox, currentMessageDate); // Add a new date separator
                            }
                        }
                    }

                    // Add the message to the chat box
                    const messageDiv = document.createElement("div");
                    messageDiv.classList.add("my-message");
                    messageDiv.setAttribute("data-date", data.message.timestamp); // Add the current date as a data attribute

                    // Create and append the content
                    const messageContent = document.createElement("p");
                    messageContent.classList.add("message-content");
                    messageContent.textContent = `${text}`;
                    messageDiv.appendChild(messageContent);

                    // Create and append the timestamp
                    const messageTime = document.createElement("p");
                    messageTime.classList.add("message-time");
                    messageTime.textContent = new Date(
                        timestamp
                    ).toLocaleTimeString([], {
                        hour: "2-digit",
                        minute: "2-digit",
                    });
                    messageDiv.appendChild(messageTime);

                    // Add the message to the chat box
                    chatBox.appendChild(messageDiv);

                    // Scroll to the bottom of the chat box
                    chatBox.scrollTop = chatBox.scrollHeight;
                })
                .finally(() => {
                    chatInput.disabled = false;
                    if (sendButton) {
                        sendButton.disabled = false;
                        sendButton.innerHTML = `
                    <svg viewBox="0 0 24 24" height="24" width="24" preserveAspectRatio="xMidYMid meet" class="send-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24">
                        <title>send</title>
                        <path fill="currentColor" d="M1.101,21.757L23.8,12.028L1.101,2.3l0.011,7.912l13.623,1.816L1.112,13.845L1.101,21.757z"></path>
                    </svg>
                `;
                        chatInput.focus();
                    }
                });
        });
    }
}

// Function to add the date separator
function addDateSeparator(chatBox, date) {
    const dateSeparatorDiv = document.createElement("div");
    dateSeparatorDiv.classList.add("date-separator");

    const dateSpan = document.createElement("span");
    dateSpan.textContent = date;

    dateSeparatorDiv.appendChild(dateSpan);
    chatBox.appendChild(dateSeparatorDiv);
}

function initializeEcho() {
    if (!window.Echo) {
        window.Echo = new Echo({
            broadcaster: "pusher",
            key: "59117f5d9a82a5680ef6",
            cluster: "eu",
            forceTLS: true,
        });
    }
}

function setupChatInput() {
    const chatInput = document.getElementById("chat-input");
    const chatForm = document.getElementById("chat-form");

    if (chatInput) {
        // Listen for input event to replace spaces with non-breaking spaces
        chatInput.addEventListener("input", function (event) {
            const value = event.target.value.replace(/ {2,}/g, (match) =>
                match.replace(/ /g, "\u00A0")
            );
            event.target.value = value;
        });

        // Handle keydown event
        chatInput.addEventListener("keydown", function (event) {
            // Allow Shift + Space to insert a new line
            if (event.key === " " && event.shiftKey) {
                event.preventDefault();
                const cursorPosition = event.target.selectionStart;
                const value = event.target.value;
                event.target.value =
                    value.slice(0, cursorPosition) +
                    "\n" +
                    value.slice(cursorPosition);
                event.target.selectionStart = cursorPosition + 1;
                event.target.selectionEnd = cursorPosition + 1;
            }

            // Handle Enter key to submit the message
            if (event.key === "Enter" && !event.shiftKey) {
                event.preventDefault(); // Prevent a new line from being added
                const messageText = chatInput.value.trim();
                if (!messageText) return;

                // Trigger form submission
                handleSendMessage(messageText);
            }
        });
    }
}
function handleSendMessage(messageText){
    const auctionId = window.location.pathname.split('/')[2];

    const chatBox = document.getElementById("chat-box");
    const chatForm = document.getElementById("chat-form");
    const chatInput = document.getElementById("chat-input");
    // Create and append the content
    chatInput.disabled = true;
    const sendButton = chatForm.querySelector("button[type='submit']");

    if (sendButton) {
        sendButton.disabled = true;
        sendButton.innerHTML = `
    <span class="loading-spinner"></span>
`;
    }

    chatInput.value = "";
    // Send the message using an AJAX request or fetch
    fetch("/send-message", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector(
                'meta[name="csrf-token"]'
            ).content,
        },
        body: JSON.stringify({
            auction_id: auctionId,
            message: messageText,
        }),
    })
        .then((response) => response.json())
        .then((data) => {
            // Extract the fields from the server response
            const { formatted_date, text, timestamp } = data.message;

            const currentMessageDate = formatted_date;
            const noMessagesDiv = chatBox.querySelector(".no-messages");
            // If chatBox has no messages, add the first date separator
            if (noMessagesDiv) {
                addDateSeparator(chatBox, currentMessageDate);
                
                chatBox.removeChild(noMessagesDiv); // Remove the "no-messages" class
            } else {
                // If chatBox already has messages, check the last message's date
                const lastMessage = chatBox.querySelector(
                    ".my-message:last-child, .other-message:last-child"
                );
                if (lastMessage) {
                    const lastMessageDate =
                        lastMessage.getAttribute("data-date");

                        const date = new Date(lastMessageDate);
                        // Format the date to "d/m/Y" (day/month/year)
                        const day = date.getDate().toString().padStart(2, '0');  // Ensure two digits for day
                        const month = (date.getMonth() + 1).toString().padStart(2, '0');  // Ensure two digits for month
                        const year = date.getFullYear();  // Get the full year

                        // Combine to get the formatted date
                        const formattedDate = `${day}/${month}/${year}`;
                    if (formattedDate !== currentMessageDate) {
                        addDateSeparator(chatBox, currentMessageDate); // Add a new date separator
                    }
                }
            }

            // Add the message to the chat box
            const messageDiv = document.createElement("div");
            messageDiv.classList.add("my-message");
            messageDiv.setAttribute("data-date", data.message.timestamp); // Add the current date as a data attribute

            // Create and append the content
            const messageContent = document.createElement("p");
            messageContent.classList.add("message-content");
            messageContent.textContent = `${text}`;
            messageDiv.appendChild(messageContent);

            // Create and append the timestamp
            const messageTime = document.createElement("p");
            messageTime.classList.add("message-time");
            messageTime.textContent = new Date(
                timestamp
            ).toLocaleTimeString([], {
                hour: "2-digit",
                minute: "2-digit",
            });
            messageDiv.appendChild(messageTime);

            // Add the message to the chat box
            chatBox.appendChild(messageDiv);

            // Scroll to the bottom of the chat box
            chatBox.scrollTop = chatBox.scrollHeight;
        })
        .finally(() => {
            chatInput.disabled = false;
            if (sendButton) {
                sendButton.disabled = false;
                sendButton.innerHTML = `
            <svg viewBox="0 0 24 24" height="24" width="24" preserveAspectRatio="xMidYMid meet" class="send-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24">
                <title>send</title>
                <path fill="currentColor" d="M1.101,21.757L23.8,12.028L1.101,2.3l0.011,7.912l13.623,1.816L1.112,13.845L1.101,21.757z"></path>
            </svg>
        `;
                chatInput.focus();
            }
        });
}

function scrollToBottom() {
    const chatBox = document.getElementById("chat-box");
    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
}

function setupLoadMoreMessages() {
    const chatContainer = document.getElementById('chat-box');
    let isLoading = false;
    let allMessagesLoaded = false;

    chatContainer.addEventListener('scroll', function () {

        if (chatContainer.scrollTop === 0 && !isLoading && !allMessagesLoaded) {
            loadMoreMessages();
        }
    });

    function loadMoreMessages() {
        let oldestMessage = chatContainer.querySelector('.my-message, .chat-other-message');
        const auctionId = window.location.pathname.split('/')[2];
        if (!oldestMessage) return;

        const lastMessageTimestamp = oldestMessage.getAttribute('data-date');
        isLoading = true;
        fetch(`/auctions/${auctionId}/loadMoreMessages`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ timestamp: lastMessageTimestamp })
        })
        .then(response => response.json())
        .then(data => {
            if (data.messages.length === 0 || !data.hasMore) {
                allMessagesLoaded = true; // Stop further loading if no messages left
            } else {
                prependMessages(data.messages);
            }
        })
        .finally(() => {
            isLoading = false; // Reset loading state
        });
    }

    function prependMessages(messages) {
        const initialScrollHeight = chatContainer.scrollHeight;
        const firstDateSeparator = document.querySelector('.date-separator span');
        let lastRenderedDate = firstDateSeparator.textContent.trim();

      
        messages.reverse().forEach(message => {
            const messageDate = message.formattedDate;
            

            // Add a date separator if it's a new day
            if (messageDate !== lastRenderedDate) {
                const dateSeparator = document.createElement('div');
                dateSeparator.classList.add('date-separator');
                dateSeparator.innerHTML = `<span>${messageDate}</span>`;
                chatContainer.prepend(dateSeparator);
                lastRenderedDate = messageDate;
            }
    
            // Render the message
            const messageElement = document.createElement('div');
            if (message.isMine) {
                messageElement.classList.add('my-message');
                messageElement.dataset.date = message.timestamp;
                messageElement.innerHTML = `
                    <p class="message-content">${message.content}</p>
                    <p class="message-time">${message.formattedTime}</p>
                `;
            } else {
                messageElement.classList.add('chat-other-message');
                messageElement.dataset.date = message.timestamp;
                messageElement.innerHTML = `
                    <div class="chat-profile-container">
                        <a href="/profile/${message.userId}" class="chat-profile-link">
                            <img src="${message.profileImage}" alt="User Image" class="chat-profile-image">
                        </a>
                        <a href="/profile/${message.userId}" class="chat-user-name-link">
                            <p class="chat-user-name">${message.username}</p>
                        </a>
                    </div>
                    <div class="chat-message-info">
                        <p class="chat-message-content">${message.content}</p>
                        <p class="chat-message-time">${message.formattedTime}</p>
                    </div>
                `;
            }
            chatContainer.prepend(messageElement);
        });

        // Maintain scroll position
        chatContainer.scrollTop = chatContainer.scrollHeight - initialScrollHeight;
    }
}

function checkAutoBidActive() {
    // Get the CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    const auctionId = window.location.pathname.split('/')[2];
    // Make a fetch request to check if the auto-bid is active
    if(document.getElementById("max-bid-text")){
        fetch(`/auctions/${auctionId}/checkAutoBidStatus`, {
            method: 'GET',  // You can use GET if you prefer, depending on your API setup
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken  // Include the CSRF token in the request header
            },
        })
        .then(response => response.json())
        .then(data => {
            if (!data.isActive) {
                document.getElementById("autoBid-container").innerHTML = "<p id='no-current-auto-bid'>No current AutoBid set</p>";
            } 
        })
    }

}

function setupLiveMoneyUpdate() {
    if (!document.querySelector('meta[name="user-id"]')) return;
    const currentUserId = document.querySelector('meta[name="user-id"]').content || null; // Assume this function returns the logged-in user's ID
    initializeEcho();
    if (!currentUserId) return; // Exit if no user is logged in

    window.Echo.private(`user-balance.${parseInt(currentUserId)}`)
        .listen('UpdateAvailableBalance', (event) => {
            updateAvailableBalance(event.availableBalance);
        });
}

// Function to update the displayed available balance
function updateAvailableBalance(newBalance) {
    const balanceElement = document.getElementById("current-available-balance-user");
    if (balanceElement) {
        balanceElement.innerHTML = 
        `Available: ${newBalance} €`;
    }
}

let notificationCount = 0;
let notificationsPage = 1;
function getNotifications() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/notifications', {
        method: 'POST',  // Use POST instead of GET
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,  // Include CSRF token
        },
        body: JSON.stringify({ page: notificationsPage }),  // Pass the page number in the body
    })
    .then(response => response.json())
    .then(data => {
        const notifications = data.notifications;
        const countNotifications = data.notificationCount;
        notificationCount = countNotifications;

        // Update the notification count
        updateNotificationCount(notificationCount);

        // Show notifications in the dropdown
        showNotificationsDropdown(notifications);
    })
    .catch(error => {
        console.error('Error fetching notifications:', error);
    });
}

function updateNotificationCount(count) {
    const notificationCountElement = document.getElementById('notification-count');

    if (count > 0) {
        notificationCountElement.textContent = count;
        notificationCountElement.style.visibility = 'visible';
    } else {
        notificationCountElement.style.visibility = 'hidden';
    }
}

function timeAgo(timestamp) {
    const now = new Date();
    const notificationTime = new Date(timestamp);
    const diffInSeconds = Math.floor((now - notificationTime) / 1000);

    if (diffInSeconds < 60) {
        return `${diffInSeconds} sec ago`;
    }

    const diffInMinutes = Math.floor(diffInSeconds / 60);
    if (diffInMinutes < 60) {
        return `${diffInMinutes} min ago`;
    }

    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) {
        return `${diffInHours} hour${diffInHours > 1 ? 's' : ''} ago`;
    }

    const diffInDays = Math.floor(diffInHours / 24);
    return `${diffInDays} day${diffInDays > 1 ? 's' : ''} ago`;
}


function showNotificationsDropdown(notifications) {

    const dropdown = document.getElementById('dropdown-notifications');
    const notificationListElement = document.getElementById('notification-list');
    const noNotificationsElement = document.getElementById('no-notifications');
    const clearAllButton = document.getElementById('clear-all');

    // Clear the previous list
    notificationListElement.innerHTML = "";
    
    if (notifications.data.length > 0) {
        setUpLazyLoadingNotification();
        notifications.data.forEach(notification => {
            
            const listItem = document.createElement('li');
            listItem.className = "py-2 px-4 border-b hover:bg-gray-100 flex justify-between items-center min-h-[3em]";

            let descriptionText;

            if (notification.path) {
                const link = document.createElement('a');
                link.href = notification.path; // Set the path as the href

                // Add neutral text color and opacity effect on hover
                link.className = "text-xs text-gray-900 hover:opacity-75 transition-opacity duration-200";

                link.onmouseover = () => {
                    link.style.textDecoration = "underline"; // Add underline
                    link.style.opacity = "0.8"; // Slight opacity effect
                };
                link.onmouseout = () => {
                    link.style.textDecoration = "none"; // Remove underline
                    link.style.opacity = "1"; // Reset opacity
                };
                link.textContent = notification.description; // Use the notification description
                link.onclick = (event) => {
                    const listItem = document.getElementById(`notification-${notification.id}`);
                    // Call function to clear the notification
                    clearNotification(notification.id,listItem);
                };

                descriptionText = link; // Assign the link to descriptionText


            } else {
                descriptionText = document.createElement('span');
                descriptionText.className = "text-xs"; // Regular text if no link
                descriptionText.textContent = notification.description;
            }

            // Create the time element for the notification
            const timeText = document.createElement('span');
            timeText.className = "text-xs text-gray-500 whitespace-nowrap mr-4";  // Smaller text with gray color
            const timeAgoText = timeAgo(notification.timestamp); // Assuming timestamp is in a standard format
            timeText.textContent = timeAgoText; // You can format this further as needed

            // Create the clear button
            const clearButton = document.createElement('button');
            clearButton.className = "text-blue-500 text-xs hover:underline"; // Style the button
            clearButton.textContent = "Clear";
            clearButton.onclick = () => {
                // Call function to clear the notification
                clearNotification(notification.id, listItem);
            };

            // Append elements to the list item
            listItem.appendChild(descriptionText);
            listItem.appendChild(timeText);
            listItem.appendChild(clearButton);
            
            notificationListElement.appendChild(listItem);
        });

        // Hide "No Notifications" message
        noNotificationsElement.classList.add('hidden');
        clearAllButton.classList.remove('hidden');

        setUpClearAllNotifications();

    } else {
        // Show "No Notifications" message
        noNotificationsElement.classList.remove('hidden');
        clearAllButton.classList.add('hidden');
    }
}

function clearNotification(notificationId, listItem) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`/notifications/${notificationId}/clear`, {
        method: 'POST', // Use POST
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken, // Laravel CSRF token
        },
    })
    .then(response => response.json())
    .then(() => {
        // Remove the notification from the list
        listItem.remove();

        notificationCount-=1;

        const notificationListElement = document.getElementById('notification-list');
        const noNotificationsElement = document.getElementById('no-notifications');
        const clearAllButton = document.getElementById('clear-all');
        const notificationCounter = document.getElementById('notification-count');

        updateNotificationCount(notificationCount);
        notificationCounter.innerHTML = `${notificationCount}`;
        
        if (notificationListElement.children.length === 0 && notificationCount > 0) {
            getNotifications();
        }
        if (notificationListElement.children.length === 0) {
            // Show "No current notifications" message
            
            noNotificationsElement.classList.remove('hidden');
            // Hide "Clear All" button
            clearAllButton.classList.add('hidden');
        }

        
    })
    .catch(error => {
        console.error('Error clearing notification:', error);
    });
}


function setUpClearAllNotifications(){
    const clearAllButton = document.getElementById('clear-all');
    clearAllButton.addEventListener('click', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/notifications/mark-all-viewed', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        }
    })
    .then(response => response.json())
    .then(() => {
        // Clear the notification list and update the UI
        const notificationListElement = document.getElementById('notification-list');
        const noNotificationsElement = document.getElementById('no-notifications');
        const clearAllButton = document.getElementById('clear-all');
        const notificationCountElement = document.getElementById('notification-count');
        notificationCount = 0;
        notificationListElement.innerHTML = ''; // Clear notifications
        noNotificationsElement.classList.remove('hidden'); // Show "No Notifications" message
        clearAllButton.classList.add('hidden'); // Hide "Clear All" button
        notificationCountElement.style.visibility = 'hidden'; // Hide count
    })
    .catch(error => {
        console.error('Error clearing notifications:', error);
    });
});
}


let isDropdownOpenNotifications = false;

function setUpGetNotifications() {
    const notificationIcon = document.querySelector('.notification-icon');
    if (!notificationIcon) return;

    notificationIcon.addEventListener('click', function () {
        toggleDropdownNotifications(); // Toggle visibility of dropdown
        if (!isDropdownOpenNotifications) {
            // Only fetch notifications if the dropdown is opening
            getNotifications();
        }
        isDropdownOpenNotifications = !isDropdownOpenNotifications;
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function (event) {
        const container = document.getElementById('notification-container');
        const dropdown = document.getElementById('dropdown-notifications');

        if (!container.contains(event.target) && isDropdownOpenNotifications) {
            dropdown.classList.remove('show');
            isDropdownOpenNotifications = false;
        }
    });
}

function toggleDropdownNotifications() {
    const dropdown = document.getElementById('dropdown-notifications');
    if (dropdown.classList.contains('show')) {
        dropdown.classList.remove('show');
        dropdown.classList.add('hide');
    } else {
        dropdown.classList.remove('hide');
        dropdown.classList.add('show');
    }
}

function getUnreadNotifications(){
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const notificationCounter = document.getElementById('notification-count');
    if (!notificationCounter) return;

    // Function to fetch unread notifications count
    fetch('/notifications/unread-count', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            }
        })
        .then(response => response.json())
        .then(data => {
            const unreadCount = data.unread_count;
            notificationCount = unreadCount;
            // Update the notification count in the UI
            updateNotificationCount(unreadCount);
        })
    .catch(error => console.error('Error fetching unread notifications count:', error)); 
}

function setUpLazyLoadingNotification() {
    const dropdown = document.getElementById('dropdown-notifications');
    const notificationListElement = document.getElementById('notification-list');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    let currentPage = notificationsPage + 1;
    let lastPage = false;  // Flag to track when no more notifications are available
    let isLoading = false; // Prevent duplicate requests

    // Function to handle scroll event and lazy loading
    const onScroll = async () => {
        if (isLoading || lastPage) return;

        const scrollPosition = dropdown.scrollTop + dropdown.clientHeight;
        const bottomPosition = dropdown.scrollHeight;

        if (scrollPosition >= bottomPosition - 50) {  // Trigger when near the bottom
            isLoading = true;

            try {
                // Use POST instead of GET
                const response = await fetch('/notifications', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,  // Include CSRF token
                    },
                    body: JSON.stringify({ page: currentPage }),  // Send the page number in the body
                });

                const data = await response.json();

                if (data.notifications.data.length > 0) {
                    data.notifications.data.forEach(notification => {
                        const listItem = createNotificationItem(notification);
                        notificationListElement.appendChild(listItem);
                    });

                    currentPage++;
                    if (currentPage > Math.ceil(notificationCount / 10)) {
                        lastPage = true;
                    }
                } else {
                    lastPage = true; // No more notifications to load
                }
            } catch (error) {
                console.error('Error loading notifications:', error);
            } finally {
                isLoading = false;
            }
        }
    };

    dropdown.addEventListener('scroll', onScroll);
}


function createNotificationItem(notification) {
    const listItem = document.createElement('li');
    listItem.className = "py-2 px-4 border-b hover:bg-gray-100 flex justify-between items-center min-h-[3em]";

    let descriptionText;

    if (notification.path) {
        const link = document.createElement('a');
        link.href = notification.path; // Set the path as the href

        // Add neutral text color and opacity effect on hover
        link.className = "text-xs text-gray-900 hover:opacity-75 transition-opacity duration-200";

        link.onmouseover = () => {
            link.style.textDecoration = "underline"; // Add underline
            link.style.opacity = "0.8"; // Slight opacity effect
        };
        link.onmouseout = () => {
            link.style.textDecoration = "none"; // Remove underline
            link.style.opacity = "1"; // Reset opacity
        };
        link.textContent = notification.description; // Use the notification description
        link.onclick = (event) => {
            const listItem = document.getElementById(`notification-${notification.id}`);
            // Call function to clear the notification
            clearNotification(notification.id, listItem);
        };

        descriptionText = link; // Assign the link to descriptionText

    } else {
        descriptionText = document.createElement('span');
        descriptionText.className = "text-xs"; // Regular text if no link
        descriptionText.textContent = notification.description;
    }

    // Create the time element for the notification
    const timeText = document.createElement('span');
    timeText.className = "text-xs text-gray-500 whitespace-nowrap mr-4";  // Smaller text with gray color
    const timeAgoText = timeAgo(notification.timestamp); // Assuming timestamp is in a standard format
    timeText.textContent = timeAgoText; // You can format this further as needed

    // Create the clear button
    const clearButton = document.createElement('button');
    clearButton.className = "text-blue-500 text-xs hover:underline"; // Style the button
    clearButton.textContent = "Clear";
    clearButton.onclick = () => {
        // Call function to clear the notification
        clearNotification(notification.id, listItem);
    };

    // Append elements to the list item
    listItem.appendChild(descriptionText);
    listItem.appendChild(timeText);
    listItem.appendChild(clearButton);

    return listItem;
}



// Run all initializations
function init() {
    if (isLoggedIn()) {
        setUpPlaceBid();
        setUpPlaceMaxBid();
        setUpCancelAutoBid();
        setInterval(checkAutoBidActive, 10000); // Check every 10 seconds
        checkAutoBidActive();
        setUpGetNotifications();
        getUnreadNotifications();
        setInterval(getUnreadNotifications, 60000); // Polling updated notification counter
        setUpToggleBidHistory();
    }
    setupAuctionChat();
    setupAuctionEcho();
    setupEuroSymbolInput();
    initializeSwiper();
    setupEuroSymbolInput();
    setUpRemoveAuction();
    updateCountdown();
    countDownId = setInterval(updateCountdown, 1000);
    setupChatInput();
    scrollToBottom();
    setupLiveMoneyUpdate();
}

document.addEventListener("DOMContentLoaded", init);
