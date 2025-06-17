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
    
    a.className = 'p-3 relative rounded bg-gray-100 flex items-center space-x-3';
    a.href = filePath;
    a.target = '_blank';
    
    a.innerHTML = isImage
    ? `<img class="w-12 h-12 object-cover rounded" src="${filePath}" alt="file preview">`
    : `<svg xmlns="http://www.w3.org/2000/svg" height="24px" class="w-12 h-12 text-gray-500"
    viewBox="0 -960 960 960" width="24px" fill="#6a7282">
    <path d="M320-240h320v-80H320v80Zm0-160h320v-80H320v80ZM240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h320l240 240v480q0 33-23.5 56.5T720-80H240Zm280-520v-200H240v640h480v-440H520ZM240-800v200-200 640-640Z"/>
    </svg> 
    `;
    
    a.innerHTML += `
    <div class="file-text">
    <p class="text-sm font-medium text-gray-700 max-w-[200px] truncate">${file.file_name}</p>
    <p class="text-xs text-left text-gray-500">${fileType}</p>
    </div>
    `;

    const save = document.createElement('a');
    save.className = 'absolute right-4';
    save.href = `${filePath}`;
    save.download=`${filePath}`;
    save.innerHTML = `<svg class="transition-all duration-500 hover:fill-black hover:scale-110" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#d1d1d1"><path d="m720-80 120-120-28-28-72 72v-164h-40v164l-72-72-28 28L720-80ZM480-800 243-663l237 137 237-137-237-137ZM120-321v-318q0-22 10.5-40t29.5-29l280-161q10-5 19.5-8t20.5-3q11 0 21 3t19 8l280 161q19 11 29.5 29t10.5 40v159h-80v-116L479-434 200-596v274l240 139v92L160-252q-19-11-29.5-29T120-321ZM720 0q-83 0-141.5-58.5T520-200q0-83 58.5-141.5T720-400q83 0 141.5 58.5T920-200q0 83-58.5 141.5T720 0ZM480-491Z"/></svg>`;
    a.appendChild(save);

    container.appendChild(a);
  });
};