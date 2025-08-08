import { state } from "../../local/state";

export function submitTaskCompletion(event, button) {
    event.preventDefault();
    event.stopPropagation();

    const taskId = button.dataset.taskId;
    const messageId = button.dataset.messageId;
    const eventName = document.getElementById("eventName")?.value;

    if (!taskId || !messageId) {
        alert("Invalid task selection.");
        return;
    }

    // Construct FormData object
    const formData = new FormData();
    formData.append("_token", document.querySelector('meta[name="csrf-token"]').getAttribute("content"));
    formData.append("task_id", taskId);
    formData.append("message_id", messageId);
    formData.append("event_id", state.currentEvent.id);
    formData.append("event_name", eventName);

    // Send request to mark task as completed
    fetch("/tasks/complete", {
        method: "POST",
        body: formData,
        headers: {
            "X-Requested-With": "XMLHttpRequest",
        }
    })
    .then(res => res.ok ? res.json() : res.json().then(err => { throw err; }))
    .then(data => {
        console.log("Task completed:", data);
        button.classList.add("completed-task");
    })
    .catch(err => {
        console.error("Error submitting task completion:", err);
        alert("Failed to complete task.");
    });
}