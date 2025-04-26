import './bootstrap';
import './UploadPhoto.js';
import './UserGroup.js';
import { start } from "@hotwired/turbo";
start();

import { createGroup, addUserToGroup, removeUserFromGroup, deleteGroup } from './UserGroup';

window.createGroup = createGroup;
window.deleteGroup = deleteGroup;
window.addUserToGroup = addUserToGroup;
window.removeUserFromGroup = removeUserFromGroup;

import { setupAccordionToggles } from './Accordion';
import { toggleSidebar } from './Sidebar';
import { setupSearchInput } from './Search';
import { setupContextMenu, toggleContextMenu, toggleSubMenu } from './ContextMenu';

document.addEventListener('turbo:load', () => {

  setupAccordionToggles();

  setupSearchInput();

  setupContextMenu();

  window.toggleSidebar = toggleSidebar;
  window.toggleContextMenu = toggleContextMenu;
  window.toggleSubMenu = toggleSubMenu;
});


import './Calendar';

import { handleDrop } from './Calendar';

window.handleDrop = handleDrop;