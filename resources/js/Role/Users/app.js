import '../../bootstrap.js';
import '../../photos/uploadPhoto.js';
import { start } from "@hotwired/turbo";
start();

import { setupSearchModalInput } from '../../navigation/searchModal.js';

document.addEventListener('turbo:load', () => {

  setupSearchModalInput();
  
});