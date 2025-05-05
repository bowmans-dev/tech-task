// state.js
export const state = {
    teamMembers: [],
    droppedFiles: [],
    existingTeamMembers: [],
  };
  
  export function addTeamMember(member) {
    state.teamMembers.push(member);
  }
  
  export function removeTeamMember(userId) {
    state.teamMembers = state.teamMembers.filter(m => m.userId !== userId);
  }
  
  export function addDroppedFile(file) {
    state.droppedFiles.push(file);
  }
  
  export function removeDroppedFile(fileName) {
    state.droppedFiles = state.droppedFiles.filter(f => f.name !== fileName);
  }
  