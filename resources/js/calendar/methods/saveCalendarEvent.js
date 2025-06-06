import { state, currentUser } from "../state";

function debounce(func, delay) {
  let timeout;
  return function (...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => func(...args), delay);
  };
}

function getUserDetails(event) {

  let eventOwnerDetails = event?.extendedProps?.eventOwnerDetails ?? {};

  return {
    userId: eventOwnerDetails.eventOwnerId ?? currentUser.userId,
    profilePicture: eventOwnerDetails.profilePicture ?? currentUser.profilePicture,
    firstName: eventOwnerDetails.firstName ?? currentUser.firstName,
    lastName: eventOwnerDetails.lastName ?? currentUser.lastName,
  };
}

function getEventMetadata(event) {
  const nameInput = document.getElementById("eventName");
  const timeInput = document.getElementById("modal-event-time");

  const eventName = nameInput.value.trim() || "New Event";
  const time = timeInput.value.trim();
  const allDay = time === "";
  const date = event ? event.start.toISOString().split("T")[0] : state.currentEvent.date;
  const eventIdStr = allDay ? date : `${date}T${time}`;

  return { eventName, time, allDay, date, eventIdStr };
}

function buildFormData({
  eventName, userDetails, date, time, allDay, teamMembers, eventIdStr, id
}) {
  const formData = new FormData();

  formData.append("event_name", eventName);
  formData.append("user_id", userDetails.userId);
  formData.append("profile_picture", userDetails.profilePicture);
  formData.append("first_name", userDetails.firstName);
  formData.append("last_name", userDetails.lastName);
  formData.append("date", date);
  formData.append("time", time);
  formData.append("allDay", allDay);
  formData.append("team_members", JSON.stringify(teamMembers));

  if (id) {
    formData.append("id", id);
  }

  state.droppedFiles.forEach(file => {
    file.eventId = id;
    file.userId = String(userDetails.userId);
    const customName = `${eventIdStr}/${file.userId}/${file.name}`;
    formData.append("files[]", file, customName);
    console.log("DROPPED FILE BEFORE SAVE: ", file);
  });

  return formData;
}

export async function saveCalendarEvent() {
  const dropZone = document.getElementById("drop-zone");
  const id = state.currentEvent.id;
  const event = calendar.getEventById(id);
  const userDetails = getUserDetails(event);
  const { eventName, time, allDay, date, eventIdStr } = getEventMetadata(event);

  const formData = buildFormData({
    eventName,
    userDetails,
    date,
    time,
    allDay,
    teamMembers: state.teamMembers,
    eventIdStr,
    id
  });

  try {
    const response = await fetch("/calendar/events/save", {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (!id && data.event?.id) {
      dropZone.setAttribute("data-event-id", data.event.id);
      state.currentEvent.id = data.event.id;
      state.droppedFiles = [];
      state.teamMembers = [];

      return data.event.id;
    }

    return id;
  } catch (error) {
    console.error("❌ Error saving event:", error);
    throw error;
  }
}

function setupAutoSave() {
  document.getElementById("eventName")?.addEventListener("input", debounce(saveCalendarEvent, 1000));
  document.getElementById("modal-event-time")?.addEventListener("change", saveCalendarEvent);
}

document.addEventListener("DOMContentLoaded", setupAutoSave);
