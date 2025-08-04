document.addEventListener('DOMContentLoaded', function () {
    let questionCount = 0;
    const questionsContainer = document.getElementById('questionsContainer');
    const btnAddQuestion = document.getElementById('addQuestionBtn');

    initializeDefaultQuestions();

    btnAddQuestion.addEventListener('click', addQuestion);

    function initializeDefaultQuestions() {
        for (let i = 1; i <= 3; i++) {
            addQuestion();
        }
    }

    function addQuestion() {
        const questionId = questionCount++;
        const questionDiv = document.createElement('div');
        questionDiv.className = 'question-item border border-gray-200 rounded-lg p-4 bg-gray-50';
        questionDiv.dataset.questionId = questionId;

        questionDiv.innerHTML = `
            <div class="flex justify-between items-start mb-3">
                <h4 class="text-md font-medium text-gray-800">Pertanyaan <span class="question-number">${questionId + 1}</span></h4>
                <button type="button" class="remove-question text-red-500 hover:text-red-700">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">Section</label>
                <input type="text" name="Section[]" placeholder="Section Pertanyaan"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pertanyaan:</label>
                <input type="text" name="question[]" placeholder="Masukkan pertanyaan anda"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
        `;

        // Tambahkan ke container
        questionsContainer.appendChild(questionDiv);

        // Tambahkan event listener untuk hapus pertanyaan
        const removeBtn = questionDiv.querySelector('.remove-question');
        removeBtn.addEventListener('click', function () {
            removeQuestion(questionId);
        });

        updateQuestionNumbers();
    }

    function removeQuestion(questionId) {
        const questionDiv = document.querySelector(`[data-question-id="${questionId}"]`);
        if (questionDiv) {
            const remainingQuestions = document.querySelectorAll('.question-item').length;
            if (remainingQuestions <= 1) {
                alert('Minimal harus ada 1 pertanyaan!');
                return;
            }
            questionDiv.remove();
            updateQuestionNumbers();
        }
    }

    function updateQuestionNumbers() {
        const questionItems = document.querySelectorAll('.question-item');
        questionItems.forEach((item, index) => {
            const numberEl = item.querySelector('.question-number');
            if (numberEl) numberEl.textContent = index + 1;
        });
    }

    const btnReset = document.getElementById('btnReset');
    const form = document.getElementById('inspectionForm');

    btnReset.addEventListener('click', function () {
        const confirmed = confirm("Anda yakin ingin mereset formulir?");
        if (confirmed) {
            form.reset(); // reset semua input
            // Jika kamu ingin hapus semua pertanyaan lalu tambah default 3 lagi:
            questionsContainer.innerHTML = '';
            questionCount = 0;
            initializeDefaultQuestions();
        }
    });
});