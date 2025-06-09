// ./dropZone/files/renderAllFiles.js

const clearContainer = (element) => {
  if (element) {
    element.innerHTML = '';
  }
};

export const renderAllFiles = (container, files) => {
  clearContainer(container);
  if (!container) return;

  files.forEach((file) => {
    if (!file || !file.file_path) {
      console.warn("Skipping invalid file:", file);
      return;
    }

    const a = document.createElement('a');
    const filePath = `/storage/${file.file_path}`;
    const fileType = file.file_path.split('.').pop().toLowerCase();
    const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(fileType);

    a.className = 'p-3 rounded bg-gray-100 flex items-center space-x-3';
    a.href = filePath;
    a.target = '_blank';

    a.innerHTML = isImage
      ? `<img class="w-12 h-12 object-cover rounded" src="${filePath}" alt="file preview">`
      : `<svg xmlns="http://www.w3.org/2000/svg" height="24px" class="w-12 h-12 text-gray-500"
            viewBox="0 -960 960 960" width="24px" fill="#6a7282">
            <path d="M320-240h320v-80H320v80Zm0-160h320v-80H320v80ZM240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h320l240 240v480q0 33-23.5 56.5T720-80H240Zm280-520v-200H240v640h480v-440H520ZM240-800v200-200 640-640Z"/>
         </svg>`;
    a.innerHTML += `
      <div class="file-text">
        <p class="text-sm font-medium text-gray-700 max-w-[200px] truncate">${file.file_name}</p>
        <p class="text-xs text-left text-gray-500">${fileType}</p>
      </div>`;

    container.appendChild(a);
  });
};