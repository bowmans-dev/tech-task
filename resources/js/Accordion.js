export function setupAccordionToggles() {
    document.addEventListener('click', function (event) {
      const toggle = event.target.closest('.hs-accordion-toggles');
      if (!toggle) return;
  
      const contentId = toggle.getAttribute('aria-controls');
      const content = document.getElementById(contentId);
      if (!content) return;
  
      if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        toggle.setAttribute('aria-expanded', 'true');
      } else {
        content.classList.add('hidden');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  
    document.addEventListener('click', function (event) {
      const button = event.target.closest('.hs-accordion-toggle');
      if (!button) return;
  
      const accordionContent = document.getElementById(button.getAttribute('aria-controls'));
      if (!accordionContent) return;
  
      if (accordionContent.classList.contains('hidden')) {
        accordionContent.classList.remove('hidden');
        button.setAttribute('aria-expanded', 'true');
      } else {
        accordionContent.classList.add('hidden');
        button.setAttribute('aria-expanded', 'false');
      }
    });
  }