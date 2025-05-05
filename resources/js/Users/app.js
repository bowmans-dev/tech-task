import '../bootstrap';
import '../UploadPhoto.js';
import { start } from "@hotwired/turbo";
start();

import { setupSearchModalInput } from '../SearchModal';

document.addEventListener('turbo:load', () => {

  setupSearchModalInput();
  
});