import { state, addTeamMember } from '../../state.js';
import { renderTeamMembers } from '../../../modal/dropZone/teamMembers/renderTeamMembers.js';
import { renderDroppedFiles } from '../../../modal/dropZone/files/renderDroppedFiles.js';

export default function updateEvent(data) {
  const newUsers = data.teamMembers.filter(user =>
    !state.existingTeamMembers.some(existing => String(existing.userId) === String(user.userId))
  );

  if (
    newUsers.length > 0 &&
    !state.existingTeamMembers.some(user => data.teamMembers.includes(user))
  ) {
    newUsers.forEach(user => addTeamMember(user));
  }

  state.droppedFiles = data.files || state.droppedFiles;

  renderTeamMembers(
    state.currentEvent.id,
    state.existingTeamMembers
  );

  // Clear transient team members after rendering
  state.teamMembers = [];

  renderDroppedFiles();
}
