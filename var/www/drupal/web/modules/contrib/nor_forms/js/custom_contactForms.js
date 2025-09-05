function contactUrlParams() {
  const url = new URL(window.location.href);
  const anchor = url.hash.slice(1);
  const formName = anchor.replace(/-container$/, "");
  const formId = `${formName}-container`;
  return formId;
}


document.addEventListener('DOMContentLoaded', function() {
  const formButtons = document.querySelectorAll('.form-links');
  const formContainers = document.querySelectorAll('.form-container');
  const formDisplayArea = document.getElementById('form-display-area');
                      
  formDisplayArea.classList.add('hidden');

  formButtons.forEach(button => {
    button.addEventListener('click', function() {
      const targetFormId = this.dataset.targetForm;

      formContainers.forEach(container => container.classList.add('hidden'));

      let targetContainer;
      if (targetFormId === 'help-with-orders') {
        targetContainer = document.getElementById('HelpOrder');
      } else {
        targetContainer = document.getElementById(targetFormId + '-container');
      }
      targetContainer.classList.remove('hidden');

      window.location.hash = targetFormId + '-container';

      formDisplayArea.classList.remove('hidden');
    });
  });


  const initialFormId = contactUrlParams();
  if (initialFormId) {
    const targetContainer = document.getElementById(initialFormId);
    if (targetContainer) {
      formContainers.forEach(container => container.classList.add('hidden'));
      targetContainer.classList.remove('hidden');
      formDisplayArea.classList.remove('hidden');
    }
  }
});




