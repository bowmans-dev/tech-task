import { state } from "../../state";

export function submitPoll(buttonElement) {
    const livePreviewClone = document.getElementById('poll-message-preview-live');
    livePreviewClone.remove();
    
    const container = buttonElement.closest(".message");
    const eventName = document.getElementById('eventName')?.value;
    const question = container.querySelector("#poll-preview-question")?.textContent.trim();
    const optionsEls = container.querySelectorAll("#poll-preview-options li");
    const options = Array.from(optionsEls).map(el => el.textContent.trim()).filter(Boolean);

    if (!question || options.length < 2) {
        alert("Poll must have a question and at least two options.");
        return;
    }

    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute("content"));
    formData.append('content', question);
    formData.append('event_id', state.currentEvent.id);
    formData.append('event_name', eventName);
    formData.append('is_poll', true);

    options.forEach(option => {
        formData.append('options[]', option);
    });

    console.log("FORM DATA: ", formData);

    fetch("/messages", {
        method: "POST",
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(res => {
        if (!res.ok) {
            return res.json().then(err => { throw err; });
        }
        return res;
    })
    .then(data => {
        console.log("Poll submitted:", data);
        // Optionally clear the preview or give feedback
    })
    .catch(err => {
        console.error("Error submitting poll:", err);
        alert("Failed to submit poll.");
    });
}
