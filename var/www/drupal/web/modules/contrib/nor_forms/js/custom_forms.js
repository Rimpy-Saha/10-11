document.addEventListener('DOMContentLoaded', function() {
  const formButtons = document.querySelectorAll('.form-button');
  const formContainers = document.querySelectorAll('.form-container');
  const formDisplayArea = document.getElementById('form-display-area');

  // Initially hide the form display area                           
  formDisplayArea.classList.add('hidden');

  formButtons.forEach(button => {
    button.addEventListener('click', function() {
      const targetFormId = this.dataset.targetForm;
      const targetContainer = document.getElementById(targetFormId + '-container');

      // Hide all form containers
      formContainers.forEach(container => container.classList.add('hidden'));

      // Show the target form container
      targetContainer.classList.remove('hidden');

      // Show the form display area
      formDisplayArea.classList.remove('hidden');
    });
  });
});