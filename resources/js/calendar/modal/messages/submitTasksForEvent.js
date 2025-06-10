import { state } from "../../state";

export function submitTasks(event, button) {
    event.preventDefault();
    event.stopPropagation();

    // Remove the live preview after submission
    const livePreviewClone = document.getElementById('task-list-preview-live');
    if (livePreviewClone) {
        livePreviewClone.remove();
    }

    // Get task list details
    const container = button.closest(".message");
    const eventName = document.getElementById('eventName')?.value;
    const topic = container.querySelector("#task-list-preview-topic")?.textContent.trim();
    console.log("SUBMIT TASK: TOPIC: ", topic);
    const taskEls = container.querySelectorAll("#task-list-preview-task li");
    const tasks = Array.from(taskEls).map(el => el.textContent.trim()).filter(Boolean);

    if (!topic || tasks.length < 1) {
        alert("Task list must have a topic and at least one task.");
        return;
    }

    // Construct FormData object
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute("content"));
    formData.append('content', topic);
    formData.append('event_id', state.currentEvent.id);
    console.log("SUBMIT TASK: state.currentEvent: ", state.currentEvent);
    console.log("SUBMIT TASK: state.currentEvent.id: ", state.currentEvent.id);
    formData.append('event_name', eventName);
    formData.append('is_task_list', 1);

    tasks.forEach(task => {
        formData.append('tasks[]', task);
    });

    // Send data via fetch API
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
        console.log("Task list submitted:", data);
    })
    .catch(err => {
        console.error("Error submitting task list:", err);
        alert("Failed to submit task list.");
    });
}