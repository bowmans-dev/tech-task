let optionCount = 0;
let livePreviewClone = null;

export function createPollOptionInput(index) {
    const wrapper = document.createElement('div');
    wrapper.className = 'relative border border-dashed border-gray-300 rounded-lg p-2';

    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'w-full text-sm py-2 px-3 border rounded-md focus:outline-none focus:ring-1 focus:ring-[#2b7fff] focus:shadow-[0_0_5px_#2b7fff]';
    input.placeholder = "+ Add";
    input.dataset.index = index;

    input.addEventListener('input', handlePollOptionInput);
    wrapper.appendChild(input);
    return wrapper;
}

export function handlePollOptionInput() {
    const container = document.getElementById('poll-options-container');
    let inputs = Array.from(container.querySelectorAll('input'));

    inputs.forEach((input, i) => {
        if (input.value.trim() === '' && i < inputs.length - 1) {
            input.parentElement.remove();
        }
    });

    inputs = Array.from(container.querySelectorAll('input'));
    const allFilled = inputs.every(input => input.value.trim() !== '');

    if (allFilled) {
        container.appendChild(createPollOptionInput(optionCount++));
    }

    updatePollPreview();
}

export function updatePollPreview() {
    const questionInput = document.getElementById('modal-user-poll-input');
    const container = document.getElementById('poll-options-container');
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
        const template = document.getElementById('poll-message-preview-template');
        livePreviewClone = template.cloneNode(true);
        livePreviewClone.id = 'poll-message-preview-live';
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

    const previewQuestion = livePreviewClone.querySelector('#poll-preview-question');
    const previewOptions = livePreviewClone.querySelector('#poll-preview-options');
    const submitContainer = livePreviewClone.querySelector('.poll-submit-container');

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

export function initializePollFeature() {
    const container = document.getElementById('poll-options-container');
    container.innerHTML = '';
    container.appendChild(createPollOptionInput(0));
    container.appendChild(createPollOptionInput(1));

    document.getElementById('modal-user-poll-input')
        .addEventListener('input', updatePollPreview);
}