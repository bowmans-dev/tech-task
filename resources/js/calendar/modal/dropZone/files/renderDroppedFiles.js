import { state } from "../../../local/state";

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
        fileDiv.target = '_blank';
        fileDiv.className = "p-3 relative rounded bg-gray-100 flex items-center space-x-3";

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

        const save = document.createElement('a');
        save.className = 'absolute right-4';
        save.href = `${filePath}`;
        save.download=`${filePath}`;
        save.innerHTML = `<svg class="transition-all duration-500 hover:fill-black hover:scale-110" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d1d1d1"><path d="m720-80 120-120-28-28-72 72v-164h-40v164l-72-72-28 28L720-80ZM480-800 243-663l237 137 237-137-237-137ZM120-321v-318q0-22 10.5-40t29.5-29l280-161q10-5 19.5-8t20.5-3q11 0 21 3t19 8l280 161q19 11 29.5 29t10.5 40v159h-80v-116L479-434 200-596v274l240 139v92L160-252q-19-11-29.5-29T120-321ZM720 0q-83 0-141.5-58.5T520-200q0-83 58.5-141.5T720-400q83 0 141.5 58.5T920-200q0 83-58.5 141.5T720 0ZM480-491Z"/></svg>`;
        fileDiv.appendChild(save);


        previewContainer.appendChild(fileDiv);
    });
    state.droppedFiles = [];
}