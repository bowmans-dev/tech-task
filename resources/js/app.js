import './bootstrap';
import './photos/uploadPhoto.js';
import './groups/userGroup.js';
import { start } from "@hotwired/turbo";
start();

import { createGroup, addUserToGroup, removeUserFromGroup, deleteGroup } from './groups/userGroup.js';

window.createGroup = createGroup;
window.deleteGroup = deleteGroup;
window.addUserToGroup = addUserToGroup;
window.removeUserFromGroup = removeUserFromGroup;

import { setupAccordionToggles } from './navigation/accordion.js';
import { toggleSidebar } from './navigation/sidebar.js';
import { setupSearchInput } from './navigation/search.js';
import { setupSearchModalInput } from './navigation/searchModal.js';
import { setupContextMenu, toggleContextMenu, toggleSubMenu } from './navigation/contextMenu.js';

document.addEventListener('turbo:load', () => {

  setupAccordionToggles();

  setupSearchInput();

  setupSearchModalInput();

  setupContextMenu();

  window.toggleSidebar = toggleSidebar;
  window.toggleContextMenu = toggleContextMenu;
  window.toggleSubMenu = toggleSubMenu;
});