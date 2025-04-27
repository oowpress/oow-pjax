/* OOW PJAX Admin JavaScript
 * Initializes CodeMirror for Custom JS textareas and manages tag input fields for selectors in the admin settings.
 */
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        /* Initialize CodeMirror for textareas with class codemirror-js */
        document.querySelectorAll('textarea.codemirror-js').forEach(function(textarea) {
            CodeMirror.fromTextArea(textarea, {
                mode: 'javascript',
                theme: 'dracula',
                lineNumbers: true,
                indentUnit: 4,
                indentWithTabs: true,
                autoCloseBrackets: true,
                matchBrackets: true
            });
        });

        /* Manage tag input fields for selectors */
        document.querySelectorAll('.oow-pjax-tags-input').forEach(function(container) {
            const input = container.querySelector('.oow-pjax-tag-input');
            const hiddenInput = container.querySelector('.oow-pjax-tags-hidden');
            const tagsContainer = container.querySelector('.oow-pjax-tags-container');

            /* Update the hidden input with space-separated tags */
            function updateHiddenInput() {
                const tags = Array.from(tagsContainer.querySelectorAll('.oow-pjax-tag'))
                    .map(tag => tag.dataset.value)
                    .filter(value => value.trim() !== '');
                hiddenInput.value = tags.join(' ');
            }

            /* Add a new tag on Enter key press */
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const value = input.value.trim();
                    if (value && !tagsContainer.querySelector(`.oow-pjax-tag[data-value="${value}"]`)) {
                        const tag = document.createElement('span');
                        tag.className = 'oow-pjax-tag';
                        tag.dataset.value = value;
                        tag.innerHTML = `${value}<span class="oow-pjax-tag-remove">×</span>`;
                        tagsContainer.insertBefore(tag, input);
                        input.value = '';
                        updateHiddenInput();
                    }
                }
            });

            /* Remove a tag on click of the remove cross */
            tagsContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('oow-pjax-tag-remove')) {
                    e.target.parentElement.remove();
                    updateHiddenInput();
                }
            });
        });
    });
})();