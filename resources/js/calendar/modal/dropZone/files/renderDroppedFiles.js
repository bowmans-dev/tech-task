import { state } from "../../../state";

export function renderDroppedFiles() {
    const previewContainer = document.getElementById('file-preview');
    if (!previewContainer) return;

    state.droppedFiles.forEach((file, index) => {
        if (!file || Object.keys(file).length === 0) {
            console.warn(`Skipping invalid file at index ${index}:`, file);
            return;
        }

        const fileType = file.fileType || "unknown";
        const fileName = file.fileName || "Unnamed File";
        const fileSize = file.fileSize ? `${file.fileSize} KB` : "Unknown size";
        const filePath = `/storage/events/${file.eventId}/${file.userId}/${fileName}`;

        const fileDiv = document.createElement("a");
        fileDiv.href = filePath; 
        fileDiv.className = "p-3 rounded bg-gray-100 flex items-center space-x-3";

        if (fileType.startsWith("image/")) {
            const img = document.createElement("img");
            img.className = "w-12 h-12 object-cover rounded";
            setTimeout(() => {
              img.src = filePath;
            }, 1000);
            fileDiv.appendChild(img);
        } else {
            const icon = document.createElement("div");
            icon.className = "w-12 h-12 bg-gray-300 rounded flex items-center justify-center text-gray-700 font-bold";
            icon.textContent = fileType.split("/")[1]?.toUpperCase() || "FILE";
            fileDiv.appendChild(icon);
        }

        const info = document.createElement("div");
        info.innerHTML = `
            <p class="text-sm font-medium text-gray-700 max-w-[200px] truncate">${fileName}</p>
            <p class="text-xs text-left text-gray-500">${fileType} · ${fileSize}</p>
        `;

        fileDiv.appendChild(info);
        previewContainer.appendChild(fileDiv);
    });
    state.droppedFiles = [];
}