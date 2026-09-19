const questionType = document.getElementById('question_type');
const choiceFields = document.getElementById('choice-fields');
const openQuestionNote = document.getElementById('open-question-note');

if (questionType && choiceFields && openQuestionNote) {
    const updateQuestionFields = () => {
        const isMultipleChoice = questionType.value === 'multiple_choice';

        choiceFields.hidden = !isMultipleChoice;
        choiceFields.disabled = !isMultipleChoice;
        openQuestionNote.hidden = isMultipleChoice;
    };

    questionType.addEventListener('change', updateQuestionFields);

    updateQuestionFields();
}
