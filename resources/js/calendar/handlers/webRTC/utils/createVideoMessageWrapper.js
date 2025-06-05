export function createVideoMessageWrapper(broadcastingUserDetails, track) {
    const wrapper = document.createElement("div");
    wrapper.className = "message-wrapper w-full flex relative justify-start";

    const messageBubble = document.createElement("div");
    messageBubble.className = "message bubble text-left min-w-[200px] p-2 mb-4 rounded-2xl relative shadow-md cursor-pointer transition-all duration-200 hover:mb-10 bg-[#ffffff]";

    const header = document.createElement("div");
    header.className = "flex flex-row align-center mb-2";

    const profileImg = document.createElement("img");
    profileImg.className = "rounded-full bg-gray-50 h-8 w-8 left-1 mr-2 flex-shrink-0 object-cover";
    profileImg.src = `/storage/${broadcastingUserDetails?.profilePicture}` || "/storage/default_profile_image.webp";
    profileImg.alt = `${broadcastingUserDetails?.firstName || "User"} ${broadcastingUserDetails?.lastName || ""}'s profile picture`;

    const nameDiv = document.createElement("div");
    nameDiv.className = "flex items-center text-sm font-medium text-gray-800";
    nameDiv.textContent = `${broadcastingUserDetails?.firstName || "Unknown"} ${broadcastingUserDetails?.lastName || ""}`;

    header.appendChild(profileImg);
    header.appendChild(nameDiv);

    const video = document.createElement("video");
    video.srcObject = new MediaStream([track]);
    video.autoplay = true;
    video.playsInline = true;
    video.muted = false;
    video.className = "w-full rounded-xl mt-2";

    // Remove when the track ends or goes inactive
    track.onended = () => wrapper.remove();
    track.oninactive = () => wrapper.remove();

    messageBubble.appendChild(header);
    messageBubble.appendChild(video);
    wrapper.appendChild(messageBubble);

    // Fullscreen toggle on click
    messageBubble.addEventListener("click", async (e) => {
        // Only trigger if the user clicked outside the header (to avoid fullscreen when clicking the name/image)
        if (e.target.closest("video")) {
            if (!document.fullscreenElement) {
                await messageBubble.requestFullscreen().catch(err => {
                    console.error("Failed to enter fullscreen:", err);
                });
            } else {
                await document.exitFullscreen().catch(err => {
                    console.error("Failed to exit fullscreen:", err);
                });
            }
        }
    });

    setTimeout(() => {
        const dropZone = document.getElementById("drop-zone");
        if (dropZone) {
            dropZone.scrollTo({ top: dropZone.scrollHeight, behavior: "smooth" });
        }
    }, 1000);

    setTimeout(() => {
        const messageContainer = document.getElementById("messages");
        const messages = messageContainer.querySelectorAll(".bubble");
        const lastMessage = messages[messages.length - 1] || null;
        const messageText = lastMessage.querySelector("p");

        if (lastMessage) {
            lastMessage.classList.add("highlight-message");
            setTimeout(() => lastMessage.classList.remove("highlight-message"), 4000);
        }
        if (messageText) {
            messageText.classList.add("message-text-color");
            setTimeout(() => messageText.classList.remove("message-text-color"), 4500);
        }

    }, 1000);

    return wrapper;
}