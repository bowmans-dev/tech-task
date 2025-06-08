export default function taskCompletedBroadcast(data) {
  const messageEl = document.getElementById(`message-${data.message_id}`);
  if (!messageEl) return;

  const bubble = messageEl.querySelector(".bubble");
  if (!bubble) return;

  // Remove existing task list block
  const taskListBlock = bubble.querySelector(".task-list-block");
  if (taskListBlock) {
    taskListBlock.remove();
  }

  // Inject updated task list HTML
  const temp = document.createElement("div");
  temp.innerHTML = data.html.trim();

  const newTaskListBlock = temp.firstElementChild;
  if (newTaskListBlock) {
    bubble.appendChild(newTaskListBlock);
  }

  console.log("Updated task list UI:", data.task_id);
}