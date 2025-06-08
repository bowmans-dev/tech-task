let optionCount = 0;
let livePreviewClone = null;

export function createTaskListInput(index) {
    const wrapper = document.createElement('div');
    wrapper.className = 'relative border border-dashed border-gray-300 rounded-lg p-2';

    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'w-full text-sm py-2 px-3 border rounded-md focus:outline-none focus:ring-1 focus:ring-[#2b7fff] focus:shadow-[0_0_5px_#2b7fff]';
    input.placeholder = "+ Add";
    input.dataset.index = index;

    input.addEventListener('input', handleTaskListInput);
    wrapper.appendChild(input);
    return wrapper;
}

export function handleTaskListInput() {
    const container = document.getElementById('task-options-container');
    let inputs = Array.from(container.querySelectorAll('input'));

    inputs.forEach((input, i) => {
        if (input.value.trim() === '' && i < inputs.length - 1) {
            input.parentElement.remove();
        }
    });

    inputs = Array.from(container.querySelectorAll('input'));
    const allFilled = inputs.every(input => input.value.trim() !== '');

    if (allFilled) {
        container.appendChild(createTaskListInput(optionCount++));
    }

    updateTaskListPreview();
}

export function updateTaskListPreview() {
    const questionInput = document.getElementById('modal-user-task-input');
    const container = document.getElementById('task-options-container');
    const question = questionInput.value.trim();
    const options = Array.from(container.querySelectorAll('input'))
        .map(input => input.value.trim())
        .filter(value => value !== '');

    if (!question && options.length === 0) {
        if (livePreviewClone) {
            livePreviewClone.remove();
            livePreviewClone = null;
        }
        return;
    }

    if (!livePreviewClone) {
        const template = document.getElementById('task-list-preview-template');
        livePreviewClone = template.cloneNode(true);
        livePreviewClone.id = 'task-list-preview-live';
        livePreviewClone.classList.remove('hidden');
        livePreviewClone.classList.add('flex');
        document.getElementById('messages').appendChild(livePreviewClone);

        setTimeout(() => {
            const dropZone = document.getElementById("drop-zone");
            if (dropZone) {
                dropZone.scrollTo({ top: dropZone.scrollHeight, behavior: "smooth" });
            }
        }, 1000);
    }

    const previewQuestion = livePreviewClone.querySelector('#task-list-preview-topic');
    const previewOptions = livePreviewClone.querySelector('#task-list-preview-task');
    const submitContainer = livePreviewClone.querySelector('.task-list-submit-container');

    previewQuestion.textContent = question || 'Your question will appear here';
    previewOptions.innerHTML = '';

    options.forEach(optionText => {
        const li = document.createElement('li');
        li.textContent = optionText;
        li.className = 'cursor-pointer px-2 py-1 rounded-md bg-white hover:bg-gray-200 hover:text-blue-600';
        previewOptions.appendChild(li);
    });

    submitContainer.style.opacity = options.length >= 2 ? '1' : '0';
}

export function initializeTasksFeature() {
    const container = document.getElementById('task-options-container');
    container.innerHTML = '';
    container.appendChild(createTaskListInput(0));
    container.appendChild(createTaskListInput(1));

    document.getElementById('modal-user-task-input')
        .addEventListener('input', updateTaskListPreview);
}