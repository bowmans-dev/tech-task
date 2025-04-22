import './bootstrap';
import './UploadPhoto.js';
import './UserGroup.js';
import { start } from "@hotwired/turbo";
start();

import { createGroup, addUserToGroup, removeUserFromGroup } from './UserGroup';

// resources/js/app.js

import { setupAccordionToggles } from './Accordion';
import { toggleSidebar } from './Sidebar';
import { setupSearchInput } from './Search';
import { setupContextMenu, toggleContextMenu, toggleSubMenu } from './ContextMenu';

document.addEventListener('DOMContentLoaded', () => {
  // Setup accordion toggles
  setupAccordionToggles();

  // Setup search functionality
  setupSearchInput();

  // Setup context menu functionality
  setupContextMenu();

  // Attach toggleSidebar, toggleContextMenu, and toggleSubMenu to the window for inline event handlers
  window.toggleSidebar = toggleSidebar;
  window.toggleContextMenu = toggleContextMenu;
  window.toggleSubMenu = toggleSubMenu;
});

window.createGroup = createGroup;
window.addUserToGroup = addUserToGroup;
window.removeUserFromGroup = removeUserFromGroup;
