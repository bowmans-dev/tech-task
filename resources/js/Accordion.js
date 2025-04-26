export function setupAccordionToggles() {
  document.body.addEventListener('click', function (event) {
      const toggle = event.target.closest('.hs-accordion-toggles, .hs-accordion-toggle');
      if (!toggle) return;

      const contentId = toggle.getAttribute('aria-controls');
      const content = document.getElementById(contentId);
      if (!content) return;

      const isHidden = content.classList.contains('hidden');
      content.classList.toggle('hidden', !isHidden);
      toggle.setAttribute('aria-expanded', isHidden.toString());
  }, { once: false });
}