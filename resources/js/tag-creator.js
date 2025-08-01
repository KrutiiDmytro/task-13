document.addEventListener('DOMContentLoaded', function () {
    const saveTagButton = document.getElementById('saveTagButton');
    const createTagForm = document.getElementById('createTagForm');
    
    // Если на странице нет формы для создания тега, ничего не делаем
    if (!createTagForm) {
        return;
    }

    const newTagNameInput = document.getElementById('newTagName');
    const tagsContainer = document.getElementById('tags-container');
    const modalElement = document.getElementById('createTagModal');
    const modal = new bootstrap.Modal(modalElement);
    const tagNameError = document.getElementById('tagNameError');

    // Получаем URL для сохранения из data-атрибута формы
    const storeUrl = createTagForm.dataset.storeUrl;

    saveTagButton.addEventListener('click', function () {
        const formData = new FormData(createTagForm);
        
        fetch(storeUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const newTag = data.tag;

                const div = document.createElement('div');
                div.className = 'form-check';
                div.innerHTML = `
                    <input class="form-check-input" type="checkbox" name="tags[]" value="${newTag.id}" id="tag-${newTag.id}" checked>
                    <label class="form-check-label" for="tag-${newTag.id}">
                        ${newTag.name}
                    </label>
                `;
                tagsContainer.prepend(div);

                newTagNameInput.value = '';
                newTagNameInput.classList.remove('is-invalid');
                modal.hide();
            } else {
                if (data.errors && data.errors.name) {
                    newTagNameInput.classList.add('is-invalid');
                    tagNameError.textContent = data.errors.name[0];
                }
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            alert('Произошла ошибка при сохранении тега.');
        });
    });

    modalElement.addEventListener('hidden.bs.modal', function () {
        newTagNameInput.value = '';
        newTagNameInput.classList.remove('is-invalid');
    });
});