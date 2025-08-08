// ./dropZone/teamMmebers/renderTeamMembers.js

import { removeTeamMember} from "../../../local/state";
import { removeTeamMemberFromCalendarEvent } from "./removeTeamMemberFromCalendarEvent";

const clearContainer = (element) => {
  if (element) {
    element.innerHTML = '';
  }
};

export const renderTeamMembers = (eventId, teamMembers) => {
  
  let container = document.getElementById('team-members');

  clearContainer(container);
  if (!container) return;

  const label = document.createElement('div');
  label.innerHTML = `<p>Team Members</p>`;
  container.appendChild(label);

  if (teamMembers && teamMembers.length) {
    teamMembers.forEach(({ userId, profilePicture, firstName, lastName }) => {

      if (profilePicture.startsWith('profile_pictures') || profilePicture.startsWith('default')) {
        profilePicture = `/storage/${profilePicture}`;
      }

      const userDiv = document.createElement('div');
      userDiv.className =
        'team-members relative flex items-center mb-2 mt-2 border border-gray-900/25 rounded-full p-1';
      userDiv.setAttribute('data-user-id', userId);

      userDiv.innerHTML = `
        <a href="/users/${userId}" class="cursor-pointer flex items-center">
          <img src="${profilePicture}" alt="${firstName} ${lastName}" class="w-8 h-8 rounded-full object-cover mr-2">
          <span class="text-sm font-medium text-gray-700">${firstName} ${lastName}</span>
        </a>
        <button class="remove-member-btn absolute cursor-pointer top-2 right-4 text-gray-500 hover:text-gray-700 z-50" 
                aria-label="Close Modal">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 z-50" fill="none" 
               viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      `;

      const removeBtn = userDiv.querySelector('.remove-member-btn');
      if (removeBtn) {
        removeBtn.addEventListener('click', () => {
          userDiv.remove();
          removeTeamMember(userId);
          removeTeamMemberFromCalendarEvent(eventId, userId);
        });
      }
      container.appendChild(userDiv);
    });
  }
};